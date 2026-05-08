// Track WhatsApp clicks
document.addEventListener('DOMContentLoaded', function() {
  const waButtons = document.querySelectorAll('.btn-whatsapp');
  
  waButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      const tanahId = this.getAttribute('data-tanah-id');
      if (tanahId && typeof tls_ajax !== 'undefined') {
        const formData = new FormData();
        formData.append('action', 'tls_track_lead');
        formData.append('nonce', tls_ajax.nonce);
        formData.append('tanah_id', tanahId);
        
        fetch(tls_ajax.ajax_url, {
          method: 'POST',
          body: formData
        });
      }
    });
  });
  
  // View Toggle
  const viewBtns = document.querySelectorAll('.view-btn');
  const listingsGrid = document.getElementById('listingsGrid');
  
  viewBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const view = this.getAttribute('data-view');
      
      viewBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      if (listingsGrid) {
        if (view === 'list') {
          listingsGrid.classList.add('list-view');
        } else {
          listingsGrid.classList.remove('list-view');
        }
        localStorage.setItem('tls_view', view);
      }
    });
  });
  
  // Restore saved view
  const savedView = localStorage.getItem('tls_view');
  if (savedView === 'list' && listingsGrid) {
    listingsGrid.classList.add('list-view');
    document.querySelector('.view-btn[data-view="list"]')?.classList.add('active');
    document.querySelector('.view-btn[data-view="grid"]')?.classList.remove('active');
  }
});
