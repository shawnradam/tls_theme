# Unified Integrated Map Portal System

This document outlines the architecture, usage, and data synchronization logic for the **Integrated Map Portal** built natively into the Tanah Lot Sabah theme.

## 1. Overview
The Integrated Map Portal is a dual-pane interface designed to provide an app-like experience for exploring land lots. It features a persistent sidebar for property listings that stays synchronized with an interactive map.

- **Frontend Location**: `front-page.php`
- **Core Logic**: `inc/map-system-unified.php`
- **Frontend Assets**: 
  - Styles: `assets/css/tlsmap.css` & `style.css`
  - Logic: `assets/js/tlsmap.js`
  - Libraries: `assets/js/esri-leaflet*`

## 2. Integration Features
- **Sidebar-Map Sync**: Clicking a property card in the sidebar automatically pans and zooms the map to that lot's boundaries.
- **Marker-Sidebar Sync**: Clicking a lot boundary on the map scrolls the sidebar to the corresponding card and highlights it.
- **Real-Time Filters**: A search bar inside the sidebar allows instant filtering of map properties by Name, Geran No, or District.
- **Visual Badges**: Automatic color-coding for different land types:
  - **Native Title (NT)**: Green
  - **Country Lease (CL)**: Blue
  - **Development Phase**: Purple
  - **Hakmilik**: Orange

## 3. How to Add Properties to the Map
The system automatically pulls data from the `tanah` post type. To ensure a property shows up on the map:

1.  **Set Location**: Enter the **Latitude** and **Longitude** in the property's custom fields.
2.  **Add Boundary (Optional but Recommended)**: Paste the GeoJSON coordinate array (e.g., `[[lat,lng],[lat,lng]...]`) into the `_tanah_boundary` field.
3.  **Set Status**: Choose from 'Available', 'Reserved', or 'Sold' to update the marker color automatically.
4.  **Geran Type**: Ensure `_tanah_jenis_geran` is set to trigger the correct badge UI.

## 4. Shortcode Usage
You can embed the map portal anywhere using the unified shortcode:

```shortcode
[tlsmap height="800px" width="100%"]
```

**Attributes:**
- `height`: The height of the map container (default: `500px`).
- `lat` / `lng`: Initial center coordinates.
- `zoom`: Initial zoom level (default: `12`).
- `post_id`: (Internal use) Highlights a specific lot on page load.

## 5. Mobile Responsiveness
On mobile devices, the portal transitions to a "Toggle View":
- Users can switch between **Map View** and **List View** using a floating action button.
- Clicking a card in List View automatically switches back to Map View and zooms to the location.

---
*Managed natively by the TanahLotSabah Theme System.*
