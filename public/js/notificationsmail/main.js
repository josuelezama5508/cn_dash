// ==================== MAIN JS ====================

$(document).ready(() => {
    renderNotifications(); // Llama la función que está en renderMail.js
  
    $('input[name="search"]').on('input', function () {
      const search = $(this).val().trim();
      renderNotifications(search);
    });
  });
  