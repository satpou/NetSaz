// sidebar collapse
const sidebar = document.getElementById('sidebar');
const collapseBtn = document.getElementById('collapseBtn');
if (sidebar && collapseBtn) {
  collapseBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    const isCollapsed = sidebar.classList.contains('collapsed');
    const label = isCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar';
    collapseBtn.title = label;
    collapseBtn.setAttribute('aria-label', label);
  });
}

// reveal on load/scroll
const io = new IntersectionObserver((entries) => {
  entries.forEach(e => { if(e.isIntersecting){ e.target.classList.add('in-view'); io.unobserve(e.target); } });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));

// count-up (harmless at 0, ready for real data)
document.querySelectorAll('[data-count]').forEach(el => {
  const target = parseFloat(el.dataset.count);
  const duration = 900;
  const start = performance.now();
  function tick(now){
    const p = Math.min((now - start) / duration, 1);
    const val = Math.floor(target * (1 - Math.pow(1 - p, 3)));
    el.textContent = val.toLocaleString('id-ID');
    if(p < 1) requestAnimationFrame(tick);
  }
  requestAnimationFrame(tick);
});

// live date
const days = ['Minggu','Senin','Selasa','Rabu','Kamis',"Jumat","Sabtu"];
const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const now = new Date();
const dateStr = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} pukul ${String(now.getHours()).padStart(2,'0')}.${String(now.getMinutes()).padStart(2,'0')}`;
const todayDateElement = document.getElementById('today-date');
if (todayDateElement) {
  todayDateElement.textContent = dateStr;
}
