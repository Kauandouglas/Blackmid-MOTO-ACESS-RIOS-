// MotoAcessórios Brasil — script.js

let cartCount = 0;

function addToCart(btn) {
  cartCount++;
  document.querySelector('.cart-badge').textContent = cartCount;

  // Animação no botão
  const original = btn.innerHTML;
  btn.innerHTML = '<i class="fa-solid fa-check"></i> ADICIONADO!';
  btn.style.background = '#007a2e';
  setTimeout(() => {
    btn.innerHTML = original;
    btn.style.background = '';
  }, 1500);

  // Toast
  showToast('Produto adicionado ao carrinho!');
}

function showToast(msg) {
  const toast = document.getElementById('toast');
  toast.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + msg;
  toast.classList.add('show');
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.remove('show'), 3000);
}

// Hero slider automático
const heroTrack = document.querySelector('.hero-slider__track');
const heroSlides = heroTrack ? Array.from(heroTrack.querySelectorAll('.hero-slide')) : [];
const heroDots = Array.from(document.querySelectorAll('.hero-dot'));

if (heroSlides.length > 0) {
  let currentHero = 0;
  let heroTimer;

  const setHeroSlide = (index) => {
    currentHero = (index + heroSlides.length) % heroSlides.length;
    heroTrack.style.transform = `translateX(-${currentHero * 100}%)`;
    heroDots.forEach((dot, i) => dot.classList.toggle('is-active', i === currentHero));
  };

  const startHeroAuto = () => {
    heroTimer = setInterval(() => {
      setHeroSlide(currentHero + 1);
    }, 4500);
  };

  const restartHeroAuto = () => {
    clearInterval(heroTimer);
    startHeroAuto();
  };

  heroDots.forEach((dot, i) => {
    dot.addEventListener('click', () => {
      setHeroSlide(i);
      restartHeroAuto();
    });
  });

  const heroSection = document.querySelector('.hero');
  if (heroSection) {
    heroSection.addEventListener('mouseenter', () => clearInterval(heroTimer));
    heroSection.addEventListener('mouseleave', restartHeroAuto);
  }

  setHeroSlide(0);
  startHeroAuto();
}

// Slider de categorias
const categoriesSlider = document.querySelector('[data-slider="categories"]');

if (categoriesSlider) {
  const viewport = categoriesSlider.querySelector('.categories-viewport');
  const track = categoriesSlider.querySelector('.categories-track');
  const items = Array.from(track.querySelectorAll('.cat-item'));
  const prevBtn = categoriesSlider.querySelector('.categories-nav--prev');
  const nextBtn = categoriesSlider.querySelector('.categories-nav--next');
  const dotsWrap = document.querySelector('.categories-dots');
  let currentPage = 0;
  let autoTimer;
  let isDragging = false;
  let startX = 0;
  let startTranslate = 0;
  let currentTranslate = 0;

  const getVisibleCount = () => {
    if (window.innerWidth <= 480) return 2;
    if (window.innerWidth <= 768) return 3;
    if (window.innerWidth <= 1100) return 4;
    return 6;
  };

  const getStep = () => {
    const item = items[0];
    if (!item) return 0;
    const styles = window.getComputedStyle(track);
    const gap = parseFloat(styles.columnGap || styles.gap || 0);
    return item.getBoundingClientRect().width + gap;
  };

  const getMaxPage = () => Math.max(0, items.length - getVisibleCount());

  const buildDots = () => {
    if (!dotsWrap) return;
    dotsWrap.innerHTML = '';
    for (let i = 0; i <= getMaxPage(); i++) {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = i === currentPage ? 'is-active' : '';
      dot.setAttribute('aria-label', `Ir para categorias ${i + 1}`);
      dot.addEventListener('click', () => {
        goToPage(i);
        restartAuto();
      });
      dotsWrap.appendChild(dot);
    }
  };

  const updateDots = () => {
    if (!dotsWrap) return;
    dotsWrap.querySelectorAll('button').forEach((dot, index) => {
      dot.classList.toggle('is-active', index === currentPage);
    });
  };

  const updateNav = () => {
    const maxPage = getMaxPage();
    if (prevBtn) prevBtn.disabled = currentPage === 0;
    if (nextBtn) nextBtn.disabled = currentPage === maxPage;
  };

  const goToPage = (page, smooth = true) => {
    const maxPage = getMaxPage();
    currentPage = Math.max(0, Math.min(page, maxPage));
    currentTranslate = -(getStep() * currentPage);
    track.style.transition = smooth ? 'transform .55s ease' : 'none';
    track.style.transform = `translate3d(${currentTranslate}px, 0, 0)`;
    updateNav();
    updateDots();
  };

  const startAuto = () => {
    autoTimer = setInterval(() => {
      const maxPage = getMaxPage();
      goToPage(currentPage >= maxPage ? 0 : currentPage + 1);
    }, 2800);
  };

  const restartAuto = () => {
    clearInterval(autoTimer);
    startAuto();
  };

  prevBtn?.addEventListener('click', () => {
    goToPage(currentPage - 1);
    restartAuto();
  });

  nextBtn?.addEventListener('click', () => {
    goToPage(currentPage + 1);
    restartAuto();
  });

  const pointerDown = (clientX) => {
    isDragging = true;
    startX = clientX;
    startTranslate = currentTranslate;
    track.classList.add('is-dragging');
    clearInterval(autoTimer);
  };

  const pointerMove = (clientX) => {
    if (!isDragging) return;
    const delta = clientX - startX;
    currentTranslate = startTranslate + delta;
    track.style.transform = `translate3d(${currentTranslate}px, 0, 0)`;
  };

  const pointerUp = () => {
    if (!isDragging) return;
    isDragging = false;
    track.classList.remove('is-dragging');
    const step = getStep() || 1;
    const snappedPage = Math.round(Math.abs(currentTranslate) / step);
    goToPage(snappedPage);
    restartAuto();
  };

  track.addEventListener('mousedown', (event) => pointerDown(event.clientX));
  window.addEventListener('mousemove', (event) => pointerMove(event.clientX));
  window.addEventListener('mouseup', pointerUp);
  track.addEventListener('touchstart', (event) => pointerDown(event.touches[0].clientX), { passive: true });
  window.addEventListener('touchmove', (event) => pointerMove(event.touches[0].clientX), { passive: true });
  window.addEventListener('touchend', pointerUp);

  categoriesSlider.addEventListener('mouseenter', () => clearInterval(autoTimer));
  categoriesSlider.addEventListener('mouseleave', restartAuto);
  window.addEventListener('resize', () => {
    buildDots();
    goToPage(currentPage, false);
  });

  buildDots();
  goToPage(0, false);
  startAuto();
}

// Slider de marcas
const brandsSlider = document.querySelector('[data-slider="brands"]');

if (brandsSlider) {
  const track = brandsSlider.querySelector('.brands-track');
  const items = Array.from(track.querySelectorAll('.brand-item'));
  const dotsWrap = document.querySelector('.brands-dots');
  let currentPage = 0;
  let autoTimer;
  let isDragging = false;
  let startX = 0;
  let startTranslate = 0;
  let currentTranslate = 0;

  const getVisibleCount = () => {
    if (window.innerWidth <= 480) return 2;
    if (window.innerWidth <= 900) return 3;
    return 4;
  };

  const getStep = () => {
    const item = items[0];
    if (!item) return 0;
    const styles = window.getComputedStyle(track);
    const gap = parseFloat(styles.columnGap || styles.gap || 0);
    return item.getBoundingClientRect().width + gap;
  };

  const getMaxPage = () => Math.max(0, items.length - getVisibleCount());

  const buildDots = () => {
    if (!dotsWrap) return;
    dotsWrap.innerHTML = '';
    for (let i = 0; i <= getMaxPage(); i++) {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = i === currentPage ? 'is-active' : '';
      dot.setAttribute('aria-label', `Ir para marcas ${i + 1}`);
      dot.addEventListener('click', () => {
        goToPage(i);
        restartAuto();
      });
      dotsWrap.appendChild(dot);
    }
  };

  const updateDots = () => {
    dotsWrap?.querySelectorAll('button').forEach((dot, index) => {
      dot.classList.toggle('is-active', index === currentPage);
    });
  };

  const goToPage = (page, smooth = true) => {
    const maxPage = getMaxPage();
    currentPage = Math.max(0, Math.min(page, maxPage));
    currentTranslate = -(getStep() * currentPage);
    track.style.transition = smooth ? 'transform .55s ease' : 'none';
    track.style.transform = `translate3d(${currentTranslate}px, 0, 0)`;
    updateDots();
  };

  const startAuto = () => {
    autoTimer = setInterval(() => {
      const maxPage = getMaxPage();
      goToPage(currentPage >= maxPage ? 0 : currentPage + 1);
    }, 2400);
  };

  const restartAuto = () => {
    clearInterval(autoTimer);
    startAuto();
  };

  const pointerDown = (clientX) => {
    isDragging = true;
    startX = clientX;
    startTranslate = currentTranslate;
    track.classList.add('is-dragging');
    clearInterval(autoTimer);
  };

  const pointerMove = (clientX) => {
    if (!isDragging) return;
    const delta = clientX - startX;
    currentTranslate = startTranslate + delta;
    track.style.transform = `translate3d(${currentTranslate}px, 0, 0)`;
  };

  const pointerUp = () => {
    if (!isDragging) return;
    isDragging = false;
    track.classList.remove('is-dragging');
    const step = getStep() || 1;
    const snappedPage = Math.round(Math.abs(currentTranslate) / step);
    goToPage(snappedPage);
    restartAuto();
  };

  track.addEventListener('mousedown', (event) => pointerDown(event.clientX));
  window.addEventListener('mousemove', (event) => pointerMove(event.clientX));
  window.addEventListener('mouseup', pointerUp);
  track.addEventListener('touchstart', (event) => pointerDown(event.touches[0].clientX), { passive: true });
  window.addEventListener('touchmove', (event) => pointerMove(event.touches[0].clientX), { passive: true });
  window.addEventListener('touchend', pointerUp);

  brandsSlider.addEventListener('mouseenter', () => clearInterval(autoTimer));
  brandsSlider.addEventListener('mouseleave', restartAuto);
  window.addEventListener('resize', () => {
    buildDots();
    goToPage(currentPage, false);
  });

  buildDots();
  goToPage(0, false);
  startAuto();
}

// Mega menu com mini carrossel
document.querySelectorAll('.has-mega-menu').forEach((menuItem) => {
  const slider = menuItem.querySelector('[data-mega-slider]');
  if (!slider) return;

  const slides = Array.from(slider.querySelectorAll('.mega-product'));
  const dots = Array.from(slider.querySelectorAll('.mega-menu__dots button'));
  let currentSlide = 0;
  let timer;

  const setSlide = (index) => {
    currentSlide = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => slide.classList.toggle('is-active', i === currentSlide));
    dots.forEach((dot, i) => dot.classList.toggle('is-active', i === currentSlide));
  };

  const startAuto = () => {
    timer = setInterval(() => setSlide(currentSlide + 1), 2400);
  };

  const stopAuto = () => clearInterval(timer);

  dots.forEach((dot, index) => {
    dot.addEventListener('click', (event) => {
      event.preventDefault();
      setSlide(index);
      stopAuto();
      startAuto();
    });
  });

  menuItem.addEventListener('mouseenter', () => {
    menuItem.classList.add('is-open');
    stopAuto();
    startAuto();
  });

  menuItem.addEventListener('mouseleave', () => {
    menuItem.classList.remove('is-open');
    stopAuto();
    setSlide(0);
  });

  setSlide(0);
});

// Scroll reveal
const revealObserver = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });

document.querySelectorAll(
  '.prod-card, .testi-card, .cat-item, .trust-item, .brand-item, .banner-card, .insta-item'
).forEach(el => {
  el.classList.add('reveal');
  revealObserver.observe(el);
});

console.log('MotoAcessórios Brasil carregado!');
