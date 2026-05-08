document.addEventListener('DOMContentLoaded', function() {
    const mapContainers = document.querySelectorAll('.tlsmap-container');
    const sidebarListings = document.getElementById('map-sidebar-listings');
    const resultsCountEl = document.getElementById('map-results-count');
    const sidebarSearch = document.getElementById('map-sidebar-search');

    const statusColors = {
        'available': { fill: '#16a34a', label: 'Available', class: 'available' },
        'reserved': { fill: '#f59e0b', label: 'Reserved', class: 'reserved' },
        'sold': { fill: '#ef4444', label: 'Sold', class: 'sold' }
    };

    function formatSqFt(acres) {
        return (parseFloat(acres) * 43560).toLocaleString(undefined, { maximumFractionDigits: 0 }) + ' SqFt';
    }

    mapContainers.forEach(container => {
        const lat = parseFloat(container.dataset.lat) || 6.13;
        const lng = parseFloat(container.dataset.lng) || 116.23;
        const zoom = parseInt(container.dataset.zoom) || 12;
        const id = container.id;

        const infoPanel = document.createElement('div');
        infoPanel.className = 'tls-info-panel';
        infoPanel.innerHTML = '<div class="close-panel"><i class="material-icons" style="font-size:20px;">close</i></div><div class="panel-inner"></div>';
        container.appendChild(infoPanel);
        const closeBtn = infoPanel.querySelector('.close-panel');
        closeBtn.onclick = () => { infoPanel.style.display = 'none'; };

        const map = L.map(id, {
            maxZoom: 22,
            zoomControl: false,
            fullscreenControl: true
        }).setView([lat, lng], zoom);

        window.tlsMap = map;

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

        const lotLayer = L.geoJSON(null, {
            onEachFeature: function(feature, layer) {
                layer.on({
                    click: function(e) {
                        L.DomEvent.stopPropagation(e);
                        handlePropertyClick(feature.properties, layer.getBounds().getCenter());
                    }
                });
            }
        }).addTo(map);

        let allProperties = [];

        function handlePropertyClick(props, latlng) {
            if (sidebarListings) {
                const card = document.querySelector(`.portal-listing-card[data-id="${props.dbId}"]`);
                if (card) {
                    document.querySelectorAll('.portal-listing-card').forEach(c => c.classList.remove('active'));
                    card.classList.add('active');
                }
            }
            updateInfoPanel(props, latlng);
        }

        function updateInfoPanel(props, latlng) {
            if (!infoPanel) return;
            const statusInfo = statusColors[props.status] || statusColors.available;
            const inner = infoPanel.querySelector('.panel-inner');
            if (!inner) return;
            
            inner.innerHTML = `
                <h3>${props.name || props.title}</h3>
                <p class="price">RM ${(props.price || 0).toLocaleString()}</p>
                <p class="meta">${props.area || '0'} ekar • ${props.grant_no || 'N/A'}</p>
                <a href="${props.link || '#'}" class="btn-detail" target="_blank">View Details</a>
            `;
            infoPanel.style.display = 'block';
        }

        function renderSidebar(props) {
            if (!sidebarListings) return;
            sidebarListings.innerHTML = '';
            resultsCountEl.innerText = props.length;

            props.forEach(p => {
                const card = document.createElement('div');
                card.className = 'portal-listing-card';
                card.dataset.id = p.dbId;
                card.innerHTML = `
                    <img src="${p.image || tlsmapConfig.themeUri + '/assets/images/placeholder.jpeg'}" class="portal-card-thumb">
                    <div class="portal-card-info">
                        <h4 class="portal-card-title">${p.name}</h4>
                        <div class="portal-card-price">RM ${(p.price || 0).toLocaleString()}</div>
                        <div class="portal-card-meta">${p.area} ekar • ${formatSqFt(p.area)}</div>
                    </div>
                `;
                card.onclick = () => {
                    document.querySelectorAll('.portal-listing-card').forEach(c => c.classList.remove('active'));
                    card.classList.add('active');
                    if (p.lat && p.lng) map.setView([p.lat, p.lng], 16);
                };
                sidebarListings.appendChild(card);
            });
        }

        // ================================================
        // LOAD DATA FROM TLS_LOTS VARIABLE
        // ================================================
        console.log('=== TLS Map Loading ===');
        console.log('TLS_LOTS:', typeof TLS_LOTS, TLS_LOTS ? TLS_LOTS.length : 'N/A');
        
        const skeleton = document.getElementById('map-skeleton');
        if (skeleton) skeleton.style.display = 'none';

        if (typeof TLS_LOTS !== 'undefined' && TLS_LOTS.length > 0) {
            console.log('Processing', TLS_LOTS.length, 'properties');
            
            TLS_LOTS.forEach((prop, idx) => {
                const lat = parseFloat(prop.lat) || 0;
                const lng = parseFloat(prop.lng) || 0;
                
                const pData = {
                    dbId: prop.dbId,
                    name: prop.name,
                    area: prop.area,
                    status: prop.status || 'available',
                    image: prop.image,
                    price: prop.price,
                    grant_no: prop.grant_no,
                    link: prop.link,
                    lat: lat,
                    lng: lng,
                    boundary: prop.boundary
                };
                
                allProperties.push(pData);
                console.log('Property', idx + 1, ':', prop.name, 'lat:', lat, 'lng:', lng);
                
                // Add to map
                if (lat !== 0 && lng !== 0) {
                    L.circleMarker([lat, lng], {
                        radius: 10,
                        fillColor: '#16a34a',
                        color: '#fff',
                        weight: 2
                    }).addTo(map).on('click', (e) => {
                        L.DomEvent.stopPropagation(e);
                        handlePropertyClick(pData, [lat, lng]);
                    });
                }
            });
            
            console.log('Render sidebar with', allProperties.length, 'items');
            renderSidebar(allProperties);
        } else {
            console.log('TLS_LOTS empty or not found');
        }

        // Search
        if (sidebarSearch) {
            sidebarSearch.oninput = (e) => {
                const val = e.target.value.toLowerCase();
                const filtered = allProperties.filter(p => 
                    p.name.toLowerCase().includes(val)
                );
                renderSidebar(filtered);
            };
        }
    });
});