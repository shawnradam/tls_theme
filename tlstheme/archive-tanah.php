<?php get_header(); ?>

<section class="listings-section">
    <div class="container">
        <div class="section-header">
            <h2>
                <?php
                if (is_search()) {
                    echo 'Search Results';
                    if (get_search_query()) {
                        echo ' for "' . esc_html(get_search_query()) . '"';
                    }
                } elseif (is_tax()) {
                    single_term_title('Properties in ');
                } else {
                    echo 'Senarai Tanah';
                }
                ?>
            </h2>
            <?php
            global $wp_query;
            $total = $wp_query->found_posts;
            ?>
            <p><?php echo $total; ?> propert<?php echo $total == 1 ? 'y' : 'ies'; ?> found</p>
        </div>

        <?php
        // Display active filters
        $active_filters = [];

        if (!empty($_GET['land_type'])) {
            $term = get_term_by('slug', $_GET['land_type'], 'land_type');
            if ($term) $active_filters[] = 'Type: ' . $term->name;
        }
        if (!empty($_GET['daerah'])) {
            $term = get_term_by('slug', $_GET['daerah'], 'daerah');
            if ($term) $active_filters[] = 'District: ' . $term->name;
        }
        if (!empty($_GET['geran'])) {
            $active_filters[] = 'Geran: ' . esc_html($_GET['geran']);
        }
        if (!empty($_GET['zoning'])) {
            $active_filters[] = 'Zoning: ' . esc_html($_GET['zoning']);
        }
        if (!empty($_GET['min_price']) || !empty($_GET['max_price'])) {
            $price_text = 'Price: ';
            if (!empty($_GET['min_price']) && !empty($_GET['max_price'])) {
                $price_text .= 'RM ' . number_format((int)$_GET['min_price']) . ' - RM ' . number_format((int)$_GET['max_price']);
            } elseif (!empty($_GET['min_price'])) {
                $price_text .= 'From RM ' . number_format((int)$_GET['min_price']);
            } else {
                $price_text .= 'Up to RM ' . number_format((int)$_GET['max_price']);
            }
            $active_filters[] = $price_text;
        }
        if (!empty($_GET['min_size']) || !empty($_GET['max_size'])) {
            $size_text = 'Size: ';
            if (!empty($_GET['min_size']) && !empty($_GET['max_size'])) {
                $size_text .= $_GET['min_size'] . ' - ' . $_GET['max_size'] . ' ekar';
            } elseif (!empty($_GET['min_size'])) {
                $size_text .= 'From ' . $_GET['min_size'] . ' ekar';
            } else {
                $size_text .= 'Up to ' . $_GET['max_size'] . ' ekar';
            }
            $active_filters[] = $size_text;
        }
        if (!empty($_GET['verified_only'])) {
            $active_filters[] = 'Verified Only';
        }

        if (!empty($active_filters)) :
        ?>
        <div class="active-filters">
            <div class="filter-label">Active Filters:</div>
            <div class="filter-tags">
                <?php foreach ($active_filters as $filter) : ?>
                    <span class="filter-tag"><?php echo $filter; ?></span>
                <?php endforeach; ?>
                <a href="<?php echo esc_url(remove_query_arg(['land_type', 'daerah', 'geran', 'zoning', 'min_price', 'max_price', 'min_size', 'max_size', 'verified_only', 'orderby', 's'])); ?>" class="clear-filters">Clear All</a>
            </div>
        </div>
        <?php endif; ?>

        <div class="listings-grid">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/card', 'land'); ?>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="no-results-box">
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <h3>No Properties Found</h3>
                    <p>Try adjusting your search filters or browse all properties.</p>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn">View All Properties</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (function_exists('the_posts_pagination')) : ?>
        <div class="pagination">
            <?php the_posts_pagination(); ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
