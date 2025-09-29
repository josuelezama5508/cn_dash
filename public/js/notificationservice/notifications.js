// Convierte la clave pública VAPID a formato Uint8Array para el PushManager
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
  
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }
  
  if ('serviceWorker' in navigator && 'PushManager' in window) {
    navigator.serviceWorker.register('/cn_dash/public/js/notificationservice/sw.js').then(async function (registration) {
      console.log('✅ Service Worker registrado');
  
      // Solicita permiso para notificaciones
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') {
        console.log('❌ Permiso de notificaciones denegado');
        return;
      }
  
      // Suscribirse al push
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array('BCjPb7dVEemXyruccqydhfkgjhK9eZBzjGT8i6Q49o9HYMyRYscCygePBzqvq_zNU3MI54Mr1-at-j1zlbV8Grc') // Tu Public Key
      });
  
      console.log('✅ Suscripción creada:', subscription);
  
      // Enviar al backend con fetchAPI
      await fetchAPI('notificationservice', 'POST', subscription.toJSON());
  
      console.log('✅ Suscripción enviada al servidor');
    }).catch(function (error) {
      console.error('❌ Error al registrar Service Worker:', error);
    });
  } else {
    console.warn('⚠️ Push messaging no soportado en este navegador');
  }
  