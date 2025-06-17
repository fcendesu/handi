# Discovery Expiry Scheduling Analysis & Recommendations

## Current Scheduling Issue Analysis

### ⚠️ **Problem with 00:00 (Midnight) Scheduling**

When you schedule the expiry command at `00:00`, you create several potential issues:

#### 1. **Timing Logic Consistency**

- `isOfferExpired()` uses `endOfDay()` - offers valid until 23:59:59 on expiry date
- `scopeExpiredPending()` uses `startOfDay()` - finds discoveries to cancel at 00:00:01+
- **Result**: Discoveries are cancelled the day AFTER they actually expire (which is correct)

#### 2. **Server Performance Issues**

- **Midnight conflicts**: Most system maintenance runs at midnight
  - Database backups
  - Log rotations
  - Cache clearing
  - Other scheduled jobs
- **Resource contention** could cause timeouts or poor performance

#### 3. **Business Considerations**

- **Customer expectations**: If offer expires on June 16, customers expect it valid until end of June 16
- **Support load**: Customer confusion if they try to act on offers that were just cancelled

## ✅ **Recommended Solutions**

### Option 1: Early Morning (Recommended)

```php
Schedule::command('discoveries:cancel-expired')
    ->dailyAt('01:30')  // 1:30 AM - After midnight maintenance, before business hours
    ->timezone('Europe/Istanbul')
    ->description('Cancel discoveries with expired offer dates');
```

**Benefits:**

- Avoids midnight maintenance conflicts
- Ensures offers truly expired (past endOfDay)
- Runs before business hours start
- Server resources available

### Option 2: Late Night

```php
Schedule::command('discoveries:cancel-expired')
    ->dailyAt('23:30')  // 11:30 PM - Before midnight maintenance
    ->timezone('Europe/Istanbul')
    ->description('Cancel discoveries with expired offer dates');
```

**Benefits:**

- Processes same-day expirations quickly
- Avoids midnight conflicts
- Cleans up before end of day

### Option 3: Multiple Times (Enterprise)

```php
// Process expired discoveries multiple times for better UX
Schedule::command('discoveries:cancel-expired')
    ->cron('30 1,9,17 * * *')  // 01:30, 09:30, 17:30
    ->timezone('Europe/Istanbul')
    ->description('Cancel expired discoveries (3x daily)');
```

**Benefits:**

- Faster processing of expired offers
- Better customer experience
- Redundancy in case of failures

## 🔧 **Additional Improvements**

### 1. Add Scheduling Monitoring

```php
Schedule::command('discoveries:cancel-expired')
    ->dailyAt('01:30')
    ->timezone('Europe/Istanbul')
    ->description('Cancel discoveries with expired offer dates')
    ->onSuccess(function () {
        \Log::info('Discovery expiry job completed successfully');
    })
    ->onFailure(function () {
        \Log::error('Discovery expiry job failed');
        // Send notification to admin
    });
```

### 2. Add Resource Management

```php
Schedule::command('discoveries:cancel-expired')
    ->dailyAt('01:30')
    ->timezone('Europe/Istanbul')
    ->withoutOverlapping(10) // Prevent overlapping executions
    ->runInBackground()      // Don't block other scheduled tasks
    ->description('Cancel discoveries with expired offer dates');
```

### 3. Consider Business Hours

```php
// Only run on business days if needed
Schedule::command('discoveries:cancel-expired')
    ->dailyAt('01:30')
    ->weekdays()  // Monday-Friday only
    ->timezone('Europe/Istanbul')
    ->description('Cancel discoveries with expired offer dates');
```

## 🎯 **Final Recommendation**

Use **Option 1 (01:30)** for most cases:

```php
Schedule::command('discoveries:cancel-expired')
    ->dailyAt('01:30')
    ->timezone('Europe/Istanbul')
    ->withoutOverlapping(10)
    ->runInBackground()
    ->description('Cancel discoveries with expired offer dates')
    ->onFailure(function () {
        \Log::error('Discovery expiry job failed - manual intervention may be required');
    });
```

This provides:

- ✅ Avoids midnight conflicts
- ✅ Ensures true expiry
- ✅ Good performance
- ✅ Error monitoring
- ✅ Prevents job overlap
- ✅ Background execution
