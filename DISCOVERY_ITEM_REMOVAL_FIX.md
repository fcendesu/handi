# Discovery Item Removal Fix

## Issue

When updating a discovery with an empty item list (removing all items), the update didn't work correctly. Items remained attached to the discovery even after the user removed them all and submitted the form.

## Root Cause

The issue was in the `DiscoveryController` update methods (both web and API). The code only processed items when `isset($validated['items'])` was true, but when no items were selected:

1. **Web forms**: The frontend only creates hidden inputs when `selectedItems.length > 0`, so when there are no items, the `items` key is not present in the request.
2. **API calls**: When sending an empty items array or omitting the items key entirely, the validation would pass but the item processing was skipped.

## Solution

### Backend Changes

Updated both `update()` and `apiUpdate()` methods in `DiscoveryController.php`:

**Before:**

```php
// Update items if present
if (isset($validated['items'])) {
    // Log detachment and remove existing items
    // Attach new items
}
```

**After:**

```php
// Always update items (even if empty array or not present)
// Log detachment of existing items before removing them
foreach ($discovery->items as $existingItem) {
    // Log detachment
}

// Remove existing items
$discovery->items()->detach();

// Attach new items if any are provided
if (!empty($validated['items'])) {
    // Attach items
}
```

### Frontend Enhancement

Added hidden inputs to ensure the `items` parameter is always present in form submissions:

**discovery/index.blade.php** and **discovery/show.blade.php:**

```html
<!-- Hidden input to ensure items array is present even when empty -->
<input type="hidden" name="items" value="" />
```

## Testing

Created comprehensive tests that verify:

1. ✅ **API updates with empty items array** - Works correctly
2. ✅ **API updates with no items key** - Works correctly
3. ✅ **Web form updates with no selected items** - Works correctly

## Impact

- **Fixed**: Users can now successfully remove all items from a discovery
- **Consistent**: Both web interface and API behave the same way
- **Logged**: All item removals are properly logged via TransactionLogService
- **Backward Compatible**: Existing functionality with items remains unchanged

## Files Modified

1. `app/Http/Controllers/DiscoveryController.php` - Fixed update logic
2. `resources/views/discovery/index.blade.php` - Added hidden input
3. `resources/views/discovery/show.blade.php` - Added hidden input

## Result

Users can now successfully update discoveries to have zero items, removing all previously selected items as expected.
