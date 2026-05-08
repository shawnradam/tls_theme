# TLS Theme - Fix Status Tracker

> **Last Updated:** 2026-05-06  
> **Repository:** https://github.com/shawnradam/tlstheme.git  
> **Latest Commit:** 43d6e43

---

## ✅ FIXED COMPLETED

### Map & Data System

| # | Issue | Fix | Date |
|---|-------|-----|------|
| 1 | Only 1 of 4 properties showing on map | Removed coordinate filter from `inc-map-data.php` and `map-system-unified.php` - ALL properties now included in TLS_LOTS | 2026-05-05 |
| 2 | JS only using AJAX, ignoring inline TLS_LOTS | Updated `tlsmap.js` to use `TLS_LOTS` as primary data source with AJAX fallback | 2026-05-05 |
| 3 | License blocking visitors from seeing properties | Disabled `disable_theme()` in `license-system.php` - visitors no longer blocked | 2026-05-05 |
| 4 | Missing debug logging for troubleshooting | Created `tls-theme-error.log` with comprehensive PHP + JS logging | 2026-05-05 |

### UI & Features

| # | Issue | Fix | Date |
|---|-------|-----|------|
| 5 | Emojis used instead of Material Icons | Replaced all emojis with Google Material Icons | 2026-05-05 |
| 6 | Info panel only showed on desktop | Fixed to show on both desktop AND mobile when clicking property | 2026-05-05 |
| 7 | No close button for info panel | Added close button (X) + ESC key support | 2026-05-05 |
| 8 | Mobile "Lihat Peta" toggle broken | Fixed `toggleMobilePortalView()` with `map.invalidateSize()` | 2026-05-05 |
| 9 | Property list only visible on mobile | Made listings section visible on ALL devices (removed `mobile-only-listings` class) | 2026-05-06 |
| 10 | No search/filter for properties below map | Added search bar with text search + status/geran filters | 2026-05-06 |

### Calculator

| # | Issue | Fix | Date |
|---|-------|-----|------|
| 11 | Calculator page missing/broken | Auto-create `/calculator` page with correct template on theme init | 2026-05-06 |

### Data & Structure

| # | Issue | Fix | Date |
|---|-------|-----|------|
| 12 | Variables used before defined in inc-map-data.php | Fixed variable order ($price, $ekar, $geran, $thumbnail defined before use) | 2026-05-05 |
| 13 | Town field missing | Added `_tanah_town` meta field with priority: Town > Daerah > 'Sabah' | 2026-05-05 |
| 14 | Property cards not searchable | Added data attributes: `data-title`, `data-geran`, `data-location`, `data-town`, `data-daerah` | 2026-05-06 |
| 15 | Debug/test files cluttering theme | Removed esri-leaflet-debug.js, map-display-optimizer.php, and other test files | 2026-05-05 |

---

## ❌ STILL NEEDS FIXING

### High Priority

| # | Issue | Details | Action Needed |
|---|-------|---------|---------------|
| 1 | **Live server not showing sidebar properties** | PHP log confirms 4 properties generated, but sidebar not rendering on live site | Verify `tlsmap.js` uploaded to live server, clear browser cache (`Ctrl+Shift+R`) |
| 2 | **2 properties have NO coordinates or boundary** | Kg. Tambalang (33343) and Kg. Lakang (33339) have empty lat/lng and no boundary drawn | Add coordinates via WordPress admin OR draw boundary for these properties |

### Medium Priority

| # | Issue | Details | Action Needed |
|---|-------|---------|---------------|
| 3 | **Browser cache preventing JS update** | Users may see old cached `tlsmap.js` | Add version parameter to script enqueue or instruct users to hard refresh |
| 4 | **Debug console.log in fab-menu.php** | FAB system has multiple `console.log` statements | Remove or wrap in development-only condition |
| 5 | **Debug console.log in front-page.php** | `console.error('sidebar or btn not found')` still present | Remove error log, handle silently |
| 6 | **Debug console.log in single-tanah.php** | `console.error('Error:', error)` present | Remove or handle properly |

### Low Priority

| # | Issue | Details | Action Needed |
|---|-------|---------|---------------|
| 7 | **OPcache on live server** | PHP OPcache may serve stale code even after file upload | Add `opcache_reset()` to `wp-config.php` temporarily |
| 8 | **Status filter not working in JS** | `data-status` attribute not on cards, status filter dropdown does nothing | Add `data-status` to card-land.php, update JS filter logic |
| 9 | **Map markers only for properties with coords** | Expected behavior but should be documented | Add tooltip or indicator for "sidebar-only" properties |

---

## 📊 CURRENT PROPERTY STATUS (from live server log)

| ID | Title | Price | Area | Geran | Lat/Lng | Boundary | Map Marker | Sidebar |
|----|-------|-------|------|-------|---------|----------|------------|---------|
| 33344 | Jalan Sulaman, Shangri La | RM 2,500,000 | 1.64 ekar | NT | No | YES (3 pts) | YES (boundary) | YES |
| 33343 | Kg. Tambalang | RM 380,000 | 1.44 ekar | NT | No | No | NO | YES |
| 33339 | Kg. Lakang | RM 250,000 | 0.21 ekar | NT | No | No | NO | YES |
| 33330 | Kg. Tambalugu | RM 1,500,000 | 0.5 ekar | CL | 6.144823, 116.229341 | YES (3 pts) | YES (marker) | YES |

**Summary:** 2 properties show on map (boundary + marker), 2 sidebar-only (no coords). All 4 should appear in sidebar.

---

## 🔧 QUICK FIX INSTRUCTIONS

### Fix #1: Live server not showing properties
```bash
# SSH into server
ssh user@tanahlotsabah.com

# Navigate to theme
cd /home12/mreshane/tanahlotsabah.com/wp-content/themes/tlstheme

# Pull latest changes
git pull origin master

# OR manually upload these files:
# - assets/js/tlsmap.js
# - inc/inc-map-data.php
# - inc/map-system-unified.php
# - front-page.php
# - template-parts/card-land.php
# - functions.php
```

### Fix #2: Add coordinates to properties
1. Go to WordPress Admin → Tanah
2. Edit "Kg. Tambalang" (ID 33343)
3. Add Latitude: `6.XXXXXX`, Longitude: `116.XXXXXX`
4. OR use Draw Boundary tool to draw the property boundary
5. Repeat for "Kg. Lakang" (ID 33339)

### Fix #3: Clear browser cache
- `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
- Or clear cache in browser settings

---

## 📝 RECENT COMMITS

| Commit | Description | Date |
|--------|-------------|------|
| `43d6e43` | Add classic property listings below map with search/filter, auto-create calculator page | 2026-05-06 |
| `c48128a` | Add comprehensive error logging to track missing properties on live server | 2026-05-05 |
| `b2e7d5f` | Clean debug code and use TLS_LOTS as primary data source | 2026-05-05 |
| `ddc1c0b` | Handle properties without coordinates on map | 2026-05-05 |

---

**END OF STATUS TRACKER - KEEP UPDATED**
