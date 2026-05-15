<?php
/**
 * Partner Tempatan Management Admin Page
 * CRUD for managing local partners displayed on property pages
 */

if (!defined('ABSPATH')) exit;

function tls_render_partner_page() {
    if (!current_user_can('manage_options')) {
        echo '<div class="notice notice-error"><p>Access denied.</p></div>';
        return;
    }

    $message = '';
    $status = '';

    $partners = get_option('tls_partners', []);

    if (!is_array($partners)) {
        $partners = [];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tls_partner_nonce']) && wp_verify_nonce($_POST['tls_partner_nonce'], 'tls_partner_action')) {
        $action = isset($_POST['partner_action']) ? sanitize_text_field($_POST['partner_action']) : '';

        if ($action === 'save_all') {
            $partners_data = isset($_POST['partners']) ? $_POST['partners'] : [];
            $saved_partners = [];

            foreach ($partners_data as $i => $p) {
                $name = isset($p['name']) ? sanitize_text_field($p['name']) : '';
                $description = isset($p['description']) ? sanitize_textarea_field($p['description']) : '';
                $url = isset($p['url']) ? esc_url_raw($p['url']) : '';
                $phone = isset($p['phone']) ? sanitize_text_field($p['phone']) : '';
                $active = isset($p['active']) ? 1 : 0;

                if (!empty($name)) {
                    $saved_partners[] = [
                        'name' => $name,
                        'description' => $description,
                        'url' => $url,
                        'phone' => $phone,
                        'active' => $active,
                        'order' => count($saved_partners),
                    ];
                }
            }

            update_option('tls_partners', $saved_partners);
            $partners = $saved_partners;
            $message = 'Partners saved successfully!';
            $status = 'success';
        }

        if ($action === 'delete') {
            $index = isset($_POST['partner_index']) ? intval($_POST['partner_index']) : -1;
            if ($index >= 0 && $index < count($partners)) {
                array_splice($partners, $index, 1);
                update_option('tls_partners', $partners);
                $message = 'Partner deleted.';
                $status = 'success';
            }
        }
    }

    ?>
    <div class="wrap" style="max-width:900px;">
        <h1 style="margin-bottom:20px;">
            <span class="dashicons dashicons-businessperson" style="font-size:28px;width:28px;height:28px;margin-right:8px;vertical-align:middle;"></span>
            Partner Tempatan Management
        </h1>

        <?php if ($message): ?>
        <div class="notice notice-<?php echo $status === 'success' ? 'success' : 'error'; ?> is-dismissible" style="border-radius:8px;margin:20px 0;">
            <p><strong><?php echo esc_html($message); ?></strong></p>
        </div>
        <?php endif; ?>

        <p style="color:#666;margin-bottom:20px;">Manage local partners displayed on property detail pages. Active partners will appear in the "Partner Tempatan" section.</p>

        <form method="post" action="">
            <?php wp_nonce_field('tls_partner_action', 'tls_partner_nonce'); ?>
            <input type="hidden" name="partner_action" value="save_all">

            <div id="partners-container">
                <?php if (!empty($partners)): ?>
                    <?php foreach ($partners as $i => $partner): ?>
                    <div class="tls-partner-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;position:relative;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
                            <h3 style="margin:0;font-size:1.1rem;"><?php echo esc_html($partner['name']); ?></h3>
                            <button type="button" onclick="removePartner(this)" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:4px 12px;cursor:pointer;font-size:0.85rem;">Remove</button>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label style="display:block;font-size:0.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Partner Name *</label>
                                <input type="text" name="partners[<?php echo $i; ?>][name]" value="<?php echo esc_attr($partner['name']); ?>" required style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.95rem;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Phone / WhatsApp</label>
                                <input type="text" name="partners[<?php echo $i; ?>][phone]" value="<?php echo esc_attr($partner['phone'] ?? ''); ?>" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.95rem;">
                            </div>
                        </div>

                        <div style="margin-top:12px;">
                            <label style="display:block;font-size:0.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Description</label>
                            <textarea name="partners[<?php echo $i; ?>][description]" rows="2" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.95rem;resize:vertical;" placeholder="Mau bersihkan tanah atau semak sempadan?"><?php echo esc_textarea($partner['description'] ?? ''); ?></textarea>
                        </div>

                        <div style="margin-top:12px;">
                            <label style="display:block;font-size:0.85rem;font-weight:600;color:#374151;margin-bottom:4px;">URL (link to partner website or WhatsApp)</label>
                            <input type="url" name="partners[<?php echo $i; ?>][url]" value="<?php echo esc_attr($partner['url'] ?? ''); ?>" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.95rem;" placeholder="https://wa.me/60123456789">
                        </div>

                        <div style="margin-top:12px;display:flex;align-items:center;gap:8px;">
                            <input type="checkbox" id="partner_active_<?php echo $i; ?>" name="partners[<?php echo $i; ?>][active]" value="1" <?php checked(!empty($partner['active'])); ?>>
                            <label for="partner_active_<?php echo $i; ?>" style="font-size:0.9rem;color:#374151;cursor:pointer;">Active (show on property pages)</label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div id="no-partners-msg" style="text-align:center;padding:40px 20px;background:#f8fafc;border-radius:12px;border:1px dashed #cbd5e1;">
                        <p style="color:#64748b;font-size:1rem;margin:0;">No partners added yet. Click "Add Partner" below to get started.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top:20px;display:flex;gap:12px;align-items:center;">
                <button type="submit" class="button button-primary" style="padding:6px 20px;height:auto;border-radius:6px;font-size:0.95rem;">Save All Partners</button>
                <button type="button" onclick="addPartner()" class="button" style="padding:6px 20px;height:auto;border-radius:6px;font-size:0.95rem;">+ Add Partner</button>
            </div>
        </form>
    </div>

    <script>
    var partnerIndex = <?php echo !empty($partners) ? count($partners) : 0; ?>;

    function addPartner() {
        var noMsg = document.getElementById('no-partners-msg');
        if (noMsg) noMsg.remove();

        var container = document.getElementById('partners-container');
        var card = document.createElement('div');
        card.className = 'tls-partner-card';
        card.style.cssText = 'background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;position:relative;';

        var i = partnerIndex;
        partnerIndex++;

        card.innerHTML = '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">' +
            '<h3 style="margin:0;font-size:1.1rem;color:#16a34a;">New Partner</h3>' +
            '<button type="button" onclick="removePartner(this)" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:4px 12px;cursor:pointer;font-size:0.85rem;">Remove</button>' +
            '</div>' +
            '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">' +
            '<div><label style="display:block;font-size:0.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Partner Name *</label>' +
            '<input type="text" name="partners[' + i + '][name]" value="" required style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.95rem;" placeholder="Juruukur Berdaftar"></div>' +
            '<div><label style="display:block;font-size:0.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Phone / WhatsApp</label>' +
            '<input type="text" name="partners[' + i + '][phone]" value="" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.95rem;" placeholder="+60 12-345 6789"></div>' +
            '</div>' +
            '<div style="margin-top:12px;"><label style="display:block;font-size:0.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Description</label>' +
            '<textarea name="partners[' + i + '][description]" rows="2" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.95rem;resize:vertical;" placeholder="Mau bersihkan tanah atau semak sempadan?"></textarea></div>' +
            '<div style="margin-top:12px;"><label style="display:block;font-size:0.85rem;font-weight:600;color:#374151;margin-bottom:4px;">URL (link to partner website or WhatsApp)</label>' +
            '<input type="url" name="partners[' + i + '][url]" value="" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.95rem;" placeholder="https://wa.me/60123456789"></div>' +
            '<div style="margin-top:12px;display:flex;align-items:center;gap:8px;">' +
            '<input type="checkbox" id="partner_active_' + i + '" name="partners[' + i + '][active]" value="1" checked>' +
            '<label for="partner_active_' + i + '" style="font-size:0.9rem;color:#374151;cursor:pointer;">Active (show on property pages)</label></div>';

        container.appendChild(card);
    }

    function removePartner(btn) {
        if (confirm('Remove this partner?')) {
            var card = btn.closest('.tls-partner-card');
            card.remove();
        }
    }
    </script>
    <?php
}