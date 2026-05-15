<?php /* Template Name: Dashboard */ ?>
<?php 
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login/'));
    exit;
}

if (isset($_POST['tls_hide_admin_bar'])) {
    update_user_meta(get_current_user_id(), 'tls_hide_admin_bar', '1');
} elseif (isset($_POST['tls_show_admin_bar'])) {
    delete_user_meta(get_current_user_id(), 'tls_hide_admin_bar');
}

$hide_admin_bar = get_user_meta(get_current_user_id(), 'tls_hide_admin_bar', true);
if (!$hide_admin_bar) {
    show_admin_bar(false);
}

get_header(); 

$current_user = wp_get_current_user();
$is_admin = current_user_can('manage_options');
?>

<div class="dashboard-page">
    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo $current_user->display_name; ?></h1>
            <div class="header-buttons">
                <form method="post" style="display:inline;">
                    <?php if ($hide_admin_bar): ?>
                    <button type="submit" name="tls_show_admin_bar" class="btn-toggle">Show Admin Bar</button>
                    <?php else: ?>
                    <button type="submit" name="tls_hide_admin_bar" class="btn-toggle">Hide Admin Bar</button>
                    <?php endif; ?>
                </form>
                <a href="<?php echo wp_logout_url(home_url('/')); ?>" class="btn-logout">Logout</a>
            </div>
        </div>
        
        <?php if ($is_admin): ?>
        <!-- Admin Dashboard -->
        <div class="dashboard-stats">
            <?php 
            $total_tanah = wp_count_posts('tanah')->publish;
            $total_leads = get_option('tls_leads_count', 0);
            $calc_leads = get_option('tls_calc_leads_count', 0);
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_tanah; ?></div>
                <div class="stat-label">Total Listings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_leads; ?></div>
                <div class="stat-label">Tanah Leads</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $calc_leads; ?></div>
                <div class="stat-label">Calculator Leads</div>
            </div>
        </div>
        
        <div class="dashboard-actions">
            <a href="<?php echo admin_url('admin.php?page=tls-tanah-map'); ?>" class="dash-action" target="_blank">
                <span class="dash-icon dashicons dashicons-location"></span>
                <span>Manage Listings</span>
            </a>
            <a href="<?php echo admin_url('admin.php?page=tls-leads'); ?>" class="dash-action" target="_blank">
                <span class="dash-icon dashicons dashicons-edit"></span>
                <span>View Leads</span>
            </a>
            <a href="<?php echo admin_url('admin.php?page=tls-hero-videos'); ?>" class="dash-action" target="_blank">
                <span class="dash-icon dashicons dashicons-video-alt3"></span>
                <span>Hero Videos</span>
            </a>
            <a href="<?php echo admin_url('admin.php?page=tls-splash'); ?>" class="dash-action" target="_blank">
                <span class="dash-image dashicons dashicons-format-image"></span>
                <span>Splash Screen</span>
            </a>
        </div>
        <?php else: ?>
        <!-- Agent Dashboard -->
        <div class="dashboard-section">
            <h2>My Listings</h2>
            <p>Manage your tanah listings here.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.dashboard-page { padding: 80px 20px 40px; }
.dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 16px; }
.dashboard-header h1 { font-size: clamp(1.5rem, 4vw, 1.75rem); font-weight: 700; line-height: 1.3; }
.header-buttons { display: flex; gap: 12px; align-items: center; }
.btn-toggle { padding: 10px 20px; background: #f59e0b; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; }
.btn-toggle:hover { background: #d97706; }
.btn-logout { padding: 10px 20px; background: #dc2626; color: #fff; text-decoration: none; border-radius: 8px; }
.btn-logout:hover { background: #b91c1c; }

.dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 40px; }
.stat-card { background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; }
.stat-number { font-size: 2rem; font-weight: 700; color: #16a34a; }
.stat-label { font-size: 14px; color: #64748b; margin-top: 8px; }

.dashboard-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; }
.dash-action { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-decoration: none; display: flex; align-items: center; gap: 12px; transition: transform 0.2s, box-shadow 0.2s; }
.dash-action:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
.dash-icon, .dash-image { font-size: 24px; }
.dash-action span:not(.dash-icon):not(.dash-image) { color: #374151; font-weight: 500; }

.dashboard-section { background: #fff; padding: 24px; border-radius: 12px; margin-bottom: 24px; }
.dashboard-section h2 { font-size: 1.25rem; margin-bottom: 12px; }

@media (max-width: 768px) {
    .dashboard-page { padding: 80px 16px 32px; }
    .dashboard-header { margin-bottom: 24px; }
    .dashboard-header h1 { font-size: 1.3rem; }
}
</style>

<?php get_footer(); ?>