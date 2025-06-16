# Discovery Offer Expiry Implementation

## Overview

Implemented automatic discovery cancellation when the offer expires and prevention of customer actions (approve/reject) after the offer valid until date.

## Features Implemented

### 1. Discovery Model Enhancements

Added new methods to `app/Models/Discovery.php`:

**Expiry Checking:**

```php
public function isOfferExpired(): bool
public function canCustomerAct(): bool
public function getDaysUntilExpiry(): ?int
```

**Database Scopes:**

```php
public function scopeExpiredPending($query)
```

**Automatic Cancellation:**

```php
public function cancelDueToExpiry(): bool
```

### 2. Controller Updates

Updated `app/Http/Controllers/DiscoveryController.php`:

**Customer Approve Method:**

- Checks if offer has expired before allowing approval
- Automatically cancels expired discoveries
- Shows appropriate error messages with expiry date

**Customer Reject Method:**

- Checks if offer has expired before allowing rejection
- Automatically cancels expired discoveries
- Shows appropriate error messages with expiry date

### 3. Enhanced Shared View

Updated `resources/views/discovery/shared.blade.php`:

**Visual Expiry Indicators:**

- Shows "Teklif Süresi Doldu" badge for expired offers
- Shows countdown (e.g., "3 gün kaldı", "Bugün sona eriyor") for valid offers
- Color-coded badges (red for expired/urgent, yellow for soon-to-expire, green for safe)

**Action Button Control:**

- Hides approve/reject buttons when offer is expired
- Shows expiry message instead of action buttons

### 4. Automatic Cancellation Command

Created `app/Console/Commands/CancelExpiredDiscoveries.php`:

**Features:**

- Finds all pending discoveries with expired offers
- Displays detailed table of expired discoveries
- Supports dry-run mode (`--dry-run`)
- Interactive confirmation before cancellation
- Logs all automatic cancellations
- Comprehensive reporting

**Usage:**

```bash
php artisan discoveries:cancel-expired           # Cancel expired discoveries
php artisan discoveries:cancel-expired --dry-run # Preview what would be cancelled
```

### 5. Scheduled Task

Added to `routes/console.php`:

```php
Schedule::command('discoveries:cancel-expired')
    ->dailyAt('09:00')
    ->timezone('Europe/Istanbul')
    ->description('Cancel discoveries with expired offer dates');
```

### 6. Transaction Logging

Enhanced `app/Services/TransactionLogService.php`:

**New Method:**

```php
public static function logSystemAction(Discovery $discovery, string $action, string $description, array $metadata = [])
```

Logs automatic cancellations with detailed metadata for audit trail.

## User Experience

### For Customers

1. **Visual Feedback:** Clear indication of offer status and time remaining
2. **Prevention:** Cannot approve/reject expired offers
3. **Clear Messages:** Descriptive error messages when attempting to act on expired offers

### For Handymen/Admins

1. **Automatic Processing:** Expired discoveries are automatically cancelled
2. **Audit Trail:** All automatic cancellations are logged with transaction logs
3. **Manual Control:** Can run cancellation command manually if needed
4. **Monitoring:** Dry-run option allows checking for expired discoveries

## Technical Implementation

### Expiry Logic

- Offers expire at the end of the `offer_valid_until` date
- Uses `endOfDay()` for expiry checks to allow actions throughout the expiry date
- Automatic cancellation adds explanatory note to `note_to_handi` field

### Visual Indicators

- **Red Badge:** "Teklif Süresi Doldu" - Offer expired
- **Red Badge:** "Bugün sona eriyor" / "1 gün kaldı" - Urgent (≤1 day)
- **Yellow Badge:** "X gün kaldı" - Warning (2-3 days)
- **Green Badge:** "X gün kaldı" - Safe (>3 days)

### Automatic Cancellation

- Runs daily at 09:00 Istanbul time
- Only affects pending discoveries
- Preserves existing notes while adding cancellation reason
- Logs actions for audit trail

## Testing

Created comprehensive test suite `tests/Feature/DiscoveryExpiryTest.php`:

### Test Coverage

1. **Basic Functionality:**

   - `test_discovery_can_check_if_offer_expired`
   - `test_discovery_can_check_if_customer_can_act`
   - `test_discovery_calculates_days_until_expiry_correctly`

2. **Customer Action Prevention:**

   - `test_customer_cannot_approve_expired_discovery`
   - `test_customer_cannot_reject_expired_discovery`

3. **Automatic Cancellation:**

   - `test_discovery_can_be_cancelled_due_to_expiry`
   - `test_expired_pending_scope_works_correctly`

4. **Command Functionality:**
   - `test_cancel_expired_discoveries_command_works`
   - `test_cancel_expired_discoveries_command_dry_run`

All tests pass successfully, ensuring robust functionality.

## Security & Data Integrity

### Validation

- Double-checks expiry status in both frontend and backend
- Prevents race conditions by checking expiry at action time
- Maintains data consistency with automatic status updates

### Audit Trail

- All automatic cancellations are logged with system performer type
- Includes detailed metadata (customer info, expiry date, discovery ID)
- Preserves original notes while adding cancellation reason

## Files Modified

### Core Functionality

1. `app/Models/Discovery.php` - Added expiry methods and scopes
2. `app/Http/Controllers/DiscoveryController.php` - Updated customer action methods
3. `resources/views/discovery/shared.blade.php` - Enhanced UI with expiry indicators

### Automation

4. `app/Console/Commands/CancelExpiredDiscoveries.php` - New command for batch processing
5. `routes/console.php` - Added scheduled task
6. `app/Services/TransactionLogService.php` - Added system action logging

### Testing

7. `tests/Feature/DiscoveryExpiryTest.php` - Comprehensive test suite

## Implementation Status

✅ **COMPLETED** - Discovery offers now automatically expire and prevent customer actions after the valid until date. Automatic cancellation runs daily to maintain data integrity.
