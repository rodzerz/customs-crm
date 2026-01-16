# Configuration Guide

## Environment Variables

Add these to your `.env` file:

```env
# File Storage
FILESYSTEM_DISK=local
FILESYSTEM_PRIVATE_DISK=private

# Risk Analysis Settings
RISK_ANALYSIS_ENABLED=true
RISK_MEDIUM_THRESHOLD=30
RISK_HIGH_THRESHOLD=100

# Webhook Settings
WEBHOOK_TIMEOUT=30
WEBHOOK_MAX_RETRIES=3
WEBHOOK_RETRY_BACKOFF=60

# Signed URLs
SIGNED_URL_EXPIRATION_HOURS=24
SIGNED_URL_REGENERATE_BEFORE_EXPIRY_HOURS=1

# Audit Logging
AUDIT_ENABLED=true
AUDIT_EXCLUDE_FIELDS=password,token,secret

# Case Settings
CASE_AUTO_TRANSITION_SCREENING=false
CASE_DEFAULT_STATUS=new
CASE_INSPECTION_THRESHOLD=60
```

## Storage Configuration

### Setup Private Disk

In `config/filesystems.php`:

```php
'private' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
    'url' => '/documents/download',
    'visibility' => 'private',
],
```

### Create Storage Directory

```bash
mkdir -p storage/app/private
chmod 755 storage/app/private
```

## Database Indexes

Migrations automatically create indexes on:
- `audit_logs.created_at`
- `audit_logs.user_id`
- `case_events.case_id`
- `case_events.created_at`
- `webhooks.event`
- `webhook_logs.webhook_id`
- `document_files.signed_url_token`

For better performance, consider adding:

```sql
CREATE INDEX idx_cases_status ON cases(status);
CREATE INDEX idx_cases_risk_score ON cases(risk_score);
CREATE INDEX idx_cases_origin_country ON cases(origin_country);
CREATE INDEX idx_case_events_event_type ON case_events(event_type);
```

## High-Risk HS Codes Configuration

Edit `app/Services/RiskAnalysisService.php`:

```php
const HIGH_RISK_HS_CODES = [
    '2710',  // Mineral oils
    '8703',  // Motor vehicles
    '2709',  // Crude oil
    '2401',  // Tobacco
    '6204',  // Women's clothing
    '6203',  // Men's clothing
];
```

## High-Risk Countries Configuration

Edit `app/Services/RiskAnalysisService.php`:

```php
const HIGH_RISK_COUNTRIES = [
    'IR', // Iran
    'SY', // Syria
    'KP', // North Korea
    'CU', // Cuba
];
```

## Webhook Configuration

### Example Webhook Setup

```php
use App\Models\Webhook;
use Illuminate\Support\Str;

// Register webhook for case status changes
Webhook::create([
    'url' => env('EXTERNAL_SYSTEM_WEBHOOK_URL'),
    'event' => 'case.status_changed',
    'secret' => Str::random(32),
    'active' => true,
]);

// Multiple events to same endpoint
$events = ['case.created', 'case.status_changed', 'inspection.completed'];
foreach ($events as $event) {
    Webhook::updateOrCreate(
        ['event' => $event, 'url' => 'https://broker.example.com/webhooks'],
        ['secret' => Str::random(32), 'active' => true]
    );
}
```

### Webhook Signature Verification (External System)

```php
// In your external system receiving webhooks

function verifyWebhookSignature($payload, $signature, $secret) {
    $hash = hash_hmac('sha256', $payload, $secret);
    $expected = 'v1=' . $hash;
    
    // Use constant-time comparison to prevent timing attacks
    return hash_equals($signature, $expected);
}

// Example handler
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
$event = $_SERVER['HTTP_X_EVENT'] ?? '';

if (verifyWebhookSignature($payload, $signature, $webhook_secret)) {
    $data = json_decode($payload, true);
    handleWebhookEvent($event, $data);
} else {
    http_response_code(401);
    die('Signature verification failed');
}
```

## Role Configuration

### Edit Permissions in RoleSeeder

```php
$inspectorRole->syncPermissions([
    'view vehicles',
    'view parties',
    'view cases',
    'view inspections',
    'view documents',
    'update cases',
    'perform inspections',
    'create inspections', // Add if needed
]);
```

### Dynamic Permission Assignment

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::findByName('inspector');
$permission = Permission::findByName('view vehicles');
$role->givePermissionTo($permission);
```

## Audit Log Retention

### Clean Old Audit Logs

Create a scheduled command:

```php
// app/Console/Commands/PurgeAuditLogs.php
protected function handle()
{
    $daysToKeep = config('app.audit_log_retention_days', 90);
    
    AuditLog::where('created_at', '<', now()->subDays($daysToKeep))
        ->delete();
}
```

Schedule in `app/Console/Kernel.php`:

```php
$schedule->command('app:purge-audit-logs')->daily();
```

## Webhook Retry Strategy

### Configure Retry Logic

Create a job:

```php
// app/Jobs/RetryFailedWebhook.php
public function handle()
{
    $failedLogs = WebhookLog::where('success', false)
        ->where('created_at', '>', now()->subHours(24))
        ->get();

    foreach ($failedLogs as $log) {
        WebhookService::send(
            $log->webhook,
            $log->event,
            $log->payload
        );
    }
}
```

## Performance Optimization

### Add Query Optimizations

```php
// When loading cases with relationships
$cases = CaseModel::with([
    'vehicle',
    'parties',
    'cargoItems',
    'inspections',
    'documents.files',
    'events'
])->paginate(50);
```

### Cache Risk Analysis Results

```php
$analysis = cache()->remember(
    "case_analysis_{$case->id}",
    now()->addHours(2),
    function () use ($case) {
        return $case->performRiskAnalysis();
    }
);
```

### Archive Old Cases

```php
// Move closed cases older than 1 year to archive
CaseModel::where('status', 'closed')
    ->where('updated_at', '<', now()->subYear())
    ->update(['archived' => true]);
```

## Security Hardening

### Rate Limiting for Webhooks

```php
// In routes/api.php
Route::post('/webhooks/receive', 'WebhookController@receive')
    ->middleware('throttle:60,1'); // 60 requests per minute
```

### IP Whitelisting for Webhooks

```php
// Middleware: AllowWhitelistedIPs
if (!in_array(request()->ip(), config('webhooks.allowed_ips', []))) {
    abort(403);
}
```

### Encrypt Sensitive Data

```php
// In model
use Illuminate\Database\Eloquent\Casts\Encrypted;

protected $casts = [
    'risk_reason' => Encrypted::class,
];
```

## Monitoring & Metrics

### Dashboard Queries

```php
// Cases by status
$byStatus = CaseModel::selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->pluck('count', 'status');

// Average risk score
$avgRisk = CaseModel::avg('risk_score');

// Inspection conversion rate
$inspectionRate = Inspection::count() / CaseModel::count();

// Document upload volume
$docVolume = Document::whereBetween('uploaded_at', [
    now()->subDays(7),
    now()
])->count();

// Webhook success rate
$webhookSuccess = WebhookLog::where('success', true)->count() / 
                  WebhookLog::count() * 100;
```

## Backup Strategy

### Include Custom Data

```bash
# Backup database
php artisan backup:run

# Backup storage including private documents
tar -czf backup_documents.tar.gz storage/app/private/

# Backup webhooks configuration
mysqldump -u user -p database webhooks > webhooks_backup.sql
```

## Testing Configuration

### Setup Test Database

```php
// phpunit.xml
<env name="DB_DATABASE" value="customs_crm_test"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_CONNECTION_SQLITE_DATABASE" value=":memory:"/>
```

### Test Risk Analysis

```php
// tests/Feature/RiskAnalysisTest.php
public function test_high_value_shipment_increases_risk()
{
    $case = CaseModel::factory()->create([
        'declared_value' => 500000,
    ]);
    
    $result = $case->performRiskAnalysis();
    
    $this->assertGreaterThan(50, $result['risk_score']);
}
```

## Deployment Checklist

- [ ] Run migrations: `php artisan migrate --force`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Optimize: `php artisan optimize`
- [ ] Create storage symlink: `php artisan storage:link`
- [ ] Seed roles: `php artisan db:seed --class=RoleSeeder`
- [ ] Set file permissions: `chmod -R 755 storage bootstrap/cache`
- [ ] Configure webhooks in production
- [ ] Set up audit log retention job
- [ ] Test webhook delivery with monitoring
- [ ] Verify audit logging with test user action

## Troubleshooting

**Q: Migrations failing?**
A: Check database connection in `.env`, ensure database exists, run `php artisan migrate:fresh` for fresh install.

**Q: File uploads not working?**
A: Verify `storage/app/private` exists and is writable. Check `FILESYSTEM_PRIVATE_DISK` in `.env`.

**Q: Webhooks not firing?**
A: Ensure webhooks are `active = true`. Check firewall allows outbound HTTPS. Review `webhook_logs` for errors.

**Q: Risk analysis not triggering?**
A: Verify `RISK_ANALYSIS_ENABLED=true` in `.env`. Ensure cargo items exist before analyzing.

**Q: Audit logs growing too fast?**
A: Implement retention policy. Consider sampling audit logs for high-volume operations.
