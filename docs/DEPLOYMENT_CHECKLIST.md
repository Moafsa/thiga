# Fiscal Documents - Deployment Checklist

## Overview

This checklist ensures safe and complete deployment of the Fiscal Documents feature (CT-e/MDF-e) from development through production.

**Feature:** Fiscal Documents Management System  
**Version:** 1.0  
**Release Date:** May 22, 2026  
**Deployment Type:** Feature Release (non-breaking)  
**Risk Level:** Low (no schema breaking changes, backward compatible)

---

## Pre-Deployment Phase (Development & Staging)

### Code Review Checklist

- [ ] All code merged to `main` branch via approved pull requests
- [ ] Code review completed by 2+ team members
- [ ] All commits signed (require GPG signatures)
- [ ] No merge conflicts
- [ ] Security scan passed (run `composer audit`)

**Verification:**
```bash
# Verify commits are signed
git log --pretty=format:"%h %G? %s" | grep -v "^[^U]" | head -5

# Check for security vulnerabilities
composer audit

# Ensure all tests pass
php artisan test
```

---

### Database Migrations Check

- [ ] Migration files reviewed for correctness
- [ ] Foreign key constraints reviewed
- [ ] Indexes verified (no missing or redundant indexes)
- [ ] Down migration method tested (can rollback successfully)

**Verification:**
```bash
# Review migration
cat database/migrations/2024_01_01_002800_create_fiscal_documents_table.php

# Dry-run in dev
php artisan migrate --dry-run

# Test rollback in dev
php artisan migrate:rollback
php artisan migrate

# Verify schema on staging
php artisan migrate:status
```

---

### Test Coverage Verification

- [ ] All 68 automated tests passing (100% pass rate)
- [ ] Code coverage report generated (target 80%+)
- [ ] Critical code paths covered:
  - [ ] Controllers: listing, filtering, cancellation
  - [ ] Services: sync operations, access key generation
  - [ ] Models: relationships, scopes, status transitions
  - [ ] Commands: sync command execution

**Verification:**
```bash
# Run all tests with coverage
php artisan test --coverage

# Generate HTML coverage report
php artisan test --coverage --coverage-html=coverage

# Check coverage percentage
grep -o 'Data coverage: [0-9.]*%' coverage/index.html
```

---

### Configuration Review

- [ ] Environment variables updated (.env.example)
- [ ] Config files reviewed for hardcoded values
- [ ] Mitt API credentials configured in staging
- [ ] CSRF token settings verified
- [ ] Session timeout settings appropriate

**Environment Variables:**
```bash
# In .env file
MITT_API_KEY=staging_key_xxxx
MITT_API_SECRET=staging_secret_xxxx
MITT_API_TIMEOUT=30
FISCAL_SYNC_ENABLED=true
FISCAL_DOCUMENTS_PER_PAGE=20
```

**Verification:**
```bash
# Test Mitt API connection
php artisan tinker
>>> $service = new \App\Services\MittService();
>>> $service->testConnection();
```

---

### Documentation Completeness

- [ ] User documentation (FISCAL_DOCUMENTS_GUIDE.md) - ✅ Complete
- [ ] API reference (FISCAL_API_REFERENCE.md) - ✅ Complete
- [ ] Testing guide (TESTING_FISCAL_DOCUMENTS.md) - ✅ Complete
- [ ] Runbooks (RUNBOOKS_FISCAL.md) - ✅ Complete
- [ ] Deployment checklist (this file) - ✅ Complete
- [ ] README updated with feature description
- [ ] Change log updated

**Verification:**
```bash
# Check all docs exist
ls -la docs/ | grep -i fiscal
```

---

### Staging Deployment

- [ ] Code deployed to staging environment
- [ ] Database migrations run successfully
- [ ] No deployment errors in logs
- [ ] Staging environment is accessible

**Deployment Commands:**
```bash
# On staging server
cd /var/www/thiga

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Restart queue workers
supervisorctl restart all

# Verify deployment
php artisan tinker --execute="echo 'OK';"
```

---

## Staging Testing Phase

### Functional Testing

**Manual Testing Checklist:**

1. **Listing Page**
   - [ ] Page loads without errors
   - [ ] All columns display correctly
   - [ ] Status badges have correct colors
   - [ ] Pagination controls work
   - [ ] Empty state displays when no documents

2. **Filtering**
   - [ ] Document type filter (CT-e/MDF-e) works
   - [ ] Status filter works (all 7 statuses)
   - [ ] Date range filter works
   - [ ] Search by access key works
   - [ ] Search by MITT number works
   - [ ] Combined filters work together
   - [ ] Filters are preserved during pagination

3. **Document Details**
   - [ ] Detail page loads with all information
   - [ ] Related entity links work (Shipment/Route)
   - [ ] XML content displays correctly
   - [ ] PDF download works (if authorized)
   - [ ] Status badges display correctly

4. **Actions**
   - [ ] Cancel button works for authorized CT-es
   - [ ] Cancellation modal appears
   - [ ] Confirmation updates status to "cancelled"
   - [ ] Cancel button hidden for non-CT-e documents
   - [ ] Cancel button hidden for non-authorized documents

5. **Multi-Tenant Isolation**
   - [ ] Tenant A users see only Tenant A documents
   - [ ] Tenant B users see only Tenant B documents
   - [ ] URL tampering cannot access other tenant's documents

**Test Data Creation:**
```bash
# Seed staging with test data
php artisan db:seed --class=FiscalDocumentSeeder --tenant-id=1
php artisan db:seed --class=FiscalDocumentSeeder --tenant-id=2

# Verify data created
php artisan tinker
>>> \App\Models\FiscalDocument::where('tenant_id', 1)->count()
>>> \App\Models\FiscalDocument::where('tenant_id', 2)->count()
```

---

### Sync Testing

- [ ] Manual sync command executes successfully
  ```bash
  php artisan fiscal:sync-mitt --dry-run
  php artisan fiscal:sync-mitt
  ```

- [ ] Test with real Mitt credentials
  - [ ] Documents created in Mitt response
  - [ ] Access keys generated correctly (35 digits)
  - [ ] Status updates from pending → authorized
  - [ ] XML content stored in database

- [ ] Test scheduled sync
  - [ ] Scheduled command runs on schedule
  - [ ] No overlapping executions
  - [ ] Logs created for each execution

**Staging Sync Verification:**
```bash
# Run sync with logging
php artisan fiscal:sync-mitt --verbose

# Check logs
tail -50 storage/logs/laravel.log

# Verify documents created
mysql -u user -p -e "SELECT COUNT(*) FROM fiscal_documents;"
```

---

### Performance Testing

- [ ] List page loads in < 1 second with 100 documents
- [ ] Filter/search completes in < 500ms
- [ ] No N+1 query problems (check with Debugbar)
- [ ] Database query count < 5 for list page
- [ ] Sync performance acceptable (100 docs/minute minimum)

**Performance Testing:**
```bash
# Install Laravel Debugbar for staging
composer require barryvdh/laravel-debugbar --dev

# Test query count
# Open /fiscal/documents in browser
# Check Debugbar → Queries tab

# Sync performance
time php artisan fiscal:sync-mitt --limit=100
# Should complete in < 2 minutes
```

---

### Browser Compatibility Testing

- [ ] Chrome (latest) - ✅ Test in staging
- [ ] Firefox (latest) - ✅ Test in staging
- [ ] Safari (latest) - ✅ Test in staging
- [ ] Edge (latest) - ✅ Test in staging
- [ ] Mobile browsers (iOS Safari, Chrome) - ✅ Test in staging

**Responsive Testing:**
```bash
# Test at different viewport sizes
# Desktop: 1280x1024
# Tablet: 768x1024
# Mobile: 375x812

# All layouts should be usable
# No horizontal scrolling except tables
# Touch targets > 44px
```

---

### Security Testing

- [ ] CSRF token validation works
  - [ ] POST requests require valid token
  - [ ] Requests without token are rejected

- [ ] Authentication required for all endpoints
  - [ ] Unauthenticated users redirected to login
  - [ ] Session timeout works correctly

- [ ] Multi-tenant isolation enforced
  - [ ] Cannot access other tenant's documents via URL
  - [ ] Cannot modify documents with foreign tenant_id

- [ ] Input validation working
  - [ ] Invalid filter values rejected
  - [ ] SQL injection prevention (parameterized queries)
  - [ ] XSS prevention (escaped output)

**Security Verification:**
```bash
# Test CSRF protection
curl -X POST https://staging.thiga.local/fiscal/documents/1/cancel \
  -H "Cookie: XSRF-TOKEN=invalid_token" \
  -d "_token=invalid" \
  -L -I # Should get 419 Unprocessable Entity

# Test authentication
curl https://staging.thiga.local/fiscal/documents \
  -I # Should get 302 redirect to login

# Test input validation
curl "https://staging.thiga.local/fiscal/documents?status=invalid" \
  -b "session_cookie=value"
# Should either reject or sanitize
```

---

## Production Deployment Phase

### Pre-Production Backup

- [ ] Full database backup created
- [ ] Backup verified (can restore successfully)
- [ ] Backup uploaded to secure location (S3/backup service)
- [ ] Backup path documented

**Backup Verification:**
```bash
# Create backup
mysqldump -u backup_user -p thiga --single-transaction > fiscal-backup.sql

# Verify backup integrity
mysql -u test_user -p -e "CREATE DATABASE thiga_test;"
mysql -u test_user -p thiga_test < fiscal-backup.sql

# Verify table counts
mysql -u test_user -p -e "SELECT COUNT(*) FROM thiga_test.fiscal_documents;"

# Drop test database
mysql -u test_user -p -e "DROP DATABASE thiga_test;"

# Upload to S3
aws s3 cp fiscal-backup.sql s3://thiga-backups/pre-deployment-$(date +%Y%m%d).sql
```

---

### Production Deployment Window

**Scheduled for:** TBD  
**Expected Duration:** 15-30 minutes  
**Maintenance Window:** Yes / No

**Deployment Window Criteria:**
- [ ] Off-peak hours (before 8 AM or after 6 PM)
- [ ] Not during month-end close (accounting sensitive)
- [ ] Not during peak shipping hours (10 AM - 4 PM)
- [ ] No major holidays
- [ ] At least 2 engineers available for monitoring

---

### Production Deployment Steps

**Step 1: Prepare Environment**

```bash
# SSH to production server
ssh -i /path/to/key deploy@production.thiga.local

# Verify current state
cd /var/www/thiga
git status
php artisan --version

# Take backup
mysqldump -u backup_user -p thiga --single-transaction | gzip > /backups/pre-deployment-$(date +%Y%m%d-%H%M%S).sql.gz
```

- [ ] Connected to production
- [ ] Current code reviewed
- [ ] Backup created successfully

**Step 2: Deploy Code**

```bash
# Pull latest code
git pull origin main

# Verify code pulled
git log --oneline -3

# Install dependencies (composer)
composer install --no-dev --optimize-autoloader

# Verify no errors
echo $?  # Should output: 0
```

- [ ] Code pulled successfully
- [ ] No merge conflicts
- [ ] Dependencies installed
- [ ] No errors during install

**Step 3: Database Migrations**

```bash
# Run migrations
php artisan migrate --force

# Verify migrations completed
php artisan migrate:status

# Check fiscal_documents table
php artisan tinker
>>> \DB::table('fiscal_documents')->count();
```

- [ ] Migrations ran successfully
- [ ] No SQL errors
- [ ] All tables present
- [ ] Indexes created

**Step 4: Cache & Config**

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
supervisorctl restart all
```

- [ ] Caches cleared
- [ ] Caches rebuilt
- [ ] Queue workers restarted
- [ ] No errors

**Step 5: Verification**

```bash
# Test application is responsive
curl -I https://production.thiga.local/fiscal/documents

# Check logs for errors
tail -20 storage/logs/laravel.log | grep -i "error\|fatal"

# Verify database connectivity
php artisan tinker --execute="echo DB::connection()->getPdo() ? 'OK' : 'FAIL';"

# Test sync command
php artisan fiscal:sync-mitt --dry-run
```

- [ ] Application is responding (HTTP 200)
- [ ] No errors in logs
- [ ] Database connected
- [ ] Sync command works

---

### Deployment Verification (In Production)

**Immediate Post-Deployment (First 30 minutes):**

- [ ] Fiscal documents page loads (https://production.thiga.local/fiscal/documents)
- [ ] No errors in user-facing pages
- [ ] Filtering works
- [ ] Database queries perform normally
- [ ] No spike in error rates (check monitoring)

**Continuous Monitoring (First 24 hours):**

- [ ] Monitor error logs for 24 hours
- [ ] Monitor database performance
- [ ] Monitor API response times
- [ ] Check customer support for new issues
- [ ] No unusual spike in exceptions

**Monitoring Dashboard Checks:**

```sql
-- Monitor error rate
SELECT 
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:00') as minute,
    COUNT(*) as count
FROM logs
WHERE channel = 'single' AND level = 'error'
  AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY minute;

-- Monitor slow queries
SELECT 
    query_time,
    lock_time,
    rows_sent,
    rows_examined,
    sql_text
FROM slow_log
WHERE start_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
  AND sql_text LIKE '%fiscal%'
ORDER BY query_time DESC
LIMIT 10;

-- Monitor sync execution
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total_docs,
    SUM(CASE WHEN status = 'authorized' THEN 1 ELSE 0 END) as authorized
FROM fiscal_documents
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY DATE(created_at);
```

---

### Rollback Plan

**Trigger Rollback If:**
- [ ] Critical functionality broken (users cannot access features)
- [ ] Data corruption detected
- [ ] Performance degradation > 50%
- [ ] Error rate > 5%
- [ ] Database migration failed

**Rollback Procedure:**

```bash
# Stop the application
systemctl stop laravel.service

# Revert code to previous commit
git reset --hard HEAD~1

# Restore database from backup
mysql -u user -p thiga < /backups/pre-deployment-$(date +%Y%m%d).sql

# Restart application
systemctl start laravel.service

# Verify rollback successful
curl -I https://production.thiga.local/

# Notify stakeholders
# Send rollback notification email
```

**Rollback Checklist:**

- [ ] Code reverted to previous version
- [ ] Database restored from backup
- [ ] Application restarted
- [ ] Functionality verified
- [ ] Stakeholders notified
- [ ] Post-mortem scheduled

---

## Post-Deployment Phase

### 24-Hour Monitoring

**Schedule:** Once per hour for 24 hours

- [ ] Error log review (no new fiscal-related errors)
- [ ] Database performance check (no slow queries)
- [ ] Sync execution verification (documents being processed)
- [ ] User feedback (no support tickets about fiscal documents)

**24-Hour Checklist:**

```bash
# Check 24-hour error summary
mysql -u user -p -e "
  SELECT 
    DATE_FORMAT(created_at, '%H:00') as hour,
    COUNT(*) as errors
  FROM logs
  WHERE channel = 'single' 
    AND level IN ('error', 'critical')
    AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
  GROUP BY hour;
"

# Check document sync count
mysql -u user -p -e "
  SELECT 
    DATE(created_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'authorized' THEN 1 ELSE 0 END) as authorized,
    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors
  FROM fiscal_documents
  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
  GROUP BY DATE(created_at);
"
```

- [ ] No critical errors
- [ ] Database performance normal
- [ ] Sync operations successful
- [ ] User experience good

---

### 7-Day Monitoring

**Schedule:** Once per day for 7 days

- [ ] Error rate trending down
- [ ] No memory leaks (memory usage stable)
- [ ] Database health normal
- [ ] Feature usage metrics collected
- [ ] Customer satisfaction (support tickets and feedback)

**7-Day Report Items:**
- [ ] Total documents created
- [ ] Sync success rate
- [ ] Average page load time
- [ ] Error rate
- [ ] Customer feedback summary

---

### Known Issues & Limitations

**Current Limitations:**
- [ ] PDF generation only for authorized documents
- [ ] XML storage limited to 4GB (LONGTEXT)
- [ ] Search is prefix-based (LIKE 'value%'), not full-text
- [ ] Batch operations limited to 100 documents per request

**Known Issues:**
- [ ] Document type icons may not render on older browsers
- [ ] Performance degrades with > 10k documents (consider archiving)
- [ ] Mitt API timeouts on high concurrency (mitigated with --limit flag)

---

### Communication Plan

**Notification Channels:**
- [ ] Status page (https://status.thiga.local)
- [ ] Customer email notifications
- [ ] Slack #deployments channel
- [ ] In-app notification banner (if critical)

**Message Template:**

```
Subject: Thiga TMS Fiscal Documents Feature Released

Dear Customers,

We are excited to announce the release of the Fiscal Documents feature for Thiga TMS.

New Features:
- Unified listing of CT-e and MDF-e documents
- Advanced filtering by status, date, document type
- Document search by access key or MITT number
- PDF/XML downloads for authorized documents
- CT-e cancellation with audit trail

To get started, visit: https://documentation.thiga.local/fiscal-documents

Contact support if you have any questions.

Best regards,
Thiga Team
```

---

### Success Criteria

**Deployment is considered SUCCESSFUL if:**

- ✅ All tests passing (100% pass rate)
- ✅ No critical errors in production logs (24+ hours)
- ✅ Database migrated successfully
- ✅ Sync operations working (documents processed)
- ✅ User can access fiscal documents page
- ✅ Filtering and search functional
- ✅ Document detail view displays correctly
- ✅ Multi-tenant isolation verified
- ✅ Performance acceptable (< 1s page load)
- ✅ No data corruption detected
- ✅ Customer feedback positive

**Deployment is considered FAILED if:**

- ❌ Critical functionality broken
- ❌ Database migration errors
- ❌ Error rate > 5%
- ❌ Data corruption detected
- ❌ Sync operations failing
- ❌ Multi-tenant isolation broken
- ❌ Performance degradation > 50%

---

## Deployment Sign-Off

**Prepared by:** Claude Haiku 4.5  
**Reviewed by:** [Engineering Lead Name]  
**Approved by:** [VP Engineering Name]  
**Deployed on:** [Deployment Date]  
**Verified by:** [QA Lead Name]  

---

## Appendix: Quick Reference

### Critical Commands

```bash
# Deployment
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force

# Verification
php artisan test
php artisan fiscal:sync-mitt --dry-run
curl https://production.thiga.local/fiscal/documents

# Rollback
git reset --hard HEAD~1
mysql -u user -p thiga < backup.sql
systemctl restart laravel.service

# Monitoring
tail -100 storage/logs/laravel.log
php artisan tinker
# In tinker:
>>> \App\Models\FiscalDocument::count();
>>> \App\Models\FiscalDocument::where('status', 'error')->count();
```

### Emergency Contacts

- **On-Call Engineer:** [Phone/Slack]
- **Engineering Lead:** [Phone/Email]
- **VP Engineering:** [Phone/Email]
- **Mitt Support:** support@mitt.com.br
- **Database Administrator:** [Contact]

### Useful Links

- Repository: https://github.com/thiga/thiga-tms
- Documentation: https://documentation.thiga.local
- Status Page: https://status.thiga.local
- Monitoring: https://monitoring.thiga.local (Grafana/Datadog/etc)
- Logs: https://logs.thiga.local (ELK/Splunk/etc)

---

**Last Updated:** May 22, 2026  
**Next Review:** After first production deployment  
**Document Version:** 1.0
