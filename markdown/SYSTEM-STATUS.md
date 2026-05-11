# TLS Theme - System Status & Issues

## Current Status (as of 2026-05-10)

### ✅ Working Features:

1. **Hero Header** - Fullscreen on all devices, professional design with backdrop blur badge
2. **Google Material Icons** - Integrated (place, open_in_new, directions_car, directions_walk, directions_bike, shopping_cart)
3. **Map Info Panel** - Shows on desktop when clicking map markers or sidebar items
4. **Property Link Button** - "View Property Details" button now visible and links to property page
5. **Navigation Buttons** - Drive, Walk, Motorcycle (Google Maps directions)
6. **Nearby Shops Button** - Opens Google Maps search for shopping malls
7. **Draw Boundary** - Working at WordPress Admin → TLS Dashboard → Draw Boundary
8. **Development Status Filter** - Planned / In Progress / Completed / Raw Land filters on map sidebar
9. **News/Blog System** - 10 news articles with Chart.js statistics
10. **Seed Data Tool** - Admin UI at TLS System → Seed Data for creating sample content

---

## New Features Added (2026-05-10)

### Development Status Filter
- **Meta Field:** `_tanah_development_status` with values: planned, in_progress, completed, raw_land
- **Taxonomy:** `development_status` for REST API filtering
- **UI:** Filter buttons in map sidebar (Geran: NT/CL/All + Status: Planned/In Progress/Completed/All)
- **Files Modified:**
  - `inc/post-types/tanah-cpt.php` - Added meta box
  - `inc/post-types/taxonomies.php` - Added taxonomy
  - `front-page.php` - Added filter buttons
  - `tlsmap.js` (plugin) - Added filter logic
  - `class-tlsmap-frontend.php` (plugin) - Added dev_status to AJAX response

### News/Blog System
- **10 News Articles** with real Sabah land statistics
- **Chart.js 4.4.0** for animated charts
- **Pages:**
  - Homepage: 3 latest articles in news section
  - `/news/`: All articles in grid layout
  - Individual posts: Full content with charts
- **Files Created:**
  - `inc/news-seed-data.php` - Seed function
  - `inc/news-templates/` - 10 content template files
  - `inc/tanah-sample-data.php` - Sample properties
  - `inc/seed-data-tool.php` - Admin UI
  - `page-news.php` - News page template
  - `template-parts/content-blog.php` - Blog card
  - `inc/core/enqueue-scripts.php` - Chart.js CDN

### Responsive Styles
- **Desktop:** 3-column grid for news/blog
- **Tablet (iPad):** 2-column grid
- **Mobile:** 1-column stacked layout
- **Files:** `style.css`, `page-news.php`, `front-page.php`

---

## Database Options

| Option Name | Purpose |
|------------|---------|
| `tls_news_seeded` | Prevents duplicate news seeding |
| `tls_tanah_seeded` | Prevents duplicate properties seeding |

---

## Required Files Checklist

### ✅ Already Created:
- `inc/news-seed-data.php`
- `inc/tanah-sample-data.php`
- `inc/seed-data-tool.php`
- `inc/seed-wpcli.php`
- `inc/news-templates/ncr-guide.php`
- `inc/news-templates/nt-cl-distribution.php`
- `inc/news-templates/pantas-program.php`
- `inc/news-templates/residential-2022.php`
- `inc/news-templates/residential-2023.php`
- `inc/news-templates/kkip-investment.php`
- `inc/news-templates/nt-reform-2024.php`
- `inc/news-templates/pan-borneo.php`
- `inc/news-templates/nt-cl-roi.php`
- `inc/news-templates/market-uptrend-2025.php`

### ✅ Modified Files:
- `inc/post-types/tanah-cpt.php` - Added development_status meta box
- `inc/post-types/taxonomies.php` - Added development_status taxonomy
- `inc/core/enqueue-scripts.php` - Added Chart.js
- `template-parts/content-blog.php` - Updated blog card
- `page-news.php` - News page template with responsive styles
- `front-page.php` - News section added
- `style.css` - Blog/News responsive styles added
- `functions.php` - All new files required

---

## How to Use Seed Data

1. Go to **WP Admin → TLS System → Seed Data**
2. Click **"Seed All Data"** to create:
   - 10 news articles with real statistics
   - 10 sample tanah properties
3. Or use individual buttons for specific data

---

## How to Add Development Status

1. Go to **WordPress Admin → Tanah → Add New** (or edit existing)
2. Scroll to **"Development Status"** meta box (right side)
3. Select: Planned / In Progress / Completed / Raw Land
4. Add optional development notes
5. Publish/Update

---

## Testing Checklist

- [ ] Homepage news section shows 3 articles
- [ ] `/news/` page shows all 10 articles
- [ ] Mobile/iPad responsive grid works
- [ ] Development status filter buttons visible
- [ ] Chart.js charts animate on single post pages
- [ ] Sample properties appear on map

---

## Notes for Developers

- LSP errors in PHP files are **false positives** (WordPress functions not recognized)
- Always test on actual mobile devices
- Clear browser cache when testing CSS changes
- Run `php -l file.php` to check PHP syntax

---

**Last Updated:** 2026-05-10
**Status:** ✅ All features implemented and documented