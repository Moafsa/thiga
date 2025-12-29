# PWA Configuration - Thiga TMS

## Changes Applied

### 1. Nginx Configuration Updates

#### Development (`docker/nginx/default.conf`)
- Added specific location blocks for PWA files:
  - `/manifest.json` - Served with `Content-Type: application/manifest+json`
  - `/sw.js` - Served with `Content-Type: application/javascript` and no-cache headers
  - `/icons/*` - Optimized caching for PWA icons

#### Production (`docker/nginx/prod.conf`)
- Same PWA configurations as development
- HTTPS already configured (HTTP → HTTPS redirect)
- Security headers maintained

### 2. Configuration Details

#### Manifest.json
```nginx
location = /manifest.json {
    add_header Content-Type application/manifest+json always;
    add_header Cache-Control "public, max-age=3600" always;
    try_files $uri =404;
}
```

#### Service Worker
```nginx
location = /sw.js {
    add_header Content-Type application/javascript always;
    add_header Cache-Control "no-cache, no-store, must-revalidate" always;
    add_header Pragma "no-cache" always;
    add_header Expires "0" always;
    try_files $uri =404;
}
```

#### PWA Icons
```nginx
location ~ ^/icons/.*\.(png|jpg|jpeg|gif|ico|svg)$ {
    add_header Cache-Control "public, max-age=31536000, immutable" always;
    try_files $uri =404;
}
```

## Testing

### Manual Testing

1. **Start the containers:**
   ```bash
   docker-compose up -d
   ```

2. **Restart Nginx to apply changes:**
   ```bash
   docker-compose restart nginx
   ```

3. **Verify Nginx configuration:**
   ```bash
   docker exec tms_saas_nginx nginx -t
   ```

4. **Test manifest.json:**
   ```bash
   curl -I http://localhost:8082/manifest.json
   ```
   Should show: `Content-Type: application/manifest+json`

5. **Test service worker:**
   ```bash
   curl -I http://localhost:8082/sw.js
   ```
   Should show: `Content-Type: application/javascript`

6. **Test in browser:**
   - Open DevTools (F12)
   - Go to Network tab
   - Navigate to `http://localhost:8082`
   - Check that `manifest.json` and `sw.js` have correct Content-Type headers

### Automated Testing

Run the test script:
```powershell
powershell -ExecutionPolicy Bypass -File test-pwa-config.ps1
```

## Browser Testing

1. Open `http://localhost:8082` in Chrome/Edge
2. Open DevTools (F12) → Application tab
3. Check:
   - **Manifest**: Should show the PWA manifest with correct icons
   - **Service Workers**: Should show registered service worker
   - **Storage**: Should show cached files

4. Test PWA installation:
   - Look for install prompt in address bar
   - Or go to DevTools → Application → Manifest → "Add to homescreen"

## Production Checklist

Before deploying to production:

- [ ] Verify `APP_URL` in `.env` uses `https://`
- [ ] Ensure SSL certificates are properly configured
- [ ] Test PWA installation on mobile devices
- [ ] Verify service worker updates correctly
- [ ] Check that all icons are accessible
- [ ] Test offline functionality

## Troubleshooting

### Nginx not serving correct Content-Type

1. Check Nginx configuration:
   ```bash
   docker exec tms_saas_nginx cat /etc/nginx/conf.d/default.conf
   ```

2. Reload Nginx:
   ```bash
   docker exec tms_saas_nginx nginx -s reload
   ```

3. Check Nginx logs:
   ```bash
   docker-compose logs nginx
   ```

### Service Worker not registering

1. Check browser console for errors
2. Verify `sw.js` is accessible: `http://localhost:8082/sw.js`
3. Check that service worker is not cached (should have no-cache headers)
4. Clear browser cache and hard reload (Ctrl+Shift+R)

### Manifest not recognized

1. Verify manifest.json is valid JSON
2. Check that all icon paths exist
3. Verify `start_url` is a valid route
4. Check browser console for manifest errors

## Files Modified

- `docker/nginx/default.conf` - Development Nginx configuration
- `docker/nginx/prod.conf` - Production Nginx configuration
- `test-pwa-config.ps1` - Test script for validation

## Notes

- The `always` directive ensures headers are added even for error responses
- Service worker must have no-cache to ensure updates are detected
- Icons use long-term caching (1 year) as they rarely change
- Manifest uses moderate caching (1 hour) to balance freshness and performance



















