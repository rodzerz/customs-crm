# Customs CRM System Documentation

## Overview
A comprehensive customs case management and risk analysis system for border control operations. The system manages vehicles, cargo, inspections, and documents while enforcing role-based access control and automated risk scoring.

## System Architecture

### 1. Role-Based Access Control

**Four Primary Roles:**

- **Admin** - System administration, user management, configuration
- **Inspector** - Performs inspections, makes release/hold/reject decisions
- **Analyst** - Performs risk analysis and monitoring
- **Broker** - Submits declarations and documents

Each role has specific permissions defined in `RoleSeeder.php`.

### 2. Case Status Flow

Cases progress through a defined workflow:

```
new → screening → in_inspection → {on_hold | released} → closed
                                      ↓
                                   rejected → closed
```

Status transitions are enforced by the `CaseModel::canTransitionTo()` method.

### 3. Risk Analysis Engine

**Automated Risk Scoring (0-100 points):**

- **HS Code Analysis** (30 points max): High-risk commodities trigger alerts
- **Route Analysis** (20 points max): Complex routes increase risk
- **Value Analysis** (25 points max): High-value and discrepant shipments
- **Origin Country Analysis** (15 points max): High-risk country detection
- **Violation History** (10 points max): Previous violations multiplier

**Risk Levels:**
- Low: 0-29 points
- Medium: 30-99 points (triggers inspection)
- High: 100+ points (mandates inspection)

**Usage:**
```php
$caseModel->performRiskAnalysis(); // Analyzes and updates case
```

### 4. Inspection Types & Decisions

**Inspection Types:**
- `document` - Document review
- `RTG` - X-ray examination
- `physical` - Physical cargo inspection

**Decisions:**
- `release` - Cargo cleared for passage
- `hold` - Cargo held for further review
- `reject` - Cargo rejected/seized

**Decisions automatically update case status:**
```php
$inspection->recordDecision('release', 'All documents verified');
// Case transitions to: released
```

### 5. Audit Logging System

Every change is tracked with:
- User ID and action type
- Model type and ID
- Old and new values
- IP address and user agent
- Timestamp

**Access Audit Logs:**
```php
use App\Services\AuditLogService;

$logs = AuditLogService::getLogs('CaseModel', $caseId);
```

### 6. Event History Tracking

Case events track all significant changes:
- Status changes
- Inspections added/completed
- Documents uploaded
- Custom business events

**Event Types:**
- `status_changed` - Status transition
- `inspection_added` - New inspection created
- `document_added` - Document uploaded
- Custom types as needed

**Access Event History:**
```php
$history = $caseModel->getEventHistory(); // Ordered by creation time
```

### 7. Secure File Upload System

**Features:**
- Signed URLs valid for 24 hours (configurable)
- Automatic token generation
- Download tracking
- Storage isolation

**Upload a Document:**
```php
use App\Services\FileUploadService;

$documentFile = FileUploadService::storeDocument($uploadedFile, $documentId);
$signedUrl = FileUploadService::getSignedUrl($documentFile);
```

**Download with Tracking:**
```php
$documentFile = FileUploadService::getFileByToken($token);
if ($documentFile) {
    return FileUploadService::downloadFile($documentFile);
}
```

### 8. Webhook Notification System

**Features:**
- Event-based notifications
- HMAC-SHA256 signature verification
- Retry tracking
- Comprehensive logging

**Webhook Events:**
- `case.created` - New case added
- `case.status_changed` - Status transition
- `case.inspection_completed` - Inspection decided
- `document.uploaded` - Document added

**Webhook Payload Example:**
```json
{
  "case_id": 1,
  "external_id": "CASE-2026-001",
  "status": "in_inspection",
  "data": {
    "old_status": "screening",
    "new_status": "in_inspection"
  }
}
```

**Header Signature:**
```
X-Signature: v1=<sha256_hmac_hash>
X-Event: case.status_changed
```

**Webhook Registration:**
```php
Webhook::create([
    'url' => 'https://external-system.com/webhooks/receive',
    'event' => 'case.status_changed',
    'secret' => Str::random(32),
    'active' => true,
]);
```

### 9. Data Validation Rules

**Built-in Validation:**

- **HS Code**: Exactly 10 digits (e.g., 8703221000)
  ```php
  'hs_code' => [new HSCodeRule()]
  ```

- **ISO Country Code**: Valid ISO 3166-1 alpha-2 (e.g., LV, DE, US)
  ```php
  'origin_country' => [new ISOCountryCodeRule()]
  ```

- **Currency Format**: Positive numbers with up to 2 decimals
  ```php
  'declared_value' => [new CurrencyFormatRule()]
  ```

- **ISO8601 Dates**: All dates stored in UTC
  ```php
  $case->arrived_at; // DateTime in UTC
  ```

## Database Schema

### Cases Table
- `id`, `external_id` (unique identifier from eGate)
- `vehicle_id` (FK)
- `status` (new, screening, in_inspection, on_hold, released, rejected, closed)
- `risk_score` (0-100)
- `route`, `origin_country`, `destination_country`
- `declared_value`, `actual_value`
- `previous_violations`
- `risk_reason` (text of analysis)
- `arrived_at`, `status_updated_at`

### Related Tables
- `case_parties` - Companies involved (declarant, consignee, etc.)
- `case_cargo_items` - Cargo lines (HS code, weight, value)
- `inspections` - Inspection records
- `documents` - Document metadata
- `document_files` - File records with signed URLs
- `case_events` - Event history
- `audit_logs` - Audit trail
- `webhooks` - Webhook configurations
- `webhook_logs` - Webhook delivery history

## API Examples

### Create a Case with Risk Analysis
```php
$case = CaseModel::create([
    'external_id' => 'CASE-2026-001',
    'vehicle_id' => 1,
    'status' => 'new',
    'origin_country' => 'CN',
    'destination_country' => 'LV',
    'declared_value' => 50000,
]);

$riskAnalysis = $case->performRiskAnalysis();
// Returns: ['risk_score' => 65, 'risk_level' => 'medium', 'should_inspect' => true, 'reasons' => [...]]
```

### Add Cargo Item
```php
$case->cargoItems()->create([
    'hs_code' => '8703221000', // Motor vehicles
    'description' => 'Used car',
    'weight' => 1500,
    'value' => 8000,
]);
```

### Create Inspection
```php
$inspection = $case->inspections()->create([
    'type' => 'document',
    'status' => 'pending',
]);

// Later, record decision
$inspection->recordDecision('release', 'All documents verified');
```

### Transition Case Status
```php
$case->transitionTo('in_inspection', 'Risk score 65 - medium risk');
// Logs event and triggers webhooks
```

### Track Audit Changes
```php
$logs = AuditLogService::getLogs('CaseModel', $caseId);
foreach ($logs as $log) {
    echo "{$log->user->name} {$log->action}ed at {$log->created_at}";
    // Shows old and new values in $log->changes
}
```

## Configuration

**Edit `.env` for:**
```
WEBHOOK_RETRY_LIMIT=3
SIGNED_URL_EXPIRATION_HOURS=24
RISK_ANALYSIS_ENABLED=true
```

## Security Considerations

1. **Audit Logging** - All changes tracked with IP and user agent
2. **Signed URLs** - Files expire after 24 hours
3. **HMAC Verification** - Webhooks signed with shared secret
4. **Role-Based Access** - Permission middleware enforces authorization
5. **Data Validation** - HS codes, countries, dates, currencies validated

## Maintenance

### Regular Tasks
- Review webhook logs for delivery failures
- Archive old audit logs quarterly
- Monitor case event history for unusual patterns
- Validate file storage for expired signed URLs

### Monitoring
- Webhook delivery rate: `WebhookLog` table
- Risk analysis accuracy: Compare `risk_score` against inspection results
- Case throughput: Query `case_events` table
- Audit trail: `audit_logs` for compliance reports
