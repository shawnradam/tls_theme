<?php
/**
 * Property Management Admin Page
 * Provides a proper admin interface for managing properties
 */

if (!defined('ABSPATH')) exit;

function tls_render_crud_page() {
    global $wpdb;
    
    // Handle form submissions
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
                $message = $result ? 'Property deleted successfully!' : 'Error deleting property.';
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
    ?>
    <div class="wrap">
        <h1>Property Management</h1>
        
        <?php if (isset($message)): ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <!-- Property Form -->
        <div class="card" style="max-width: 1200px; padding: 20px; margin-bottom: 20px;">
            <h2><?php echo $edit_data ? 'Edit Property' : 'Add New Property'; ?></h2>
            <form method="POST">
                <?php wp_nonce_field('tls_crud_action', 'tls_crud_nonce'); ?>
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
                <input type="hidden" name="post_id" value="<?php echo $edit_data ? $edit_data['id'] : '0'; ?>">
                
                <table class="form-table">
                    <tr>
                        <th><label for="property_title">Title *</label></th>
                        <td><input type="text" name="property_title" id="property_title" 
                                   value="<?php echo $edit_data ? esc_attr($edit_data['title']) : ''; ?>" 
                                   required style="width: 100%;"></td>
                    </tr>
                    <tr>
                        <th><label for="property_description">Description</label></th>
                        <td><textarea name="property_description" id="property_description" rows="5" 
                                      style="width: 100%;"><?php echo $edit_data ? esc_textarea($edit_data['content']) : ''; ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="property_id">Property ID</label></th>
                        <td><input type="text" name="property_id" id="property_id" 
                                   value="<?php echo $edit_data ? esc_attr($edit_data['property_id']) : ''; ?>" 
                                   placeholder="TLS-001" style="width: 300px;"></td>
                    </tr>
                    <tr>
                        <th><label for="property_price">Price (RM)</label></th>
                        <td><input type="number" name="property_price" id="property_price" step="1000"
                                   value="<?php echo $edit_data ? esc_attr($edit_data['price']) : ''; ?>" 
                                   style="width: 300px;"></td>
                    </tr>
                    <tr>
                        <th><label for="property_area">Area (Acres)</label></th>
                        <td><input type="text" name="property_area" id="property_area"
                                   value="<?php echo $edit_data ? esc_attr($edit_data['area']) : ''; ?>" 
                                   style="width: 300px;"></td>
                    </tr>
                    <tr>
                        <th><label for="property_geran">Grant Type</label></th>
                        <td>
                            <select name="property_geran" id="property_geran" style="width: 300px;">
                                <option value="CL" <?php selected($edit_data['geran'] ?? '', 'CL'); ?>>Country Lease (CL)</option>
                                <option value="NT" <?php selected($edit_data['geran'] ?? '', 'NT'); ?>>Native Title (NT)</option>
                                <option value="P" <?php selected($edit_data['geran'] ?? '', 'P'); ?>>Pajakan (P)</option>
                                <option value="Hakmilik" <?php selected($edit_data['geran'] ?? '', 'Hakmilik'); ?>>Freehold (Hakmilik)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="property_zoning">Zoning</label></th>
                        <td>
                            <select name="property_zoning" id="property_zoning" style="width: 300px;">
                                <option value="Kediaman" <?php selected($edit_data['zoning'] ?? '', 'Kediaman'); ?>>Residential</option>
                                <option value="Komersial" <?php selected($edit_data['zoning'] ?? '', 'Komersial'); ?>>Commercial</option>
                                <option value="Perindustrian" <?php selected($edit_data['zoning'] ?? '', 'Perindustrian'); ?>>Industrial</option>
                                <option value="Pertanian" <?php selected($edit_data['zoning'] ?? '', 'Pertanian'); ?>>Agriculture</option>
                                <option value="Campuran" <?php selected($edit_data['zoning'] ?? '', 'Campuran'); ?>>Mixed Use</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="property_town">Town/City</label></th>
                        <td><input type="text" name="property_town" id="property_town"
                                   value="<?php echo $edit_data ? esc_attr($edit_data['town']) : ''; ?>" 
                                   placeholder="Kota Kinabalu" style="width: 300px;"></td>
                    </tr>
                    <tr>
                        <th><label for="property_daerah">Daerah</label></th>
                        <td>
                            <select name="property_daerah" id="property_daerah" style="width: 300px;">
                                <option value="">Select Daerah</option>
                                <?php 
                                $daerah_terms = get_terms(['taxonomy' => 'daerah', 'hide_empty' => false]);
                                foreach ($daerah_terms as $term): ?>
                                    <option value="<?php echo esc_attr($term->name); ?>" 
                                            <?php selected($edit_data['daerah'] ?? '', $term->name); ?>>
                                        <?php echo esc_html($term->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="property_latitude">Latitude</label></th>
                        <td><input type="text" name="property_latitude" id="property_latitude"
                                   value="<?php echo $edit_data ? esc_attr($edit_data['latitude']) : ''; ?>" 
                                   placeholder="5.9804" style="width: 300px;"></td>
                    </tr>
                    <tr>
                        <th><label for="property_longitude">Longitude</label></th>
                        <td><input type="text" name="property_longitude" id="property_longitude"
                                   value="<?php echo $edit_data ? esc_attr($edit_data['longitude']) : ''; ?>" 
                                   placeholder="116.0735" style="width: 300px;"></td>
                    </tr>
                    <tr>
                        <th><label for="property_status">Status</label></th>
                        <td>
                            <select name="post_status" id="property_status" style="width: 300px;">
                                <option value="publish" <?php selected($edit_data['status'] ?? '', 'publish'); ?>>Published</option>
                                <option value="draft" <?php selected($edit_data['status'] ?? '', 'draft'); ?>>Draft</option>
                                <option value="pending" <?php selected($edit_data['status'] ?? '', 'pending'); ?>>Pending Review</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Verified</th>
                        <td><label><input type="checkbox" name="property_verified" value="1" 
                                          <?php checked($edit_data['verified'] ?? '', '1'); ?>> Mark as verified</label></td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php echo $edit_data ? 'Update Property' : 'Create Property'; ?></button>
                    <?php if ($edit_data): ?>
                        <a href="<?php echo admin_url('admin.php?page=tls-property-crud'); ?>" class="button">Cancel</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        
        <!-- Properties List -->
        <div class="card" style="max-width: 1200px; padding: 20px;">
            <h2>All Properties</h2>
            <?php
            $properties = new WP_Query([
                'post_type' => 'tanah',
                'posts_per_page' => -1,
                'post_status' => 'any',
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            
            if ($properties->have_posts()): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Property ID</th>
                            <th>Price</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($properties->have_posts()): $properties->the_post(); 
                            $id = get_the_ID();
                            $property_id = get_post_meta($id, '_tanah_property_id', true);
                            $price = get_post_meta($id, '_tanah_harga', true);
                            $town = get_post_meta($id, '_tanah_town', true);
                            $daerah_terms = get_the_terms($id, 'daerah');
                            $daerah = $daerah_terms && !is_wp_error($daerah_terms) ? $daerah_terms[0]->name : '';
                            $location = $town ?: ($daerah ?: 'Sabah');
                        ?>
                            <tr>
                                <td><strong><?php the_title(); ?></strong></td>
                                <td><?php echo esc_html($property_id ?: '-'); ?></td>
                                <td>RM <?php echo number_format($price ?: 0); ?></td>
                                <td><?php echo esc_html($location); ?></td>
                                <td>
                                    <span class="status-<?php echo get_post_status(); ?>">
                                        <?php echo ucfirst(get_post_status()); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=tls-property-crud&edit=' . $id), 'edit_property'); ?>" 
                                       class="button button-small">Edit</a>
                                    <form method="POST" style="display: inline-block;" 
                                          onsubmit="return confirm('Delete this property?');">
                                        <?php wp_nonce_field('tls_crud_action', 'tls_crud_nonce'); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="post_id" value="<?php echo $id; ?>">
                                        <button type="submit" class="button button-small button-link-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No properties found. Add one above!</p>
            <?php endif; wp_reset_postdata(); ?>
        </div>
    </div>
    <?php
}

// Remove the frontend page creation since we're using admin page
// (Comment out the after_switch_theme hook from functions.php)
