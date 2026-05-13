<?php
/**
 * Property Management Admin Page
 * Provides a proper admin interface for managing properties
 */

if (!defined('ABSPATH')) exit;

function tls_render_crud_page() {
    global $wpdb;
    
    // Handle form submissions
    $message = '';
    $status = 'success';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tls_crud_nonce']) && wp_verify_nonce($_POST['tls_crud_nonce'], 'tls_crud_action')) {
        
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
            
            if ($action === 'create' || $action === 'update') {
                $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
                
                $post_data = [
                    'post_title'   => sanitize_text_field($_POST['property_title']),
                    'post_content' => wp_kses_post($_POST['property_description']),
                    'post_status'  => sanitize_text_field($_POST['post_status']),
                    'post_type'    => 'tanah',
                ];
                
                if ($action === 'create') {
                    $post_id = wp_insert_post($post_data);
                    $message = $post_id ? 'Property created successfully!' : 'Error creating property.';
                } else {
                    $post_data['ID'] = $post_id;
                    $result = wp_update_post($post_data);
                    $message = $result ? 'Property updated successfully!' : 'Error updating property.';
                }
                
                if ($post_id && !is_wp_error($post_id)) {
                    // Save meta fields
                    $meta_fields = [
                        '_tanah_harga' => 'property_price',
                        '_tanah_keluasan' => 'property_area',
                        '_tanah_jenis_geran' => 'property_geran',
                        '_tanah_zoning' => 'property_zoning',
                        '_tanah_property_id' => 'property_id',
                        '_tanah_latitude' => 'property_latitude',
                        '_tanah_longitude' => 'property_longitude',
                        '_tanah_town' => 'property_town',
                        '_tanah_building_size' => 'property_building_size',
                        '_tanah_building_unit' => 'property_building_unit',
                    ];
                    
                    foreach ($meta_fields as $meta_key => $field_name) {
                        if (isset($_POST[$field_name])) {
                            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field_name]));
                        }
                    }
                    
                    // Handle verified checkbox
                    $verified = isset($_POST['property_verified']) ? '1' : '0';
                    update_post_meta($post_id, '_tanah_verified', $verified);
                    
                    // Handle daerah taxonomy
                    if (isset($_POST['property_daerah'])) {
                        $daerah = sanitize_text_field($_POST['property_daerah']);
                        if (!empty($daerah)) {
                            wp_set_object_terms($post_id, $daerah, 'daerah', false);
                        }
                    }
                }
            }
            
            if ($action === 'delete' && isset($_POST['post_id'])) {
                $post_id = intval($_POST['post_id']);
                $result = wp_delete_post($post_id, true);
                if ($result) {
                    $message = 'Property deleted successfully!';
                    $status = 'success';
                } else {
                    $message = 'Error deleting property.';
                    $status = 'error';
                }
            }
        }
    }
    
    // Get edit data if editing
    $edit_data = null;
    if (isset($_GET['edit']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'edit_property')) {
        $edit_id = intval($_GET['edit']);
        $post = get_post($edit_id);
        if ($post && $post->post_type === 'tanah') {
            $edit_data = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'status' => $post->post_status,
                'property_id' => get_post_meta($post->ID, '_tanah_property_id', true),
                'price' => get_post_meta($post->ID, '_tanah_harga', true),
                'area' => get_post_meta($post->ID, '_tanah_keluasan', true),
                'geran' => get_post_meta($post->ID, '_tanah_jenis_geran', true),
                'zoning' => get_post_meta($post->ID, '_tanah_zoning', true),
                'town' => get_post_meta($post->ID, '_tanah_town', true),
                'latitude' => get_post_meta($post->ID, '_tanah_latitude', true),
                'longitude' => get_post_meta($post->ID, '_tanah_longitude', true),
                'building_size' => get_post_meta($post->ID, '_tanah_building_size', true),
                'building_unit' => get_post_meta($post->ID, '_tanah_building_unit', true),
                'verified' => get_post_meta($post->ID, '_tanah_verified', true),
            ];
            
            $daerah_terms = get_the_terms($post->ID, 'daerah');
            $edit_data['daerah'] = ($daerah_terms && !is_wp_error($daerah_terms)) ? $daerah_terms[0]->name : '';
        }
    }

    $properties_count = wp_count_posts('tanah')->publish;
    ?>
    <div class="wrap tls-admin-modern">
        <div class="tls-admin-header">
            <h1>
                <span class="dashicons dashicons-portfolio"></span> 
                Property Portfolio Management
            </h1>
            <p>Directly manage your land listings, pricing, and availability status.</p>
        </div>

        <?php if ($message): ?>
            <div class="notice notice-<?php echo $status; ?> is-dismissible" style="border-radius:8px; margin: 20px 0;">
                <p><strong><?php echo esc_html($message); ?></strong></p>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php wp_nonce_field('tls_crud_action', 'tls_crud_nonce'); ?>
            <input type="hidden" name="action" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
            <input type="hidden" name="post_id" value="<?php echo $edit_data ? $edit_data['id'] : '0'; ?>">

            <div class="tls-grid-layout">
                <!-- Main Column: Editor -->
                <div class="tls-grid-col main-col">
                    <div class="tls-card">
                        <div class="card-header">
                            <span class="dashicons dashicons-edit"></span>
                            <h2><?php echo $edit_data ? 'Edit Property Details' : 'Add New Property'; ?></h2>
                        </div>

                        <div class="tls-form-row">
                            <label for="property_title">Property Listing Title *</label>
                            <input type="text" name="property_title" id="property_title" class="tls-input"
                                   value="<?php echo $edit_data ? esc_attr($edit_data['title']) : ''; ?>" 
                                   placeholder="e.g. 5 Acres Agriculture Land at Tuaran" required>
                        </div>

                        <div class="tls-form-row">
                            <label for="property_description">Market Description</label>
                            <textarea name="property_description" id="property_description" rows="6" class="tls-input"
                                      placeholder="Provide attractive details about the property..."><?php echo $edit_data ? esc_textarea($edit_data['content']) : ''; ?></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="tls-form-row">
                                <label for="property_id">Internal Reference ID</label>
                                <input type="text" name="property_id" id="property_id" class="tls-input"
                                       value="<?php echo $edit_data ? esc_attr($edit_data['property_id']) : ''; ?>" 
                                       placeholder="TLS-001">
                            </div>
                            <div class="tls-form-row">
                                <label for="property_price">Asking Price (RM)</label>
                                <input type="number" name="property_price" id="property_price" step="1000" class="tls-input"
                                       value="<?php echo $edit_data ? esc_attr($edit_data['price']) : ''; ?>" 
                                       placeholder="500000">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="tls-form-row">
                                <label for="property_area">Total Area (Acres)</label>
                                <input type="text" name="property_area" id="property_area" class="tls-input"
                                       value="<?php echo $edit_data ? esc_attr($edit_data['area']) : ''; ?>" 
                                       placeholder="1.5">
                            </div>
                            <div class="tls-form-row">
                                <label for="property_geran">Grant Type (Title)</label>
                                <select name="property_geran" id="property_geran" class="tls-input">
                                    <option value="CL" <?php selected($edit_data['geran'] ?? '', 'CL'); ?>>Country Lease (CL)</option>
                                    <option value="NT" <?php selected($edit_data['geran'] ?? '', 'NT'); ?>>Native Title (NT)</option>
                                    <option value="P" <?php selected($edit_data['geran'] ?? '', 'P'); ?>>Pajakan (P)</option>
                                    <option value="Hakmilik" <?php selected($edit_data['geran'] ?? '', 'Hakmilik'); ?>>Freehold (Hakmilik)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="tls-card">
                        <div class="card-header">
                            <span class="dashicons dashicons-location"></span>
                            <h2>Location & Zoning</h2>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="tls-form-row">
                                <label for="property_town">Town / City</label>
                                <input type="text" name="property_town" id="property_town" class="tls-input"
                                       value="<?php echo $edit_data ? esc_attr($edit_data['town']) : ''; ?>" 
                                       placeholder="Kota Kinabalu">
                            </div>
                            <div class="tls-form-row">
                                <label for="property_daerah">Administrative District</label>
                                <select name="property_daerah" id="property_daerah" class="tls-input">
                                    <option value="">Select District...</option>
                                    <?php 
                                    $daerah_terms = get_terms(['taxonomy' => 'daerah', 'hide_empty' => false]);
                                    foreach ($daerah_terms as $term): ?>
                                        <option value="<?php echo esc_attr($term->name); ?>" 
                                                <?php selected($edit_data['daerah'] ?? '', $term->name); ?>>
                                            <?php echo esc_html($term->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="tls-form-row">
                                <label for="property_latitude">GPS Latitude</label>
                                <input type="text" name="property_latitude" id="property_latitude" class="tls-input"
                                       value="<?php echo $edit_data ? esc_attr($edit_data['latitude']) : ''; ?>" 
                                       placeholder="5.9804">
                            </div>
                            <div class="tls-form-row">
                                <label for="property_longitude">GPS Longitude</label>
                                <input type="text" name="property_longitude" id="property_longitude" class="tls-input"
                                       value="<?php echo $edit_data ? esc_attr($edit_data['longitude']) : ''; ?>" 
                                       placeholder="116.0735">
                            </div>
                        </div>

                        <div class="tls-form-row">
                            <label for="property_zoning">Zoning Classification</label>
                            <select name="property_zoning" id="property_zoning" class="tls-input">
                                <option value="Kediaman" <?php selected($edit_data['zoning'] ?? '', 'Kediaman'); ?>>Residential (Kediaman)</option>
                                <option value="Komersial" <?php selected($edit_data['zoning'] ?? '', 'Komersial'); ?>>Commercial (Komersial)</option>
                                <option value="Perindustrian" <?php selected($edit_data['zoning'] ?? '', 'Perindustrian'); ?>>Industrial (Perindustrian)</option>
                                <option value="Pertanian" <?php selected($edit_data['zoning'] ?? '', 'Pertanian'); ?>>Agriculture (Pertanian)</option>
                                <option value="Campuran" <?php selected($edit_data['zoning'] ?? '', 'Campuran'); ?>>Mixed Use (Campuran)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Status & Save -->
                <div class="tls-grid-col side-col">
                    <div class="tls-card">
                        <div class="card-header">
                            <span class="dashicons dashicons-visibility"></span>
                            <h2>Publishing</h2>
                        </div>
                        
                        <div class="tls-form-row">
                            <label for="property_status">Listing Status</label>
                            <select name="post_status" id="property_status" class="tls-input">
                                <option value="publish" <?php selected($edit_data['status'] ?? '', 'publish'); ?>>Published (Live)</option>
                                <option value="draft" <?php selected($edit_data['status'] ?? '', 'draft'); ?>>Draft (Internal)</option>
                                <option value="pending" <?php selected($edit_data['status'] ?? '', 'pending'); ?>>Pending Review</option>
                            </select>
                        </div>

                        <div class="tls-form-row" style="margin-bottom: 30px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="property_verified" value="1" <?php checked($edit_data['verified'] ?? '', '1'); ?>>
                                <strong>Verified Listing</strong>
                            </label>
                            <p class="description">Shows a trust badge on the property card.</p>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                <span class="dashicons dashicons-saved"></span> 
                                <?php echo $edit_data ? 'Update Property' : 'Publish Property'; ?>
                            </button>
                            
                            <?php if ($edit_data): ?>
                                <a href="<?php echo admin_url('admin.php?page=tls-property-crud'); ?>" class="btn btn-secondary" style="width: 100%;">
                                    Cancel Edit
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tls-card">
                        <div class="card-header">
                            <span class="dashicons dashicons-chart-pie"></span>
                            <h2>Overview</h2>
                        </div>
                        <div class="status-grid" style="grid-template-columns: 1fr;">
                            <div class="status-item active">
                                <div class="val"><?php echo number_format($properties_count); ?></div>
                                <div class="lab">Total Live Properties</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Properties List -->
        <div class="tls-card" style="margin-top: 24px;">
            <div class="card-header">
                <span class="dashicons dashicons-list-view"></span>
                <h2>Complete Inventory</h2>
            </div>
            <?php
            $properties = new WP_Query([
                'post_type' => 'tanah',
                'posts_per_page' => 20,
                'post_status' => 'any',
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            
            if ($properties->have_posts()): ?>
                <table class="tls-table">
                    <thead>
                        <tr>
                            <th>Property Title</th>
                            <th>ID</th>
                            <th>Price</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($properties->have_posts()): $properties->the_post(); 
                            $id = get_the_ID();
                            $p_id = get_post_meta($id, '_tanah_property_id', true);
                            $price = get_post_meta($id, '_tanah_harga', true);
                            $town = get_post_meta($id, '_tanah_town', true);
                            $daerah_terms = get_the_terms($id, 'daerah');
                            $daerah = $daerah_terms && !is_wp_error($daerah_terms) ? $daerah_terms[0]->name : '';
                            $p_status = get_post_status();
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700;"><?php the_title(); ?></div>
                                    <div style="font-size: 11px; color: var(--tls-text-muted);">Added <?php echo get_the_date(); ?></div>
                                </td>
                                <td><span class="badge badge-info"><?php echo esc_html($p_id ?: '-'); ?></span></td>
                                <td><strong>RM <?php echo number_format($price ?: 0); ?></strong></td>
                                <td><?php echo esc_html($town ?: ($daerah ?: 'Sabah')); ?></td>
                                <td>
                                    <?php if ($p_status === 'publish'): ?>
                                        <span class="badge badge-success">Live</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#f1f5f9; color:#64748b;"><?php echo ucfirst($p_status); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=tls-property-crud&edit=' . $id), 'edit_property'); ?>" 
                                           class="btn btn-secondary" style="padding: 6px 12px;">Edit</a>
                                        <form method="POST" style="display: inline-block;" onsubmit="return confirm('Delete this property permanently?');">
                                            <?php wp_nonce_field('tls_crud_action', 'tls_crud_nonce'); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="post_id" value="<?php echo $id; ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: 6px 12px;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div style="margin-top: 20px; text-align: center;">
                    <a href="<?php echo admin_url('edit.php?post_type=tanah'); ?>" class="btn btn-secondary">
                        View All in WordPress Manager
                    </a>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: var(--tls-text-muted);">
                    <span class="dashicons dashicons-info" style="font-size: 40px; width:40px; height:40px;"></span>
                    <p>No properties found in your database.</p>
                </div>
            <?php endif; wp_reset_postdata(); ?>
        </div>
    </div>
    <?php
}

// Remove the frontend page creation since we're using admin page
// (Comment out the after_switch_theme hook from functions.php)
