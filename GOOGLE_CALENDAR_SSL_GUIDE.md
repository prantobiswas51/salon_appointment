# Google Calendar SSL Configuration Guide

This document helps you resolve SSL certificate issues when connecting to Google Calendar API in development environments.

## Quick Diagnosis

Run the SSL check command to diagnose issues:

```bash
php artisan calendar:ssl-check
```

## Common SSL Issues & Solutions

### Issue 1: SSL Certificate Problem (cURL error 60)

**Error Message:** 
```
SSL certificate problem: unable to get local issuer certificate
```

**Solution for Development:**
Add this to your `.env` file:
```bash
GOOGLE_SSL_VERIFY=false
```

⚠️ **WARNING:** Only use `GOOGLE_SSL_VERIFY=false` in development environments. Never use this in production.

### Issue 2: Missing Google Calendar Credentials

**Error Message:**
```
Google Calendar credentials not found
```

**Solution:**
1. Create the directory: `storage/app/google/`
2. Place your Google Calendar credentials JSON file at: `storage/app/google/credentials.json`
3. Ensure the file contains valid OAuth 2.0 or Service Account credentials

### Issue 3: Invalid Calendar ID

**Error Message:**
```
Calendar not found or access denied
```

**Solution:**
1. Verify your `GOOGLE_CALENDAR_ID` in the `.env` file
2. Use `primary` for your main calendar
3. Ensure the service account has access to the calendar

## Environment Configuration

Add these variables to your `.env` file:

```bash
# Google Calendar Configuration
GOOGLE_CALENDAR_ID=primary
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=your-redirect-uri

# SSL Configuration (development only)
GOOGLE_SSL_VERIFY=false
```

## Testing the Configuration

1. **Check SSL connectivity:**
   ```bash
   php artisan calendar:ssl-check
   ```

2. **Test sync functionality:**
   ```bash
   php artisan tinker
   ```
   ```php
   App\Services\EventParserService::syncGoogleCalendarEvents()
   ```

3. **Verify dashboard sync:**
   - Go to your dashboard
   - Click the "Sync Calendar" button
   - Check for success/error messages

## Production Configuration

For production environments:

1. **Keep SSL verification enabled:**
   ```bash
   GOOGLE_SSL_VERIFY=true
   ```

2. **Use proper SSL certificates:**
   - Ensure your server has updated CA certificates
   - Install proper SSL certificate bundle
   - Configure cURL with correct certificate paths

3. **Use Service Account authentication:**
   - More secure than OAuth for server-to-server communication
   - Configure service account credentials properly
   - Grant necessary calendar permissions

## Troubleshooting

### If sync button shows "Configuration Error":
1. Check Google Calendar credentials exist
2. Verify calendar ID is correct
3. Ensure proper API permissions

### If sync takes too long:
1. Check network connectivity
2. Verify SSL configuration
3. Check Google API quotas and limits

### If events don't sync:
1. Verify calendar permissions
2. Check date range filters
3. Ensure event format is "Client Name - Service"

## Support

For additional help:
1. Run the diagnostic command: `php artisan calendar:ssl-check`
2. Check Laravel logs: `storage/logs/laravel.log`
3. Review Google Calendar API documentation
4. Verify API quota limits in Google Cloud Console