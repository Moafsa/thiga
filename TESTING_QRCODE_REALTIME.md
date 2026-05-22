# 🧪 Testing WhatsApp Real-Time QR Code Implementation

## Overview
This document provides comprehensive testing procedures for the new real-time QR code functionality and WhatsApp AI agent diagnostics.

## What Was Implemented

### 1. **Real-Time QR Code Endpoints**
- `POST /whatsapp/integrations/{integration}/connect` - Initiates WuzAPI session and fetches QR code
- `GET /whatsapp/integrations/{integration}/check-status` - Checks connection status in real-time

### 2. **Backend Methods**
```php
// In WhatsAppIntegrationManager
- startSessionInWuzapi(WhatsAppIntegration $integration, string $sessionName): bool
- getStatus(WhatsAppIntegration $integration): string
- getQrCode(WhatsAppIntegration $integration): ?string
```

### 3. **Frontend Changes**
- JavaScript polling every 5 seconds (matching ConextBot behavior)
- Auto-transitions to "✓ Conectado!" when connection detected
- Auto-reloads page after successful connection

---

## Testing Procedures

### Part 1: Real-Time QR Code Testing

#### Step 1: Navigate to WhatsApp Integration Settings
```
1. Open application in browser
2. Go to: Settings → Integrations → WhatsApp
3. Click button "Conectar WhatsApp" or "+ Nova Integração"
```

#### Step 2: Verify QR Code Modal Opens
```
Expected behavior:
- Modal dialog appears
- Shows "Carregando..." (Loading) spinner
- Modal title: "Conectar WhatsApp"
```

#### Step 3: Verify QR Code Generation (New Endpoint)
```
Timing: QR code should appear within 2 seconds
Expected behavior:
- Loading spinner disappears
- QR code image appears (SVG or image format)
- QR code should be scannable

What's happening behind the scenes:
1. Frontend calls: POST /whatsapp/integrations/{integration}/connect
2. Backend:
   a. Creates session in WuzAPI with session_name = "tenant-{id}-integration-{id}"
   b. Configures webhook URL for incoming messages
   c. Fetches QR code from WuzAPI
   d. Returns session_name, status, qr_code as JSON
3. Frontend starts polling every 5 seconds for status updates
```

#### Step 4: Real-Time Status Polling
```
Timing: Every 5 seconds, check for connection status
Expected behavior during polling:
- No visible changes while waiting for scan
- Console should show periodic requests to: GET /whatsapp/integrations/{integration}/check-status
```

#### Step 5: Scan QR Code with WhatsApp
```
1. Open WhatsApp on your mobile device
2. Go to: Settings → Linked Devices → Link a Device (for desktop/web)
3. Point camera at QR code in modal
4. Scan the QR code
```

#### Step 6: Verify Automatic Connection Detection
```
Expected behavior:
1. Within 5 seconds of scan, modal updates to show:
   - ✓ icon in green
   - Text: "Conectado com sucesso! Fechando..."
   - Success message in green color (rgba(46, 204, 113))

2. Modal closes automatically after 2 seconds

3. Page reloads automatically

4. WhatsApp integration status shows as "connected"
```

#### Step 7: Verify Integration Status Update
```
After page reload:
- Integration row shows: ✓ Connected
- Connection time is updated
- Can now see WhatsApp's display_phone (the linked WhatsApp account)
```

---

### Part 2: WhatsApp AI Agent Diagnostics

#### Understanding the Message Flow

```
Incoming WhatsApp Message (from user phone)
         ↓
WuzAPI receives message via webhook
         ↓
POST /api/webhooks/whatsapp (WebhookController@whatsapp)
         ↓
Authenticates integration via token or instanceName
         ↓
WebhookController::handleMessage()
         ↓
WhatsAppAiService::processMessage()
         ↓
Check 1: Is AI enabled for tenant? ❓
         ↓
Check 2: Does tenant have OpenAI API key? ❓
         ↓
Check 3: Generate AI response ❓
         ↓
Check 4: Does integration have user token? ❓
         ↓
WuzAPI::sendTextMessage() - Send response
         ↓
AI response appears in WhatsApp chat
```

#### Diagnostic Check 1: Is AI Enabled?

**How to check:**
1. In application, go to: `Settings → WhatsApp AI`
2. Look for toggle: "Enable AI Responses"
3. Should be `ON` (green/enabled)

**If disabled:**
- No AI responses will be sent for ANY WhatsApp integration
- Solution: Turn ON the toggle

**CLI Check:**
```bash
docker-compose exec app php artisan tinker
> $tenant = App\Models\Tenant::first();
> $metadata = $tenant->metadata ?? [];
> $aiSettings = $metadata['whatsapp_ai'] ?? [];
> $aiSettings['ai_enabled'] ?? false  # Should return true
```

#### Diagnostic Check 2: OpenAI API Key

**How to check:**
1. Method A - Via application UI:
   - `Settings → WhatsApp AI`
   - Look for "OpenAI API Key" field
   - Should be filled with `sk-...`

2. Method B - Via environment:
   ```bash
   cat .env | grep OPENAI
   # Should show: OPENAI_API_KEY=sk-...
   ```

**If not configured:**
- AI will return error message: "A inteligência artificial não está configurada no momento."
- Solution: Set OpenAI API key in settings or `.env`

**CLI Check:**
```bash
docker-compose exec app php artisan tinker
> $tenant = App\Models\Tenant::first();
> $apiKey = $tenant->resolveOpenAiApiKey();
> $apiKey ? "✓ Configured" : "✗ Not configured"
```

#### Diagnostic Check 3: Integration Token

**How to check:**
1. In database, check if integration has a token:
   ```bash
   docker-compose exec app php artisan tinker
   > $integration = App\Models\WhatsAppIntegration::first();
   > $token = $integration->getUserToken();
   > $token ? "✓ Token exists" : "✗ No token"
   ```

**If no token:**
- AI response generated but not sent back to user
- Solution: Recreate the integration to generate a new token

#### Diagnostic Check 4: WuzAPI Connectivity

**Check if WuzAPI is running:**
```bash
curl http://localhost:21465/health
# Should return: {"status":"ok"} or similar
```

**If WuzAPI not responding:**
- Messages won't be sent back
- Check Docker logs:
  ```bash
  docker-compose logs wuzapi | tail -50
  ```

#### Diagnostic Check 5: Recent Logs

**View all WhatsApp-related errors:**
```bash
docker-compose exec app tail -100 storage/logs/laravel.log | grep -i "whatsapp\|openai"
```

**Expected log entries for successful flow:**
```
[2026-05-22 22:30:15] local.INFO: WhatsApp webhook received
[2026-05-22 22:30:16] local.INFO: WhatsApp AI processing: AI response sent
```

**Error patterns to look for:**
```
✗ "OpenAI API key not configured"
✗ "OpenAI Error: invalid_api_key"
✗ "WuzAPI session not found"
✗ "WhatsApp AI processing error"
```

---

## Troubleshooting Guide

### Problem: QR Code doesn't appear

**Possible causes:**
1. WuzAPI not running
   - Solution: Check `docker-compose logs wuzapi`
   - Restart: `docker-compose restart wuzapi`

2. New routes not loaded
   - Solution: Clear cache
   ```bash
   docker-compose exec app php artisan route:cache
   docker-compose exec app php artisan config:cache
   ```

3. Session name not being set correctly
   - Check logs for errors in `startSessionInWuzapi()`
   - Verify tenant_id and integration_id are correct

**Debug steps:**
1. Open browser DevTools (F12)
2. Go to Console tab
3. Look for fetch errors calling `/whatsapp/integrations/{id}/connect`
4. Check Network tab for response details

### Problem: "Conectado!" message appears but not actually connected

**Possible causes:**
1. Status check is incorrectly reporting connected
   - Check WuzAPI session status manually
   - Verify session_name is set in database

2. Connection is lost after detection
   - Check if WhatsApp mobile session expires
   - Verify webhook URL is reachable by WuzAPI

### Problem: AI responses not being sent

**Diagnostic flowchart:**
```
1. Did user receive the message? (webhook triggered)
   NO: Check WebhookController logs
   
2. Is AI enabled for this tenant?
   NO: Enable in Settings → WhatsApp AI
   
3. Is OpenAI API key configured?
   NO: Add key in Settings or .env
   
4. Check logs for "OpenAI Error"
   YES: Check API key validity, rate limits
   
5. Does integration have token?
   NO: Recreate integration to generate token
   
6. Can WuzAPI be reached?
   NO: Check Docker, check endpoint configuration
   
7. All checks passed but still no response?
   - Contact support with logs
```

---

## Quick Diagnostic Commands

```bash
# Check Docker containers status
docker-compose ps

# View app logs
docker-compose logs app | tail -100

# Check WuzAPI health
curl http://localhost:21465/health

# Verify routes are loaded
docker-compose exec app php artisan route:list | grep whatsapp

# Test PHP syntax of modified files
docker-compose exec app php -l app/Services/WhatsAppIntegrationManager.php
docker-compose exec app php -l app/Http/Controllers/Settings/WhatsAppIntegrationController.php

# Check database for integrations
docker-compose exec app php artisan tinker
> App\Models\WhatsAppIntegration::get()->each(fn($i) => echo "ID: {$i->id}, Status: {$i->status}, Token: " . ($i->getUserToken() ? "✓" : "✗") . "\n");
```

---

## Expected Behavior Summary

### ✅ Successful QR Code Flow
1. User clicks "Conectar WhatsApp"
2. Modal opens with loading spinner
3. QR code appears within 2 seconds
4. User scans QR with WhatsApp mobile
5. Status polls every 5 seconds
6. Connected detected within 5-10 seconds of scan
7. Modal shows success message
8. Modal closes after 2 seconds
9. Page reloads
10. Integration shows as "connected"
11. WhatsApp is ready to send/receive messages

### ✅ Successful AI Agent Flow
1. Message arrives at WhatsApp
2. WuzAPI sends webhook to `/api/webhooks/whatsapp`
3. Application receives and authenticates
4. Checks if AI is enabled ✓
5. Checks if OpenAI key is configured ✓
6. Calls OpenAI API to generate response
7. Sends response back via WuzAPI
8. Response appears in WhatsApp chat within 1-2 seconds

---

## Files Modified

| File | Changes |
|------|---------|
| `routes/web.php` | Added 2 new routes for QR code real-time |
| `app/Http/Controllers/Settings/WhatsAppIntegrationController.php` | Added `connect()` and `checkStatus()` methods |
| `app/Services/WhatsAppIntegrationManager.php` | Enhanced `getQrCode()`, added 2 new methods |
| `resources/views/settings/integrations/whatsapp/index.blade.php` | Updated JavaScript for polling |

---

## Commits

All changes have been committed to git:
```
- feat: Add real-time QR code endpoints and polling
- fix: Remove duplicate methods in WhatsAppIntegrationManager
- fix: Remove duplicate startStatusCheck in Blade template
- fix: Enhance getQrCode to support both session_name and token approaches
```

---

**Version:** 1.0  
**Date:** 2026-05-22  
**Status:** ✅ Implementation Complete, Ready for Testing
