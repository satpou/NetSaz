document.querySelector(`.ct-form`).addEventListener(`submit`,function(e){e.preventDefault();var t=document.getElementById(`name`).value,n=document.getElementById(`email`).value,r=document.getElementById(`subject`).value,i=document.getElementById(`message`).value;if(!t||!n||!r||!i){alert(`Mohon isi semua kolom`);return}var a=`Halo, saya `+t+` (`+n+`)

*Subjek:* `+r+`

Pesan: `+i,o=`https://wa.me/6281287282084?text=`+encodeURIComponent(a);window.open(o,`_blank`)});