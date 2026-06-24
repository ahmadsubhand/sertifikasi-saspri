// frontend/web/firebase-messaging-sw.js

// Mengimpor SDK Firebase versi 8 (Compat) khusus untuk Service Worker
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

const params = new URL(location).searchParams;

const firebaseConfig = {
    apiKey: params.get('apiKey'),
    authDomain: params.get('authDomain'),
    projectId: params.get('projectId'),
    storageBucket: params.get('storageBucket'),
    messagingSenderId: params.get('messagingSenderId'),
    appId: params.get('appId')
};

// Inisialisasi Firebase
firebase.initializeApp(firebaseConfig);

// Inisialisasi Firebase Messaging
const messaging = firebase.messaging();

// Menangkap notifikasi saat aplikasi berjalan di background (tab ditutup)
messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Menerima pesan background ', payload);

    // Firebase SDK akan secara otomatis memunculkan notifikasi jika payload.notification tersedia dari backend.

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/images/matasapi.svg',
        data: payload.data // Menyimpan link untuk aksi saat notifikasi di-klik
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Event saat pengguna mengklik notifikasi (misal: membuka link web)
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    if (event.notification.data && event.notification.data.web_link) {
        event.waitUntil(
            clients.openWindow(event.notification.data.web_link)
        );
    }
});