<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;

$this->registerCss("
    .notif-dropdown-menu {
        width: 320px;
        max-height: 400px;
        overflow-y: auto;
    }
    @media (max-width: 576px) {
        .notif-dropdown-menu { width: 100vw; }
    }
    .notif-item { transition: background-color 0.2s; }
    .notif-item:hover { background-color: #f8f9fa; }
    .notif-item.unread { background-color: #e9ecef; border-left: 3px solid #0d6efd; }
");

?>
<header class="s-bg-main border-bottom shadow-sm">
    <nav class="container-fluid d-flex align-items-center justify-content-between py-2 px-3">

        <div class="d-flex align-items-center gap-2">

            <?php if (!Yii::$app->user->isGuest): ?>
            <a class="d-lg-none text-white text-decoration-none fs-3"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarOffcanvas"
                aria-controls="sidebarOffcanvas">
                <i class="fa-solid fa-bars"></i>
            </a>
            <?php endif ?>

            <a href="<?= Url::to(Yii::$app->homeUrl) ?>" class="d-flex gap-2 align-items-center text-decoration-none text-white">
                <?= Html::img('@web/images/matasapi.svg', [
                    'alt' => 'Matasapi Digdaya Logo',
                    'class' => 'bg-white rounded-3 p-1',
                    'style' => 'width: 45px; height: 45px; object-fit: contain;'
                ]) ?>
                <div class="d-flex flex-column lh-sm">
                    <h2 class="mb-0 fs-5 fw-bold text-uppercase tracking-wide">Sertifikasi</h2>
                    <small class="text-white-50 font-monospace" style="font-size: 10px; letter-spacing: 0.5px;">SASPRI-K</small>
                </div>
            </a>
        </div>

        <div class="d-flex align-items-center text-decoration-none gap-3">
            <?php if (Yii::$app->user->isGuest): ?>
                <div class="d-flex gap-4">
                    <a href="<?= Url::to('/site/login') ?>" class="text-white text-decoration-none">Login</a>
                    <a href="<?= Url::to('/site/signup') ?>" class="text-white text-decoration-none">Signup</a>
                </div>
            <?php else: ?>
                <div class="notifdropdown">
                    <a href="#" class="text-white text-decoration-none position-relative align-middle" data-bs-toggle="dropdown" aria-expanded="false" id="notifDropdownBtn">
                        <i class="fa-solid fa-bell fs-4"></i>
                        <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none; font-size: 0.65rem;">
                            0
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow notif-dropdown-menu p-0">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light sticky-top">
                            <h6 class="mb-0 fw-bold">Notifikasi</h6>
                            <button class="btn btn-sm text-primary text-decoration-none p-0" id="btnMarkAllRead">Tandai semua dibaca</button>
                        </div>
                        <!-- <div class="p-2 border-bottom text-center">
                            <button id="btnEnablePush" class="btn btn-sm btn-outline-primary w-100">
                                <i class="fa-solid fa-mobile-screen"></i> Aktifkan Push Notifikasi
                            </button>
                        </div> -->
                        <div id="notifListContainer">
                            <div class="text-center p-3 text-muted">Memuat...</div>
                        </div>
                    </div>
                </div>

                <div class="accountdropdown">
                    <a href="#" class="d-flex gap-2 align-items-center text-white text-decoration-none dropdown-toggle acc-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <p class="mb-0 d-none d-md-block"><?= Html::encode(\Yii::$app->user->identity?->username) ?></p>
                        <i class="fa-solid fa-circle-user fs-4 mb-0"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow w-fit">
                        <li class="d-block d-md-none px-3 py-2 border-bottom text-muted">
                            Hi, <?= Html::encode(\Yii::$app->user->identity?->username) ?>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= Url::to(['/profile/edit']) ?>"><i class="fa-solid fa-user-pen me-2"></i> Edit Profil</a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="<?= Url::to(['/site/logout']) ?>" data-method="post">
                                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif ?>
        </div>

    </nav>
</header>
<?php
$listUrl = Url::to(['/notification/list']);
$unreadCountUrl = Url::to(['/notification/unread-count']);
$markReadUrl = Url::to(['/notification/mark-read']);
$markAllReadUrl = Url::to(['/notification/mark-all-read']);
$registerTokenUrl = Url::to(['/notification/register-token']);
$unregisterTokenUrl = Url::to(['/notification/unregister-token']);
$firebaseParams = \Yii::$app->params['firebase'];
$firebaseJson = \yii\helpers\Json::encode($firebaseParams);

$js = <<<JS

// 1. Inisialisasi Firebase Config (Sama persis dengan di file Service Worker)
var configParams = $firebaseJson;

var firebaseConfig = {
    apiKey: configParams.apiKey,
    authDomain: configParams.authDomain,
    projectId: configParams.projectId,
    storageBucket: configParams.storageBucket,
    messagingSenderId: configParams.messagingSenderId,
    appId: configParams.appId
};
firebase.initializeApp(firebaseConfig);
var messaging = firebase.messaging();

if ('serviceWorker' in navigator) {
    var queryString = new URLSearchParams(configParams).toString();
    var swUrl = '/firebase-messaging-sw.js?' + queryString;

    navigator.serviceWorker.register(swUrl, { scope: '/' })
    .then(function(registration) {
        messaging.useServiceWorker(registration);
    }).catch(console.error);
}

$(document).ready(function() {
    var \$notifBadge = $('#notifBadge');
    var \$notifListContainer = $('#notifListContainer');
    var \$notifDropdownBtn = $('#notifDropdownBtn');
    var \$btnMarkAllRead = $('#btnMarkAllRead');
    var \$btnEnablePush = $('#btnEnablePush');
    var \$toastloc= $('#toast-location');

    if ('Notification' in window) {
        if (Notification.permission === 'granted') {
            \$btnEnablePush.html('<i class="fa-solid fa-check"></i> Push Notifikasi Aktif')
                          .removeClass('btn-outline-primary').addClass('btn-success')
                          .prop('disabled', true);
        } else if (Notification.permission === 'denied') {
            \$btnEnablePush.html('<i class="fa-solid fa-ban"></i> Notifikasi Diblokir')
                          .removeClass('btn-outline-primary').addClass('btn-danger')
                          .prop('disabled', true);
        }
    }

    // Fungsi Fetch Jumlah Belum Dibaca
    function fetchUnreadCount() {
        $.ajax({
            url: '$unreadCountUrl',
            type: 'GET',
            success: function(data) {
                var count = data.total !== undefined ? data.total : data;
                if (count > 0) {
                    \$notifBadge.text(count > 99 ? '99+' : count).show();
                } else {
                    \$notifBadge.hide();
                }
            },
            error: function(err) {
                console.error('Gagal mengambil jumlah notifikasi', err);
            }
        });
    }

    // Fungsi Fetch Daftar Notifikasi
    function fetchNotifications() {
        \$notifListContainer.html('<div class="text-center p-3 text-muted">Memuat...</div>');
        
        $.ajax({
            url: '$listUrl',
            type: 'GET',
            success: function(data) {
                var notifications = data.notifications || [];
                if (notifications.length === 0) {
                    \$notifListContainer.html('<div class="text-center p-3 text-muted">Belum ada notifikasi.</div>');
                    return;
                }

                var html = '';
                $.each(notifications, function(i, notif) {
                    var isUnread = notif.read_at === null;
                    var unreadClass = isUnread ? 'unread' : '';
                    var btnHtml = isUnread ? '<button class="btn btn-link btn-sm p-0 mt-1 text-decoration-none mark-read-btn">Tandai dibaca</button>' : '';
                    
                    // Tambahkan atribut data-link dan CSS cursor pointer jika web_link ada isinya
                    var linkAttr = notif.web_link ? ' data-link="' + notif.web_link + '" style="cursor: pointer;" title="Klik untuk membuka"' : '';
                    
                    html += '<div class="p-3 border-bottom notif-item position-relative ' + unreadClass + '" data-id="' + notif.id + '"' + linkAttr + '>' +
                                '<div class="fw-bold fs-6 mb-1 text-dark">' + notif.title + '</div>' +
                                '<div class="text-muted small">' + notif.body + '</div>' +
                                '<div class="position-relative" style="z-index: 2;">' + btnHtml + '</div>' +
                            '</div>';
                });
                
                \$notifListContainer.html(html);
            },
            error: function(err) {
                console.error('Gagal memuat notifikasi', err);
            }
        });
    }

    \$notifListContainer.on('click', '.notif-item', function(e) {
        // Jika yang diklik adalah tombol "Tandai dibaca", hentikan agar tidak ikut pindah halaman
        if ($(e.target).hasClass('mark-read-btn')) {
            return; 
        }

        var link = $(this).data('link');
        var notifId = $(this).data('id');

        if (link) {
            // [BONUS UX]: Jika item belum dibaca, langsung tandai dibaca di background sebelum redirect
            if ($(this).hasClass('unread')) {
                $.ajax({ 
                    url: '$markReadUrl?notification_id=' + notifId,
                    type: 'POST', 
                    async: false 
                });
            }

            // Pastikan URL mengarah ke base root jika formatnya relatif (misal: 'tim-mandiri/index' menjadi '/tim-mandiri/index')
            var finalUrl = link.startsWith('/') || link.startsWith('http') ? link : '/' + link;
            window.location.href = finalUrl;
        }
    });

    // Event saat dropdown lonceng diklik (Dibuka) menggunakan event Bootstrap 5
    \$notifDropdownBtn.on('show.bs.dropdown', function () {
        fetchNotifications();
    });

    // Event listener dinamis untuk klik "Tandai dibaca" per item
    \$notifListContainer.on('click', '.mark-read-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var notifId = $(this).closest('.notif-item').data('id');
        $.ajax({
            url: '$markReadUrl?notification_id=' + notifId,
            type: 'POST',
            success: function() {
                fetchNotifications();
                fetchUnreadCount();
            }
        });
    });

    // Event listener untuk "Tandai semua dibaca"
    \$btnMarkAllRead.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        $.ajax({
            url: '$markAllReadUrl',
            type: 'POST',
            success: function() {
                fetchNotifications();
                fetchUnreadCount();
            }
        });
    });

    // 2. Logika Meminta Izin dan Mendapatkan Token
    \$btnEnablePush.on('click', function(e) {
        e.preventDefault();

        // Munculkan popup izin bawaan browser
        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                console.log('Izin notifikasi diberikan.');

                // Ganti dengan VAPID KEY Anda dari Firebase Console
                messaging.getToken({ vapidKey: configParams.vapidKey })
                .then(function(currentToken) {
                    if (currentToken) {
                        // Kirim token ke backend Yii2
                        $.ajax({
                            url: '$registerTokenUrl',
                            type: 'POST',
                            data: { token: currentToken },
                            success: function(res) {
                                console.log('Token berhasil disimpan', res);
                                // Ubah tampilan tombol
                                \$btnEnablePush
                                    .html('<i class="fa-solid fa-check"></i> Push Notifikasi Aktif')
                                    .removeClass('btn-outline-primary')
                                    .addClass('btn-success')
                                    .prop('disabled', true);
                            }
                        });
                    } else {
                        console.log('Tidak dapat membuat token FCM.');
                    }
                }).catch(function(err) {
                    console.log('Terjadi kesalahan saat mengambil token.', err);
                });
            } else {
                console.log('Izin notifikasi ditolak oleh user.');
            }
        });
    });
    
    //  // not working disabled for now
    // // 3. Menangkap Notifikasi saat User sedang membuka web (Foreground)
    // messaging.onMessage(function(payload) {
    //     console.log('Notifikasi masuk saat web sedang dibuka: ', payload);
        
    //     // Refresh otomatis agar angka badge bertambah
    //     fetchUnreadCount();
        
    //     // Jika dropdown sedang terbuka, refresh list notifikasinya juga
    //     if (\$notifDropdownBtn.hasClass('show')) {
    //         fetchNotifications();
    //     }

    //     var title = payload.data?.title || payload.notification?.title || 'Notifikasi Baru';
    //     var body = payload.data?.body || payload.notification?.body || '';

    //     var html = '<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">'+
    //                 '<div class="toast-header">'+
    //                     '<i class="fa-solid fa-tower-broadcast me-2"></i>'+
    //                     '<strong class="me-auto overflow-x-hidden">'+ title +'</strong>'+
    //                     '<small>Baru Saja</small>'+
    //                     '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>' +
    //                 '</div>'+
    //                 '<div class="toast-body">'+ body +'.</div>'+
    //             '</div>';

    //     // Menambahkan toast/alert JavaScript (misal: SweetAlert atau Toastr) di sini jika ingin
    //     \$toastloc.html(html)
    //     var lastToastNode = \$toastloc.find('.toast').last()[0];
    //     if (lastToastNode) {
    //         var bsToast = new bootstrap.Toast(lastToastNode);
    //         bsToast.show();
    //     }
    // });

    // Panggil fetch jumlah pesan belum dibaca saat halaman dimuat
    if (\$notifBadge.length) {
        fetchUnreadCount();
    }

    // current solution for notif
    if (window.isUserLoggedIn) {
        const fetchUnreadInterval = setInterval(() => {
            fetchNotifications();
            fetchUnreadCount();
            // console.log('loop')
        }, 30000);
    }
});
JS;
$this->registerJs($js);
?>

<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>