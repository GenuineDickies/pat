<div class="login-form">
    <div class="login-header">
        <h2><?php echo SITE_NAME; ?></h2>
        <p class="text-muted">Please sign in to your account</p>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo SITE_URL; ?>login">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" required
                       placeholder="Enter your email address">
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" required
                       placeholder="Enter your password">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">
                Remember me
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>

        <div class="text-center">
            <small class="text-muted">
                <i class="bi bi-shield-check"></i>
                Secure login for authorized personnel only
            </small>
        </div>
    </form>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = this.querySelector('i');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
});
</script>
