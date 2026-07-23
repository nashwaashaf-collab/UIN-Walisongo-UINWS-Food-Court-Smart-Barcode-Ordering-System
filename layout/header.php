<?php
if (!defined('APP_STARTED')) {
    define('APP_STARTED', true);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Food Court') ?></title>
    <link rel="stylesheet" href="<?= base_url('layout/style.css') ?>">
</head>
<body>
<header class="topbar">
    <div class="brand"><a href="<?= base_url('index.php') ?>">Food Court</a></div>
    <?php
    $__current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $__current_path = rtrim(str_replace('\\', '/', $__current_path), '/');
    $__nav_paths = [
        'admin' => base_url('admin/index.php'),
        'dashboard' => base_url('pelanggan/dashboard.php'),
        'cart' => base_url('pelanggan/cart.php'),
        'stand' => base_url('stand/index.php'),
        'login' => base_url('login.php'),
        'register' => base_url('register.php'),
    ];
    ?>
    <nav>
        <?php if (isLoggedIn()): ?>
            <?php if (currentUser()['role'] === 'admin'): ?>
                <a class="nav-toggle <?= $__current_path === $__nav_paths['admin'] ? 'active' : '' ?>" href="<?= $__nav_paths['admin'] ?>">Admin</a>
            <?php elseif (currentUser()['role'] === 'pelanggan'): ?>
                <a class="nav-toggle <?= $__current_path === $__nav_paths['dashboard'] ? 'active' : '' ?>" href="<?= $__nav_paths['dashboard'] ?>">Dashboard</a>
                <a class="nav-toggle <?= $__current_path === $__nav_paths['cart'] ? 'active' : '' ?>" href="<?= $__nav_paths['cart'] ?>">Cart</a>
            <?php else: ?>
                <a class="nav-toggle <?= $__current_path === $__nav_paths['stand'] ? 'active' : '' ?>" href="<?= $__nav_paths['stand'] ?>">Stand Panel</a>
            <?php endif; ?>
            <a class="nav-toggle" href="<?= base_url('logout.php') ?>">Logout</a>
        <?php else: ?>
            <a class="nav-toggle <?= $__current_path === $__nav_paths['login'] ? 'active' : '' ?>" href="<?= $__nav_paths['login'] ?>">Login</a>
            <a class="nav-toggle <?= $__current_path === $__nav_paths['register'] ? 'active' : '' ?>" href="<?= $__nav_paths['register'] ?>">Daftar</a>
        <?php endif; ?>
    </nav>
</header>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const nav = document.querySelector('.topbar nav');
    if (!nav) return;
    const links = Array.from(nav.querySelectorAll('a.nav-toggle'));
    function clearActive() {
        links.forEach(a => a.classList.remove('active'));
    }
    function setActiveFor(link){
        clearActive();
        link.classList.add('active');
    }
    // Set active based on current URL path
    const currentPath = window.location.pathname.replace(/\/+$/,'');
    let matched = false;
    links.forEach(a => {
        try{
            const path = new URL(a.href, window.location.origin).pathname.replace(/\/+$/,'');
            if (path === currentPath) {
                setActiveFor(a);
                matched = true;
            }
        }catch(e){/* ignore invalid URLs */}
    });
    // If no match found, keep server-side classes
    // Add click handlers to show active immediately on click
    links.forEach(a => {
        a.addEventListener('click', function(e){
            // visually set active immediately
            setActiveFor(a);
            // allow navigation to proceed
        });
    });
});
</script>
<main class="main-content">
<?= showFlash('success') . showFlash('error') ?>

<style>
.alert {
    animation: slideInLeft 0.3s ease-in-out;
}

.alert.auto-hide {
    animation: slideOutLeft 0.3s ease-in-out 4.7s forwards;
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOutLeft {
    to {
        opacity: 0;
        transform: translateX(-20px);
    }
}

.toast-container {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 350px;
}

.toast {
    background: white;
    border-left: 4px solid #28a745;
    padding: 15px 15px 15px 15px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: slideInLeft 0.3s ease-in-out;
    min-width: 280px;
}

.toast.error {
    border-left-color: #dc3545;
}

.toast.success {
    border-left-color: #28a745;
}

.toast-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #999;
    padding: 0;
    margin-left: 15px;
    flex-shrink: 0;
}

.toast-close:hover {
    color: #333;
}

@keyframes slideOutLeft {
    to {
        opacity: 0;
        transform: translateX(-20px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.classList.add('auto-hide');
        setTimeout(() => {
            alert.style.animation = 'slideOutLeft 0.3s ease-in-out forwards';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Add close button to alerts
    alerts.forEach(alert => {
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = 'background:none;border:none;font-size:24px;cursor:pointer;position:absolute;right:10px;top:10px;color:#999;';
        closeBtn.onclick = function() {
            alert.style.animation = 'slideOutLeft 0.3s ease-in-out forwards';
            setTimeout(() => alert.remove(), 300);
        };
        alert.style.position = 'relative';
        alert.appendChild(closeBtn);
    });
});
</script>

