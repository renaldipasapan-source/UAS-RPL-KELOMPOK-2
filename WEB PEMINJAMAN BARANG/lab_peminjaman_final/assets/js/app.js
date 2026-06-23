// Lab Peminjaman — app.js

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm || 'Yakin ingin menghapus data ini?')) e.preventDefault();
  });
});

// Preview image upload
document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
  input.addEventListener('change', function () {
    const preview = document.getElementById(this.dataset.preview);
    if (preview && this.files[0]) {
      preview.src = URL.createObjectURL(this.files[0]);
      preview.classList.remove('d-none');
    }
  });
});

// Auto-dismiss alerts after 4s
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(a => {
    const bsAlert = bootstrap.Alert.getOrCreateInstance(a);
    bsAlert.close();
  });
}, 4000);
