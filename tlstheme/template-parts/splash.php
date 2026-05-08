<?php 
if (!is_front_page()) return;

$enabled = get_option('tls_splash_enabled', 1);
if (!$enabled) return;

$img_id = get_option('tls_splash_img', 0);
if (!$img_id) return;

$img_url = wp_get_attachment_url($img_id);
if (!$img_url) return;
?>

<div class="splash-screen" id="splashScreen">
    <div class="splash-bg-img" style="background-image: url(<?php echo $img_url; ?>);"></div>
    <div class="splash-overlay"></div>
    <div class="splash-content">
        <button class="splash-btn" id="splashBtn">Get started</button>
    </div>
</div>

<style>
.splash-screen { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; }
.splash-bg-img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; }
.splash-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.35); }
.splash-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; text-align: center; }
.splash-btn { padding: 14px 48px; background: var(--white); color: #16a34a; border: none; border-radius: 30px; font-size: 1rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 20px rgba(0,0,0,0.2); transition: all 0.3s ease; }
.splash-btn:hover { transform: scale(1.05); box-shadow: 0 6px 25px rgba(0,0,0,0.3); }
body.splash-done .splash-screen { display: none; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var splash = document.getElementById('splashScreen');
    var btn = document.getElementById('splashBtn');
    if (btn) {
        btn.addEventListener('click', function() {
            splash.style.opacity = '0';
            setTimeout(function(){ document.body.classList.add('splash-done'); }, 500);
        });
    }
});
</script>