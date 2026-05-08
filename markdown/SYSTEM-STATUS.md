# TLS Theme - System Status & Issues

## Current Status (as of 2026-05-04)

### ✅ Working Features:
1. **Hero Header** - Fullscreen on all devices, professional design with backdrop blur badge
2. **Google Material Icons** - Integrated (place, open_in_new, directions_car, directions_walk, directions_bike, shopping_cart)
3. **Map Info Panel** - Shows on desktop when clicking map markers or sidebar items
4. **Property Link Button** - "View Property Details" button now visible and links to property page
5. **Navigation Buttons** - Drive, Walk, Motorcycle (Google Maps directions)
6. **Nearby Shops Button** - Opens Google Maps search for shopping malls
7. **Demo Page** - Created at `http://localhost/maps/demo-property/`
8. **Draw Boundary** - Working at WordPress Admin → TLS Dashboard → Draw Boundary
9. **Sidebar Card Buttons** - "place" (Show on Map) and "open_in_new" (View Details) now visible with proper CSS
10. **Hidden Recent Listings on Desktop** - Only shows on mobile (`.mobile-only-listings` class)

---

## ❌ Current Issues to Fix:

### Issue #1: "Lihat Peta" Button on Mobile
**Problem:** When clicking "Lihat Peta" (Show Map) on mobile, the map doesn't display properly. User needs to zoom in/out to see the map contents.

**Current Behavior:**
- Button toggles sidebar visibility
- When sidebar hides, map should show but needs manual zoom adjustment
- `toggleMobilePortalView()` function exists but map doesn't auto-fit to show contents

**Expected Behavior:**
- When clicking "Lihat Peta", map should automatically zoom to show the properties/content
- Map should be fully visible without manual zoom adjustment

**File Location:** `front-page.php:155-165`

---

### Issue #2: Property Details Not Showing in List View
**Problem:** User mentioned "i didn't see any walking, driving, or nearby shop that i can add/edit". These features are **automatically captured** from property coordinates, but user is confused about how they work.

**Clarification:**
- **Drive/Walk/Motorcycle buttons** - Auto-generated from property's Latitude & Longitude coordinates
- **Nearby Shops** - Auto-generated from coordinates using Google Maps search
- **NO manual input needed** - System captures these automatically

**How it works:**
1. Add property in WordPress Admin → Tanah → Add New
2. Enter coordinates in custom fields (`_tanah_latitude`, `_tanah_longitude`)
3. OR use "Draw Boundary" tool to set the property location
4. System automatically generates:
   - Google Maps directions links (Drive, Walk, Bike)
   - Nearby Shops search link
   - Property page link (`get_permalink()`)

**User Perception Issue:** User thinks they need to manually add these features, but they're automatic.

---

### Issue #3: Map Zoom Behavior
**Problem:** When clicking sidebar items or map markers, the zoom level might not be optimal.

**Current Fix Applied:**
- `fitBounds()` now used for properties with boundaries
- Falls back to `setView([lat, lng], 16)` for properties without boundaries
- Added `isValid()` check and try-catch for invalid boundaries

**Needs Testing:** Verify zoom works correctly on both desktop and mobile.

---

## Required Agents/Features to Create:

### Agent #1: Property Data Validator
**Purpose:** Validate that all properties have required data (coordinates, boundary, images, links)

**Tasks:**
- Check if all properties have valid coordinates (lat/lng)
- Verify property links are generated correctly (not `false`)
- Ensure boundaries are valid GeoJSON
- Generate report of properties missing critical data

**Implementation:** Create `inc/property-validator.php`

---

### Agent #2: Map Display Optimizer
**Purpose:** Fix mobile map display issues when toggling "Lihat Peta"

**Tasks:**
- Fix `toggleMobilePortalView()` to properly resize map after sidebar toggle
- Ensure map fits bounds/zoom after sidebar hides
- Add `map.invalidateSize()` with proper timing
- Test on various mobile screen sizes

**File to Modify:** `front-page.php:155-165`

---

### Agent #3: User Guide Creator
**Purpose:** Create documentation so user understands automatic features

**Tasks:**
- Document how Drive/Walk/Motorcycle buttons work (automatic from coordinates)
- Explain Nearby Shops feature (automatic from coordinates)
- Create step-by-step guide: "How to add a property with full map features"
- Add screenshots/diagrams

**Output:** `PROPERTY-GUIDE.md`

---

## Files Modified (Recent Session):

1. **`assets/js/tlsmap.js`**
   - Fixed JavaScript syntax errors (missing commas in array literals)
   - Added try-catch for `fitBounds()` with `isValid()` check
   - Fixed `linkHtml` variable hoisting issue
   - Updated buttons to use Google Material Icons
   - Added `window.findNearbyShops()` function

2. **`assets/css/tlsmap.css`**
   - Added `.portal-card-actions` and `.action-btn` styles
   - Added `.panel-btn-primary` full styles (display, padding, color)
   - Added Material Icons compatibility

3. **`inc/map-system-unified.php`**
   - Fixed PHP ternary operator for `link` property
   - Changed `'link' => get_permalink($id) ?: ''` to proper syntax

4. **`front-page.php`**
   - Fixed "Lihat Peta" button text (changed from "Lihat Senarai")
   - Added `map.invalidateSize()` in `toggleMobilePortalView()`
   - Added scroll to map section when showing map

5. **`inc/core/enqueue-scripts.php`**
   - Added Google Material Icons CSS

6. **`page-demo-property.php`** (Created)
   - Demo page template for testing property links

7. **`inc/admin-map-pages.php`**
   - Draw Boundary feature (existing, working)

---

## How to Test Current System:

### Test 1: Info Panel & Buttons (Desktop)
1. Open `http://localhost/maps/`
2. Click on a map marker or sidebar item
3. ✅ Info panel should appear at top-right
4. ✅ "View Property Details" button should link to property page
5. ✅ Drive/Walk/Motorcycle buttons should open Google Maps
6. ✅ Nearby Shops should open Google Maps search

### Test 2: Mobile "Lihat Peta" Button
1. Open `http://localhost/maps/` on mobile or dev tools mobile view
2. Click "Lihat Senarai" to show sidebar
3. Click on a property in sidebar
4. Click "Lihat Peta" to show map
5. ❌ **ISSUE:** Map may not display properly, needs zoom adjustment

### Test 3: Property Creation Flow
1. WordPress Admin → Tanah → Add New
2. Fill in: Title, Price, Area, Geran Type
3. Set coordinates (Latitude & Longitude) or use Draw Boundary
4. Publish
5. Verify: Property appears on map with all buttons working

---

## Next Steps:

1. **Fix "Lihat Peta" mobile display issue** (Agent #2)
2. **Create Property Data Validator** (Agent #1)
3. **Create User Guide** (Agent #3)
4. **Test thoroughly** on desktop, tablet, and mobile
5. **Remove any remaining debug code**

---

## Notes for Developers:

- LSP errors in PHP files are **false positives** (WordPress functions not recognized by PHP language server)
- Always run `node -c file.js` to check JavaScript syntax
- Always run `php -l file.php` to check PHP syntax
- Test on actual mobile devices, not just browser dev tools
- Clear browser cache when testing CSS/JS changes

---

**Last Updated:** 2026-05-04
**Status:** Partial fix applied, needs mobile display optimization
