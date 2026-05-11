<?php
/**
 * Sample Tanah Properties Seed Data
 * Creates placeholder properties with development status
 *
 * @package TanahLotSabah
 */

if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', 'tls_seed_sample_tanah');
function tls_seed_sample_tanah() {
    if (get_transient('tls_tanah_seeding')) return;
    set_transient('tls_tanah_seeding', true, 30 * DAY_IN_SECONDS);
    
    if (get_option('tls_tanah_seeded')) return;

    $properties = [
        [
            'title' => 'Jalan Sulaman, Shangri La',
            'price' => 2500000,
            'size' => '1.64',
            'geran' => 'NT',
            'status' => 'completed',
            'dev_status' => 'completed',
            'lat' => 6.0279,
            'lng' => 116.2474,
            'town' => 'Kota Kinabalu',
            'daerah' => 'Kota Kinabalu',
            'zoning' => 'Komersial',
            'notes' => 'Prime commercial land near Shangri La Hotel, fully developed with road access'
        ],
        [
            'title' => 'Kg. Tambalugu',
            'price' => 1500000,
            'size' => '0.5',
            'geran' => 'CL',
            'status' => 'available',
            'dev_status' => 'in_progress',
            'lat' => 6.1448,
            'lng' => 116.2293,
            'town' => 'Kota Kinabalu',
            'daerah' => 'Kota Kinabalu',
            'zoning' => 'Kediaman',
            'notes' => 'Residential land with active development in progress'
        ],
        [
            'title' => 'Telupid Development Zone',
            'price' => 890000,
            'size' => '5.2',
            'geran' => 'NT',
            'status' => 'available',
            'dev_status' => 'planned',
            'lat' => 5.9567,
            'lng' => 117.1328,
            'town' => 'Telupid',
            'daerah' => 'Beluran',
            'zoning' => 'Pertanian',
            'notes' => 'Planned agricultural development zone under PANTAS programme'
        ],
        [
            'title' => 'Inanam Industrial Plot',
            'price' => 3200000,
            'size' => '3.8',
            'geran' => 'CL',
            'status' => 'available',
            'dev_status' => 'completed',
            'lat' => 5.9921,
            'lng' => 116.1875,
            'town' => 'Inanam',
            'daerah' => 'Kota Kinabalu',
            'zoning' => 'Perindustrian',
            'notes' => 'Fully serviced industrial land at KKIP zone'
        ],
        [
            'title' => 'Penampang Town Centre',
            'price' => 1800000,
            'size' => '0.75',
            'geran' => 'CL',
            'status' => 'available',
            'dev_status' => 'completed',
            'lat' => 5.9356,
            'lng' => 116.1033,
            'town' => 'Penampang',
            'daerah' => 'Penampang',
            'zoning' => 'Komersial',
            'notes' => 'Commercial land in Penampang town centre'
        ],
        [
            'title' => 'Lahad Datu Palm Oil Zone',
            'price' => 650000,
            'size' => '10.5',
            'geran' => 'NT',
            'status' => 'available',
            'dev_status' => 'in_progress',
            'lat' => 5.0216,
            'lng' => 118.3281,
            'town' => 'Lahad Datu',
            'daerah' => 'Lahad Datu',
            'zoning' => 'Pertanian',
            'notes' => 'Agricultural land near POIC Lahad Datu'
        ],
        [
            'title' => 'Putatan Mixed Development',
            'price' => 2100000,
            'size' => '2.3',
            'geran' => 'CL',
            'status' => 'reserved',
            'dev_status' => 'planned',
            'lat' => 5.9203,
            'lng' => 116.0583,
            'town' => 'Putatan',
            'daerah' => 'Penampang',
            'zoning' => 'Campuran',
            'notes' => 'Planned mixed development near KKIA'
        ],
        [
            'title' => 'Sandakan Harbour View',
            'price' => 1350000,
            'size' => '1.2',
            'geran' => 'CL',
            'status' => 'available',
            'dev_status' => 'completed',
            'lat' => 5.8386,
            'lng' => 118.1167,
            'town' => 'Sandakan',
            'daerah' => 'Sandakan',
            'zoning' => 'Komersial',
            'notes' => 'Waterfront commercial land in Sandakan'
        ],
        [
            'title' => 'Tuaran Rice Bowl Area',
            'price' => 480000,
            'size' => '8.4',
            'geran' => 'NT',
            'status' => 'available',
            'dev_status' => 'raw_land',
            'lat' => 6.1778,
            'lng' => 116.2311,
            'town' => 'Tuaran',
            'daerah' => 'Tuaran',
            'zoning' => 'Pertanian',
            'notes' => 'Raw agricultural land, ideal for rice cultivation'
        ],
        [
            'title' => 'Kinarut Township Expansion',
            'price' => 980000,
            'size' => '1.8',
            'geran' => 'CL',
            'status' => 'available',
            'dev_status' => 'in_progress',
            'lat' => 5.8522,
            'lng' => 116.0747,
            'town' => 'Kinarut',
            'daerah' => 'Penampang',
            'zoning' => 'Kediaman',
            'notes' => 'Part of Tropika Park City masterplan development'
        ]
    ];

    foreach ($properties as $prop) {
        $post_id = wp_insert_post([
            'post_title' => $prop['title'],
            'post_content' => $prop['notes'],
            'post_status' => 'publish',
            'post_type' => 'tanah'
        ]);

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_tanah_harga', $prop['price']);
            update_post_meta($post_id, '_tanah_keluasan', $prop['size']);
            update_post_meta($post_id, '_tanah_jenis_geran', $prop['geran']);
            update_post_meta($post_id, '_tanah_status', $prop['status']);
            update_post_meta($post_id, '_tanah_development_status', $prop['dev_status']);
            update_post_meta($post_id, '_tanah_latitude', $prop['lat']);
            update_post_meta($post_id, '_tanah_longitude', $prop['lng']);
            update_post_meta($post_id, '_tanah_town', $prop['town']);
            update_post_meta($post_id, '_tanah_zoning', $prop['zoning']);

            wp_set_object_terms($post_id, $prop['daerah'], 'daerah');
            wp_set_object_terms($post_id, $prop['geran'], 'jenis_geran');
            wp_set_object_terms($post_id, $prop['dev_status'], 'development_status');
        }
    }

    update_option('tls_tanah_seeded', true);
}