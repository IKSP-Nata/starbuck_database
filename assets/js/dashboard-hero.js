(function () {
    'use strict';

    var products = Array.isArray(window.HERO_PRODUCTS) ? window.HERO_PRODUCTS : [];
    var heroEl = document.getElementById('dashboard-hero');
    if (!heroEl || products.length === 0) {
        return;
    }

    var ghostTextEl = document.getElementById('hero-ghost-text');
    var titleEl = document.getElementById('hero-title');
    var priceEl = document.getElementById('hero-price');
    var orderBtnEl = document.getElementById('hero-order-btn');
    var stageEl = document.getElementById('hero-stage');
    var imgEl = document.getElementById('hero-product-img');
    var fallbackEl = document.getElementById('hero-fallback-avatar');
    var thumbsWrapEl = document.getElementById('hero-thumbs');
    var counterEl = document.getElementById('hero-counter');
    var thumbButtons = thumbsWrapEl ? Array.prototype.slice.call(thumbsWrapEl.querySelectorAll('.hero-thumb')) : [];

    var currentIndex = 0;
    var activeTimeline = null;
    var fallbackOutTimer = null;
    var fallbackCleanTimer = null;

    function prefersReducedMotion() {
        return Boolean(
            window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches
        );
    }

    function splitTitleLines(name) {
        var trimmed = (name || '').trim();
        if (!trimmed) {
            return ['Menu Starbuck'];
        }
        var words = trimmed.split(/\s+/);
        if (words.length > 2) {
            var lastWord = words.pop();
            return [words.join(' '), lastWord];
        }
        return [trimmed];
    }

    function getInitials(product) {
        if (product && product.inisial) {
            return String(product.inisial);
        }
        var name = (product && product.nama ? String(product.nama) : 'PR').trim();
        return (name || 'PR').slice(0, 2).toUpperCase();
    }

    function formatPrice(product) {
        if (product && product.hargaFormat) {
            return String(product.hargaFormat);
        }
        var amount = Number(product && product.harga ? product.harga : 0) || 0;
        return 'Rp ' + amount.toLocaleString('id-ID');
    }

    function showFallbackAvatar(initials) {
        if (imgEl) {
            imgEl.classList.add('d-none');
        }
        if (fallbackEl) {
            fallbackEl.textContent = initials;
            fallbackEl.classList.remove('d-none');
        }
    }

    function updateProductImage(product) {
        var initials = getInitials(product);
        var prodId = String(product.id || '');
        var gambarUrl = product && product.gambarUrl ? String(product.gambarUrl).trim() : '';

        if (!imgEl || !gambarUrl) {
            if (imgEl) {
                imgEl.dataset.productId = prodId;
                imgEl.removeAttribute('src');
            }
            showFallbackAvatar(initials);
            return;
        }

        imgEl.dataset.productId = prodId;
        imgEl.alt = product.nama || 'Menu Starbuck';
        imgEl.onerror = function () {
            if (imgEl.dataset.productId === prodId) {
                showFallbackAvatar(initials);
            }
        };

        if (fallbackEl) {
            fallbackEl.textContent = initials;
            fallbackEl.classList.add('d-none');
        }
        imgEl.classList.remove('d-none');
        imgEl.src = gambarUrl;

        if (imgEl.complete && imgEl.naturalWidth === 0) {
            showFallbackAvatar(initials);
        }
    }

    function renderProduct(product, index) {
        var nama = product.nama || 'Menu Starbuck';

        if (ghostTextEl) {
            ghostTextEl.textContent = nama;
        }

        if (titleEl) {
            var lines = splitTitleLines(nama);
            titleEl.textContent = '';
            lines.forEach(function (lineText) {
                var lineSpan = document.createElement('span');
                lineSpan.className = 'hero-title-line';
                var innerSpan = document.createElement('span');
                innerSpan.className = 'hero-title-line-inner';
                innerSpan.textContent = lineText;
                lineSpan.appendChild(innerSpan);
                titleEl.appendChild(lineSpan);
            });
        }

        if (priceEl) {
            priceEl.textContent = formatPrice(product);
        }

        if (orderBtnEl) {
            var baseUrl = '/starbuck/pages/transactions/add.php';
            orderBtnEl.href = product.id ? baseUrl + '?id_produk=' + encodeURIComponent(String(product.id)) : baseUrl;
        }

        updateProductImage(product);

        if (counterEl) {
            counterEl.textContent = (index + 1) + ' / ' + products.length;
        }
    }

    function updateThumbnails(activeIndex) {
        var behavior = prefersReducedMotion() ? 'auto' : 'smooth';
        thumbButtons.forEach(function (btn, idx) {
            var isActive = idx === activeIndex;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            if (isActive && typeof btn.scrollIntoView === 'function') {
                btn.scrollIntoView({
                    inline: 'center',
                    block: 'nearest',
                    behavior: behavior
                });
            }
        });
    }

    function clearFallbackTimers() {
        if (fallbackOutTimer) {
            window.clearTimeout(fallbackOutTimer);
            fallbackOutTimer = null;
        }
        if (fallbackCleanTimer) {
            window.clearTimeout(fallbackCleanTimer);
            fallbackCleanTimer = null;
        }
    }

    // Fallback transisi CSS sederhana (fade 250ms) bila GSAP tidak tersedia / reduced motion
    function transitionWithCssFallback(product, targetIndex) {
        if (activeTimeline) {
            activeTimeline.kill();
            activeTimeline = null;
        }
        clearFallbackTimers();
        heroEl.classList.remove('is-animating');
        heroEl.classList.add('is-changing', 'is-fading-out');

        fallbackOutTimer = window.setTimeout(function () {
            renderProduct(product, targetIndex);
            // Force reflow agar browser menerapkan transisi kembali ke opacity 1
            void heroEl.offsetWidth;
            heroEl.classList.remove('is-fading-out');

            fallbackCleanTimer = window.setTimeout(function () {
                heroEl.classList.remove('is-changing');
                fallbackCleanTimer = null;
            }, 250);
        }, 130);
    }

    // Transisi GSAP (~0,9s: keluar 0,35s -> ganti konten -> masuk 0,55s)
    function transitionWithGsap(product, targetIndex) {
        clearFallbackTimers();
        heroEl.classList.remove('is-changing', 'is-fading-out');

        if (activeTimeline) {
            activeTimeline.kill();
            activeTimeline = null;
        }

        heroEl.classList.add('is-animating');

        var currentTitleInners = titleEl ? titleEl.querySelectorAll('.hero-title-line-inner') : [];
        var outMetaTargets = [priceEl, orderBtnEl].filter(Boolean);

        var tl = window.gsap.timeline({
            onComplete: function () {
                heroEl.classList.remove('is-animating');
                activeTimeline = null;
            }
        });
        activeTimeline = tl;

        // 1. Fase Keluar (0,35s)
        if (stageEl) {
            tl.to(stageEl, {
                opacity: 0,
                y: 24,
                scale: 0.96,
                duration: 0.35,
                ease: 'power2.inOut'
            }, 0);
        }

        if (ghostTextEl) {
            tl.to(ghostTextEl, {
                opacity: 0,
                x: -60,
                duration: 0.35,
                ease: 'power2.inOut'
            }, 0);
        }

        if (currentTitleInners.length > 0) {
            tl.to(currentTitleInners, {
                opacity: 0,
                y: -16,
                duration: 0.30,
                stagger: 0.05,
                ease: 'power2.inOut'
            }, 0);
        }

        if (outMetaTargets.length > 0) {
            tl.to(outMetaTargets, {
                opacity: 0,
                y: -10,
                duration: 0.28,
                stagger: 0.04,
                ease: 'power2.inOut'
            }, 0.03);
        }

        // 2. Ganti Konten & 3. Fase Masuk (0,55s)
        tl.add(function () {
            renderProduct(product, targetIndex);

            var newTitleInners = titleEl ? titleEl.querySelectorAll('.hero-title-line-inner') : [];
            var inMetaTargets = [priceEl, orderBtnEl].filter(Boolean);

            var inTl = window.gsap.timeline({
                onComplete: function () {
                    heroEl.classList.remove('is-animating');
                    activeTimeline = null;
                }
            });
            activeTimeline = inTl;

            if (stageEl) {
                inTl.fromTo(
                    stageEl,
                    { opacity: 0, y: 36, scale: 0.94 },
                    { opacity: 1, y: 0, scale: 1, duration: 0.55, ease: 'power3.out', clearProps: 'transform' },
                    0
                );
            }

            if (ghostTextEl) {
                inTl.fromTo(
                    ghostTextEl,
                    { opacity: 0, x: 80 },
                    { opacity: 1, x: 0, duration: 0.55, ease: 'power3.out', clearProps: 'transform' },
                    0
                );
            }

            if (newTitleInners.length > 0) {
                inTl.fromTo(
                    newTitleInners,
                    { opacity: 0, yPercent: 105, y: 0 },
                    { opacity: 1, yPercent: 0, y: 0, duration: 0.50, stagger: 0.05, ease: 'power3.out', clearProps: 'transform' },
                    0.02
                );
            }

            if (inMetaTargets.length > 0) {
                inTl.fromTo(
                    inMetaTargets,
                    { opacity: 0, y: 12 },
                    { opacity: 1, y: 0, duration: 0.45, stagger: 0.06, ease: 'power3.out', clearProps: 'transform' },
                    0.08
                );
            }
        });
    }

    function selectProduct(nextIndex) {
        if (nextIndex < 0 || nextIndex >= products.length) {
            return;
        }
        if (nextIndex === currentIndex) {
            return;
        }

        currentIndex = nextIndex;
        var product = products[currentIndex];
        updateThumbnails(currentIndex);

        if (window.gsap && typeof window.gsap.timeline === 'function' && !prefersReducedMotion()) {
            transitionWithGsap(product, currentIndex);
        } else {
            transitionWithCssFallback(product, currentIndex);
        }
    }

    function stepProduct(delta) {
        if (products.length <= 1) {
            return;
        }
        var nextIndex = (currentIndex + delta + products.length) % products.length;
        selectProduct(nextIndex);
    }

    // Klik thumbnail
    thumbButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var idx = parseInt(btn.getAttribute('data-index'), 10);
            if (!isNaN(idx)) {
                selectProduct(idx);
            }
        });
    });

    // Navigasi keyboard ArrowLeft / ArrowRight saat fokus di dalam hero atau strip
    heroEl.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            stepProduct(-1);
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            stepProduct(1);
        }
    });

    // Swipe sentuh pada area foto produk (ambang 50px)
    if (stageEl) {
        var touchStartX = 0;
        var touchStartY = 0;
        var isTrackingTouch = false;

        stageEl.addEventListener('touchstart', function (e) {
            if (e.touches && e.touches.length === 1) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
                isTrackingTouch = true;
            }
        }, { passive: true });

        stageEl.addEventListener('touchend', function (e) {
            if (!isTrackingTouch || !e.changedTouches || e.changedTouches.length === 0) {
                return;
            }
            isTrackingTouch = false;

            var deltaX = e.changedTouches[0].clientX - touchStartX;
            var deltaY = e.changedTouches[0].clientY - touchStartY;

            if (Math.abs(deltaX) >= 50 && Math.abs(deltaX) > Math.abs(deltaY)) {
                if (deltaX < 0) {
                    stepProduct(1);
                } else {
                    stepProduct(-1);
                }
            }
        }, { passive: true });
    }

    // Preload seluruh gambar produk saat idle agar pergantian tidak berkedip
    function preloadAllImages() {
        var load = function () {
            products.forEach(function (prod) {
                if (prod && prod.gambarUrl) {
                    var preloader = new Image();
                    preloader.src = prod.gambarUrl;
                }
            });
        };

        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(load, { timeout: 2000 });
        } else {
            window.setTimeout(load, 300);
        }
    }

    preloadAllImages();
})();
