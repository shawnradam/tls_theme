document.addEventListener('DOMContentLoaded', function() {
    const mapContainers = document.querySelectorAll('.tlsmap-container');
const sidebarListings = document.getElementById('map-sidebar-listings');
    const resultsCountEl = document.getElementById('map-results-count');
    const sidebarSearch = document.getElementById('map-sidebar-search');
    
    console.log('=== DOM Elements Check ===');
    console.log('sidebarListings found:', sidebarListings !== null);
    console.log('resultsCountEl found:', resultsCountEl !== null);
    console.log('sidebarSearch found:', sidebarSearch !== null);
    console.log('=======================');

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
            console.log('=== renderSidebar called with', props.length, 'properties ===');
            if (!sidebarListings) {
                console.log('ERROR: sidebarListings element not found!');
                return;
            }
            sidebarListings.innerHTML = '';
            resultsCountEl.innerText = props.length;
            console.log('Rendering', props.length, 'property cards');

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
        console.log('====================');

        // Use AJAX to load data from server - this ensures data is always loaded
        console.log('Loading data via AJAX...');
        console.log('AJAX URL:', tlsmapConfig.ajaxUrl);
        
        fetch(`${tlsmapConfig.ajaxUrl}?action=tls_get_lots`)
            .then(res => {
                console.log('Response status:', res.status);
                console.log('Response headers:', res.headers);
                return res.text();
            })
            .then(text => {
                console.log('Raw response:', text.substring(0, 1000));
                try {
                    const res = JSON.parse(text);
                    console.log('Parsed JSON response:', JSON.stringify(res).substring(0, 500));
                    const skeleton = document.getElementById('map-skeleton');
                    if (skeleton) skeleton.style.display = 'none';

                    if (res.success && res.data && res.data.properties) {
                        console.log('Processing', res.data.properties.length, 'properties from AJAX');
                        res.data.properties.forEach(prop => {
                            const lat = parseFloat(prop.lat || 0);
                            const lng = parseFloat(prop.lng || 0);
                            
                            const pData = {
                                dbId: prop.id,
                                name: prop.name,
                                area: prop.area || '0',
                                status: prop.status || 'available',
                                image: prop.image || '',
                                price: prop.price || 0,
                                grant_no: prop.grant || 'N/A',
                                link: prop.link || '#',
                                lat: lat,
                                lng: lng,
                                boundary: prop.boundary
                            };
                            
                            allProperties.push(pData);
                            console.log('Added property:', prop.name, 'lat:', lat, 'lng:', lng);

                            if (prop.boundary && lat !== 0 && lng !== 0) {
                                try {
                                    let geoData = typeof prop.boundary === 'string' ? JSON.parse(prop.boundary) : prop.boundary;
                                    if (geoData && geoData.type === 'Polygon' && geoData.coordinates) {
                                        geoData.properties = pData;
                                        lotLayer.addData(geoData);
                                    }
                                } catch (e) { console.warn('Boundary error:', e); }
                            } else if (lat !== 0 && lng !== 0) {
                                L.circleMarker([lat, lng], {
                                    radius: 10,
                                    fillColor: statusColors[pData.status]?.fill || "#16a34a",
                                    color: "#fff",
                                    weight: 2
                                }).addTo(map).on('click', (e) => {
                                    handlePropertyClick(pData, [lat, lng]);
                                });
                            }
                        });
                    } else if (res.data && res.data.manual) {
                        console.log('Processing', res.data.manual.length, 'manual lots from AJAX');
                        res.data.manual.forEach(prop => {
                            const pData = {
                                dbId: prop.id,
                                name: prop.lot_name,
                                area: prop.area_size || '0',
                                status: prop.lot_status || 'available',
                                image: prop.lot_image || '',
                                price: prop.lot_price || 0,
                                grant_no: prop.lot_grant || 'N/A',
                                link: '#',
                                lat: 0,
                                lng: 0
                            };
                            allProperties.push(pData);
                        });
                    }
                    
                    console.log('Calling renderSidebar with', allProperties.length, 'properties');
                    renderSidebar(allProperties);
                    console.log('Done loading properties');
                } catch (err) {
                    console.error('JSON parse error:', err);
                    console.error('Raw text that failed:', text.substring(0, 500));
                }
            })
            .catch(err => {
                console.error('AJAX error:', err);
            });
    });

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
