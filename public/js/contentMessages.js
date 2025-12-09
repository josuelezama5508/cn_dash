// =====================================
// MODAL GENÉRICO DE MENSAJES / ERRORES
// =====================================

// Crea el modal y su estilo si no existe
function initGenericModal() {
    if (document.getElementById('genericErrorModal')) return;
  
    const modal = document.createElement('div');
    modal.id = 'genericErrorModal';
    modal.className = 'modal-overlay';
    modal.style.display = 'none';
    modal.innerHTML = `
      <div class="modal-container">
        <h2 id="genericModalTitle" class="modal-title"></h2>
        <p id="genericErrorMessage" class="modal-message"></p>
        <button id="closeGenericModalBtn" class="modal-btn">Cerrar</button>
      </div>
    `;
    document.body.appendChild(modal);
  
    const style = document.createElement('style');
    style.textContent = `
      .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.55);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
      }
      .modal-container {
        background: #fff;
        border-radius: 12px;
        padding: 22px 28px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        width: 380px;
        max-width: 90%;
        text-align: center;
        animation: fadeIn 0.25s ease-in-out;
      }
      .modal-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: #2c3e50;
      }
      .modal-message {
        font-size: 1rem;
        margin-bottom: 20px;
        color: #333;
      }
      .modal-btn {
        background: #2c3e50;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
      }
      .modal-btn:hover { background: #1a252f; }
      @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
      }
    `;
    document.head.appendChild(style);
  
    modal.addEventListener('click', (e) => {
      if (e.target === modal) hideErrorModal();
    });
  
    document.getElementById('closeGenericModalBtn').addEventListener('click', hideErrorModal);
  }
  
  // Muestra el modal con un mensaje dinámico
  function showErrorModal(data) {
    initGenericModal();
  
    const modal = document.getElementById('genericErrorModal');
    const title = document.getElementById('genericModalTitle');
    const msg = document.getElementById('genericErrorMessage');
  
    let messageText = 'Ocurrió un error inesperado.';
    let titleText = 'Aviso';
  
    if (typeof data === 'string') {
      messageText = data;
    } else if (typeof data === 'object' && data !== null) {
      if (data.error) {
        messageText = data.error;
        titleText = 'Error';
      } else if (data.message) {
        messageText = data.message;
        titleText = 'Mensaje';
      } else if (data.status && typeof data.status === 'string') {
        messageText = data.status;
      }
    }
  
    title.textContent = titleText;
    msg.textContent = messageText;
    modal.style.display = 'flex';
  }
  
  // Oculta el modal
  function hideErrorModal() {
    const modal = document.getElementById('genericErrorModal');
    if (modal) modal.style.display = 'none';
  }
  
  // Inicializar automáticamente al cargar el script
  document.addEventListener('DOMContentLoaded', initGenericModal);
  // =====================================
// MODAL DE MENSAJES POSITIVOS / ÉXITO
// =====================================

// Crea el modal y su estilo si no existe
function initSuccessModal() {
  if (document.getElementById('genericSuccessModal')) return;

  const modal = document.createElement('div');
  modal.id = 'genericSuccessModal';
  modal.className = 'modal-overlay';
  modal.style.display = 'none';
  modal.innerHTML = `
    <div class="modal-container success">
      <h2 id="genericSuccessTitle" class="modal-title"></h2>
      <p id="genericSuccessMessage" class="modal-message"></p>
      <button id="closeSuccessModalBtn" class="modal-btn success-btn">Aceptar</button>
    </div>
  `;
  document.body.appendChild(modal);

  const style = document.createElement('style');
  style.textContent = `
    /* Reutiliza estilos base del modal existente */
    .modal-container.success {
      border-left: 6px solid #2ecc71;
    }
    .modal-title {
      margin-bottom: 10px;
    }
    .success-btn {
      background: #2ecc71;
    }
    .success-btn:hover {
      background: #239b56;
    }
  `;
  document.head.appendChild(style);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) hideSuccessModal();
  });

  document.getElementById('closeSuccessModalBtn').addEventListener('click', hideSuccessModal);
}

// Mostrar modal de éxito
function showSuccessModal(data) {
  initSuccessModal();

  const modal = document.getElementById('genericSuccessModal');
  const title = document.getElementById('genericSuccessTitle');
  const msg = document.getElementById('genericSuccessMessage');

  let messageText = 'Se ha completado el envío.';
  let titleText = 'Éxito';

  if (typeof data === 'string') {
    messageText = data;
  } else if (typeof data === 'object' && data !== null) {
    if (data.message) {
      messageText = data.message;
    } else if (data.status) {
      messageText = data.status;
    }
  }

  title.textContent = titleText;
  msg.textContent = messageText;
  modal.style.display = 'flex';
}

// Ocultar modal
function hideSuccessModal() {
  const modal = document.getElementById('genericSuccessModal');
  if (modal) modal.style.display = 'none';
}

// Inicializar automáticamente
document.addEventListener('DOMContentLoaded', initSuccessModal);

  