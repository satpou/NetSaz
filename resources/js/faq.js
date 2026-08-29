document.addEventListener('alpineinit', () => {
    Alpine.data('accordion', () => ({
        open: false,

        init() {
            // If the initially open state is true, set the open property to true
            if (this.$el.getAttribute('x-init') && this.$el.getAttribute('x-init').includes('open:true')) {
                this.open = true
            }
        },

        toggle() {
            this.open = !this.open
        }
    }))
})

window.addEventListener('scroll', () => {
    const progress = document.getElementById('progress');
    if (progress) {
        const h = document.documentElement;
        const scrolled = (h.scrollTop) / (h.scrollHeight - h.clientHeight) * 100;
        progress.style.width = scrolled + '%';
    }
});

// reveal on scroll
const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('in-view');
            io.unobserve(e.target);
        }
    });
}, { threshold: 0.15 });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));

// animated counters in stats bar
const counterIo = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseFloat(el.dataset.count);
        const suffix = el.dataset.suffix || '';
        const duration = 1400;
        const start = performance.now();

        function tick(now) {
            const p = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - p, 3);
            const val = Math.floor(target * eased);
            el.textContent = val.toLocaleString('id-ID') + suffix;
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
        counterIo.unobserve(el);
    });
}, { threshold: 0.4 });
document.querySelectorAll('.stat-num').forEach(el => counterIo.observe(el));

// Live ticking ledger counter in hero signature
const hub = document.getElementById('hub-count');
if (hub) {
    let total = 128.4;
    setInterval(() => {
        total += Math.random() * 0.3;
        hub.textContent = 'Rp ' + total.toFixed(1).replace('.', ',') + 'Jt';
    }, 2400);
}
