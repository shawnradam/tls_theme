<?php /* Template Name: Login Page */ ?>
<?php get_header(); ?>

<div class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="<?php echo get_template_directory_uri(); ?>/headerbgpnglogo.webp" alt="TanahLotSabah">
            </div>
            
            <h1>Welcome Back</h1>
            <p class="login-subtitle">Login to your account</p>
            
            <form method="post" id="front-login-form">
                <input type="hidden" name="redirect_to" value="<?php echo home_url('/dashboard/'); ?>">
                <input type="hidden" name="nonce" id="login_nonce" value="<?php echo wp_create_nonce('tls_login_nonce'); ?>">
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" name="log" id="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="pwd" id="password" required>
                </div>
                
                <div class="form-remember">
                    <label>
                        <input type="checkbox" name="rememberme" value="forever"> Remember me
                    </label>
                </div>
                
                <button type="submit" class="login-submit-btn">Login</button>
                
                <div class="login-error" id="loginError"></div>
            </form>
            
            <div class="login-links">
                <a href="<?php echo wp_lostpassword_url(); ?>">Forgot Password?</a>
            </div>
        </div>
    </div>
</div>

<style>
.login-page {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    padding: 40px 20px;
}

.login-container {
    width: 100%;
    max-width: 420px;
}

.login-box {
    background: #fff;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.login-logo {
    text-align: center;
    margin-bottom: 24px;
}

.login-logo img {
    height: 50px;
    width: auto;
}

.login-box h1 {
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 8px;
}

.login-subtitle {
    text-align: center;
    color: #64748b;
    margin-bottom: 32px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
    color: #374151;
}

.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.2s;
}

.form-group input:focus {
    outline: none;
    border-color: #16a34a;
}

.form-remember {
    margin-bottom: 20px;
}

.form-remember label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #64748b;
    cursor: pointer;
}

.login-submit-btn {
    width: 100%;
    padding: 14px;
    background: #16a34a;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.login-submit-btn:hover {
    background: #15803d;
}

.login-error {
    margin-top: 16px;
    padding: 12px;
    background: #fef2f2;
    color: #dc2626;
    border-radius: 8px;
    font-size: 14px;
    display: none;
}

.login-error.show {
    display: block;
}

.login-links {
    margin-top: 24px;
    text-align: center;
}

.login-links a {
    color: #16a34a;
    font-size: 14px;
    text-decoration: none;
}

.login-links a:hover {
    text-decoration: underline;
}
</style>

<script>
document.getElementById('front-login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var btn = this.querySelector('.login-submit-btn');
    var error = document.getElementById('loginError');
    var nonce = document.getElementById('login_nonce').value;
    var username = document.getElementById('username').value;
    var password = document.getElementById('password').value;
    var remember = this.querySelector('input[name="rememberme"]').checked;
    
    btn.disabled = true;
    btn.textContent = 'Logging in...';
    error.classList.remove('show');
    
    var formData = new FormData();
    formData.append('action', 'tls_ajax_login');
    formData.append('nonce', nonce);
    formData.append('username', username);
    formData.append('password', password);
    formData.append('remember', remember ? 1 : 0);
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.href = data.data.redirect || '<?php echo home_url('/dashboard/'); ?>';
        } else {
            error.textContent = data.data.message || 'Login failed';
            error.classList.add('show');
            btn.disabled = false;
            btn.textContent = 'Login';
        }
    })
    .catch(function() {
        // Fallback: submit normally
        var redirect = '<?php echo home_url('/dashboard/'); ?>';
        var loginUrl = '<?php echo wp_login_url(); ?>';
        var actionUrl = loginUrl + '?redirect_to=' + encodeURIComponent(redirect);
        
        // Create hidden input and submit
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'redirect_to';
        input.value = redirect;
        
        var nonce = document.createElement('input');
        nonce.type = 'hidden';
        nonce.name = 'wpnonce';
        nonce.value = '<?php echo wp_create_nonce('login_nonce'); ?>';
        
        var form = document.getElementById('front-login-form');
        form.action = loginUrl;
        form.appendChild(input);
        form.appendChild(nonce);
        form.submit();
    });
});
</script>

<?php get_footer(); ?>