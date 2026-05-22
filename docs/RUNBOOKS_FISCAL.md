# Fiscal Documents Runbook

## Overview

This operational runbook provides step-by-step procedures for managing, troubleshooting, and monitoring the Fiscal Documents system (CT-e/MDF-e) in Thiga TMS production environment.

**System:** Thiga TMS - Fiscal Documents Module  
**Version:** 1.0  
**Last Updated:** May 22, 2026  
**On-Call Team:** Infrastructure & Platform Team

---

## Table of Contents

1. [Normal Operations](#normal-operations)
2. [Monitoring & Health Checks](#monitoring--health-checks)
3. [Common Tasks](#common-tasks)
4. [Troubleshooting Guide](#troubleshooting-guide)
5. [Emergency Procedures](#emergency-procedures)
6. [Performance Tuning](#performance-tuning)
7. [Data Management](#data-management)

---

## Normal Operations

### Daily Sync Process

**Purpose:** Synchronize new/updated fiscal documents from Mitt API

**Frequency:** Automatic (via scheduled job) or manual trigger

**Scheduled Job Setup:**

In `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Run every 6 hours
    $schedule->command('fiscal:sync-mitt')
        ->everyTwelveHours()
        ->withoutOverlapping()
        ->onFailure(function () {
            // Log to monitoring system
            \Log::error('fiscal:sync-mitt failed');
        })
        ->onSuccess(function () {
            \Log::info('fiscal:sync-mitt succeeded');
        });
}
```

### Manual Sync

**When to use:** Emergency sync, single-tenant sync, or testing

**Command:**
```bash
# Sync all tenants
php artisan fiscal:sync-mitt

# Sync single tenant
php artisan fiscal:sync-mitt --tenant-id=5

# Preview changes without saving
php artisan fiscal:sync-mitt --dry-run

# Limit to 100 documents
php artisan fiscal:sync-mitt --limit=100
```

**Expected Output:**
```
Syncing fiscal documents...
[Tenant: 1] CT-es: 25 created, 3 updated, 1 failed
[Tenant: 2] CT-es: 12 created, 0 updated, 0 failed
[Tenant: 3] MDF-es: 5 created, 0 updated, 0 failed
---
Summary: 42 documents synced, 1 failure
Sync completed successfully. Exit code: 0
```

**Failure Handling:**
- Exit code non-zero indicates failure
- Check logs: `storage/logs/laravel.log`
- Retry failed documents manually
- Alert on-call engineer if repeated failures

---

## Monitoring & Health Checks

### Key Metrics to Monitor

1. **Sync Health**
   - Successful syncs per day
   - Failed syncs per day
   - Documents pending > 12 hours
   - Documents in error state

2. **API Health**
   - Mitt API response time
   - Sefaz authorization latency
   - API error rate (target: < 0.1%)

3. **Database Health**
   - `fiscal_documents` table row count
   - Query performance (index usage)
   - Lock contention
   - Disk space usage

### Monitoring Queries

**Check recent sync status:**
```sql
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'authorized' THEN 1 ELSE 0 END) as authorized,
    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors
FROM fiscal_documents
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY DATE(created_at);
```

**Find documents stuck in pending state:**
```sql
SELECT id, document_type, access_key, created_at, status
FROM fiscal_documents
WHERE status = 'pending'
  AND created_at < DATE_SUB(NOW(), INTERVAL 12 HOUR)
  AND tenant_id = ?
ORDER BY created_at ASC;
```

**Check for duplicate access keys:**
```sql
SELECT access_key, COUNT(*) as count
FROM fiscal_documents
GROUP BY access_key
HAVING COUNT(*) > 1;
```

**Document distribution by status:**
```sql
SELECT 
    document_type,
    status,
    COUNT(*) as count,
    DATE(created_at) as date
FROM fiscal_documents
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY document_type, status, DATE(created_at)
ORDER BY date DESC, document_type, status;
```

### Health Check Script

Create `scripts/fiscal-health-check.sh`:
```bash
#!/bin/bash

echo "=== Fiscal Documents Health Check ==="
echo "Time: $(date)"
echo ""

# Check database connectivity
echo "1. Database Connectivity:"
php artisan tinker --execute="echo DB::connection()->getPdo() ? 'OK' : 'FAIL';"

# Check recent syncs
echo ""
echo "2. Recent Sync Status:"
php artisan tinker --execute="
\$past24h = \App\Models\FiscalDocument::where('created_at', '>=', now()->subHours(24))->count();
\$pending = \App\Models\FiscalDocument::where('status', 'pending')->count();
\$errors = \App\Models\FiscalDocument::where('status', 'error')->count();
echo \"Documents created (24h): \$past24h\n\";
echo \"Pending: \$pending\n\";
echo \"Errors: \$errors\n\";
"

# Check Mitt API connectivity
echo ""
echo "3. Mitt API Connectivity:"
php artisan tinker --execute="
try {
    \$service = new \App\Services\MittService();
    \$service->ping();
    echo \"Mitt API: OK\n\";
} catch (\Exception \$e) {
    echo \"Mitt API: FAIL - \" . \$e->getMessage() . \"\n\";
}
"

echo ""
echo "=== Health Check Complete ==="
```

**Run regularly:**
```bash
chmod +x scripts/fiscal-health-check.sh
scripts/fiscal-health-check.sh

# Run every hour via cron
0 * * * * /path/to/thiga/scripts/fiscal-health-check.sh >> /var/log/fiscal-health.log 2>&1
```

---

## Common Tasks

### Task 1: Manually Fix Document Status

**Scenario:** Document stuck in "validating" state for 24 hours

**Steps:**

1. Verify document exists:
```bash
php artisan tinker
>>> $doc = \App\Models\FiscalDocument::find(123);
>>> dd($doc);
```

2. Check current status:
```php
>>> $doc->status
=> "validating"

>>> $doc->created_at
=> Carbon\Carbon @1716374400
```

3. Query Mitt API directly:
```php
>>> $service = new \App\Services\MittService();
>>> $response = $service->getDocumentStatus($doc->access_key);
>>> dd($response);
```

4. Update status based on Mitt response:
```php
>>> $doc->update(['status' => 'authorized', 'authorized_at' => now()]);
>>> $doc->refresh();
```

5. Verify update:
```php
>>> $doc->status
=> "authorized"
```

**Exit tinker:**
```php
>>> exit
```

### Task 2: Retry Failed Sync

**Scenario:** Sync command failed for specific tenant

**Steps:**

1. Check failure reason:
```bash
tail -100 storage/logs/laravel.log | grep "fiscal:sync-mitt"
```

2. Get tenant ID:
```bash
php artisan tinker
>>> \App\Models\Tenant::where('company_name', 'like', '%CompanyName%')->value('id');
=> 5
```

3. Retry sync:
```bash
php artisan fiscal:sync-mitt --tenant-id=5 --force
```

4. Monitor output for errors
5. If still failing, check Mitt API:
```bash
php artisan tinker
>>> $service = new \App\Services\MittService();
>>> $service->testConnection();
```

### Task 3: Generate Missing PDF/XML

**Scenario:** Document authorized but PDF/XML not generated

**Steps:**

1. Find affected documents:
```sql
SELECT id, access_key, status 
FROM fiscal_documents 
WHERE status = 'authorized' 
  AND (pdf_content IS NULL OR xml_content IS NULL)
LIMIT 10;
```

2. Regenerate via command:
```bash
php artisan fiscal:generate-documents --start-id=100 --end-id=150
```

3. Verify regeneration:
```bash
php artisan tinker
>>> $doc = \App\Models\FiscalDocument::find(100);
>>> $doc->pdf_content ? 'OK' : 'FAIL';
```

### Task 4: Bulk Update Document Status

**Scenario:** Mass update needed (e.g., all "error" → "pending" after Mitt issue resolved)

**Steps:**

1. **Backup database first:**
```bash
mysqldump -u user -p database > backup-$(date +%Y%m%d-%H%M%S).sql
```

2. **Update in stages (not all at once):**
```php
php artisan tinker
>>> \App\Models\FiscalDocument::where('status', 'error')
    ->where('created_at', '>=', now()->subHours(6))
    ->take(100)
    ->update(['status' => 'pending']);
```

3. **Trigger re-sync:**
```bash
php artisan fiscal:sync-mitt --tenant-id=X
```

4. **Verify:**
```sql
SELECT status, COUNT(*) FROM fiscal_documents 
WHERE updated_at >= NOW() - INTERVAL 10 MINUTE
GROUP BY status;
```

### Task 5: Audit Document Changes

**Scenario:** Verify who made changes to a fiscal document

**Steps:**

```php
php artisan tinker
>>> $doc = \App\Models\FiscalDocument::find(123);

// Check change history (if audit logging implemented)
>>> \App\Models\AuditLog::where('model_id', 123)
    ->where('model_type', 'FiscalDocument')
    ->latest()
    ->get(['action', 'changes', 'user_id', 'created_at']);
```

**Example output:**
```
[
  {
    "action": "updated",
    "changes": {"status": ["pending", "authorized"]},
    "user_id": 5,
    "created_at": "2024-05-15 12:00:00"
  }
]
```

---

## Troubleshooting Guide

### Issue 1: Sync Command Fails with "API Timeout"

**Error Message:**
```
[ERROR] Mitt API connection timeout after 30 seconds
```

**Root Causes:**
1. Mitt API service down
2. Network connectivity issues
3. Firewall blocking access
4. High API latency

**Diagnosis:**

```bash
# Check network connectivity to Mitt
curl -I https://api.mitt.com.br/health

# Check if API is responding
php artisan tinker
>>> $service = new \App\Services\MittService();
>>> $service->testConnection();
```

**Resolution:**

1. **If Mitt is down:**
   - Wait for service to recover
   - Schedule retry in 30 minutes
   - Notify Mitt support if outage > 1 hour

2. **If network issue:**
   - Check firewall rules
   - Verify server has internet connectivity
   - Check DNS resolution: `nslookup api.mitt.com.br`

3. **If high latency:**
   - Reduce concurrent sync processes
   - Increase timeout in config: `MITT_API_TIMEOUT=60`
   - Contact Mitt for performance issues

**Recovery:**
```bash
# Clear failed jobs
php artisan queue:flush

# Restart sync after issue resolved
php artisan fiscal:sync-mitt --force
```

---

### Issue 2: Database Lock Timeout

**Error Message:**
```
SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded
```

**Root Causes:**
1. Long-running sync operation
2. Heavy query contention
3. Missing indexes

**Diagnosis:**

```sql
-- Show active locks
SHOW PROCESSLIST WHERE STATE LIKE '%Lock%';

-- Check table statistics
SELECT table_name, table_rows, data_length 
FROM information_schema.TABLES 
WHERE table_schema = 'thiga' 
  AND table_name = 'fiscal_documents';
```

**Resolution:**

1. **Increase lock timeout (temporary):**
```php
// In config/database.php
'mysql' => [
    'driver' => 'mysql',
    // ... other settings
    'options' => [
        PDO::ATTR_TIMEOUT => 300, // 5 minutes
    ],
],
```

2. **Add missing indexes:**
```sql
-- Ensure these indexes exist
CREATE INDEX idx_fiscal_tenant_type 
  ON fiscal_documents(tenant_id, document_type);

CREATE INDEX idx_fiscal_status 
  ON fiscal_documents(status);

CREATE INDEX idx_fiscal_created 
  ON fiscal_documents(created_at DESC);
```

3. **Reduce batch size:**
```bash
# Sync 50 documents at a time instead of 500
php artisan fiscal:sync-mitt --limit=50
```

**Prevention:**
- Run sync during off-peak hours
- Implement query result caching
- Add read replicas for reporting queries

---

### Issue 3: Duplicate Access Keys Generated

**Error Message:**
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
```

**Root Causes:**
1. Race condition during concurrent syncs
2. Access key generation algorithm collision
3. Manual document creation without validation

**Diagnosis:**

```sql
-- Find duplicates
SELECT access_key, COUNT(*) as count
FROM fiscal_documents
GROUP BY access_key
HAVING COUNT(*) > 1;
```

**Resolution:**

1. **Identify problematic documents:**
```sql
SELECT id, tenant_id, access_key, created_at
FROM fiscal_documents
WHERE access_key IN (
    SELECT access_key 
    FROM fiscal_documents 
    GROUP BY access_key 
    HAVING COUNT(*) > 1
)
ORDER BY access_key, created_at;
```

2. **Remove duplicates (keep first):**
```sql
DELETE from fiscal_documents 
WHERE id NOT IN (
    SELECT id FROM (
        SELECT MIN(id) as id
        FROM fiscal_documents
        GROUP BY access_key
    ) as temp
)
AND access_key IN (
    SELECT access_key 
    FROM (
        SELECT access_key
        FROM fiscal_documents
        GROUP BY access_key
        HAVING COUNT(*) > 1
    ) as dupes
);
```

3. **Verify fix:**
```sql
SELECT access_key, COUNT(*) 
FROM fiscal_documents 
GROUP BY access_key 
HAVING COUNT(*) > 1;
-- Should return empty result set
```

**Prevention:**
- Implement database-level uniqueness constraint (already done)
- Use `withoutOverlapping()` in scheduled job
- Add pessimistic locking: `->lockForUpdate()`

---

### Issue 4: CT-e/MDF-e Status Not Updating

**Symptoms:**
- Documents stuck in "validating" state
- Authorized_at timestamp not being set
- Users unable to download PDFs

**Root Causes:**
1. Webhook not receiving status updates from Mitt
2. Event listener not processing status changes
3. Sefaz delay in issuing authorization

**Diagnosis:**

```bash
# Check event queue
php artisan queue:failed

# Check webhook logs
tail -100 storage/logs/webhooks.log | grep -i "fiscal\|mitt"

# Verify database events
php artisan tinker
>>> \App\Models\FiscalDocument::where('status', 'validating')
    ->where('created_at', '<', now()->subHours(24))
    ->count();
```

**Resolution:**

1. **If webhook not responding:**
```bash
# Test webhook endpoint
curl -X POST https://thiga.local/webhooks/fiscal/status-update \
  -H "Content-Type: application/json" \
  -H "X-Mitt-Token: secret_token" \
  -d '{
    "document_id": "123",
    "status": "authorized"
  }'
```

2. **Replay failed events:**
```php
php artisan tinker
>>> $events = \App\Models\WebhookLog::where('status', 'failed')
    ->where('type', 'fiscal')
    ->get();

>>> foreach ($events as $event) {
      event(new \App\Events\FiscalDocumentStatusChanged($event->data));
    }
```

3. **Manual status update (as last resort):**
```php
>>> $doc = \App\Models\FiscalDocument::find(123);
>>> $doc->update([
      'status' => 'authorized',
      'authorized_at' => now(),
    ]);
```

---

### Issue 5: High Memory Usage During Sync

**Error Message:**
```
Allowed memory size of 536870912 bytes exhausted
```

**Root Causes:**
1. Processing too many documents in one batch
2. Huge XML/PDF content loaded in memory
3. N+1 query problem

**Diagnosis:**

```bash
# Monitor memory during sync
watch -n 1 'ps aux | grep "fiscal:sync-mitt"'

# Check current memory limit
php -i | grep memory_limit
```

**Resolution:**

1. **Increase PHP memory limit (temporary):**
```bash
php -d memory_limit=1024M artisan fiscal:sync-mitt
```

2. **Process in smaller batches:**
```bash
# Sync 50 documents at a time
php artisan fiscal:sync-mitt --limit=50

# Run multiple times
for i in {1..10}; do
    php artisan fiscal:sync-mitt --limit=50
    sleep 60
done
```

3. **Optimize queries:**
```php
// Use lazy() for large result sets
FiscalDocument::where('status', 'pending')
    ->lazy(500) // Process 500 at a time
    ->each(function ($doc) {
        // Process document
    });
```

4. **Configure memory in artisan command:**
```php
// In SyncMittDocuments.php
public function handle()
{
    ini_set('memory_limit', '512M');
    // ... sync logic
}
```

---

## Emergency Procedures

### Emergency 1: Critical Sync Failure - All Tenants

**Situation:** Sync command failing for all tenants, documents piling up

**Immediate Actions:**

1. **Stop the sync:**
```bash
# Kill any running sync processes
pkill -f "fiscal:sync-mitt"

# Disable scheduled job temporarily
# Edit app/Console/Kernel.php, comment out fiscal:sync-mitt schedule
```

2. **Investigate:**
```bash
tail -200 storage/logs/laravel.log > /tmp/fiscal-error.log
grep -i "error\|exception\|fatal" /tmp/fiscal-error.log
```

3. **Get on-call engineer:**
- Slack: @platform-oncall
- Phone: Check on-call schedule
- Status: Update status page

4. **Notify stakeholders:**
- Email: affected-customers@thiga.local
- Subject: "Fiscal Documents Processing Delayed"
- ETA: T.B.D.

**Recovery:**

1. **Fix root cause** (see Troubleshooting section above)

2. **Rebuild missing documents:**
```bash
# Get the tenant ID
php artisan tinker
>>> $tenants = \App\Models\Tenant::all();
>>> foreach ($tenants as $t) echo "{$t->id}: {$t->company_name}\n";
```

3. **Resume sync gradually:**
```bash
# Start with single tenant
php artisan fiscal:sync-mitt --tenant-id=1 --limit=50

# Monitor for issues
watch -n 5 'php artisan tinker --execute="echo \App\Models\FiscalDocument::where(created_at, >=, now()->subHours(1))->count();"'

# If OK, expand to other tenants
php artisan fiscal:sync-mitt --tenant-id=2 --limit=50
# ... repeat for each tenant
```

4. **Re-enable scheduled job:**
- Uncomment in app/Console/Kernel.php
- Test: `php artisan schedule:run`

---

### Emergency 2: Data Corruption - Duplicate Access Keys

**Situation:** Duplicate access keys discovered, documents unable to save

**Immediate Actions:**

1. **Prevent further damage:**
```sql
-- Disable sync temporarily
-- ALTER TABLE fiscal_documents DISABLE KEYS;
-- Or use application flag

// In app/Services/FiscalService.php
if (config('fiscal.sync_disabled')) {
    throw new Exception("Sync temporarily disabled for maintenance");
}
```

2. **Create backup:**
```bash
mysqldump -u user -p --single-transaction thiga > backup-$(date +%Y%m%d-%H%M%S).sql
```

3. **Investigate scope:**
```sql
SELECT COUNT(*) as duplicate_count
FROM fiscal_documents
GROUP BY access_key
HAVING COUNT(*) > 1;
```

4. **Notify engineering:**
- Severity: Critical
- Action required: Data cleanup
- Impact: All tenants

**Resolution:**

See Issue 3 in Troubleshooting Guide above for detailed fix.

---

### Emergency 3: Sefaz Authority Unreachable

**Situation:** Sefaz authentication/validation endpoints down

**Symptoms:**
- Documents stuck in "validating" or "processing"
- New syncs fail with "Sefaz unreachable"
- Customer support flooded with tickets

**Immediate Actions:**

1. **Verify Sefaz status:**
```bash
# Check official Sefaz status page
curl -s https://www1.sefaz.fazenda.gov.br/status/ | grep -i "ct-e\|sefaz"

# Check Mitt status (our intermediary)
curl -s https://status.mitt.com.br/
```

2. **Notify customers:**
- Email: affected-customers@thiga.local
- Content: "Sefaz authentication temporarily unavailable"
- ETA: Checking Sefaz status page

3. **Pause processing:**
```php
// In app/Services/FiscalService.php
if (!$this->sefazIsAvailable()) {
    Log::warning('Sefaz unavailable, pausing fiscal document processing');
    return null;
}
```

**Recovery:**

1. **Wait for Sefaz to recover** (usually 15-60 minutes)

2. **Resume sync:**
```bash
php artisan fiscal:sync-mitt --limit=100
```

3. **Monitor authorization queue:**
```sql
SELECT COUNT(*) as pending
FROM fiscal_documents
WHERE status IN ('validating', 'processing')
  AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

4. **Bulk retry after recovery:**
```bash
# Re-validate all stuck documents
php artisan fiscal:retry-validation --hours=2
```

---

## Performance Tuning

### Optimize Sync Speed

**Current baseline:** 100 documents in ~30 seconds

**Bottleneck analysis:**

```php
// Add timing to sync command
$start = microtime(true);

$documents = FiscalDocument::where('status', 'pending')
    ->with(['shipment', 'route'])
    ->limit(100)
    ->get();

foreach ($documents as $doc) {
    $this->syncDocument($doc); // ~300ms per doc
}

$duration = microtime(true) - $start;
$this->info("Synced {$count} documents in {$duration}s");
```

**Optimization strategies:**

1. **Use batch processing:**
```php
DB::statement('INSERT INTO fiscal_documents (...)
  SELECT ... FROM temp_staging
  WHERE processed = 0
  LIMIT 1000');
```

2. **Lazy load relationships:**
```php
FiscalDocument::where('status', 'pending')
    ->lazy(500)
    ->each(function ($doc) {
        // Process without loading all into memory
    });
```

3. **Queue heavy operations:**
```php
dispatch(new SyncFiscalDocumentJob($document))->onConnection('documents');
```

4. **Add caching:**
```php
$accessKey = Cache::remember("fiscal:access-key:{$doc->id}", 3600, function () {
    return $doc->generateAccessKey();
});
```

---

### Database Optimization

**Analyze slow queries:**
```bash
# Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1; -- Log queries > 1 second

# Review slow log
tail /var/log/mysql/slow.log | grep fiscal
```

**Run EXPLAIN analysis:**
```sql
EXPLAIN SELECT * FROM fiscal_documents 
WHERE tenant_id = 1 AND status = 'pending' 
ORDER BY created_at DESC LIMIT 20;

-- Should use indexes on (tenant_id, status, created_at)
```

**Optimize with indexes:**
```sql
-- Composite index for common filter
CREATE INDEX idx_fiscal_filter 
ON fiscal_documents(tenant_id, status, document_type, created_at DESC);

-- Separate index for authorized documents (smaller result set)
CREATE INDEX idx_fiscal_authorized 
ON fiscal_documents(tenant_id, status) WHERE status = 'authorized';

-- Archive old records
ALTER TABLE fiscal_documents
ADD PARTITION BY RANGE(YEAR(created_at)) (
  PARTITION p2023 VALUES LESS THAN (2024),
  PARTITION p2024 VALUES LESS THAN (2025),
  PARTITION p2025 VALUES LESS THAN (2026),
  PARTITION pmax VALUES LESS THAN MAXVALUE
);
```

---

## Data Management

### Backup & Recovery

**Automated daily backup:**
```bash
# In cron: runs at 2 AM daily
0 2 * * * /usr/local/bin/backup-fiscal-db.sh
```

**Backup script:**
```bash
#!/bin/bash
DATE=$(date +%Y%m%d-%H%M%S)
BACKUP_FILE="/backups/fiscal/fiscal-$DATE.sql.gz"

mysqldump -u backup_user -p'secure_password' \
  --single-transaction \
  --quick \
  --lock-tables=false \
  thiga fiscal_documents | gzip > $BACKUP_FILE

# Verify
gunzip -t $BACKUP_FILE && echo "Backup verified OK" || echo "Backup FAILED"

# Keep only last 30 days
find /backups/fiscal -name "fiscal-*.sql.gz" -mtime +30 -delete

# Send to S3
aws s3 cp $BACKUP_FILE s3://thiga-backups/fiscal/
```

**Restore from backup:**
```bash
# List available backups
ls -lht /backups/fiscal/ | head -10

# Restore specific backup
gunzip < /backups/fiscal/fiscal-20240515-020000.sql.gz | mysql -u user -p thiga

# Verify restore
mysql -u user -p -e "SELECT COUNT(*) FROM fiscal_documents;"
```

### Archiving Old Data

**Archive documents older than 12 months:**
```sql
-- Create archive table
CREATE TABLE fiscal_documents_archive LIKE fiscal_documents;

-- Move old data
INSERT INTO fiscal_documents_archive
SELECT * FROM fiscal_documents
WHERE created_at < DATE_SUB(NOW(), INTERVAL 12 MONTH);

-- Delete from live table
DELETE FROM fiscal_documents
WHERE created_at < DATE_SUB(NOW(), INTERVAL 12 MONTH);

-- Verify
SELECT COUNT(*) FROM fiscal_documents; -- Should decrease
SELECT COUNT(*) FROM fiscal_documents_archive; -- Should increase
```

### Data Retention Policy

| Data | Retention | Action |
|------|-----------|--------|
| Authorized documents | Indefinite | Keep in live table |
| Pending/Error documents | 90 days | Archive after 90 days |
| Sync logs | 30 days | Delete after 30 days |
| Audit logs | 2 years | Archive after 1 year, delete after 2 years |
| XML/PDF content | 7 years | Archive after 6 months |

---

## Escalation Path

**Issue Severity Classification:**

| Severity | Response Time | Escalation | Example |
|----------|---------------|-----------|---------|
| P1 (Critical) | 15 min | VP Engineering | All syncs failing, data corruption |
| P2 (High) | 1 hour | Engineering Lead | Single tenant sync failing, 10%+ errors |
| P3 (Medium) | 4 hours | Platform Team | Slow sync, individual document failures |
| P4 (Low) | 24 hours | Backlog | UI improvements, optimization ideas |

**Contact Information:**

- **On-Call Engineer:** Page via PagerDuty (fiscal-documents)
- **Engineering Lead:** daniel.engineering@thiga.local
- **VP Engineering:** vp.engineering@thiga.local
- **Mitt Support:** support@mitt.com.br (9-17h business hours)
- **Sefaz Support:** Check https://www1.sefaz.fazenda.gov.br for status

---

**Last Updated:** May 22, 2026  
**Next Review:** May 22, 2027  
**Owner:** Platform Engineering Team
