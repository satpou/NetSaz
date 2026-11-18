document.addEventListener('DOMContentLoaded', () => {
  // scroll progress bar
  const progress = document.getElementById('progress');
  if (progress) {
    window.addEventListener('scroll', () => {
      const h = document.documentElement;
      const scrolled = (h.scrollTop) / (h.scrollHeight - h.clientHeight) * 100;
      progress.style.width = scrolled + '%';
    });
  }

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

  // Continuous Auto-Slide Pricing Showcase
  initAutoSlidePricing();
});

function initAutoSlidePricing() {
  const wrapper = document.querySelector('.pricing-carousel-wrapper');
  const track = document.querySelector('.pricing-carousel-track');
  if (!wrapper || !track) return;

  let pos = 0;
  let isHovered = false;
  let isDragging = false;
  let startX = 0;
  let startPos = 0;
  let dragDelta = 0;
  let lastTime = performance.now();

  // Speed: 48 pixels per second (smooth, calm, readable, continuous)
  const speedPerSecond = 48;

  function getHalfWidth() {
    return track.scrollWidth / 2;
  }

  function tick(now) {
    const dt = Math.min((now - lastTime) / 1000, 0.1);
    lastTime = now;

    if (!isHovered && !isDragging) {
      pos -= speedPerSecond * dt;
      const halfWidth = getHalfWidth();
      if (halfWidth > 0 && Math.abs(pos) >= halfWidth) {
        pos += halfWidth;
      }
      track.style.transform = `translate3d(${pos}px, 0, 0)`;
    }

    requestAnimationFrame(tick);
  }

  requestAnimationFrame(tick);

  // Pause when hovered
  wrapper.addEventListener('mouseenter', () => { isHovered = true; });
  wrapper.addEventListener('mouseleave', () => {
    isHovered = false;
    lastTime = performance.now();
  });

  // Mouse & Touch Dragging
  function handleDragStart(clientX) {
    isDragging = true;
    startX = clientX;
    startPos = pos;
    dragDelta = 0;
    track.classList.add('is-dragging');
  }

  function handleDragMove(clientX) {
    if (!isDragging) return;
    const delta = clientX - startX;
    dragDelta = delta;
    pos = startPos + delta;

    const halfWidth = getHalfWidth();
    if (halfWidth > 0) {
      if (pos > 0) {
        pos -= halfWidth;
        startPos -= halfWidth;
      } else if (Math.abs(pos) >= halfWidth) {
        pos += halfWidth;
        startPos += halfWidth;
      }
    }
    track.style.transform = `translate3d(${pos}px, 0, 0)`;
  }

  function handleDragEnd() {
    if (!isDragging) return;
    isDragging = false;
    track.classList.remove('is-dragging');
    lastTime = performance.now();
  }

  // Mouse drag
  track.addEventListener('mousedown', (e) => {
    if (e.button !== 0) return;
    handleDragStart(e.clientX);
  });

  window.addEventListener('mousemove', (e) => {
    if (isDragging) {
      e.preventDefault();
      handleDragMove(e.clientX);
    }
  });

  window.addEventListener('mouseup', handleDragEnd);

  // Touch drag
  track.addEventListener('touchstart', (e) => {
    if (e.touches.length === 1) {
      handleDragStart(e.touches[0].clientX);
    }
  }, { passive: true });

  track.addEventListener('touchmove', (e) => {
    if (isDragging && e.touches.length === 1) {
      handleDragMove(e.touches[0].clientX);
    }
  }, { passive: true });

  track.addEventListener('touchend', handleDragEnd);
  track.addEventListener('touchcancel', handleDragEnd);

  // Prevent link click if user dragged
  track.addEventListener('click', (e) => {
    if (Math.abs(dragDelta) > 10) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  // Pause when tab not active
  document.addEventListener('visibilitychange', () => {
    isHovered = document.hidden;
    lastTime = performance.now();
  });
}