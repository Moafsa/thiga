# Fiscal Documents Testing Guide

## Overview

This document describes the comprehensive automated test suite for the Fiscal Documents feature (CT-e and MDF-e) in Thiga TMS. The test suite includes 68 automated tests across 6 test files, covering listing, filtering, synchronization, and end-to-end workflows.

## Test Suite Summary

### Total Tests: 68
- **Feature Tests:** 45 tests (4 files)
- **Unit Tests:** 23 tests (2 files)
- **Coverage Target:** 80%+
- **Framework:** PHPUnit 10.1 with Laravel RefreshDatabase trait

## Test Files

### 1. Feature Tests

#### FiscalDocumentListingTest.php (12 tests)
Tests the fiscal documents listing functionality and multi-tenant isolation.

**Test Cases:**
- `testCanViewFiscalDocumentsIndex` — Page loads successfully
- `testCannotAccessWithoutAuthentication` — Auth required
- `testCannotAccessOtherTenantDocuments` — Multi-tenant isolation
- `testDocumentsTableContainsCorrectColumns` — Table structure
- `testPaginationWorks` — 20 items per page, navigation works
- `testEmptyStateWhenNoDocuments` — Empty state display
- `testDocumentCountDisplay` — Count is accurate
- `testCteDocumentsDisplayedCorrectly` — CT-e type display
- `testMdfeDocumentsDisplayedCorrectly` — MDF-e type display
- `testStatusBadgesDisplayed` — Status colors and emojis
- `testRelatedEntityLinksAvailable` — Shipment/Route links

**Coverage:** Controller listing logic, view rendering, multi-tenant scoping

---

#### FiscalDocumentFilterTest.php (15 tests)
Tests all filtering and search functionality.

**Test Cases:**
- `testFilterByCteDocumentType` — Filter to CT-e only
- `testFilterByMdfeDocumentType` — Filter to MDF-e only
- `testFilterByPendingStatus` — Pending status filter
- `testFilterByAuthorizedStatus` — Authorized status filter
- `testFilterByErrorStatus` — Error status filter
- `testFilterByDateRange` — Date from/to filtering
- `testSearchByCompleteAccessKey` — Full access key search
- `testSearchByPartialAccessKey` — Partial access key search
- `testSearchByMittNumber` — MITT number search
- `testCombinedFilters` — Multiple filters together
- `testFilterQueryStringPreservedInPagination` — Query string persistence
- `testFilterWithNoResults` — Empty filter results
- `testResetFilters` — Clear all filters
- `testFilterRespectsTenantIsolation` — Tenant filtering isolation

**Coverage:** Filter logic, search queries, query builders, pagination with filters

---

#### FiscalDocumentSyncTest.php (8 tests)
Tests the synchronization command for Mitt API integration.

**Test Cases:**
- `testSyncMittDocumentsCommandRuns` — Command executes
- `testSyncMittCreatesCtesForShipments` — CT-e creation
- `testSyncMittCreatesMdfsForRoutes` — MDF-e creation
- `testSyncMittUpdatesPendingDocuments` — Status updates
- `testSyncMittWithTenantId` — --tenant-id option
- `testSyncMittHandlesApiErrors` — Error handling
- `testSyncMittGeneratesValidAccessKeys` — 35-digit key generation
- `testSyncMittIsIdempotent` — Prevents duplicates

**Coverage:** Artisan command, data synchronization, API integration

---

#### FiscalDocumentE2ETest.php (10 tests)
End-to-end integration tests covering complete workflows.

**Test Cases:**
- `testCompleteFlowFromCreateToView` — Create → List → Filter → View
- `testCancelAuthorizedCteWorkflow` — Authorization and cancellation
- `testMultiTenantIsolationEndToEnd` — Full tenant isolation flow
- `testCompleteSearchWorkflow` — Search by key and number
- `testPaginationWithFiltering` — Pagination and filters together
- `testDateRangeFilteringWorkflow` — Date filtering workflow
- `testCombinedFiltersWithPagination` — Complex filter scenarios
- `testEmptyResultsDisplay` — No results handling
- `testShipmentToDocumentRelationship` — Shipment associations
- `testRouteToDocumentRelationship` — Route associations

**Coverage:** Complete user workflows, multi-step processes, relationship integrity

---

### 2. Unit Tests

#### FiscalServiceTest.php (13 tests)
Tests the FiscalService business logic.

**Test Cases:**
- `testFiscalServiceCanBeInstantiated` — Service class
- `testGenerateAccessKeyFormat` — 35-digit key format
- `testGenerateAccessKeyIsUnique` — Uniqueness validation
- `testStatusTransitionsAreValid` — Valid status values
- `testPendingStatusCanTransitionToAuthorized` — Status change
- `testAuthorizedCanBeCancelled` — Cancellation capability
- `testFiscalDocumentHasRequiredFields` — Field validation
- `testFiscalDocumentBelongsToShipment` — Shipment relationship
- `testFiscalDocumentBelongsToRoute` — Route relationship
- `testFiscalDocumentCanCheckIfCte` — Type checking
- `testFiscalDocumentCanCheckIfMdfe` — Type checking
- `testFiscalDocumentCanCheckIfAuthorized` — Authorization check
- `testFiscalDocumentScopeByTenant` — Tenant scoping

**Coverage:** Model logic, relationships, validation, type checking

---

#### FiscalDocumentSeederTest.php (10 tests)
Tests the data seeder for test environment setup.

**Test Cases:**
- `testSeederCanBeInstantiated` — Seeder class
- `testSeederCreatesDocuments` — Document creation
- `testSeederCreates50To100Documents` — Count range validation
- `testSeederCreatesVariedStatusDocuments` — Status variety
- `testSeederCreatesValidAccessKeys` — Valid key format
- `testSeederCreatesValidMittNumbers` — Valid MITT numbers
- `testSeederCreatesValidDocumentTypes` — Valid document types
- `testSeederAssociatesWithShipmentsAndRoutes` — Relationships
- `testSeederCreatesDocumentsWithTimestamps` — Timestamp generation
- `testSeederRespectsMultiTenancy` — Tenant support
- `testSeederCreatesMixedDocumentTypes` — CT-e and MDF-e mix

**Coverage:** Seeder logic, data generation, validation

---

## Running the Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test File
```bash
php artisan test tests/Feature/FiscalDocumentListingTest.php
php artisan test tests/Feature/FiscalDocumentFilterTest.php
php artisan test tests/Feature/FiscalDocumentSyncTest.php
php artisan test tests/Unit/FiscalServiceTest.php
php artisan test tests/Unit/FiscalDocumentSeederTest.php
php artisan test tests/Feature/FiscalDocumentE2ETest.php
```

### Run With Coverage Report
```bash
php artisan test --coverage
php artisan test --coverage --coverage-html=coverage
```

### Run in Parallel (Faster)
```bash
php artisan test --parallel
```

## Test Environment Setup

### Database
- SQLite in-memory database (fastest for tests)
- RefreshDatabase trait ensures isolation
- Each test gets a clean database state

### Multi-Tenant Setup
Every test creates:
1. A `Plan` (subscription plan)
2. A `Tenant` associated with the plan
3. A `User` associated with the tenant
4. Related models (Shipment, Route, etc.)

### Test Data
- Uses Laravel Factories for consistent data generation
- Access keys: Always 35 digits (valid format)
- MITT numbers: Random positive integers
- Statuses: Valid enum values (pending, authorized, error, etc.)

## Success Criteria

✅ **Pass Rate:** 100% of tests passing
✅ **Code Coverage:** 80%+ for critical code paths
✅ **Execution Speed:** All 68 tests < 60 seconds
✅ **Isolation:** No test dependencies or shared state
✅ **Repeatability:** Tests are deterministic and repeatable

## Common Test Patterns

### Basic Feature Test
```php
public function testCanViewFiscalDocumentsIndex()
{
    $this->actingAs($this->user)
        ->get('/fiscal/documents')
        ->assertStatus(200)
        ->assertViewIs('fiscal.all.index');
}
```

### Database Assertion
```php
public function testSyncMittCreatesCtesForShipments()
{
    $shipment = Shipment::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
    
    $this->artisan('fiscal:sync-mitt');
    
    $this->assertDatabaseHas('fiscal_documents', [
        'tenant_id' => $this->tenant->id,
        'shipment_id' => $shipment->id,
        'document_type' => 'cte',
    ]);
}
```

### Filter Testing
```php
public function testFilterByCteDocumentType()
{
    // Create test data
    // Apply filter
    // Assert results
}
```

## Manual Testing Checklist

After automated tests pass, run manual tests to verify user experience.

### Listagem Básica
- [ ] Page loads without errors
- [ ] All columns display correctly
- [ ] Status badges have correct colors
- [ ] Related entity links work

### Filtros
- [ ] Document type filter works
- [ ] Status filter works
- [ ] Date range filter works
- [ ] Search by access key works
- [ ] Search by MITT number works
- [ ] Combined filters work
- [ ] Query strings preserved in pagination

### Ações
- [ ] View button opens detail page
- [ ] PDF download works (if authorized)
- [ ] XML download works (if authorized)
- [ ] Cancel button works (CT-e only)

### Responsividade
- [ ] Desktop view (1280px)
- [ ] Tablet view (768px)
- [ ] Mobile view (375px)

### Performance
- [ ] Listing 100 documents < 1 second
- [ ] Filtering < 500ms
- [ ] No N+1 queries (check with Debugbar)

## Troubleshooting

### Tests Timeout
- Check database connection
- Reduce test data size if needed
- Run with `--parallel` for faster execution

### Assertion Failures
- Check factory definitions match model structure
- Verify relationships are properly loaded with eager loading
- Check multi-tenant scoping is working

### Database Errors
- Ensure migrations ran successfully
- Check RefreshDatabase trait is used
- Verify foreign key relationships

## CI/CD Integration

These tests should be run:
- Before every commit (pre-commit hook)
- On every pull request
- Before deployment to staging
- Before production deployment

```bash
# Pre-commit hook
php artisan test --no-coverage
exit $?
```

## Next Steps

1. ✅ Create test files (DONE)
2. ⏳ Run all tests and verify pass rate
3. ⏳ Generate coverage report
4. ⏳ Execute manual testing checklist
5. ⏳ Document any issues and fixes
6. ⏳ Deploy to staging with tests

## References

- **PHPUnit Documentation:** https://phpunit.de/documentation.html
- **Laravel Testing:** https://laravel.com/docs/testing
- **Factories:** https://laravel.com/docs/factories
- **Assertions:** https://laravel.com/docs/testing#available-assertions

---

**Last Updated:** May 21, 2026
**Test Suite Version:** 1.0
**Status:** ✅ Complete - Ready for Execution
