# Fiscal Documents Management Guide

## Overview

The Thiga TMS platform provides a comprehensive system for managing fiscal documents, including CT-e (Conhecimento de Transporte Eletrônico) and MDF-e (Manifesto Eletrônico de Documento Fiscal) documents.

## Accessing Fiscal Documents

### Main Dashboard
- **URL**: `/fiscal` or `/fiscal/documents`
- **Name**: Fiscal Documents
- **Requires**: Authentication + Tenant access

### Separate Views
- **CT-e List**: `/fiscal/ctes`
- **MDF-e List**: `/fiscal/mdfes`

## Features

### 1. Consolidated Listing
View all CT-e and MDF-e documents in a single, unified interface with side-by-side filtering and searching capabilities.

**Columns:**
- **Type**: Document type (CT-e/MDF-e) with icon
- **Number**: MITT document number
- **Access Key**: Unique identifier (truncated display)
- **Related Entity**: Link to associated Shipment or Route
- **Status**: Color-coded status badge with emoji indicator
- **Created Date**: Document creation timestamp and authorization date (if applicable)
- **Actions**: View, Download PDF/XML, Cancel (CT-e only)

### 2. Powerful Filtering

#### By Document Type
- All Types
- CT-e only
- MDF-e only

#### By Status
- Pending (🟡) — Waiting to be sent to Sefaz
- Validating (🔵) — Initial validation in progress
- Processing (🟣) — Being processed by Sefaz
- Authorized (🟢) — Approved and valid
- Rejected (🔴) — Rejected by Sefaz
- Cancelled (⚪) — Document cancelled
- Error (🔴) — Processing error occurred

#### By Date Range
- Date From: Filter documents created on or after this date
- Date To: Filter documents created on or before this date

#### By Search
- Access Key: Unique fiscal document identifier
- MITT Number: Identification number from Mitt API

### 3. Status Indicators

Each document displays its current status with:
- **Color Badge**: Visual status representation
- **Emoji**: Quick visual reference
- **Text**: Human-readable status name

**Authorized vs. Pending:**
- Authorized documents show both `created_at` and `authorized_at` dates
- Pending documents show creation date only

### 4. Related Entity Links

Click document type to navigate:
- **CT-e** → Associated Shipment details
- **MDF-e** → Associated Route details

Display client/driver information for quick identification.

### 5. Action Buttons

#### View (👁️)
Opens the detailed view of the fiscal document with:
- Full metadata
- XML content (if available)
- PDF (if authorized)
- Status timeline
- Detailed error messages (if applicable)

#### Download PDF (📄)
- Available only for authorized documents
- Opens/downloads the official PDF version
- Suitable for printing and archival

#### Download XML (📝)
- Available only for authorized documents
- Opens/downloads the raw XML file
- For integration and validation purposes

#### Cancel CT-e (❌)
- Available only for authorized CT-e documents
- Requires confirmation
- Cannot be undone
- Provides justification field (15-255 characters)

### 6. Pagination

- **Items per page**: 20 documents
- **Navigation**: Previous/Next buttons + page numbers
- **Totals**: Shows "1-20 of 247 documents" format

## Workflow Examples

### Example 1: Find All Pending CT-es

1. Navigate to `/fiscal/documents`
2. Under Filters:
   - Set Document Type: "CT-e"
   - Set Status: "Pending"
   - Click "Apply"
3. Review all pending CT-es
4. For each pending document:
   - Check creation date and time
   - Verify related shipment details
   - Monitor for status changes

### Example 2: Search by Access Key

1. Navigate to `/fiscal/documents`
2. Under Filters:
   - Enter full or partial access key in "Search" field
   - Click "Apply"
3. Result shows exact document match
4. Click "View" to see full details

### Example 3: Filter by Date Range

1. Navigate to `/fiscal/documents`
2. Under Filters:
   - Set Date From: `2024-05-01`
   - Set Date To: `2024-05-31`
   - Click "Apply"
3. Review all documents created in May 2024
4. Combine with other filters for further refinement

### Example 4: Cancel an Authorized CT-e

1. Navigate to `/fiscal/documents`
2. Filter for authorized CT-es:
   - Set Document Type: "CT-e"
   - Set Status: "Authorized"
   - Click "Apply"
3. Find the CT-e to cancel
4. Click ❌ (Cancel button)
5. Confirm action in modal
6. Status changes to "Cancelled"

## Quick Links

Sidebar quick links for common tasks:
- **Pending Documents**: Pre-filtered view of pending docs
- **Authorized Documents**: Pre-filtered view of authorized docs
- **Documents with Errors**: Pre-filtered view of failed docs

## API Integration

### Synchronize Mitt Documents

Run command to sync documents from Mitt API:

```bash
php artisan fiscal:sync-mitt
```

**Options:**
```bash
# Sync only for specific tenant
php artisan fiscal:sync-mitt --tenant-id=123
```

**Output:**
```
✅ Synchronization complete!
├─ CT-e: 25
├─ MDF-e: 20
└─ Total: 45
```

## Data Structure

### Fiscal Document Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Unique identifier |
| `tenant_id` | UUID | Organization/tenant |
| `document_type` | enum | 'cte' or 'mdfe' |
| `shipment_id` | FK | Associated shipment (CT-e) |
| `route_id` | FK | Associated route (MDF-e) |
| `mitt_id` | string | ID from Mitt system |
| `mitt_number` | integer | Document number |
| `access_key` | string | Unique 35-digit key |
| `status` | enum | See Status Indicators section |
| `xml` | text | Raw XML content |
| `pdf_url` | string | URL to authorized PDF |
| `xml_url` | string | URL to authorized XML |
| `error_message` | string | Error description if failed |
| `error_details` | JSON | Detailed error information |
| `mitt_response` | JSON | Full API response from Mitt |
| `sent_at` | timestamp | When sent to Sefaz |
| `authorized_at` | timestamp | When authorized by Sefaz |
| `cancelled_at` | timestamp | When cancelled |
| `created_at` | timestamp | Record creation time |
| `updated_at` | timestamp | Last update time |

## Performance Considerations

### Query Optimization

- Queries use indexes on: `(tenant_id, document_type, status)`
- Related data loaded efficiently with `with()` clause
- Pagination prevents loading excessive data

### Best Practices

1. **Use date ranges** to limit result sets
2. **Combine filters** for specific searches
3. **Check pagination** for large result sets
4. **Bookmark quick links** for frequently accessed views

## Troubleshooting

### Documents Not Showing

**Possible Causes:**
1. Documents not yet synchronized from Mitt
2. Wrong date range in filter
3. Incorrect tenant/organization context

**Solution:**
```bash
# Manually sync Mitt documents
php artisan fiscal:sync-mitt
```

### Access Key Not Found

**Issue**: Searching for an access key returns no results

**Solution:**
1. Verify the correct access key (should be 35 digits)
2. Check if document belongs to correct tenant
3. Verify document hasn't been deleted
4. Try broader search (e.g., first 8 characters)

### Status Not Updating

**Issue**: Document status appears stale

**Solution:**
1. Refresh the page (Ctrl+F5)
2. Run manual sync:
   ```bash
   php artisan fiscal:sync-mitt
   ```
3. Check Mitt system directly for actual status

### PDF/XML Not Available

**Issue**: Download buttons show but files don't download

**Possible Causes:**
1. Document not yet authorized
2. URL expired
3. Permissions issue

**Solution:**
1. Wait for authorization to complete
2. Refresh page to get updated URLs
3. Contact support if persists

## Related Documentation

- **TROUBLESHOOTING_FISCAL_DOCUMENTS.md** — Advanced troubleshooting
- **SHIPMENT_VALUE_FIELDS.md** — Invoice and shipment structures
- **ROUTE_CALCULATION_EXPLANATION.md** — Route costing

## Support

For additional help:
- Check Troubleshooting section above
- Review related documentation
- Contact technical support team
