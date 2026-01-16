# Customs CRM - Quick Reference Guide

## Common Operations

### 1. Create and Analyze a Case

```php
use App\Models\CaseModel;
use App\Services\RiskAnalysisService;

// Create case from eGate API
$case = CaseModel::create([
    'external_id' => 'CASE-2026-00145',
    'vehicle_id' => 123,
    'status' => 'new',
    'arrived_at' => now(),
    'origin_country' => 'CN',
    'destination_country' => 'LV',
    'declared_value' => 25000.00,
    'route' => 'CN -> DE -> LV',
]);

// Add cargo items
$case->cargoItems()->create([
    'hs_code' => '6204291000', // Women's jackets
    'description' => 'Textile jackets',
    'weight' => 500, // kg
    'value' => 25000.00,
]);

// Perform risk analysis
$analysis = $case->performRiskAnalysis();
// Result: ['risk_score' => 45, 'risk_level' => 'medium', 'should_inspect' => true, ...]

// Move to screening
$case->transitionTo('screening', 'Initial screening started');
```

### 2. Create Inspection and Record Decision

```php
use App\Models\Inspection;

// Create inspection
$inspection = $case->inspections()->create([
    'type' => 'document', // or 'RTG', 'physical'
    'status' => 'pending',
]);

// Inspector reviews documents
// ...

// Record decision - automatically updates case status
$inspection->recordDecision('release', 'All documents verified and compliant');
// Case now: released
```

### 3. Upload Document with Signed URL

```php
use App\Services\FileUploadService;

// Create document record
$document = $case->documents()->create([
    'type' => 'invoice',
    'uploaded_at' => now(),
]);

// Upload file
$documentFile = FileUploadService::storeDocument(request()->file('document'), $document->id);

// Generate signed URL for download
$downloadUrl = FileUploadService::getSignedUrl($documentFile);
// https://customs-crm.test/documents/download/abc123xyz...
```

### 4. View Case History and Audit Trail

```php
// Case event history (status changes, inspections, documents)
$events = $case->getEventHistory();
foreach ($events as $event) {
    echo "{$event->event_type}: {$event->description}";
    // status_changed: Moved from screening to in_inspection
    // document_added: Invoice uploaded
}

// Audit log (who changed what and when)
use App\Services\AuditLogService;

$logs = AuditLogService::getLogs('CaseModel', $case->id);
foreach ($logs as $log) {
    echo "{$log->user->name} updated case:";
    echo "Status: {$log->changes['old']['status']} → {$log->changes['new']['status']}";
    echo "IP: {$log->ip_address}, {$log->created_at}";
}
```

### 5. Handle High-Risk Cases

```php
// Check if inspection needed
if ($case->risk_score >= 60) {
    // Create inspection task
    $case->inspections()->create([
        'type' => 'physical',
        'status' => 'pending',
    ]);
    
    $case->transitionTo('in_inspection', 'High-risk case (score: 65)');
}

// View risk reasons
echo $case->risk_reason;
// "High-risk commodity (HS: 6204291000); High-value shipment (€25000.00)"
```

### 6. Manage Webhooks

```php
use App\Models\Webhook;

// Register webhook
Webhook::create([
    'url' => 'https://broker-system.example.com/webhooks/customs',
    'event' => 'case.status_changed',
    'secret' => str()->random(32), // HMAC key
    'active' => true,
]);

// Check webhook delivery history
$webhook = Webhook::find(1);
$logs = $webhook->logs()->latest()->limit(10)->get();

foreach ($logs as $log) {
    echo $log->success ? '✓' : '✗';
    echo " {$log->event} - HTTP {$log->status_code}";
}
```

### 7. Data Validation Examples

```php
use App\Rules\HSCodeRule;
use App\Rules\ISOCountryCodeRule;
use App\Rules\CurrencyFormatRule;

$validated = request()->validate([
    'hs_code' => ['required', new HSCodeRule()], // Must be 10 digits
    'origin_country' => ['required', new ISOCountryCodeRule()], // ISO alpha-2
    'declared_value' => ['required', new CurrencyFormatRule()], // Positive decimal
    'arrived_at' => ['required', 'date_format:Y-m-d\TH:i:sZ'], // ISO8601 UTC
]);
```

### 8. Query Cases by Risk Level

```php
// High-risk cases needing inspection
$highRiskCases = CaseModel::where('risk_score', '>=', 100)
    ->where('status', '!=', 'closed')
    ->with('vehicle', 'parties', 'cargoItems')
    ->get();

// Pending inspections
$pendingCases = CaseModel::where('status', 'in_inspection')
    ->whereHas('inspections', function ($q) {
        $q->where('status', 'pending');
    })
    ->get();

// Cases by origin country
$riskyCases = CaseModel::where('origin_country', 'IR')
    ->orWhere('origin_country', 'SY')
    ->get();
```

### 9. Generate Compliance Report

```php
// All changes to case for audit trail
$auditTrail = AuditLogService::getLogs('CaseModel', $caseId);

// All events for case narrative
$eventHistory = $case->getEventHistory();

// Document audit trail
$documentAudit = AuditLogService::getLogs('Document', $documentId);

// Export for compliance
$report = [
    'case' => $case,
    'events' => $eventHistory,
    'audit_log' => $auditTrail,
    'inspections' => $case->inspections,
    'documents' => $case->documents,
    'generated_at' => now(),
    'generated_by' => auth()->user()->name,
];
```

### 10. Monitor System Health

```php
// Check webhook delivery rate
$successRate = \App\Models\WebhookLog::where('success', true)->count() / 
              \App\Models\WebhookLog::count() * 100;
echo "Webhook delivery success: {$successRate}%";

// Average case processing time
$avgTime = \App\Models\CaseEvent::where('event_type', 'status_changed')
    ->whereIn('data->new_status', ['released', 'rejected'])
    ->avg(\DB::raw('EXTRACT(EPOCH FROM (created_at - created_at))'));

// Cases per status
$statusCount = CaseModel::groupBy('status')->selectRaw('status, COUNT(*) as count')->get();

// Recent audit activity
$recentAudit = AuditLogService::getLogs(null, null, 100)->groupBy('model_type');
```

## Role Permissions Reference

### Inspector Permissions
- view vehicles, parties, cases, inspections, documents
- update cases
- perform inspections

### Analyst Permissions
- view vehicles, parties, cases, inspections, documents
- view analytics

### Broker Permissions
- view cases, documents
- create cases
- submit declarations

### Admin Permissions
- All permissions
- manage users
- Full system access

## API Endpoints (Add to Routes)

```php
// Case management
Route::post('/cases', 'CaseController@store');
Route::post('/cases/{case}/analyze', 'CaseController@analyze');
Route::post('/cases/{case}/transition', 'CaseController@transition');

// Inspections
Route::post('/inspections', 'InspectionController@store');
Route::post('/inspections/{inspection}/decide', 'InspectionController@recordDecision');

// Documents
Route::post('/documents/{document}/upload', 'DocumentController@upload');
Route::get('/documents/download/{token}', 'DocumentController@download');

// Webhooks
Route::post('/admin/webhooks', 'WebhookController@store');
Route::get('/admin/webhooks/{webhook}/logs', 'WebhookController@logs');

// Audit & History
Route::get('/cases/{case}/history', 'CaseController@history');
Route::get('/cases/{case}/audit', 'CaseController@audit');
```

## Troubleshooting

**Q: Why didn't the risk analysis trigger?**
A: Ensure cargo items are added before `performRiskAnalysis()`. Empty cargo means no score.

**Q: Webhook not being sent?**
A: Check `webhooks` table - ensure `active = true` and event matches. Review `webhook_logs` for errors.

**Q: Status transition failed?**
A: Check `VALID_TRANSITIONS` in CaseModel. Only valid paths allowed (e.g., can't go from 'closed' anywhere).

**Q: File download returning 404?**
A: Signed URL may have expired (24h). Call `FileUploadService::getSignedUrl()` to regenerate.

**Q: Audit log not recording changes?**
A: Ensure user is authenticated. Audit logs require `Auth::check()` to be true.
