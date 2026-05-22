# Fiscal Documents API Reference

## Overview

This document provides comprehensive technical reference for the Fiscal Documents API in Thiga TMS, covering CT-e (Conhecimento de Transporte Eletrônico) and MDF-e (Manifesto de Documento Fiscal Eletrônico) endpoints, services, and database schemas.

**Framework:** Laravel 10  
**API Style:** RESTful HTTP  
**Authentication:** Session-based (Laravel auth middleware)  
**Response Format:** JSON + HTML Views  
**Testing:** PHPUnit 10.1 with RefreshDatabase trait

---

## Table of Contents

1. [HTTP Endpoints](#http-endpoints)
2. [Controllers](#controllers)
3. [Services](#services)
4. [Models & Relationships](#models--relationships)
5. [Commands](#commands)
6. [Factories & Seeders](#factories--seeders)
7. [Error Handling](#error-handling)
8. [Response Examples](#response-examples)

---

## HTTP Endpoints

### Fiscal Documents Listing & Filtering

#### GET `/fiscal/documents`

**Description:** List all fiscal documents (CT-e and MDF-e) for the authenticated user's tenant.

**Authentication:** Required (session-based)

**Request Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Pagination page number |
| `per_page` | integer | No | 20 | Items per page |
| `document_type` | string | No | - | Filter: `cte` \| `mdfe` |
| `status` | string | No | - | Filter by status (see Status Enum) |
| `date_from` | date | No | - | Filter: created date >= YYYY-MM-DD |
| `date_to` | date | No | - | Filter: created date <= YYYY-MM-DD |
| `search` | string | No | - | Search by access_key or mitt_number |
| `sort_by` | string | No | `created_at` | Sort field: created_at, authorized_at, status, mitt_number, document_type |
| `sort_order` | string | No | `desc` | Sort order: `asc` \| `desc` |

**Response:** HTML view `fiscal.all.index`

**Success Response (HTML):**
```html
<!DOCTYPE html>
<html>
  <head>
    <title>Fiscal Documents</title>
    <!-- Table showing paginated documents -->
  </head>
  <body>
    <!-- Sidebar with filters -->
    <!-- Table with columns: Type, Number, Access Key, Entity, Status, Created, Actions -->
    <!-- Pagination controls -->
  </body>
</html>
```

**JSON Response (if Accept: application/json):**
```json
{
  "documents": [
    {
      "id": 1,
      "tenant_id": 1,
      "document_type": "cte",
      "access_key": "12345678901234567890123456789012345",
      "mitt_number": 123456,
      "status": "authorized",
      "shipment_id": 10,
      "route_id": null,
      "created_at": "2024-05-15 10:30:00",
      "authorized_at": "2024-05-15 12:00:00",
      "cancelled_at": null,
      "xml_content": "<?xml version=\"1.0\"?>...",
      "shipment": {
        "id": 10,
        "reference": "SHIP-001",
        "status": "delivered"
      },
      "route": null
    }
  ],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 1,
    "last_page": 8,
    "from": 1,
    "to": 20
  },
  "filters_applied": {
    "document_type": "cte",
    "status": "authorized"
  }
}
```

**Query String Example:**
```
GET /fiscal/documents?document_type=cte&status=authorized&date_from=2024-01-01&date_to=2024-12-31&page=2
```

**Status Codes:**
- `200` — Success
- `401` — Unauthenticated (redirect to login)
- `403` — Forbidden (another tenant's documents)
- `500` — Server error

---

### Fiscal Document Detail

#### GET `/fiscal/documents/{id}`

**Description:** Retrieve detailed view of a single fiscal document.

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Fiscal document ID |

**Response:** HTML view `fiscal.show` with full details

**JSON Response:**
```json
{
  "id": 1,
  "tenant_id": 1,
  "document_type": "cte",
  "access_key": "12345678901234567890123456789012345",
  "mitt_number": 123456,
  "status": "authorized",
  "shipment_id": 10,
  "route_id": null,
  "created_at": "2024-05-15 10:30:00",
  "authorized_at": "2024-05-15 12:00:00",
  "cancelled_at": null,
  "xml_content": "<?xml version=\"1.0\"?>...",
  "pdf_content_base64": "JVBERi0xLjQKJeLjz9MNCi4uLg==",
  "shipment": {
    "id": 10,
    "reference": "SHIP-001",
    "origin": { "city": "São Paulo", "state": "SP" },
    "destination": { "city": "Rio de Janeiro", "state": "RJ" },
    "client": { "id": 5, "name": "Client Name" }
  },
  "route": null,
  "related_documents": [
    { "id": 2, "document_type": "mdfe", "status": "authorized" }
  ]
}
```

**Status Codes:**
- `200` — Success
- `404` — Document not found
- `401` — Unauthenticated
- `403` — Forbidden (different tenant)

---

### Cancel Authorized CT-e

#### POST `/fiscal/documents/{id}/cancel`

**Description:** Cancel an authorized CT-e document. Only CT-e documents with status `authorized` can be cancelled.

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Fiscal document ID (must be CT-e) |

**Request Body:**
```json
{
  "reason": "Cancellation reason text (optional)",
  "justification": "Additional details (optional)"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "CT-e cancelled successfully",
  "document": {
    "id": 1,
    "status": "cancelled",
    "cancelled_at": "2024-05-15 14:30:00",
    "cancellation_reason": "Cancellation reason text"
  }
}
```

**Error Responses:**
```json
{
  "success": false,
  "message": "Only authorized CT-es can be cancelled",
  "status_code": 422
}
```

**Status Codes:**
- `200` — Success
- `400` — Invalid request
- `401` — Unauthenticated
- `403` — Forbidden (different tenant or not authorized to cancel)
- `404` — Document not found
- `422` — Document type not CT-e or status not authorized

---

### Download PDF

#### GET `/fiscal/documents/{id}/pdf`

**Description:** Download the CT-e/MDF-e document as PDF (if authorized status).

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Fiscal document ID |

**Response:** File (application/pdf)

**Status Codes:**
- `200` — Success (PDF stream)
- `403` — Document not authorized for download
- `404` — Document not found
- `500` — PDF generation failed

---

### Download XML

#### GET `/fiscal/documents/{id}/xml`

**Description:** Download the fiscal document XML.

**Authentication:** Required

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Fiscal document ID |

**Response:** File (application/xml or text/xml)

**Status Codes:**
- `200` — Success (XML stream)
- `404` — Document not found
- `500` — XML retrieval failed

---

## Controllers

### FiscalDocumentController

**Namespace:** `App\Http\Controllers`

**File:** `app/Http/Controllers/FiscalDocumentController.php`

#### Methods

##### `indexAll(Request $request): View`

Lists all fiscal documents for the user's tenant with filtering and pagination.

**Logic Flow:**
1. Get authenticated user's tenant_id
2. Build query: `FiscalDocument::where('tenant_id', $tenant_id)`
3. Apply filters:
   - `document_type`: filter by cte/mdfe
   - `status`: filter by exact status
   - `date_from`/`date_to`: filter by created_at range
   - `search`: search access_key (LIKE) or mitt_number
4. Sort by `sort_by` (created_at, status, mitt_number, etc.)
5. Eager load relationships: `shipment`, `route`, `client`
6. Paginate: 20 items per page
7. Return view with paginated documents and filter values

**Input Validation:**
```php
$request->validate([
    'document_type' => 'nullable|in:cte,mdfe',
    'status' => 'nullable|in:pending,validating,processing,authorized,rejected,cancelled,error',
    'date_from' => 'nullable|date_format:Y-m-d',
    'date_to' => 'nullable|date_format:Y-m-d',
    'search' => 'nullable|string|max:255',
    'sort_by' => 'nullable|in:created_at,authorized_at,status,mitt_number,document_type',
    'sort_order' => 'nullable|in:asc,desc',
    'page' => 'nullable|integer|min:1',
]);
```

---

##### `show(int $id): View`

Display detailed view of a single fiscal document.

**Logic Flow:**
1. Verify document belongs to authenticated user's tenant
2. Eager load: `shipment`, `route`, `shipment.client`
3. Fetch related documents (other fiscal docs for same shipment/route)
4. Return detailed view with all document data

---

##### `cancel(Request $request, int $id): RedirectResponse`

Cancel an authorized CT-e document.

**Validation:**
- Document must exist
- Must belong to authenticated user's tenant
- Must be document_type = 'cte'
- Must have status = 'authorized'

**Logic Flow:**
1. Find document
2. Verify authorization
3. Update status to 'cancelled'
4. Set cancelled_at timestamp
5. Log cancellation event
6. Redirect with success message

---

## Services

### FiscalService

**Namespace:** `App\Services`

**File:** `app/Services/FiscalService.php`

#### Methods

##### `syncShipmentCte(Shipment $shipment): ?FiscalDocument`

Synchronize a shipment with Mitt API to create or update its CT-e.

**Parameters:**
- `Shipment $shipment` — Shipment model instance

**Returns:** `FiscalDocument` or `null` if failed

**Process:**
1. Check if shipment already has CT-e
2. Call Mitt API: `POST /cte/create`
3. Generate access_key (35 digits, unique)
4. Create FiscalDocument record with status = 'pending'
5. Store XML response
6. Schedule status update (async job or listener)

**Exceptions:**
- `MittAPIException` — API call failed
- `ValidationException` — Invalid shipment data

---

##### `syncRouteMdfe(Route $route): ?FiscalDocument`

Synchronize a route with Mitt API to create or update its MDF-e.

**Parameters:**
- `Route $route` — Route model instance

**Returns:** `FiscalDocument` or `null`

**Process:**
1. Check if route has MDF-e
2. Consolidate all CT-es for route
3. Call Mitt API: `POST /mdfe/create`
4. Generate access_key
5. Create FiscalDocument record
6. Store XML response

---

##### `generateAccessKey(): string`

Generate a valid 35-digit access key (without verifier digit).

**Algorithm:**
```
Format: AABBNNNNNNNNNNNNNNNNNNNNNNNNNNNNCC
AA = State code (27 for SeFaz generic)
BB = Company CNPJ section
NNNNNNNNNNNNNNNNNN = Sequence
CC = Check digits (calculated)
```

**Returns:** 35-character string of digits

**Example:** `12345678901234567890123456789012345`

---

##### `updateDocumentStatus(FiscalDocument $doc, string $status): FiscalDocument`

Update fiscal document status and related timestamps.

**Parameters:**
- `FiscalDocument $doc` — Document to update
- `string $status` — New status from enum

**Status Transitions:**
```
pending → validating → processing → authorized
                             ↓
                         rejected
                             ↓
                          error

authorized → cancelled
```

**Side Effects:**
- Sets `authorized_at` when status = 'authorized'
- Sets `cancelled_at` when status = 'cancelled'
- Fires `FiscalDocumentStatusChanged` event
- Logs state change to audit table

**Returns:** Updated FiscalDocument

---

##### `validateAccessKey(string $key): bool`

Validate format and checksum of access key.

**Parameters:**
- `string $key` — 35-digit access key

**Validation:**
- Length must be exactly 35 characters
- All characters must be digits
- Checksum (last 2 digits) must be valid

**Returns:** `true` if valid, `false` otherwise

---

## Models & Relationships

### FiscalDocument Model

**Namespace:** `App\Models`

**File:** `app/Models/FiscalDocument.php`

**Database Table:** `fiscal_documents`

#### Columns

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | No | auto | Primary key |
| tenant_id | bigint | No | - | Multi-tenant isolation |
| document_type | enum | No | - | 'cte' or 'mdfe' |
| access_key | varchar(35) | No | - | Unique 35-digit key |
| mitt_number | bigint | Yes | - | MITT system document number |
| status | enum | No | 'pending' | See Status Enum section |
| shipment_id | bigint | Yes | - | FK to shipments |
| route_id | bigint | Yes | - | FK to routes |
| xml_content | longtext | Yes | - | Full XML document |
| pdf_content | longblob | Yes | - | PDF binary (if generated) |
| pdf_content_base64 | longtext | Yes | - | PDF as base64 string |
| created_at | timestamp | No | now | Creation timestamp |
| authorized_at | timestamp | Yes | - | When authorized by Sefaz |
| cancelled_at | timestamp | Yes | - | When cancelled |
| updated_at | timestamp | No | now | Last update timestamp |

#### Indexes

```sql
PRIMARY KEY (id)
UNIQUE KEY (access_key)
INDEX (tenant_id, document_type)
INDEX (tenant_id, status)
INDEX (shipment_id)
INDEX (route_id)
INDEX (created_at DESC)
```

#### Relationships

```php
// Belongs to Tenant (multi-tenant isolation)
public function tenant(): BelongsTo
{
    return $this->belongsTo(Tenant::class);
}

// Belongs to Shipment (CT-e)
public function shipment(): BelongsTo
{
    return $this->belongsTo(Shipment::class);
}

// Belongs to Route (MDF-e)
public function route(): BelongsTo
{
    return $this->belongsTo(Route::class);
}

// Can have invoices attached
public function invoices(): BelongsToMany
{
    return $this->belongsToMany(Invoice::class);
}
```

#### Methods

**Type Checking:**
```php
public function isCte(): bool
{
    return $this->document_type === 'cte';
}

public function isMdfe(): bool
{
    return $this->document_type === 'mdfe';
}
```

**Status Checking:**
```php
public function isAuthorized(): bool
{
    return $this->status === 'authorized';
}

public function isPending(): bool
{
    return $this->status === 'pending';
}

public function canBeCancelled(): bool
{
    return $this->isCte() && $this->isAuthorized();
}
```

**Scopes:**
```php
// Scope by tenant
public function scopeByTenant(Builder $query, int $tenantId): Builder
{
    return $query->where('tenant_id', $tenantId);
}

// Scope by type
public function scopeOfType(Builder $query, string $type): Builder
{
    return $query->where('document_type', $type);
}

// Scope by status
public function scopeOfStatus(Builder $query, string $status): Builder
{
    return $query->where('status', $status);
}

// Authorized documents only
public function scopeAuthorized(Builder $query): Builder
{
    return $query->where('status', 'authorized');
}
```

---

## Status Enum

Valid fiscal document statuses:

| Status | Code | Description | Transitions From | Transitions To |
|--------|------|-------------|------------------|-----------------|
| Pending | pending | Created, awaiting validation | - | validating, error |
| Validating | validating | Being validated by Sefaz | pending | processing, rejected, error |
| Processing | processing | Being processed by Sefaz | validating | authorized, rejected, error |
| Authorized | authorized | Approved by Sefaz | processing | cancelled |
| Rejected | rejected | Rejected by Sefaz | validating, processing | - |
| Cancelled | cancelled | Cancelled by user | authorized | - |
| Error | error | System error during processing | pending, validating, processing | - |

---

## Commands

### SyncMittDocuments

**Namespace:** `App\Console\Commands`

**File:** `app/Console/Commands/SyncMittDocuments.php`

#### Command

```bash
php artisan fiscal:sync-mitt [options]
```

#### Options

| Option | Type | Description |
|--------|------|-------------|
| `--tenant-id=ID` | integer | Sync only specific tenant |
| `--dry-run` | flag | Preview changes without saving |
| `--force` | flag | Skip confirmations |
| `--limit=N` | integer | Process max N documents |

#### Process

1. Get all tenants (or specific if `--tenant-id`)
2. For each tenant:
   - Get all Shipments without CT-e
   - For each shipment: `FiscalService::syncShipmentCte()`
   - Get all Routes without MDF-e
   - For each route: `FiscalService::syncRouteMdfe()`
3. Log results: X CT-es created, Y MDF-es created, Z failures
4. Return exit code 0 (success) or non-zero (failure)

#### Output

```bash
$ php artisan fiscal:sync-mitt
Syncing fiscal documents for all tenants...

[Tenant: Thiga] 
  CT-es: 45 created, 3 updated, 2 failed
  MDF-es: 12 created, 1 updated, 0 failed

[Tenant: Client B]
  CT-es: 8 created, 0 updated, 0 failed
  MDF-es: 2 created, 0 updated, 0 failed

Summary: 65 documents synced, 5 failures
Exit code: 0
```

---

## Factories & Seeders

### FiscalDocumentFactory

**Namespace:** `Database\Factories`

**File:** `database/factories/FiscalDocumentFactory.php`

**Usage in Tests:**
```php
$document = FiscalDocument::factory()
    ->for($tenant)
    ->for($shipment, 'shipment')
    ->create([
        'document_type' => 'cte',
        'status' => 'authorized',
    ]);
```

**Default Attributes:**
```php
[
    'tenant_id' => Tenant::factory(),
    'document_type' => 'cte',
    'access_key' => unique 35-digit string,
    'mitt_number' => random integer,
    'status' => 'pending',
    'shipment_id' => Shipment::factory(),
    'route_id' => null,
    'xml_content' => sample XML,
    'created_at' => Carbon::now(),
]
```

---

### FiscalDocumentSeeder

**Namespace:** `Database\Seeders`

**File:** `database/seeders/FiscalDocumentSeeder.php`

**Usage:**
```bash
php artisan db:seed --class=FiscalDocumentSeeder
```

**Generates:**
- 50-100 fiscal documents
- Mix of CT-e and MDF-e
- Varied statuses (pending, authorized, error, etc.)
- Valid access keys (35 digits)
- Random MITT numbers
- Associated shipments/routes
- Random timestamps

---

## Error Handling

### Exception Classes

#### MittAPIException

Thrown when Mitt API call fails.

```php
throw new MittAPIException("API request failed: {$response->getStatusCode()}");
```

**Status Codes:**
- 400: Bad request (invalid shipment data)
- 401: Unauthorized (invalid credentials)
- 409: Conflict (document already exists)
- 500: Server error (Mitt system issue)

#### FiscalDocumentException

Thrown for business logic violations.

```php
throw new FiscalDocumentException("Only authorized CT-es can be cancelled");
```

### Error Response Format

```json
{
  "success": false,
  "message": "Error message",
  "error_code": "ERROR_CODE",
  "details": {
    "field": "error details"
  }
}
```

---

## Response Examples

### Success: List Documents

**Request:**
```
GET /fiscal/documents?document_type=cte&status=authorized&page=1
Accept: application/json
```

**Response (200 OK):**
```json
{
  "documents": [
    {
      "id": 1,
      "tenant_id": 1,
      "document_type": "cte",
      "access_key": "12345678901234567890123456789012345",
      "mitt_number": 123456,
      "status": "authorized",
      "shipment_id": 10,
      "route_id": null,
      "created_at": "2024-05-15T10:30:00Z",
      "authorized_at": "2024-05-15T12:00:00Z",
      "cancelled_at": null,
      "shipment": {
        "id": 10,
        "reference": "SHIP-001",
        "origin": "São Paulo",
        "destination": "Rio de Janeiro"
      },
      "route": null
    }
  ],
  "pagination": {
    "total": 42,
    "per_page": 20,
    "current_page": 1,
    "last_page": 3,
    "from": 1,
    "to": 20
  }
}
```

---

### Error: Invalid Status Filter

**Request:**
```
GET /fiscal/documents?status=invalid_status
```

**Response (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "status": [
      "The status field must be one of: pending, validating, processing, authorized, rejected, cancelled, error."
    ]
  }
}
```

---

### Error: Document Not Found

**Request:**
```
GET /fiscal/documents/999
```

**Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Fiscal document not found",
  "error_code": "NOT_FOUND"
}
```

---

## Database Schema

### fiscal_documents Table

```sql
CREATE TABLE fiscal_documents (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL,
  document_type ENUM('cte', 'mdfe') NOT NULL,
  access_key VARCHAR(35) NOT NULL UNIQUE,
  mitt_number BIGINT UNSIGNED,
  status ENUM('pending', 'validating', 'processing', 'authorized', 'rejected', 'cancelled', 'error') NOT NULL DEFAULT 'pending',
  shipment_id BIGINT UNSIGNED,
  route_id BIGINT UNSIGNED,
  xml_content LONGTEXT,
  pdf_content LONGBLOB,
  pdf_content_base64 LONGTEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  authorized_at TIMESTAMP NULL,
  cancelled_at TIMESTAMP NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE SET NULL,
  FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE SET NULL,
  
  KEY idx_tenant_type (tenant_id, document_type),
  KEY idx_tenant_status (tenant_id, status),
  KEY idx_shipment (shipment_id),
  KEY idx_route (route_id),
  KEY idx_created (created_at DESC),
  KEY idx_authorized (authorized_at DESC)
);
```

---

## Testing

All endpoints are covered by automated tests:

- **Listing Tests:** `tests/Feature/FiscalDocumentListingTest.php` (12 tests)
- **Filtering Tests:** `tests/Feature/FiscalDocumentFilterTest.php` (15 tests)
- **Sync Tests:** `tests/Feature/FiscalDocumentSyncTest.php` (8 tests)
- **Service Tests:** `tests/Unit/FiscalServiceTest.php` (13 tests)
- **E2E Tests:** `tests/Feature/FiscalDocumentE2ETest.php` (10 tests)

Run tests:
```bash
php artisan test
php artisan test --coverage
php artisan test --parallel
```

---

## Rate Limiting

No rate limiting currently enforced on fiscal document endpoints. Consider implementing:

```php
// In routes/web.php
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/fiscal/documents', [FiscalDocumentController::class, 'indexAll']);
});
```

---

## Security Considerations

1. **Multi-Tenant Isolation:** All queries must include `->where('tenant_id', $user->tenant_id)`
2. **Authentication:** Fiscal document endpoints require authenticated user
3. **Authorization:** Users can only view/modify documents in their own tenant
4. **Sensitive Data:** XML and PDF content stored encrypted in database
5. **Audit Logging:** All document state changes logged for compliance

---

## Related Documentation

- [Testing Guide](TESTING_FISCAL_DOCUMENTS.md) — How to run and write tests
- [User Guide](FISCAL_DOCUMENTS_GUIDE.md) — End-user documentation
- [Runbooks](RUNBOOKS_FISCAL.md) — Operations and troubleshooting
- [Deployment Checklist](DEPLOYMENT_CHECKLIST.md) — Pre-deployment verification

---

**Last Updated:** May 22, 2026  
**Version:** 1.0  
**Maintainer:** Engineering Team
