# Fullscreen Footer Documentation

## Overview

The theme has a "fullscreen footer" feature that, on mobile devices, expands the footer to fill the entire screen when the user scrolls to the bottom. This was designed for agent listing pages and property listing pages.

However, for **front-page.php** and **single-tanah.php**, this behavior causes issues with scrolling (footer "sticks" and doesn't flow normally). This document explains how the system works and how to maintain it.

## How It Works

### CSS Control (style.css)

The fullscreen footer CSS is wrapped in a media query that only applies when the body has the `enable-fullscreen-footer` class:

```css
@media (max-width: 768px) {
  /* Normal pages: footer is always relative (not sticky) */
  body:not(.enable-fullscreen-footer) footer.site-footer {
    position: relative !important;
    min-height: auto;
  }
  
  /* Pages with enable-fullscreen-footer: footer expands on scroll */
  body.enable-fullscreen-footer footer.site-footer.is-at-bottom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    /* ... */
  }
}
```

### JavaScript Control (footer.php)

The JavaScript only activates the fullscreen logic if the body has `enable-fullscreen-footer`:

```javascript
var footer = document.querySelector('footer.site-footer');
if (window.innerWidth < 768 && footer && document.body.classList.contains('enable-fullscreen-footer')) {
    window.addEventListener('scroll', function() {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 50) {
            footer.classList.add('is-at-bottom');
        } else {
            footer.classList.remove('is-at-bottom');
        }
    });
}
```

## Pages Configuration

### Normal Scroll Pages (footer flows normally)
- **front-page.php** - Has `disable-fullscreen-footer` body class
- **single-tanah.php** - Has `disable-fullscreen-footer` body class
- Any page that adds `disable-fullscreen-footer` to body class

### Fullscreen Footer Pages
- Pages that add `enable-fullscreen-footer` to body class
- Default: All other pages (no special class needed)

## How to Add/Remove Fullscreen Footer

### To Disable Fullscreen Footer on a Page
Add this at the top of the template file (after `get_header()`):

```php
add_filter('body_class', function($classes) {
    $classes[] = 'disable-fullscreen-footer';
    return $classes;
});
```

### To Enable Fullscreen Footer on a Page
Add this at the top of the template file:

```php
add_filter('body_class', function($classes) {
    $classes[] = 'enable-fullscreen-footer';
    return $classes;
});
```

## Currently Configured

| Page | Footer Behavior | Class Added |
|------|----------------|------------|
| front-page.php | Normal scroll | disable-fullscreen-footer |
| single-tanah.php | Normal scroll | disable-fullscreen-footer |
| archive-tanah.php | Fullscreen | enable-fullscreen-footer (default) |
| Single Agent pages | Fullscreen | enable-fullscreen-footer (default) |
| Other pages | Fullscreen | enable-fullscreen-footer (default) |

## Agent Pages

The agent tools system (`inc/agent-tools/agent-tools.php`) uses the mobile sticky bar which is independent of the fullscreen footer system. The `mobile-sticky-bar` in `footer.php` is controlled by the `show_mobile_footer` theme mod, not the fullscreen footer system.