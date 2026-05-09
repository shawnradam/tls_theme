// TLS Map v4.0 - Direct AJAX loading with fallback
// Uses REST API to fetch properties
console.log('=== TLS Map v4.0 LOADED ===');

document.addEventListener('DOMContentLoaded', function() {
    const mapContainers = document.querySelectorAll('.tlsmap-container');
    const sidebarListings = document.getElementById('map-sidebar-listings');
    const resultsCountEl = document.getElementById('map-results-count');
    const sidebarSearch = document.getElementById('map-sidebar-search');
    
    const statusColors = {
        'available': { fill: '#16a34a', label: 'Available' },
        'reserved': { fill: '#f59e0b', label: 'Reserved' },
        'sold': { fill: '#ef4444', label: 'Sold' }
    };

    function formatSqFt(acres) {
        return (parseFloat(acres || 0) * 43560).toLocaleString(undefined, { maximumFractionDigits: 0 }) + ' SqFt';
    }

    mapContainers.forEach(container => {
        const lat = parseFloat(container.dataset.lat) || 6.13;
        const lng = parseFloat(container.dataset.lng) || 116.23;
        const zoom = parseInt(container.dataset.zoom) || 12;
        const id = container.id;

        // Create info panel
        const infoPanel = document.createElement('div');
        infoPanel.className = 'tls-info-panel';
        infoPanel.innerHTML = '<div class="close-panel" onclick="this.parentElement.style.display=\'none\'"><i class="material-icons" style="font-size:20px;">close</i></div><div class="panel-inner"></div>';
        container.appendChild(infoPanel);

        // Initialize map
        const map = L.map(id, {
            maxZoom: 22,
            zoomControl: false,
            fullscreenControl: true
        }).setView([lat, lng], zoom);

        window.tlsMap = map;

        // Add tile layers
        const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OSM',
            maxZoom: 22
        });
        
        const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '© Esri',
            maxZoom: 22
        });
        
        osm.addTo(map);
        L.control.layers({ "Local Map": osm, "Satellite View": satellite }).addTo(map);
        L.control.zoom({ position: 'topleft' }).addTo(map);

        // GeoJSON layer for boundaries
        const lotLayer = L.geoJSON(null, {
            style: function(feature) {
                const status = feature.properties?.status || 'available';
                const color = statusColors[status]?.fill || '#16a34a';
                return { color: color, weight: 2, fillColor: color, fillOpacity: 0.3 };
            },
            onEachFeature: function(feature, layer) {
                layer.on('click', function(e) {
                    L.DomEvent.stopPropagation(e);
                    updateInfoPanel(feature.properties, layer.getBounds().getCenter());
                    highlightSidebarCard(feature.properties.dbId);
                });
            }
        }).addTo(map);

        let allProperties = [];

        function updateInfoPanel(props, latlng) {
            const inner = infoPanel.querySelector('.panel-inner');
            if (!inner) return;
            
            inner.innerHTML = `
                <h3>${props.name || props.title || 'Property'}</h3>
                <p class="price">RM ${(props.price || 0).toLocaleString()}</p>
                <p class="meta">${props.area || props.ekar || '0'} ekar • ${props.grant_no || props.geran || props.grant || 'N/A'}</p>
                ${props.link ? `<a href="${props.link}" class="btn-detail" target="_blank">View Details</a>` : ''}
            `;
            infoPanel.style.display = 'block';
        }

        function highlightSidebarCard(dbId) {
            document.querySelectorAll('.portal-listing-card').forEach(c => c.classList.remove('active'));
            const card = document.querySelector(`.portal-listing-card[data-id="${dbId}"]`);
            if (card) {
                card.classList.add('active');
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function renderSidebar(props) {
            if (!sidebarListings) return;
            sidebarListings.innerHTML = '';
            resultsCountEl.textContent = props.length;

            if (props.length === 0) {
                sidebarListings.innerHTML = '<p style="text-align:center; padding:20px;">Tiada hartanah dijumpai.</p>';
                return;
            }

            props.forEach(p => {
                const card = document.createElement('div');
                card.className = 'portal-listing-card';
                card.dataset.id = p.id || p.dbId;
                card.dataset.name = (p.name || p.title || '').toLowerCase();
                
                const statusInfo = statusColors[p.status] || statusColors.available;
                const imgSrc = p.image || p.img || 'https://tanahlotsabah.com/wp-content/themes/tlstheme/assets/images/placeholder.jpeg';
                
                card.innerHTML = `
                    <img src="${imgSrc}" class="portal-card-thumb" alt="${p.name || p.title}">
                    <div class="portal-card-info">
                        <div class="portal-card-top">
                            <span class="portal-card-status" style="background:${statusInfo.fill}20; color:${statusInfo.fill};">${statusInfo.label}</span>
                        </div>
                        <h4 class="portal-card-title">${p.name || p.title}</h4>
                        <div class="portal-card-price">RM ${(p.price || 0).toLocaleString()}</div>
                        <div class="portal-card-meta">${p.area || p.ekar || '0'} ekar • ${formatSqFt(p.area || p.ekar)}</div>
                    </div>
                `;
                
                card.onclick = () => {
                    document.querySelectorAll('.portal-listing-card').forEach(c => c.classList.remove('active'));
                    card.classList.add('active');
                    if (p.lat && p.lng && parseFloat(p.lat) !== 0) {
                        map.setView([parseFloat(p.lat), parseFloat(p.lng)], 16);
                    }
                };
                
                sidebarListings.appendChild(card);
            });
        }

        // Hide skeleton
        const skeleton = document.getElementById('map-skeleton');
        if (skeleton) skeleton.style.display = 'none';

        // Fetch properties via AJAX
        console.log('Fetching properties via AJAX...');
        fetch(`${tlsmapConfig.ajaxUrl}?action=tls_get_lots`)
            .then(res => res.json())
            .then(data => {
                console.log('AJAX Response:', data);
                
                if (data.success && data.data) {
                    const properties = data.data.properties || [];
                    console.log('Got', properties.length, 'properties from AJAX');
                    
                    allProperties = properties;
                    
                    // Add markers and boundaries to map
                    properties.forEach(prop => {
                        const propLat = parseFloat(prop.lat) || 0;
                        const propLng = parseFloat(prop.lng) || 0;
                        
                        // Add boundary if exists
                        if (prop.boundary) {
                            try {
                                let boundary = prop.boundary;
                                if (typeof boundary === 'string') {
                                    boundary = JSON.parse(boundary);
                                }
                                
                                if (boundary && boundary.type === 'Polygon' && boundary.coordinates) {
                                    const geoJson = {
                                        type: 'Polygon',
                                        coordinates: boundary.coordinates,
                                        properties: {
                                            dbId: prop.id,
                                            name: prop.name,
                                            area: prop.area,
                                            price: prop.price,
                                            status: prop.status,
                                            grant_no: prop.grant,
                                            link: prop.link
                                        }
                                    };
                                    lotLayer.addData(geoJson);
                                    console.log('Added polygon for:', prop.name);
                                }
                            } catch (e) {
                                console.warn('Boundary parse error:', e);
                            }
                        }
                        // Add circle marker if has coords
                        else if (propLat !== 0 && propLng !== 0) {
                            const statusColor = statusColors[prop.status]?.fill || '#16a34a';
                            L.circleMarker([propLat, propLng], {
                                radius: 10,
                                fillColor: statusColor,
                                color: '#fff',
                                weight: 2,
                                fillOpacity: 1
                            }).addTo(map).on('click', function(e) {
                                L.DomEvent.stopPropagation(e);
                                updateInfoPanel({
                                    dbId: prop.id,
                                    name: prop.name,
                                    area: prop.area,
                                    price: prop.price,
                                    status: prop.status,
                                    grant_no: prop.grant,
                                    link: prop.link
                                }, [propLat, propLng]);
                                highlightSidebarCard(prop.id);
                            });
                            console.log('Added marker for:', prop.name, 'at', propLat, propLng);
                        }
                    });
                    
                    // Render sidebar
                    renderSidebar(properties);
                    console.log('Sidebar rendered with', properties.length, 'items');
                } else {
                    console.log('No properties found in response');
                    renderSidebar([]);
                }
            })
            .catch(err => {
                console.error('AJAX Error:', err);
                console.log('Fallback: checking TLS_LOTS variable');
                
                // Fallback to TLS_LOTS variable if AJAX fails
                if (typeof TLS_LOTS !== 'undefined' && TLS_LOTS.length > 0) {
                    console.log('Using TLS_LOTS fallback:', TLS_LOTS.length, 'properties');
                    allProperties = TLS_LOTS;
                    renderSidebar(TLS_LOTS);
                } else {
                    console.log('No data available');
                    renderSidebar([]);
                }
            });

        // Search functionality
        if (sidebarSearch) {
            sidebarSearch.addEventListener('input', function(e) {
                const filter = e.target.value.toLowerCase();
                const filtered = allProperties.filter(p => 
                    (p.name || p.title || '').toLowerCase().includes(filter) ||
                    (p.geran || p.grant || '').toLowerCase().includes(filter)
                );
                renderSidebar(filtered);
            });
        }
    });
});