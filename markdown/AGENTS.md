# Development Rules

## Skills
- **dev-lifecycle**: Apply the dev-lifecycle skill for structured SDLC workflow. Use phases: requirements → design → planning → implementation → testing
- **Always run `npx ai-devkit@latest lint` BEFORE starting any phase or task** to verify docs/ai/ structure is valid

## Core Principle
**functions.php is READ ONLY for manual additions.** All new functionality goes into `/inc/` folder.

---

## Adding New Features

### 1. Create new inc file
```
inc/inc-FEATURE-NAME.php
```

### 2. Add to functions.php (ONLY this line)
```php
require_once TLS_THEME_DIR . '/inc/inc-FEATURE-NAME.php';
```

### 3. Register menu items INSIDE the inc file
```php
add_action('admin_menu', function() {
    add_submenu_page('tls-dashboard', 'Page Name', 'Page Name', 'manage_options', 'page-slug', 'page_function');
});
```

### 4. Create page function in same file
```php
function page_function() {
    // Page content
}
```

---

## Example: Adding Admin Page

### Wrong (adding directly to functions.php):
```php
// DO NOT DO THIS
add_submenu_page('tls-dashboard', 'New Page', 'New Page', 'manage_options', 'new-page', 'new_page_function');
```

### Correct (create inc file):

**1. Create `inc/inc-new-page.php`:**
```php
<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function() {
    add_submenu_page('tls-dashboard', 'New Page', 'New Page', 'manage_options', 'new-page', 'new_page_function');
});

function new_page_function() {
    echo '<div class="wrap"><h1>New Page</h1></div>';
}
```

**2. Add to functions.php:**
```php
require_once TLS_THEME_DIR . '/inc/inc-new-page.php';
```

---

## Inc Folder Structure

```
inc/
├── core/
│   ├── theme-setup.php
│   ├── enqueue-scripts.php
│   └── security.php
├── post-types/
│   ├── tanah-cpt.php
│   └── taxonomies.php
├── admin-map-pages.php      ← All map admin pages
├── map-system.php           ← Map class & shortcode
├── fab-system.php
├── license-system.php
├── agent-tools/
│   └── agent-tools.php
├── news-seed-data.php        ← News/Blog seed data
├── tanah-sample-data.php     ← Sample properties seed data
├── seed-data-tool.php       ← Admin seed data management
└── news-templates/           ← News post content templates
    ├── ncr-guide.php
    ├── nt-cl-distribution.php
    ├── pantas-program.php
    ├── residential-2022.php
    ├── residential-2023.php
    ├── kkip-investment.php
    ├── nt-reform-2024.php
    ├── pan-borneo.php
    ├── nt-cl-roi.php
    └── market-uptrend-2025.php
```

---

## TLS Theme CRUD Files (Development Features)

### Core Files Created

| File | Purpose | CRUD |
|------|---------|------|
| `inc/post-types/tanah-cpt.php` | Tanah (Land) CPT with Development Status meta box | Create, Read, Update |
| `inc/post-types/taxonomies.php` | Custom taxonomies (daerah, jenis_geran, land_type, development_status) | Create, Read |
| `inc/news-seed-data.php` | Seed 10 news articles with real Sabah statistics | Create |
| `inc/tanah-sample-data.php` | Seed 10 sample tanah properties | Create |
| `inc/seed-data-tool.php` | Admin UI for managing seed data | Create, Delete |
| `inc/core/enqueue-scripts.php` | Chart.js 4.4.0 CDN + theme assets | Enqueue |
| `front-page.php` | News section on homepage | Read |
| `page-news.php` | News archive page template | Read |
| `template-parts/content-blog.php` | Blog card template | Read |

### News Templates (10 articles)

| Year | Template | Data |
|------|----------|------|
| 2020 | `ncr-guide.php` | NCR conditions, 7 types doughnut chart |
| 2021 | `nt-cl-distribution.php` | NT 54% vs CL 46% pie chart |
| 2022 | `pantas-program.php` | 27,572 NT grants bar+line chart |
| 2022 | `residential-2022.php` | RM2.78B quarterly bar chart |
| 2023 | `kkip-investment.php` | RM8.32B investment doughnut chart |
| 2023 | `residential-2023.php` | RM2.37B annual comparison chart |
| 2024 | `nt-reform-2024.php` | 16,760 NT titles timeline bar chart |
| 2024 | `pan-borneo.php` | Highway progress horizontal bar chart |
| 2024 | `nt-cl-roi.php` | NT vs CL ROI comparison chart |
| 2025 | `market-uptrend-2025.php` | RM5.17T line chart |

### Database Options Created

| Option Name | Purpose |
|-------------|---------|
| `tls_news_seeded` | Flag to prevent duplicate news seeding |
| `tls_tanah_seeded` | Flag to prevent duplicate properties seeding |

---

## Custom Post Types & Taxonomies

### Tanah CPT Meta Fields

| Field | Meta Key | Type |
|-------|----------|------|
| Price | `_tanah_harga` | number |
| Size (Ekar) | `_tanah_keluasan` | text |
| Grant Type | `_tanah_jenis_geran` | select (NT/CL) |
| Status | `_tanah_status` | select (available/reserved/sold) |
| Development Status | `_tanah_development_status` | select (planned/in_progress/completed/raw_land) |
| Development Notes | `_tanah_development_notes` | textarea |
| Latitude | `_tanah_latitude` | text |
| Longitude | `_tanah_longitude` | text |
| Town | `_tanah_town` | text |
| Zoning | `_tanah_zoning` | text |

### Taxonomies

| Taxonomy | Object Type | Hierarchical | REST |
|---------|-------------|--------------|------|
| daerah | tanah | Yes | Yes |
| jenis_geran | tanah | Yes | Yes |
| land_type | tanah | Yes | No |
| development_status | tanah | Yes | Yes |

---

## Templates

| Template | File | Purpose |
|----------|------|---------|
| News Template | `page-news.php` | News archive page (page template) |
| Blog Card | `template-parts/content-blog.php` | Single blog post card |

---

## Summary

1. **NO direct code additions to functions.php**
2. **Create new file in `/inc/`**
3. **Only add `require_once` line to functions.php**
4. **Menu registration inside inc file**
5. **All new features documented in this file**