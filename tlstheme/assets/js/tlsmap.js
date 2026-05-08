document.addEventListener('DOMContentLoaded', function() {
    const mapContainers = document.querySelectorAll('.tlsmap-container');
    const sidebarListings = document.getElementById('map-sidebar-listings');
    const resultsCountEl = document.getElementById('map-results-count');
    const sidebarSearch = document.getElementById('map-sidebar-search');

    const colors = tlsmapConfig.colors || {
        available: '#16a34a',
        reserved: '#f59e0b',
        sold: '#ef4444'
    };

    const statusColors = {
        'available': { fill: colors.available, label: 'Available', class: 'available' },
        'reserved': { fill: colors.reserved, label: 'Reserved', class: 'reserved' },
        'sold': { fill: colors.sold, label: 'Sold', class: 'sold' },
        'development': { fill: '#8b5cf6', label: 'Development Phase', class: 'development' }
    };

    function formatSqFt(acres) {
        return (parseFloat(acres) * 43560).toLocaleString(undefined, { maximumFractionDigits: 0 }) + ' SqFt';
    }

    mapContainers.forEach(container => {
        const lat = parseFloat(container.dataset.lat) || parseFloat(tlsmapConfig.defaultLat);
        const lng = parseFloat(container.dataset.lng) || parseFloat(tlsmapConfig.defaultLng);
        const zoom = parseInt(container.dataset.zoom) || parseInt(tlsmapConfig.defaultZoom);
        const id = container.id;

        const isPostEditor = tlsmapConfig.isPostEditor && (id === 'tls_tanah_editor' || id === 'tls_tanah_editor_meta');

        let infoPanel = null;
        if (!isPostEditor) {
            infoPanel = document.createElement('div');
            infoPanel.className = 'tls-info-panel';
            infoPanel.innerHTML = '<div class="close-panel"><i class="material-icons" style="font-size:20px;">close</i></div><div class="panel-inner"></div>';
            container.appendChild(infoPanel);
            const closeBtn = infoPanel.querySelector('.close-panel');
            closeBtn.onclick = () => { infoPanel.style.display = 'none'; };
            
            // ESC key to close info panel
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && infoPanel.style.display === 'block') {
                    infoPanel.style.display = 'none';
                }
            });
        }

        const map = L.map(id, {
            maxZoom: 22,
            zoomControl: false,
            fullscreenControl: true,
            fullscreenControlOptions: { position: 'topleft' },
            rotate: true,
            touchRotate: true,
            shiftKeyRotate: true,
            attributionControl: true
        }).setView([lat, lng], zoom);

        window.tlsMap = map;

        const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OSM',
            detectRetina: true,
            maxZoom: 22,
            maxNativeZoom: 19
        });
        
        const satellite = L.layerGroup([
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: '© Esri',
                maxZoom: 22,
                maxNativeZoom: 19
            }),
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 22,
                maxNativeZoom: 19
            })
        ]);
        
        osm.addTo(map);
        L.control.layers({ "Local Map": osm, "Satellite View": satellite }).addTo(map);
        L.control.zoom({ position: 'topleft' }).addTo(map);

        const lotLayer = L.geoJSON(null, {
            onEachFeature: function(feature, layer) {
                const props = feature.properties;
                layer.on({
                    mouseover: function() { this.setStyle({ weight: 4, opacity: 1, fillOpacity: 0.5 }); },
                    mouseout: function() { this.setStyle({ weight: 1.5, opacity: 0.8, fillOpacity: 0.3 }); },
                    click: function(e) {
                        L.DomEvent.stopPropagation(e);
                        handlePropertyClick(props, layer.getBounds().getCenter());
                    }
                });
            },
            style: function(feature) {
                const statusInfo = statusColors[feature.properties.status] || statusColors.available;
                return { color: "#000", weight: 1.5, opacity: 0.8, fillColor: statusInfo.fill, fillOpacity: 0.3 };
            }
        }).addTo(map);

        let allProperties = [];

        function handlePropertyClick(props, latlng) {
            if (sidebarListings) {
                const card = document.querySelector(`.portal-listing-card[data-id="${props.dbId}"]`);
                if (card) {
                    document.querySelectorAll('.portal-listing-card').forEach(c => c.classList.remove('active'));
                    card.classList.add('active');
                    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
            
            // On mobile, hide sidebar FIRST, then show info panel
            if (window.innerWidth <= 768) {
                const sidebar = document.querySelector('.map-portal-sidebar');
                sidebar.classList.remove('show');
                document.getElementById('portal-view-btn').innerHTML = '<i class="fas fa-list"></i> Lihat Senarai';
            }
            
            // Show info panel on both desktop and mobile
            updateInfoPanel(props, latlng);
            
            // Resize map after sidebar hides
            if (window.innerWidth <= 768) {
                setTimeout(function() {
                    map.invalidateSize();
                }, 350);
            }
            
            map.panTo(latlng);
        }

        function updateInfoPanel(props, latlng) {
            if (!infoPanel) return;
            
            const statusInfo = statusColors[props.status] || statusColors.available;
            const inner = infoPanel.querySelector('.panel-inner');
            if (!inner) return;
            
            const lat = props.lat || (latlng && latlng.lat) || (latlng && latlng[0]) || '';
            const lng = props.lng || (latlng && latlng.lng) || (latlng && latlng[1]) || '';
            
            const linkHtml = (props.link && props.link !== false && props.link !== 'false') 
                ? `<a href="${props.link}" target="_blank" class="panel-btn panel-btn-primary">View Property Details</a>` 
                : '';
            
            let navHtml = '';
            if (lat && lng) {
                navHtml = `
                    <div class="panel-navigation">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&travelmode=driving" target="_blank" class="panel-btn panel-btn-nav">
                            <i class="material-icons">directions_car</i>
                            Drive
                        </a>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&travelmode=walking" target="_blank" class="panel-btn panel-btn-nav">
                            <i class="material-icons">directions_walk</i>
                            Walk
                        </a>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&travelmode=bicycling" target="_blank" class="panel-btn panel-btn-nav">
                            <i class="material-icons">directions_bike</i>
                            Motorcycle
                        </a>
                    </div>
                    <button onclick="window.findNearbyShops(${lat}, ${lng})" class="panel-btn panel-btn-shops">
                        <i class="material-icons">shopping_cart</i>
                        Nearby Shops
                    </button>`;
            }
            
            inner.innerHTML = `
                <img src="${props.image || tlsmapConfig.themeUri + '/assets/images/placeholder.jpeg'}" class="panel-image">
                <div class="panel-body">
                    <span class="status-pill ${statusInfo.class}">${statusInfo.label}</span>
                    <h3>${props.name}</h3>
                    <span class="price-tag">RM ${props.price || 'Contact'}</span>
                    <div class="detail-grid">
                        <div class="detail-item"><span class="detail-label">Size</span><span class="detail-value">${props.area} Acres</span></div>
                        <div class="detail-item"><span class="detail-label">Geran</span><span class="detail-value">${props.grant_no || 'N/A'}</span></div>
                    </div>
                    ${linkHtml}
                    ${navHtml}
                </div>
            `;
            infoPanel.style.display = 'block';
        }

        function renderSidebar(props) {
            if (!sidebarListings) return;
            sidebarListings.innerHTML = '';
            resultsCountEl.innerText = props.length;

            props.forEach(p => {
                const statusInfo = statusColors[p.status] || statusColors.available;
                const card = document.createElement('div');
                card.className = 'portal-listing-card';
                card.dataset.id = p.dbId;
                card.innerHTML = `
                    <img src="${p.image || tlsmapConfig.themeUri + '/assets/images/placeholder.jpeg'}" class="portal-card-thumb">
                    <div class="portal-card-info">
                        <div class="portal-card-top">
                            <span class="portal-card-status ${statusInfo.class}" style="background:${statusInfo.fill}20; color:${statusInfo.fill};">${statusInfo.label}</span>
                            <div class="portal-card-actions">
                                <button class="action-btn map-zoom-btn" title="Show on Map"><i class="material-icons">place</i></button>
                                ${p.link ? `<a href="${p.link}" class="action-btn details-btn" title="View Details"><i class="material-icons">open_in_new</i></a>` : ''}
                            </div>
                        </div>
                        <h4 class="portal-card-title">${p.name}</h4>
                        <div class="portal-card-price">RM ${p.price || 'Hubungi'}</div>
                        <div class="portal-card-meta">${p.area} ekar • ${formatSqFt(p.area)}</div>
                    </div>
                `;

                const zoomBtn = card.querySelector('.map-zoom-btn');
                zoomBtn.onclick = (e) => {
                    e.stopPropagation();
                    zoomToProperty(p);
                };

                card.onclick = () => {
                    document.querySelectorAll('.portal-listing-card').forEach(c => c.classList.remove('active'));
                    card.classList.add('active');
                    zoomToProperty(p);
                };
                sidebarListings.appendChild(card);
            });
        }

        function zoomToProperty(p) {
            // Store selected property globally for mobile map view
            window.selectedProperty = p;
            
            try {
                if (p.boundary) {
                    const geoJson = JSON.parse(p.boundary);
                    const layer = L.geoJSON(geoJson);
                    const bounds = layer.getBounds();
                    if (bounds.isValid()) {
                        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 18 });
                    } else if (p.lat && p.lng) {
                        map.setView([p.lat, p.lng], 16);
                    }
                } else if (p.lat && p.lng) {
                    map.setView([p.lat, p.lng], 16);
                }
            } catch(e) {
                if (p.lat && p.lng) {
                    map.setView([p.lat, p.lng], 16);
                }
            }
            
            // Show info panel on both desktop and mobile
            updateInfoPanel(p, map.getCenter());
            
            // On mobile, hide sidebar to show map with info panel
            if (window.innerWidth <= 768) {
                const sidebar = document.querySelector('.map-portal-sidebar');
                if (sidebar && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    document.getElementById('portal-view-btn').innerHTML = '<i class="fas fa-list"></i> Lihat Senarai';
                    setTimeout(function() {
                        map.invalidateSize();
                    }, 350);
                }
            }
        }

        // Use TLS_LOTS (loaded inline by inc/inc-map-data.php) - THIS IS THE PRIMARY DATA SOURCE
        var tlsDebug = [];
        tlsDebug.push('TLSMap init');
        tlsDebug.push('TLS_LOTS defined: ' + (typeof TLS_LOTS !== 'undefined'));
        tlsDebug.push('TLS_LOTS length: ' + (typeof TLS_LOTS !== 'undefined' ? TLS_LOTS.length : 'N/A'));

        // Check TLS_LOTS status
        console.log('=== TLS Map Debug ===');
        console.log('TLS_LOTS type:', typeof TLS_LOTS);
        console.log('TLS_LOTS length:', typeof TLS_LOTS !== 'undefined' ? TLS_LOTS.length : 'N/A');
        if (typeof TLS_LOTS !== 'undefined' && TLS_LOTS.length > 0) {
            console.log('Sample TLS_LOTS[0]:', JSON.stringify(TLS_LOTS[0]));
        }
        console.log('====================');

        if (typeof TLS_LOTS !== 'undefined' && TLS_LOTS.length > 0) {
            tlsDebug.push('Using TLS_LOTS as source');
            const skeleton = document.getElementById('map-skeleton');
            if (skeleton) skeleton.style.display = 'none';

            TLS_LOTS.forEach(function(prop, idx) {
                const lat = parseFloat(prop.lat || prop.latitude || 0);
                const lng = parseFloat(prop.lng || prop.longitude || 0);
                console.log('Processing prop ' + idx + ':', prop.name || prop.title, 'lat:', lat, 'lng:', lng, 'boundary:', prop.boundary ? 'yes' : 'no');
                
                const pData = {
                    dbId: prop.dbId || prop.id || prop.ID,
                    name: prop.name || prop.title || prop.post_title,
                    area: prop.area || prop.ekar || prop.size,
                    status: prop.status || 'available',
                    image: prop.image || prop.img || prop.thumbnail,
                    price: prop.price || 0,
                    grant_no: prop.grant || prop.geran || prop.grant_no || prop.type,
                    link: prop.link || prop.permalink || prop.url,
                    lat: lat,
                    lng: lng,
                    boundary: prop.boundary
                };
                
                allProperties.push(pData);

                // Handle boundary - convert simple array to GeoJSON if needed
                console.log('DEBUG: prop.boundary for', prop.name || prop.title, ':', JSON.stringify(prop.boundary).substring(0, 200));
                if (prop.boundary) {
                    try {
                        let geoData = prop.boundary;
                        if (typeof geoData === 'string') {
                            geoData = JSON.parse(geoData);
                        }
                        
                        // Check if it's a simple array (not GeoJSON)
                        if (Array.isArray(geoData) && geoData.length > 0) {
                            // Convert [[lat,lng], [lat,lng],...] to GeoJSON Polygon
                            // Note: GeoJSON uses [lng, lat] order
                            if (typeof geoData[0] === 'object' && !geoData[0].type) {
                                const polygonCoords = [geoData.map(function(coord) {
                                    // coord[0] = lat, coord[1] = lng in your data
                                    // GeoJSON needs [lng, lat] so we swap
                                    return [coord[1], coord[0]]; // [lng, lat]
                                })];
                                geoData = {
                                    type: 'Polygon',
                                    coordinates: polygonCoords
                                };
                                console.log('Converted simple array to GeoJSON Polygon');
                            }
                        }
                        
                        if (geoData && geoData.type === 'Polygon' && geoData.coordinates) {
                            geoData.properties = pData;
                            lotLayer.addData(geoData);
                            console.log('Added Polygon boundary for:', prop.name || prop.title);
                        } else if (geoData && geoData.type === 'Point' && geoData.coordinates) {
                            geoData.properties = pData;
                            lotLayer.addData(geoData);
                            console.log('Added Point boundary for:', prop.name || prop.title);
                        } else {
                            console.log('Boundary format not recognized:', geoData);
                        }
                    } catch (e) { console.warn('Boundary parse error:', e); }
                } else if (lat !== 0 && lng !== 0) {
                    L.circleMarker([lat, lng], {
                        radius: 8,
                        fillColor: statusColors[pData.status]?.fill || "#1a73e8",
                        color: "#fff",
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 1
                    })
                    .addTo(map)
                    .on('click', (e) => {
                        L.DomEvent.stopPropagation(e);
                        handlePropertyClick(pData, [lat, lng]);
                    });
                    console.log('Added circle marker for:', prop.name || prop.title);
                } else {
                    console.log('No coordinates or boundary for:', prop.name || prop.title);
                }
            });
            console.log('Total markers added:', allProperties.length);
            tlsDebug.push('Total processed: ' + allProperties.length);
            tlsDebug.push('Calling renderSidebar');
            renderSidebar(allProperties);
            tlsDebug.push('renderSidebar done');

            // Send debug log to server
            fetch(tlsmapConfig.ajaxUrl + '?action=tls_log_debug', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'data=' + encodeURIComponent(JSON.stringify(tlsDebug))
            }).catch(function() {});
        } else {
            tlsDebug.push('TLS_LOTS empty/undefined, using AJAX fallback');
            // Fallback to AJAX if TLS_LOTS is not available
            fetch(`${tlsmapConfig.ajaxUrl}?action=tls_get_lots`)
                .then(res => res.json())
                .then(res => {
                    const skeleton = document.getElementById('map-skeleton');
                    if (skeleton) skeleton.style.display = 'none';

                    if (res.success && res.data && res.data.properties) {
                        res.data.properties.forEach(prop => {
                            const pData = {
                                dbId: prop.id,
                                name: prop.name,
                                area: prop.area,
                                status: prop.status,
                                image: prop.image,
                                price: prop.price,
                                grant_no: prop.grant,
                                link: prop.link,
                                lat: prop.lat,
                                lng: prop.lng,
                                boundary: prop.boundary
                            };
                            allProperties.push(pData);

                            if (prop.boundary) {
                                try {
                                    const geoData = JSON.parse(prop.boundary);
                                    geoData.properties = pData;
                                    lotLayer.addData(geoData);
                                } catch (e) {}
                            } else if (prop.lat && prop.lng) {
                                L.circleMarker([prop.lat, prop.lng], {
                                    radius: 8,
                                    fillColor: statusColors[prop.status]?.fill || "#1a73e8",
                                    color: "#fff",
                                    weight: 2,
                                    opacity: 1,
                                    fillOpacity: 1
                                })
                                .addTo(map)
                                .on('click', (e) => {
                                    L.DomEvent.stopPropagation(e);
                                    handlePropertyClick(pData, [prop.lat, prop.lng]);
                                });
                            }
                        });
                        renderSidebar(allProperties);
                    }
                });
        }

        if (sidebarSearch) {
            sidebarSearch.oninput = (e) => {
                const val = e.target.value.toLowerCase();
                const filtered = allProperties.filter(p => 
                    p.name.toLowerCase().includes(val) || 
                    (p.grant_no && p.grant_no.toLowerCase().includes(val))
                );
                renderSidebar(filtered);
            };
        }

        window.findNearbyShops = function(lat, lng) {
            if (!lat || !lng) {
                return;
            }
            const query = encodeURIComponent('shopping mall near ' + lat + ',' + lng);
            window.open(`https://www.google.com/maps/search/?api=1&query=${query}`, '_blank');
        };
    });
});
