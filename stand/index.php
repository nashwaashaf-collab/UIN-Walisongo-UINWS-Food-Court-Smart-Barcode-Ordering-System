<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('stand');
$stand = getStandForUser(currentUser()['id']);
if (!$stand) {
    flash('error', 'Akun stand belum terhubung dengan data stand.');
    redirect('index.php');
}
$orderCount = getOrderCountByStand($stand['id']);
$title = 'Panel Stand - Food Court';
require_once __DIR__ . '/../layout/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div id="toast-container" style="position: fixed; top: 20px; left: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 350px;"></div>

<div class="section-title">
    <h2>Panel Stand: <?= htmlspecialchars($stand['name']) ?></h2>
</div>
<div class="card-grid" style="grid-template-columns: repeat(auto-fit,minmax(240px,1fr));">
    <div class="card">
        <div class="card-body">
            <h3>Informasi Stand</h3>
            <p><?= htmlspecialchars($stand['description']) ?></p>
            <?php if (!empty($stand['qris_image'])): ?>
                <p><strong>QRIS:</strong></p>
                <img src="<?= base_url('assets/' . $stand['qris_image']) ?>" alt="QRIS <?= htmlspecialchars($stand['name']) ?>" style="max-width: 240px; display:block; margin-top: 12px;">
            <?php endif; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>Menu</h3>
            <p class="tag"><?= getMenusByStandAll($stand['id']) ? count(getMenusByStandAll($stand['id'])) : 0 ?> Item</p>
            <a href="<?= base_url('stand/menus.php') ?>" class="btn-secondary">Kelola Menu</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>Pesanan Stand</h3>
            <p class="tag"><?= $orderCount ?> Pesanan</p>
            <a href="<?= base_url('stand/orders.php') ?>" class="btn-secondary">Lihat Pesanan</a>
        </div>
    </div>
</div>

</body>
</html>
<script>
(function() {
    const POLL_INTERVAL = 5000;
    const API_URL = '<?= base_url('stand/api-orders.php') ?>';
    let lastCheckTime = parseInt(localStorage.getItem('lastOrderCheck') || '0');
    let dismissedOrders = JSON.parse(localStorage.getItem('dismissedOrders') || '[]');

    function playNotificationSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (e) {
            console.log('Sound failed:', e);
        }
    }

    function showToast(order) {
        const container = document.getElementById('toast-container');
        const menus = order.menu_list || 'Menu tidak tersedia';
        
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.setAttribute('data-order-id', order.id);
        toast.style.cssText = `
            background: white;
            border-left: 4px solid #28a745;
            padding: 12px 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            animation: slideInLeft 0.3s ease-in-out;
            min-width: 280px;
        `;
        
        const content = document.createElement('div');
        content.style.cssText = 'flex: 1; word-break: break-word;';
        content.innerHTML = `
            <strong style="display: block; margin-bottom: 4px; color: #28a745;">🔔 Pesanan Masuk</strong>
            <div style="font-size: 0.9rem; margin-bottom: 4px;">
                <strong>${order.pelanggan_name}</strong>
            </div>
            <div style="font-size: 0.85rem; color: #666; margin-bottom: 2px;">
                ${menus}
            </div>
            <div style="font-size: 0.85rem; color: #999;">
                Rp ${new Intl.NumberFormat('id-ID').format(order.total)}
            </div>
        `;
        
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            padding: 0;
            margin-left: 10px;
            flex-shrink: 0;
        `;
        closeBtn.onmouseover = () => closeBtn.style.color = '#333';
        closeBtn.onmouseout = () => closeBtn.style.color = '#999';
        closeBtn.onclick = function() {
            dismissOrder(order.id);
            toast.style.animation = 'slideOutLeft 0.3s ease-in-out forwards';
            setTimeout(() => toast.remove(), 300);
        };
        
        toast.appendChild(content);
        toast.appendChild(closeBtn);
        container.appendChild(toast);

        // Auto-hide after 5 seconds
        const autoHideTimer = setTimeout(() => {
            if (document.contains(toast)) {
                dismissOrder(order.id);
                toast.style.animation = 'slideOutLeft 0.3s ease-in-out forwards';
                setTimeout(() => {
                    if (document.contains(toast)) toast.remove();
                }, 300);
            }
        }, 5000);
    }

    function dismissOrder(orderId) {
        if (!dismissedOrders.includes(orderId)) {
            dismissedOrders.push(orderId);
            localStorage.setItem('dismissedOrders', JSON.stringify(dismissedOrders));
        }
    }

    function checkNewOrders() {
        fetch(API_URL + '?last_check=' + lastCheckTime)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    lastCheckTime = data.current_time;
                    localStorage.setItem('lastOrderCheck', lastCheckTime);
                    
                    data.orders.forEach(order => {
                        if (!dismissedOrders.includes(order.id)) {
                            showToast(order);
                            playNotificationSound();
                        }
                    });
                }
            })
            .catch(e => console.log('Poll error:', e));
    }

    checkNewOrders();
    setInterval(checkNewOrders, POLL_INTERVAL);
})();
</script>

<style>
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
</style>

<?php require_once __DIR__ . '/../layout/footer.php';
