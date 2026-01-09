# Shipment Value Fields Documentation

## Overview

The `Shipment` model has three distinct value fields that serve different purposes in the transportation and logistics system:

1. **`value`** - Total value of the goods/merchandise (invoice value)
2. **`goods_value`** - Value of the goods (same as `value` in most cases)
3. **`freight_value`** - Freight/shipping cost value

## Field Definitions

### `value` (decimal:2)
- **Purpose**: Total value of the goods/merchandise being transported
- **Source**: Usually comes from the invoice (NF) or CT-e document
- **Usage**: 
  - Displayed in route summaries
  - Used for calculating total route revenue (`total_revenue`)
  - Used in financial reports and summaries
  - Used for freight calculation (as `invoiceValue` parameter)

### `goods_value` (decimal:2)
- **Purpose**: Value of the goods (synonymous with `value` in most cases)
- **Source**: Usually the same as `value` from CT-e or invoice
- **Usage**:
  - Stored for compatibility and historical reasons
  - Used in route settings as `total_goods_value`
  - Currently maintained in sync with `value`

### `freight_value` (decimal:2, nullable)
- **Purpose**: The freight/shipping cost charged to the customer
- **Source**: 
  - Calculated using `FreightCalculationService` based on weight, volume, destination, etc.
  - Manually entered when creating routes with addresses
  - Extracted from freight calculation metadata
- **Usage**:
  - Used in invoicing (`InvoiceItem.freight_value`)
  - Used for freight cost calculations
  - May differ from `value` as it represents the shipping cost, not the goods value

## Current Implementation

### When Creating Shipments from CT-e XML:
```php
'value' => $cteData['value'] ?? 0,           // Invoice/goods value from CT-e
'goods_value' => $cteData['value'] ?? 0,     // Same as value
'freight_value' => null,                     // Not set from XML (calculated separately)
```

### When Creating Shipments from Addresses:
```php
'freight_value' => $deliveryAddress['freight_value'] ?? null,  // User input
'value' => $deliveryAddress['freight_value'] ?? 0,             // Currently same as freight_value
'goods_value' => $deliveryAddress['freight_value'] ?? 0,      // Currently same as freight_value
```

**Note**: Currently, when creating from addresses, all three fields receive the same value (freight_value). This is a simplification that may need review.

## Recommendations

### Option 1: Keep Current Structure (Recommended)
- **`value`**: Always represents the invoice/goods value
- **`goods_value`**: Keep as alias/synonym of `value` for compatibility
- **`freight_value`**: Represents the freight cost (may be calculated or manually entered)

**When creating from addresses**: 
- If user enters a "freight value", it should go to `freight_value`
- `value` and `goods_value` should remain 0 or be calculated separately if invoice value is available

### Option 2: Unify Fields
- Remove `goods_value` (redundant with `value`)
- Keep `value` for goods/invoice value
- Keep `freight_value` for freight cost

## Usage in Code

### Route Revenue Calculation
```php
$totalRevenue = $route->shipments()->sum('value') ?? 0;  // Uses 'value', not 'freight_value'
```

### Freight Calculation
```php
$freightService->calculate(
    $tenant,
    $destination,
    $weight,
    $volume,
    $shipment->value,  // Uses 'value' as invoice value for ad valorem calculations
    []
);
```

### Invoicing
```php
'freight_value' => $shipment->freight_value ?? $this->calculateFreightValue($shipment)
```

## Migration Considerations

If we decide to change the current behavior:
1. Update `RouteController::processAddresses()` to separate freight_value from value
2. Add a field for invoice/goods value when creating from addresses
3. Update views to display the correct field
4. Consider adding validation to ensure value and freight_value are not confused

## Current Status

✅ **Observer Pattern**: `ShipmentObserver` automatically updates route `total_revenue` when shipments are created/updated/deleted

✅ **Consistency**: Both XML and address creation now populate `value` and `goods_value` fields

⚠️ **Clarification Needed**: When creating from addresses, should `freight_value` input be treated as:
- A) The freight cost (goes to `freight_value`, `value` stays 0)
- B) The invoice/goods value (goes to `value` and `goods_value`, `freight_value` calculated separately)
- C) Both (user enters freight, system calculates or estimates goods value)

**Current implementation uses option C (all fields get the same value)**, which may not be semantically correct but works for the current use case.







