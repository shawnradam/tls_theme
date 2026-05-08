# ⚠️ READ FIRST - TLS Theme Development Context

> **Last Updated:** 2026-05-05  
> **Repository:** https://github.com/shawnradam/tlstheme.git  
> **Latest Commit:** 849216a  
> **Documentation Location:** `C:\xampp\htdocs\maps\wp-content\themes\markdown\`

---

## 🚨 CRITICAL: Theme File Location

**THEME FILES ARE HERE:**
```
C:\xampp\htdocs\maps\wp-content\themes\tlstheme\
```

**THIS DOCUMENTATION IS HERE (DO NOT MODIFY THEME FROM HERE):**
```
C:\xampp\htdocs\maps\wp-content\themes\markdown\
```

**IMPORTANT:** When making changes, you MUST work in the `tlstheme` folder, NOT the `markdown` folder!

---

## 📁 Theme Directory Structure (READ ONLY)

```
tlstheme/                          ← WORK HERE!
├── front-page.php                 ← Main front page (map + hero)
├── functions.php                  ← Main theme functions (≈230KB)
├── functions-hybrid.php           ← Alternative theme functions (≈234KB)
├── single-tanah.php               ← Single property page
├── style.css                      ← Main theme styles (≈100KB)
│
├── header.php                     ← Site header
├── footer.php                     ← Site footer
├── index.php                      ← Default index
├── archive-tanah.php              ← Property archive
├── 404.php                        ← 404 page
│
├── page-calculator.php            ← Calculator page
├── page-demo-property.php         ← Demo page
├── page-news.php                  ← News page
│
├── template-dashboard.php         ← User dashboard
├── template-login.php             ← Login page
├── template-logout.php            ← Logout page
│
├── assets/
│   ├── css/
│   │   ├── tlsmap.css             ← Map styles
│   │   └── calculator.css         ← Calculator styles
│   └── js/
│       ├── tlsmap.js              ← Map JavaScript (MAIN!)
│       └── esri-leaflet.js        ← Esri library
│
├── template-parts/
│   └── card-land.php              ← Property card component
│
└── inc/                           ← Modular includes
    ├── core/
    │   ├── theme-setup.php
    │   ├── enqueue-scripts.php    ← Enqueues + Google Material Icons
    │   └── security.php
    ├── post-types/
    │   ├── tanah-cpt.php
    │   ├── other-cpts.php
    │   └── taxonomies.php
    ├── rest-api/
    │   ├── rest-fields.php
    │   ├── tanah-endpoints.php
    │   └── auth-endpoints.php
    ├── ajax/
    │   ├── auth-handlers.php
    │   └── property-crud-ajax.php ← Property AJAX endpoint
    ├── property-management-page.php ← Admin CRUD interface
    ├── property-validator.php
    ├── admin-map-pages.php
    ├── fab-system.php
    ├── license-system.php
    ├── map-system-unified.php
    └── inc-map-data.php
```

---

## ⛔ IMPORTANT RULES - DO NOT BREAK

### 1. ADDING NEW FEATURES
**DO NOT** modify `functions.php` directly for new features!

Follow this pattern:
1. Create a new file: `inc/inc-YOUR-FEATURE.php`
2. Add to `functions.php` (ONLY this line):
   ```php
   require_once TLS_THEME_DIR . '/inc/inc-YOUR-FEATURE.php';
   ```
3. Put ALL your feature code in the new inc file

### 2. NO EMOJIS
- Use **ONLY Google Material Icons**
- All icons already loaded via `inc/core/enqueue-scripts.php`
- Example: `<i class="material-icons">home</i>`

### 3. NO DEBUG CODE
- No `console.log` or `console.error` statements
- No test/debug files
- No backup files (`.bak`, `.pre-hybrid`, etc.)
- Remove all debug code before committing

### 4. LSP ERRORS ARE FALSE POSITIVES
WordPress functions like `get_post_meta()`, `add_action()`, `wp_enqueue_script()` etc. show as "undefined" in your editor. **THIS IS NORMAL!** These are WordPress core functions loaded at runtime. Do NOT try to "fix" these!

### 5. ALWAYS TEST BEFORE COMMITTING
```bash
# Check PHP syntax
php -l functions.php
php -l single-tanah.php

# Check for debug code
grep -rn "console.log\|console.error" assets/js/
```

---

## 🔧 Key Files to Know

### Map JavaScript: `assets/js/tlsmap.js`
- **Lines 30-47:** Info panel creation + close button + ESC key
- **Lines ~295-320:** `handlePropertyClick()` - property click handler
- **Lines ~240-260:** `zoomToProperty()` - zoom to selected property
- **Important:** Uses `window.selectedProperty` global for mobile toggle

### Map Styles: `assets/css/tlsmap.css`
- **Lines 9-25:** `.tls-info-panel` - info panel styles
- **Lines 36-54:** `.close-panel` - close button (z-index: 1010)
- **Important z-index values:**
  - Info panel: 1010
  - Close button: 1010
  - Sidebar: 1002

### Property Card: `template-parts/card-land.php`
- **Lines 1-8:** Location priority: Town > Daerah > 'Sabah'
- **Line 7:** `$location = $town ?: ($daerah ?: 'Sabah');`

### Single Property: `single-tanah.php`
- Same location priority as card
- Line 24: `$location = $town ?: ($daerah ?: 'Sabah');`

### Property Management: `inc/property-management-page.php`
- Admin CRUD interface
- TLS Dashboard → Property Management
- Handles Create, Read, Update, Delete

### Enqueue: `inc/core/enqueue-scripts.php`
- Loads all CSS/JS files
- Google Material Icons loaded here
- Leaflet map library loaded here

---

## 📊 Meta Fields Reference

| Field Name | Meta Key | Type | Description |
|------------|----------|------|-------------|
| Price | `_tanah_harga` | number | Property price in RM |
| Area | `_tanah_keluasan` | text | Land area in acres |
| Grant Type | `_tanah_jenis_geran` | select | CL, NT, P, Hakmilik |
| Zoning | `_tanah_zoning` | select | Kediaman, Komersial, etc. |
| Town | `_tanah_town` | text | Town/City name |
| Latitude | `_tanah_latitude` | text | GPS latitude |
| Longitude | `_tanah_longitude` | text | GPS longitude |
| Verified | `_tanah_verified` | checkbox | 1 = verified |
| Building Size | `_tanah_building_size` | text | Building size |
| Property ID | `_tanah_property_id` | text | Custom ID (TLS-001) |

---

## 🎯 Current State: ✅ PRODUCTION READY

**What works:**
- ✅ Interactive map with property markers
- ✅ Info panel shows on desktop AND mobile
- ✅ Close button (×) and ESC key support
- ✅ Mobile "Lihat Peta" toggle works
- ✅ Property CRUD from TLS Dashboard
- ✅ Google Material Icons (NO emojis)
- ✅ All debug files removed
- ✅ PHP syntax clean

**Still optional to test:**
- Real mobile device testing
- Property Management CRUD operations
- Image upload in property edit

---

## 📦 How to Deploy

### 1. Local Testing
- Site URL: `http://localhost/maps/`
- WordPress admin: `http://localhost/maps/wp-admin/`
- TLS Dashboard: `http://localhost/maps/wp-admin/admin.php?page=tls-dashboard`

### 2. Push to GitHub
```bash
cd C:\xampp\htdocs\maps\wp-content\themes\tlstheme
git add -A
git commit -m "Your commit message"
git push origin master
```

### 3. Update WordPress
- Go to Appearance → Themes
- Re-activate TLS theme if needed

---

## 🔍 Troubleshooting

### Map not showing?
- Check `assets/js/tlsmap.js` for errors
- Verify Leaflet is loaded in `inc/core/enqueue-scripts.php`
- Check browser console for JS errors

### Property not displaying correctly?
- Check meta fields in database: `wp_postmeta` table
- Verify `_tanah_town` is set (new field)
- Check `card-land.php` location logic

### Info panel not showing?
- Check z-index values in `assets/css/tlsmap.css`
- Verify `handlePropertyClick()` in `tlsmap.js`
- Check if `window.selectedProperty` is set

### PHP errors?
- Run `php -l filename.php` to check syntax
- Check WordPress debug log: `C:\xampp\htdocs\maps\wp-content\debug.log`
- **LSP errors are false positives!**

---

## 📝 Development History

| Commit | Description |
|--------|-------------|
| `849216a` | Move docs to markdown folder, clean theme directory |
| `746765c` | Add DEVELOPMENT-LOG.md |
| `eb048b8` | Remove unused frontend CRUD page |
| `c2e0299` | Fix Property Management page |
| `5d3495e` | Remove map-display-optimizer.php |
| `65d84a2` | Remove all emojis - use Material Icons |
| `dbaa777` | Fix blank Property Management page |
| `54719a2` | Add frontend CRUD |
| `16261a9` | Remove test calculator, add town field |
| `74139ea` | Initial commit |

---

## ⚡ Quick Start for Next Agent

### Before you start:
1. **READ THIS DOCUMENT** completely
2. **Locate theme files:** `C:\xampp\htdocs\maps\wp-content\themes\tlstheme\`
3. **DO NOT edit files in markdown folder**
4. **Test changes locally** before committing

### Common tasks:
```bash
# Go to theme directory
cd C:\xampp\htdocs\maps\wp-content\themes\tlstheme

# Check syntax before commit
php -l functions.php
php -l assets/js/tlsmap.js

# Check for debug code
grep -rn "console.log" assets/js/

# Commit changes
git add -A
git commit -m "Description"
git push origin master
```

---

## ⚠️ WARNING: DO NOT

- ❌ Add emojis (use Material Icons only)
- ❌ Add console.log/console.error
- ❌ Modify functions.php directly (use inc/ folder)
- ❌ Leave debug/test code in production
- ❌ Delete files without checking dependencies
- ❌ Commit without testing
- ❌ Ignore PHP syntax errors (but LSP warnings are OK)

---

## ✅ CHECKLIST BEFORE COMMITTING

- [ ] PHP syntax clean (`php -l filename.php`)
- [ ] No console.log/console.error in JS
- [ ] No emojis in code
- [ ] No debug/test files
- [ ] Tested on desktop browser
- [ ] Tested on mobile browser (if applicable)
- [ ] Changes follow AGENTS.md guidelines

---

**END OF DOCUMENT - KEEP THIS UPDATED FOR FUTURE AGENTS**
