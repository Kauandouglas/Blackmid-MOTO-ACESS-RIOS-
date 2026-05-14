const buyButtons = document.querySelectorAll('.js-buy');
const cartCount = document.querySelector('.cart-count');
const toTop = document.querySelector('.to-top');
const menuToggle = document.querySelector('.menu-toggle');
const mainNav = document.querySelector('.main-nav');
const menuClose = document.querySelector('.menu-close');
const navList = document.querySelector('.main-nav ul');
const navLinks = document.querySelectorAll('.main-nav a');
const header = document.querySelector('.header');
const megaMenu = document.querySelector('#mega-menu');
const megaCategoriesList = document.querySelector('#mega-categories-list');
const megaPreviewTrack = document.querySelector('#mega-preview-track');
const megaDots = document.querySelector('#mega-dots');
const megaPrev = document.querySelector('#mega-prev');
const megaNext = document.querySelector('#mega-next');
const categoriesSlider = document.querySelector('[data-slider="categories"]');
const topStripTrack = document.querySelector('#top-strip-track');
const benefitsGrid = document.querySelector('.benefits-grid');
const searchForm = document.querySelector('.search[data-search-suggest-url]');
const searchInput = searchForm?.querySelector('input[name="q"]');
const searchSuggestions = searchForm?.querySelector('[data-search-suggestions]');

const megaData = {};

let total = 0;
let megaCurrentProducts = [];
let megaCurrentSlide = 0;
let megaAutoTimer;

const buildMobileCategorySubmenus = () => {
  if (!mainNav) return;

  const categoryLinks = mainNav.querySelectorAll('.main-nav ul > li > a[data-menu-key]');

  categoryLinks.forEach((link) => {
    const key = link.dataset.menuKey;
    const groups = megaData[key] || [];
    if (!groups.length) return;

    const parent = link.parentElement;
    if (!parent || parent.querySelector('.mobile-submenu')) return;

    const submenu = document.createElement('ul');
    submenu.className = 'mobile-submenu';
    submenu.innerHTML = groups
      .map((group) => `<li><a href="#">${group.name}</a></li>`)
      .join('');

    parent.classList.add('has-children');
    parent.appendChild(submenu);
  });
};

buildMobileCategorySubmenus();

if (searchForm && searchInput && searchSuggestions) {
  let searchTimer;
  let searchController;

  const hideSuggestions = () => {
    searchSuggestions.hidden = true;
    searchSuggestions.innerHTML = '';
  };

  const renderSuggestions = (products) => {
    if (!products.length) {
      searchSuggestions.innerHTML = '<div class="search-suggestions__empty">Nenhum produto encontrado.</div>';
      searchSuggestions.hidden = false;
      return;
    }

    const escapeHtml = (value) => String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    searchSuggestions.innerHTML = products.map(product => `
      <a class="search-suggestions__item" href="${product.url}">
        <img class="search-suggestions__image" src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">
        <span class="search-suggestions__content">
          <strong>${escapeHtml(product.name)}</strong>
          <span>${escapeHtml(product.category)}</span>
        </span>
        <span class="search-suggestions__price">${escapeHtml(product.price)}</span>
      </a>
    `).join('');
    searchSuggestions.hidden = false;
  };

  const fetchSuggestions = () => {
    const query = searchInput.value.trim();
    if (query.length < 2) {
      hideSuggestions();
      return;
    }

    if (searchController) searchController.abort();
    searchController = new AbortController();

    const url = `${searchForm.dataset.searchSuggestUrl}?q=${encodeURIComponent(query)}`;

    fetch(url, {
      headers: { 'Accept': 'application/json' },
      signal: searchController.signal
    })
      .then(response => response.json())
      .then(data => renderSuggestions(data.products || []))
      .catch(error => {
        if (error.name !== 'AbortError') hideSuggestions();
      });
  };

  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(fetchSuggestions, 180);
  });

  searchInput.addEventListener('focus', () => {
    if (searchInput.value.trim().length >= 2) fetchSuggestions();
  });

  document.addEventListener('click', (event) => {
    if (!searchForm.contains(event.target)) hideSuggestions();
  });

  searchForm.addEventListener('submit', hideSuggestions);
}

if (topStripTrack) {
  const topStripItems = Array.from(topStripTrack.querySelectorAll('.top-strip-item'));
  let topStripIndex = 0;

  const setTopStripSlide = (index) => {
    topStripIndex = (index + topStripItems.length) % topStripItems.length;
    topStripTrack.style.transform = `translate3d(-${topStripIndex * 100}%, 0, 0)`;
  };

  setInterval(() => {
    setTopStripSlide(topStripIndex + 1);
  }, 2300);
}

if (benefitsGrid) {
  const benefitItems = Array.from(benefitsGrid.querySelectorAll('article'));
  let benefitsIndex = 0;
  let benefitsTimer;

  const setBenefitsSlide = (index, smooth = true) => {
    if (window.innerWidth > 900 || !benefitItems.length) {
      benefitsGrid.style.transition = '';
      benefitsGrid.style.transform = '';
      benefitsIndex = 0;
      return;
    }

    benefitsIndex = (index + benefitItems.length) % benefitItems.length;
    benefitsGrid.style.transition = smooth ? 'transform 0.42s ease' : 'none';
    benefitsGrid.style.transform = `translate3d(-${benefitsIndex * 100}%, 0, 0)`;
  };

  const startBenefitsAuto = () => {
    clearInterval(benefitsTimer);
    if (window.innerWidth > 900 || benefitItems.length <= 1) return;

    benefitsTimer = setInterval(() => {
      setBenefitsSlide(benefitsIndex + 1);
    }, 2400);
  };

  window.addEventListener('resize', () => {
    setBenefitsSlide(0, false);
    startBenefitsAuto();
  });

  setBenefitsSlide(0, false);
  startBenefitsAuto();
}

buyButtons.forEach((button) => {
  button.addEventListener('click', () => {
    total += 1;
    if (cartCount) cartCount.textContent = total;

    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fa-solid fa-check"></i> ADICIONADO';
    button.style.filter = 'brightness(1.15)';

    setTimeout(() => {
      button.innerHTML = originalText;
      button.style.filter = '';
    }, 900);
  });
});

window.addEventListener('scroll', () => {
  if (!toTop) return;
  toTop.classList.toggle('show', window.scrollY > 400);
});

if (toTop) {
  toTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

if (menuToggle && mainNav) {
  const openMenu = () => {
    mainNav.classList.add('is-open');
    document.body.classList.add('menu-open');
  };

  const closeMenu = () => {
    mainNav.classList.remove('is-open');
    document.body.classList.remove('menu-open');
  };

  menuToggle.addEventListener('click', () => {
    if (mainNav.classList.contains('is-open')) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  if (menuClose) {
    menuClose.addEventListener('click', closeMenu);
  }

  navLinks.forEach((link) => {
    link.addEventListener('click', (event) => {
      if (window.innerWidth <= 1050) {
        const isCategoryLink = link.matches('.main-nav ul > li > a[data-menu-key]');
        if (isCategoryLink) {
          const parent = link.parentElement;
          const submenu = parent?.querySelector('.mobile-submenu');

          if (parent && submenu) {
            event.preventDefault();

            const siblings = parent.parentElement ? Array.from(parent.parentElement.children) : [];
            siblings.forEach((item) => {
              if (item !== parent) item.classList.remove('is-expanded');
            });

            parent.classList.toggle('is-expanded');
            return;
          }
        }

        closeMenu();
      }
    });
  });

  mainNav.addEventListener('click', (event) => {
    if (window.innerWidth > 1050) return;
    const submenuLink = event.target.closest('.mobile-submenu a');
    if (submenuLink) {
      closeMenu();
    }
  });

  document.addEventListener('click', (event) => {
    if (window.innerWidth > 1050) return;
    const clickedInsideMenu = mainNav.contains(event.target);
    const clickedToggle = menuToggle.contains(event.target);
    if (!clickedInsideMenu && !clickedToggle) {
      closeMenu();
    }
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 1050) {
      closeMenu();
    }
  });
}

if (navList && navLinks.length) {
  const setIndicator = (link) => {
    if (!link || window.innerWidth <= 1050) return;
    const linkRect = link.getBoundingClientRect();
    const listRect = navList.getBoundingClientRect();
    navList.style.setProperty('--indicator-left', `${linkRect.left - listRect.left}px`);
    navList.style.setProperty('--indicator-width', `${linkRect.width}px`);
    navList.style.setProperty('--indicator-opacity', '1');
  };

  const resetToActive = () => {
    const activeLink = navList.querySelector('a.active') || navLinks[0];
    setIndicator(activeLink);
  };

  navLinks.forEach((link) => {
    link.addEventListener('mouseenter', () => setIndicator(link));
  });

  navList.addEventListener('mouseleave', resetToActive);
  window.addEventListener('resize', resetToActive);

  resetToActive();
}

if (categoriesSlider) {
  const categoriesTrack = categoriesSlider.querySelector('.categories-track');
  const categoriesItems = categoriesTrack ? Array.from(categoriesTrack.querySelectorAll('.cat-item')) : [];
  const categoriesPrev = categoriesSlider.querySelector('.categories-prev');
  const categoriesNext = categoriesSlider.querySelector('.categories-next');
  let categoriesCurrent = 0;
  let categoriesAutoTimer;

  const getVisibleCount = () => {
    if (window.innerWidth <= 680) return 3;
    if (window.innerWidth <= 1050) return 4;
    return 4;
  };

  const getStep = () => {
    const item = categoriesItems[0];
    if (!item) return 0;
    const styles = window.getComputedStyle(categoriesTrack);
    const gap = parseFloat(styles.columnGap || styles.gap || 0);
    return item.getBoundingClientRect().width + gap;
  };

  const getMaxIndex = () => Math.max(0, categoriesItems.length - getVisibleCount());

  const updateCategoriesNav = () => {
    const max = getMaxIndex();
    if (categoriesPrev) categoriesPrev.disabled = categoriesCurrent === 0;
    if (categoriesNext) categoriesNext.disabled = categoriesCurrent === max;
  };

  const goToCategory = (index, smooth = true) => {
    categoriesCurrent = Math.max(0, Math.min(index, getMaxIndex()));
    categoriesTrack.style.transition = smooth ? 'transform 0.5s ease' : 'none';
    categoriesTrack.style.transform = `translate3d(-${categoriesCurrent * getStep()}px, 0, 0)`;
    updateCategoriesNav();
  };

  const startCategoriesAuto = () => {
    clearInterval(categoriesAutoTimer);
    categoriesAutoTimer = setInterval(() => {
      const max = getMaxIndex();
      goToCategory(categoriesCurrent >= max ? 0 : categoriesCurrent + 1);
    }, 2600);
  };

  const restartCategoriesAuto = () => {
    startCategoriesAuto();
  };

  categoriesPrev?.addEventListener('click', () => {
    goToCategory(categoriesCurrent - 1);
    restartCategoriesAuto();
  });

  categoriesNext?.addEventListener('click', () => {
    goToCategory(categoriesCurrent + 1);
    restartCategoriesAuto();
  });

  categoriesSlider.addEventListener('mouseenter', () => clearInterval(categoriesAutoTimer));
  categoriesSlider.addEventListener('mouseleave', startCategoriesAuto);

  window.addEventListener('resize', () => {
    goToCategory(categoriesCurrent, false);
  });

  goToCategory(0, false);
  startCategoriesAuto();
}

if (megaMenu && navList && header && megaCategoriesList && megaPreviewTrack && megaDots && megaPrev && megaNext) {
  const navKeys = navList.querySelectorAll('a[data-menu-key]');

  const setMegaSlide = (index) => {
    if (!megaCurrentProducts.length) return;
    megaCurrentSlide = (index + megaCurrentProducts.length) % megaCurrentProducts.length;
    const cards = megaPreviewTrack.querySelectorAll('.mega-product');
    const dots = megaDots.querySelectorAll('button');
    cards.forEach((card, i) => card.classList.toggle('is-active', i === megaCurrentSlide));
    dots.forEach((dot, i) => dot.classList.toggle('is-active', i === megaCurrentSlide));
  };

  const renderMegaProducts = (products) => {
    megaCurrentProducts = products;
    megaCurrentSlide = 0;
    megaPreviewTrack.innerHTML = products
      .map((product, index) => `
        <article class="mega-product ${index === 0 ? 'is-active' : ''}">
          <img src="${product.image}" alt="${product.title}">
          <div>
            <span class="tag">${product.tag}</span>
            <h5>${product.title}</h5>
            <p>${product.desc}</p>
            <strong>${product.price}</strong>
          </div>
        </article>
      `)
      .join('');

    megaDots.innerHTML = products
      .map((_, index) => `<button type="button" class="${index === 0 ? 'is-active' : ''}" aria-label="Produto ${index + 1}"></button>`)
      .join('');

    megaDots.querySelectorAll('button').forEach((dot, index) => {
      dot.addEventListener('click', () => {
        setMegaSlide(index);
        restartMegaAuto();
      });
    });
  };

  const renderMegaCategories = (key) => {
    const categories = megaData[key] || [];
    if (!categories.length) return;

    megaCategoriesList.innerHTML = categories
      .map((category, index) => `
        <li>
          <button type="button" class="${index === 0 ? 'is-active' : ''}" data-cat-index="${index}">
            <span>${category.name}</span>
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </li>
      `)
      .join('');

    renderMegaProducts(categories[0].products);

    megaCategoriesList.querySelectorAll('button').forEach((button) => {
      button.addEventListener('mouseenter', () => {
        megaCategoriesList.querySelectorAll('button').forEach((item) => item.classList.remove('is-active'));
        button.classList.add('is-active');
        const categoryIndex = Number(button.dataset.catIndex);
        renderMegaProducts(categories[categoryIndex].products);
        restartMegaAuto();
      });
    });
  };

  const startMegaAuto = () => {
    clearInterval(megaAutoTimer);
    if (megaCurrentProducts.length <= 1) return;
    megaAutoTimer = setInterval(() => {
      setMegaSlide(megaCurrentSlide + 1);
    }, 2800);
  };

  const restartMegaAuto = () => {
    startMegaAuto();
  };

  const openMega = (key) => {
    if (window.innerWidth <= 1050 || !megaData[key]) return;
    renderMegaCategories(key);
    megaMenu.classList.add('is-open');
    startMegaAuto();
  };

  const closeMega = () => {
    megaMenu.classList.remove('is-open');
    clearInterval(megaAutoTimer);
  };

  navKeys.forEach((item) => {
    item.addEventListener('mouseenter', () => {
      openMega(item.dataset.menuKey);
    });
  });

  header.addEventListener('mouseleave', closeMega);
  megaMenu.addEventListener('mouseenter', () => {
    if (!megaMenu.classList.contains('is-open')) return;
    clearInterval(megaAutoTimer);
  });
  megaMenu.addEventListener('mouseleave', startMegaAuto);

  megaPrev.addEventListener('click', () => {
    setMegaSlide(megaCurrentSlide - 1);
    restartMegaAuto();
  });

  megaNext.addEventListener('click', () => {
    setMegaSlide(megaCurrentSlide + 1);
    restartMegaAuto();
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth <= 1050) closeMega();
  });
}
