document.querySelector('.ct-form').addEventListener('submit', function(e) {
  e.preventDefault();
  var name = document.getElementById('name').value;
  var email = document.getElementById('email').value;
  var subject = document.getElementById('subject').value;
  var message = document.getElementById('message').value;
  if (!name || !email || !subject || !message) {
    alert('Mohon isi semua kolom');
    return;
  }
  var pesanLengkap = "Halo, saya " + name + " (" + email + ")\n\n" +
                      "*Subjek:* " + subject + "\n\n" +
                      "Pesan: " + message;
  var url = "https://wa.me/6281287282084?text=" + encodeURIComponent(pesanLengkap);
  window.open(url, '_blank');
});
