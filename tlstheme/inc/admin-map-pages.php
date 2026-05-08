<?php
/**
 * TLS Map System - Admin Pages
 * Draw boundary and property map management using Leaflet & Esri
 */

if (!defined('ABSPATH')) exit;

// ============================================
// ADMIN MENU REGISTRATION
// ============================================
add_action('admin_menu', function() {
    global $submenu;
    if (!isset($submenu['tls-dashboard'])) return;
    
    add_submenu_page(
        'tls-dashboard',
        'Draw Boundary',
        'Draw Boundary',
        'manage_options',
        'tls-tanah-boundary',
        'tls_draw_boundary_page'
    );
    
    add_submenu_page(
        'tls-dashboard',
        'Map Settings',
        'Map Settings',
        'manage_options',
        'tls-map-settings',
        'tls_map_settings_page'
    );
}, 99);

// ============================================
// MAP SETTINGS PAGE
// ============================================
function tls_map_settings_page() {
    if (isset($_POST['tls_save_map_settings']) && wp_verify_nonce($_POST['tls_nonce'], 'tls_save_map_settings')) {
        update_option('tlsmap_arcgis_api_key', sanitize_text_field($_POST['arcgis_api_key']));
        echo '<div class="notice notice-success"><p>Map settings saved!</p></div>';
    }
    
    $apiKey = get_option('tlsmap_arcgis_api_key', '');
    ?>
    <div class="wrap">
        <h1>Map Settings</h1>
        <div class="card" style="max-width: 800px; padding: 20px;">
            <h2>ArcGIS Vector Configuration</h2>
            <p>To use <strong>Vector Style Mapping</strong> (crisp, modern maps), you need an ArcGIS API Key.</p>
            <ol>
                <li>Get a free key at <a href="https://developers.arcgis.com/" target="_blank">developers.arcgis.com</a></li>
                <li>Ensure "Basemaps" scope is enabled for your key.</li>
            </ol>
            
            <form method="post">
                <?php wp_nonce_field('tls_save_map_settings', 'tls_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="arcgis_api_key">ArcGIS API Key</label></th>
                        <td>
                            <input name="arcgis_api_key" type="text" id="arcgis_api_key" value="<?php echo esc_attr($apiKey); ?>" class="regular-text">
                            <p class="description">Required for high-resolution vector tiles. If empty, the map will fallback to satellite imagery.</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="tls_save_map_settings" id="submit" class="button button-primary" value="Save Settings">
                </p>
            </form>
        </div>
    </div>
    <?php
}

// ============================================
// DRAW BOUNDARY PAGE
// ============================================
function tls_draw_boundary_page() {
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    
    if (isset($_POST['tls_save_boundary']) && wp_verify_nonce($_POST['tls_nonce'], 'tls_save_boundary')) {
        $boundary = $_POST['boundary_coords'];
        $boundary_style = $_POST['boundary_style'];
        if ($post_id && $boundary) {
            update_post_meta($post_id, '_tanah_boundary', sanitize_text_field($boundary));
            if ($boundary_style) {
                update_post_meta($post_id, '_tanah_boundary_style', sanitize_text_field($boundary_style));
            }
            echo '<div class="notice notice-success"><p>Boundary saved successfully!</p></div>';
        }
    }
    
    $posts = get_posts([
        'post_type' => 'tanah',
        'post_status' => ['publish', 'draft'],
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    $selected_post = $post_id ? get_post($post_id) : null;
    $boundary = $selected_post ? get_post_meta($post_id, '_tanah_boundary', true) : '';
    $lat = $selected_post ? get_post_meta($post_id, '_tanah_latitude', true) : '';
    $lng = $selected_post ? get_post_meta($post_id, '_tanah_longitude', true) : '';
    
    ?>
    <div class="wrap">
        <h1>Draw Land Boundary
            <a href="<?php echo admin_url('admin.php?page=tls-tanah-map'); ?>" class="button">Back to List</a>
        </h1>
        
        <div style="margin-bottom:20px; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #ccd0d4;">
            <label for="tanahSelect" style="font-weight: 600; margin-right: 10px;">Select Tanah Lot:</label>
            <select id="tanahSelect" onchange="window.location='<?php echo admin_url('admin.php?page=tls-tanah-boundary&post_id='); ?>'+this.value" style="padding:5px; min-width: 300px;">
                <option value="">-- Select tanah lot --</option>
                <?php foreach ($posts as $post): ?>
                <option value="<?php echo $post->ID; ?>" <?php selected($post_id, $post->ID); ?>><?php echo esc_html($post->post_title); ?> (ID: <?php echo $post->ID; ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if ($selected_post): ?>
        <?php
        $geran = get_post_meta($post_id, '_tanah_jenis_geran', true);
        $zoning = get_post_meta($post_id, '_tanah_zoning', true);
        $harga = get_post_meta($post_id, '_tanah_harga', true);
        $ekar = get_post_meta($post_id, '_tanah_keluasan', true);
        ?>
        
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0 0 5px 0;"><?php echo esc_html($selected_post->post_title); ?></h3>
                <p style="margin: 0; opacity: 0.9;"><?php echo esc_html($geran ?: '-'); ?> | <?php echo esc_html($zoning ?: '-'); ?></p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 24px; font-weight: bold;"><?php echo $ekar ? $ekar . ' ekar' : '-'; ?></div>
            </div>
        </div>
        
        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="background: #fff; padding: 15px; border-radius: 8px; flex: 1;">
                <h4 style="margin: 0 0 15px 0;">Quick Style By Geran</h4>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <button type="button" class="button style-by-geran" data-fill="#16a34a" data-stroke="#14532d" data-geran="NT">NT</button>
                    <button type="button" class="button style-by-geran" data-fill="#3b82f6" data-stroke="#1e40af" data-geran="CL">CL</button>
                    <button type="button" class="button style-by-geran" data-fill="#f59e0b" data-stroke="#92400e" data-geran="P">P</button>
                    <button type="button" class="button style-by-geran" data-fill="#8b5cf6" data-stroke="#4c1d95" data-geran="Hakmilik">Freehold</button>
                </div>
            </div>
            <div style="background: #fff; padding: 15px; border-radius: 8px; min-width: 250px;">
                <h4 style="margin: 0 0 15px 0;">Custom Colors</h4>
                <input type="color" id="fillColor" value="#16a34a"> Fill 
                <input type="color" id="strokeColor" value="#14532d"> Stroke
            </div>
        </div>
        
        <div style="display:flex; gap:10px; margin-bottom:15px; align-items: center;">
            <button type="button" id="startDrawBtn" class="button button-primary">Start Drawing</button>
            <button type="button" id="clearBtn" class="button">Clear All</button>
            <button type="button" id="undoBtn" class="button">Undo</button>
            <span id="pointCount" style="padding:6px 12px; background:#f0f0f1; border-radius:4px;">0 points</span>
            <span id="areaDisplay" style="padding:6px 12px; background:#fff7ed; border-radius:4px;">Area: 0 ekar</span>
        </div>
        
        <div id="boundaryMap" style="height:500px; width:100%; border-radius:8px;"></div>
        
        <form method="post" id="boundaryForm" style="margin-top:20px;">
            <?php wp_nonce_field('tls_save_boundary', 'tls_nonce'); ?>
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            <input type="hidden" name="boundary_coords" id="boundaryCoords" value="<?php echo esc_attr($boundary); ?>">
            <input type="hidden" name="boundary_style" id="boundaryStyle" value="">
            <button type="submit" name="tls_save_boundary" class="button button-primary button-large">Save Boundary</button>
        </form>
        
        <!-- Leaflet Resources -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.2.0/dist/maplibre-gl.css">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/maplibre-gl@4.2.0/dist/maplibre-gl.js"></script>
        <script src="https://unpkg.com/esri-leaflet@3.0.12/dist/esri-leaflet.js"></script>
        <script src="https://unpkg.com/esri-leaflet-vector@4.2.3/dist/esri-leaflet-vector.js"></script>
        
        <script>
        // GLOBAL ERROR SUPPRESSION - AGGRESSIVE
        (function() {
            var originalError = console.error;
            var originalWarn = console.warn;
            var suppressedStrings = ['missing required property', 'MaplibreGLLayer', 'style validation', 'version', 'sources', 'layers'];
            
            var shouldSuppress = function(args) {
                if (!args || !args[0]) return false;
                var msg = String(args[0]).toLowerCase();
                return suppressedStrings.some(function(s) { return msg.includes(s.toLowerCase()); });
            };

            console.error = function() {
                if (shouldSuppress(arguments)) return;
                originalError.apply(console, arguments);
            };
            console.warn = function() {
                if (shouldSuppress(arguments)) return;
                originalWarn.apply(console, arguments);
            };
            
            // Catch unhandled promise rejections which MapLibre sometimes uses for validation errors
            window.addEventListener('unhandledrejection', function(event) {
                if (shouldSuppress([event.reason])) {
                    event.preventDefault();
                }
            });
        })();

        document.addEventListener('DOMContentLoaded', function() {
            var mapLat = <?php echo floatval($lat ?: 5.9804); ?>;
            var mapLng = <?php echo floatval($lng ?: 116.0735); ?>;
            var geranType = '<?php echo esc_js($geran); ?>';
            var apiKey = '<?php echo esc_js(get_option('tlsmap_arcgis_api_key', '')); ?>';
            
            var drawingPoints = [];
            var isDrawing = false;
            var currentFillColor = '#16a34a';
            var currentStrokeColor = '#14532d';
            
            // Bounds for Sabah region
            var sabahBounds = L.latLngBounds([3.8, 115.0], [7.5, 119.5]);
            
            var map = L.map('boundaryMap', {
                maxBounds: sabahBounds,
                maxBoundsViscosity: 1.0,
                minZoom: 8
            }).setView([mapLat, mapLng], 16);

            // Ensure map container maintains dimensions (prevent collapse)
            var mapContainer = document.getElementById('boundaryMap');
            if (mapContainer) {
                mapContainer.style.minHeight = '500px';
                mapContainer.style.position = 'relative';
            }

            // Force map to recognize container size
            setTimeout(function() { map.invalidateSize(); }, 500);
            
            // USE STREETS AS PRIMARY FOR DRAWING (Better for context)
            var baseLayer;
            if (apiKey && typeof L.esri.Vector !== 'undefined') {
                try {
                    baseLayer = L.esri.Vector.vectorBasemapLayer('ArcGIS:Streets', {
                        apiKey: apiKey
                    });
                    baseLayer.on('error', function() {
                        map.removeLayer(baseLayer);
                        L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                            attribution: 'Tiles &copy; Esri'
                        }).addTo(map);
                    });
                    baseLayer.addTo(map);
                } catch(e) {
                    L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}').addTo(map);
                }
            } else {
                // FALLBACK TO RELIABLE RASTER STREETS
                L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri'
                }).addTo(map);
            }

            var polygonLayer = L.polygon([], {
                color: currentStrokeColor,
                fillColor: currentFillColor,
                fillOpacity: 0.4,
                weight: 3
            }).addTo(map);

            var markersLayer = L.layerGroup().addTo(map);

            function updateStyle(fill, stroke) {
                currentFillColor = fill;
                currentStrokeColor = stroke;
                
                polygonLayer.setStyle({
                    color: stroke,
                    fillColor: fill
                });
                
                document.getElementById('fillColor').value = fill;
                document.getElementById('strokeColor').value = stroke;
                
                document.getElementById('boundaryStyle').value = JSON.stringify({
                    fillColor: fill,
                    strokeColor: stroke,
                    geran: geranType
                });
            }

            function updatePolygon() {
                polygonLayer.setLatLngs(drawingPoints);
                
                markersLayer.clearLayers();
                drawingPoints.forEach(function(latlng, index) {
                    // Use L.marker with a divIcon for easier native dragging
                    var m = L.marker(latlng, {
                        draggable: true,
                        icon: L.divIcon({
                            className: 'drag-handle',
                            html: '<div style="width:12px;height:12px;background:#fff;border:2px solid '+currentStrokeColor+';border-radius:50%;"></div>',
                            iconSize: [12, 12],
                            iconAnchor: [6, 6]
                        })
                    }).addTo(markersLayer);
                    
                    m.on('drag', function(e) {
                        var newPos = e.target.getLatLng();
                        drawingPoints[index] = [newPos.lat, newPos.lng];
                        polygonLayer.setLatLngs(drawingPoints);
                        
                        // Update area in real-time
                        if (drawingPoints.length >= 3) {
                            var area = calculateArea(drawingPoints);
                            document.getElementById('areaDisplay').textContent = 'Area: ' + (area * 0.000247105).toFixed(3) + ' ekar';
                        }
                    });

                    m.on('dragend', function() {
                        document.getElementById('boundaryCoords').value = JSON.stringify(drawingPoints);
                    });
                });
                
                document.getElementById('pointCount').textContent = drawingPoints.length + ' points';
                document.getElementById('boundaryCoords').value = JSON.stringify(drawingPoints);
                
                if (drawingPoints.length >= 3) {
                    var area = calculateArea(drawingPoints);
                    document.getElementById('areaDisplay').textContent = 'Area: ' + (area * 0.000247105).toFixed(3) + ' ekar';
                }
            }
            
            function calculateArea(coords) {
                var radius = 6378137;
                var area = 0;
                for (var i = 0; i < coords.length; i++) {
                    var p1 = coords[i];
                    var p2 = coords[(i + 1) % coords.length];
                    area += ((p2[1] * Math.PI / 180) - (p1[1] * Math.PI / 180)) * (2 + Math.sin(p1[0] * Math.PI / 180) + Math.sin(p2[0] * Math.PI / 180));
                }
                return Math.abs(area * radius * radius / 2);
            }

            // Load existing
            var saved = document.getElementById('boundaryCoords').value;
            if (saved) {
                try {
                    var pts = JSON.parse(saved);
                    if (Array.isArray(pts)) {
                        drawingPoints = pts.map(function(p) {
                            if (p.lat !== undefined) return [p.lat, p.lng];
                            if (p[0] > 90 || p[0] < -90) return [p[1], p[0]]; 
                            return p;
                        });
                        updatePolygon();
                        if (drawingPoints.length > 0) {
                            map.fitBounds(polygonLayer.getBounds(), { padding: [50, 50] });
                        }
                    }
                } catch(e) {}
            }
            
            map.on('click', function(e) {
                if (!isDrawing) return;
                drawingPoints.push([e.latlng.lat, e.latlng.lng]);
                updatePolygon();
            });

            document.getElementById('startDrawBtn').addEventListener('click', function() {
                isDrawing = !isDrawing;
                this.textContent = isDrawing ? 'Stop Drawing' : 'Start Drawing';
                this.classList.toggle('button-primary');
                document.getElementById('boundaryMap').style.cursor = isDrawing ? 'crosshair' : '';
            });

            document.getElementById('clearBtn').addEventListener('click', function() {
                drawingPoints = [];
                updatePolygon();
            });

            document.getElementById('undoBtn').addEventListener('click', function() {
                drawingPoints.pop();
                updatePolygon();
            });

            document.querySelectorAll('.style-by-geran').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    updateStyle(this.dataset.fill, this.dataset.stroke);
                });
            });

            document.getElementById('fillColor').addEventListener('input', function() { updateStyle(this.value, currentStrokeColor); });
            document.getElementById('strokeColor').addEventListener('input', function() { updateStyle(currentFillColor, this.value); });
            
            updateStyle(currentFillColor, currentStrokeColor);
        });
        </script>
        <style>
            #boundaryMap { cursor: default; }
            .card { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
        </style>
        <?php else: ?>
        <p>Select a lot to start.</p>
        <?php endif; ?>
    </div>
    <?php
}
