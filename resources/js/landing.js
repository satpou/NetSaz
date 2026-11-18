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

  // Interactive Pricing Carousel & Slider
  initPricingCarousel();
});

function initPricingCarousel() {
  const container = document.querySelector('.pricing-carousel-container');
  const track = document.querySelector('.pricing-carousel-track');
  if (!container || !track) return;

  const cards = Array.from(track.querySelectorAll('.carousel-card'));
  const dots = Array.from(document.querySelectorAll('.carousel-dot'));
  const prevBtns = Array.from(document.querySelectorAll('.prev-btn, .float-prev'));
  const nextBtns = Array.from(document.querySelectorAll('.next-btn, .float-next'));

  if (cards.length === 0) return;

  let currentIndex = 0;
  let isDragging = false;
  let startX = 0;
  let scrollStart = 0;
  let totalDragDelta = 0;
  let autoTimer = null;
  const autoPlayDelay = 4000;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function getTargetScroll(index) {
    if (!cards[index]) return 0;
    const card = cards[index];
    const trackWidth = track.clientWidth;
    const cardWidth = card.offsetWidth;
    // Center card within track viewport
    return card.offsetLeft - (trackWidth - cardWidth) / 2;
  }

  function scrollToIndex(index, smooth = true) {
    if (cards.length === 0) return;
    if (index < 0) index = cards.length - 1;
    if (index >= cards.length) index = 0;

    currentIndex = index;
    const target = getTargetScroll(currentIndex);
    const behavior = (!prefersReducedMotion && smooth) ? 'smooth' : 'auto';

    track.scrollTo({
      left: target,
      behavior: behavior
    });

    updateActiveDot(currentIndex);
  }

  function updateActiveDot(index) {
    dots.forEach((dot, idx) => {
      const isActive = idx === index;
      dot.classList.toggle('active', isActive);
      dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });
  }

  // Scroll sync to update dots and current index on manual scroll / swipe
  let isTicking = false;
  track.addEventListener('scroll', () => {
    if (isDragging) return;
    if (!isTicking) {
      window.requestAnimationFrame(() => {
        const trackCenter = track.scrollLeft + track.clientWidth / 2;
        let closestIndex = 0;
        let minDistance = Infinity;

        cards.forEach((card, idx) => {
          const cardCenter = card.offsetLeft + card.offsetWidth / 2;
          const dist = Math.abs(trackCenter - cardCenter);
          if (dist < minDistance) {
            minDistance = dist;
            closestIndex = idx;
          }
        });

        currentIndex = closestIndex;
        updateActiveDot(currentIndex);
        isTicking = false;
      });
      isTicking = true;
    }
  }, { passive: true });

  // Navigation Arrows
  prevBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      scrollToIndex(currentIndex - 1);
      resetAutoPlay();
    });
  });

  nextBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      scrollToIndex(currentIndex + 1);
      resetAutoPlay();
    });
  });

  // Dots
  dots.forEach((dot, idx) => {
    dot.addEventListener('click', (e) => {
      e.preventDefault();
      scrollToIndex(idx);
      resetAutoPlay();
    });
  });

  // Mouse Drag To Scroll
  track.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.pageX - track.offsetLeft;
    scrollStart = track.scrollLeft;
    totalDragDelta = 0;
    track.classList.add('is-dragging');
    pauseAutoPlay();
  });

  window.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    e.preventDefault();
    const x = e.pageX - track.offsetLeft;
    const walk = (x - startX) * 1.5;
    track.scrollLeft = scrollStart - walk;
    totalDragDelta += Math.abs(walk);
  });

  window.addEventListener('mouseup', () => {
    if (!isDragging) return;
    isDragging = false;
    track.classList.remove('is-dragging');
    resumeAutoPlay();
  });

  // Prevent link click if user was dragging
  track.addEventListener('click', (e) => {
    if (totalDragDelta > 10) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  // Auto-play timer
  function startAutoPlay() {
    if (autoTimer) clearInterval(autoTimer);
    autoTimer = setInterval(() => {
      scrollToIndex(currentIndex + 1);
    }, autoPlayDelay);
  }

  function pauseAutoPlay() {
    if (autoTimer) {
      clearInterval(autoTimer);
      autoTimer = null;
    }
  }

  function resumeAutoPlay() {
    pauseAutoPlay();
    startAutoPlay();
  }

  function resetAutoPlay() {
    pauseAutoPlay();
    startAutoPlay();
  }

  container.addEventListener('mouseenter', pauseAutoPlay);
  container.addEventListener('mouseleave', resumeAutoPlay);
  container.addEventListener('touchstart', pauseAutoPlay, { passive: true });
  container.addEventListener('touchend', resumeAutoPlay, { passive: true });

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      pauseAutoPlay();
    } else {
      resumeAutoPlay();
    }
  });

  // Initial scroll position
  updateActiveDot(0);
  startAutoPlay();
}