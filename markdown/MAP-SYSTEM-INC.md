# TLS Map System

## Structure

```
wp-content/themes/tanahlotsabah/
├── functions.php              ← DO NOT ADD to this file
├── inc/
│   └── admin-map-pages.php    ← Contains ALL map admin functionality
├── assets/js/
│   └── mapbox-gl.js           ← Mapbox GL JS library
└── assets/css/
```

## Files

### inc/admin-map-pages.php
Contains:
- Menu registration (admin_menu hook)
- `tls_map_settings_page()` - Mapbox token settings
- `tls_draw_boundary_page()` - Draw property boundaries

### assets/js/
- `esri-leaflet.js` - Esri Leaflet core
- `esri-leaflet-vector.js` - Esri vector tiles
- `mapbox-gl.js` - Mapbox GL JS (alternative map engine)

## Rules

**DO NOT add any menu items or functions to functions.php**

All new features must be added to `/inc/` folder files:
- Menu registration → `inc/admin-map-pages.php`
- New admin pages → Create new file in `/inc/` or add to existing
- New functionality → Create new file in `/inc/`

## Map Services

| Service | API Key | Features |
|---------|---------|----------|
| Mapbox GL JS | Required (free tier: 50k/mo) | Vector tiles, custom styling |
| Esri Tiled Map | Free, no key needed | Standard tiled maps |
| OSM Leaflet | Free, no key needed | Basic tiles |

## Setup

1. Go to **TLS System > Map Settings**
2. Enter Mapbox Access Token from https://account.mapbox.com/
3. Go to **TLS System > Draw Boundary**
4. Select property and draw boundary

## Property Styling

Polygons auto-color based on:
- **Geran Type**: NT (green), CL (blue), P (orange), Hakmilik (purple)
- **Zoning**: Residential (blue), Commercial (orange), Agriculture (green), etc.

Style is saved with boundary to `_tanah_boundary_style` meta field.