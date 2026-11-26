const pushForm = document.getElementById('web-push-form');
const pushButton = pushForm.querySelector('button');
const pushStatus = pushForm.querySelector('[role="status"]');

if (!window.isSecureContext || !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
    pushStatus.textContent = 'Notifikasi membutuhkan HTTPS atau localhost dan browser yang mendukung Web Push.';
} else {
    pushButton.disabled = false;
    pushButton.addEventListener('click', async () => {
        pushButton.disabled = true;
        try {
            if (await Notification.requestPermission() !== 'granted') {
                pushStatus.textContent = 'Izin notifikasi belum diberikan. Cek pengaturan situs di browser.';
                return;
            }
            await navigator.serviceWorker.register(pushForm.dataset.worker);
            const registration = await navigator.serviceWorker.ready;
            const publicKey = pushForm.dataset.key.replace(/-/g, '+').replace(/_/g, '/');
            const key = Uint8Array.from(atob(publicKey), char => char.charCodeAt(0));
            let subscription = await registration.pushManager.getSubscription();
            const oldKey = new Uint8Array(subscription?.options.applicationServerKey ?? []);
            if (subscription && (oldKey.length !== key.length || oldKey.some((value, index) => value !== key[index]))) {
                if (pushForm.dataset.test === '1') {
                    pushStatus.textContent = 'Browser memakai kunci lama. Hubungkan ulang dari Pengaturan PUTU.';
                    return;
                }
                await subscription.unsubscribe();
                subscription = null;
            }
            subscription ??= await registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: key });
            pushForm.elements.subscription.value = JSON.stringify(subscription);
            pushForm.requestSubmit();
        } catch {
            pushStatus.textContent = 'Browser belum bisa dihubungkan. Cek izin lalu coba lagi.';
        } finally {
            pushButton.disabled = false;
        }
    });
}
