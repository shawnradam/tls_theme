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
└── agent-tools/
    └── agent-tools.php
```

---

## Existing Inc Files

| File | Purpose |
|------|---------|
| `admin-map-pages.php` | Map settings, Draw boundary |
| `map-system.php` | TLSMap class, shortcode |
| `fab-system.php` | Floating action button |
| `license-system.php` | License management |
| `agent-tools.php` | Agent functionality |

---

## Summary

1. **NO direct code additions to functions.php**
2. **Create new file in `/inc/`**
3. **Only add `require_once` line to functions.php**
4. **Menu registration inside inc file**