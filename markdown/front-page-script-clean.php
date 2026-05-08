<script>
// Global variable to store selected property for mobile map view
let selectedProperty = null;

function toggleMobilePortalView() {
    const sidebar = document.querySelector('.map-portal-sidebar');
    const btn = document.getElementById('portal-view-btn');
    if (!sidebar || !btn) return;
    
    const isShowing = sidebar.classList.toggle('show');
    
    if (isShowing) {
        // Sidebar is now VISIBLE (showing listings)
        btn.innerHTML = '<i class="fas fa-map"></i> Lihat Peta';
        
        // Scroll to map section
        const mapSection = document.getElementById('map-portal');
        if (mapSection) {
            mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else {
        // Sidebar is now HIDDEN (show map)
        btn.innerHTML = '<i class="fas fa-list"></i> Lihat Senarai';
        
        // Resize map and zoom to selected property
        setTimeout(function() {
            if (window.tlsMap) {
                window.tlsMap.invalidateSize();
                
                // Zoom to selected property or default view
                if (window.selectedProperty && window.selectedProperty.lat && window.selectedProperty.lng) {
                    window.tlsMap.setView([window.selectedProperty.lat, window.selectedProperty.lng], 16);
                } else if (selectedProperty && selectedProperty.lat && selectedProperty.lng) {
                    window.tlsMap.setView([selectedProperty.lat, selectedProperty.lng], 16);
                } else {
                    window.tlsMap.setView([6.13, 116.23], 12);
                }
            }
        }, 400);
    }
}
</script>
