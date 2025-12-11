document.addEventListener('DOMContentLoaded', function () {
  const editProfileBtn = document.getElementById('editProfileBtn');
  const editProfileModal = document.getElementById('editProfileModal');
  const closeModal = document.getElementById('closeModal');

  if (editProfileBtn && editProfileModal) {
    editProfileBtn.addEventListener('click', function () {
      editProfileModal.style.display = 'flex';
    });

    closeModal.addEventListener('click', function () {
      editProfileModal.style.display = 'none';
    });

    editProfileModal.addEventListener('click', function (e) {
      if (e.target === editProfileModal) {
        editProfileModal.style.display = 'none';
      }
    });
  }

  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.5s';
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  });
});
