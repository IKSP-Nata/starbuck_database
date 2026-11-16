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

    var DEFAULT_GLOW = { r: 78, g: 52, b: 46 };
    var currentGlow = { r: DEFAULT_GLOW.r, g: DEFAULT_GLOW.g, b: DEFAULT_GLOW.b };
    var glowCache = Object.create(null);
    var glowTween = null;
    var glowRafId = null;

    var sampleCanvas = document.createElement('canvas');
    sampleCanvas.width = 24;
    sampleCanvas.height = 24;
    var sampleCtx = null;
    try {
        sampleCtx = sampleCanvas.getContext('2d', { willReadFrequently: true });
    } catch (e) {
        sampleCtx = null;
    }

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

    function setGlowCssVar(rgb) {
        var r = Math.max(0, Math.min(255, Math.round(rgb.r)));
        var g = Math.max(0, Math.min(255, Math.round(rgb.g)));
        var b = Math.max(0, Math.min(255, Math.round(rgb.b)));
        heroEl.style.setProperty('--hero-glow', r + ', ' + g + ', ' + b);
    }

    function animateGlowTo(targetRgb) {
        var target = targetRgb || DEFAULT_GLOW;

        if (glowTween) {
            glowTween.kill();
            glowTween = null;
        }
        if (glowRafId) {
            window.cancelAnimationFrame(glowRafId);
            glowRafId = null;
        }

        if (prefersReducedMotion()) {
            currentGlow.r = target.r;
            currentGlow.g = target.g;
            currentGlow.b = target.b;
            setGlowCssVar(currentGlow);
            return;
        }

        if (window.gsap && typeof window.gsap.to === 'function') {
            glowTween = window.gsap.to(currentGlow, {
                r: target.r,
                g: target.g,
                b: target.b,
                duration: 0.6,
                ease: 'power2.out',
                onUpdate: function () {
                    setGlowCssVar(currentGlow);
                },
                onComplete: function () {
                    glowTween = null;
                }
            });
            return;
        }

        // Fallback animasi RGB 600ms tanpa GSAP
        var startR = currentGlow.r;
        var startG = currentGlow.g;
        var startB = currentGlow.b;
        var startTime = window.performance && window.performance.now ? window.performance.now() : Date.now();
        var duration = 600;

        function step(now) {
            var currentNow = typeof now === 'number' ? now : Date.now();
            var elapsed = currentNow - startTime;
            var t = Math.min(1, elapsed / duration);
            var eased = 1 - Math.pow(1 - t, 3);

            currentGlow.r = startR + (target.r - startR) * eased;
            currentGlow.g = startG + (target.g - startG) * eased;
            currentGlow.b = startB + (target.b - startB) * eased;
            setGlowCssVar(currentGlow);

            if (t < 1) {
                glowRafId = window.requestAnimationFrame(step);
            } else {
                glowRafId = null;
            }
        }

        glowRafId = window.requestAnimationFrame(step);
    }

    function extractDominantGlow(imageElement) {
        if (!sampleCtx || !imageElement || !imageElement.naturalWidth) {
            return DEFAULT_GLOW;
        }

        try {
            sampleCtx.clearRect(0, 0, 24, 24);
            sampleCtx.drawImage(imageElement, 0, 0, 24, 24);
            var pixels = sampleCtx.getImageData(0, 0, 24, 24).data;

            var totalR = 0;
            var totalG = 0;
            var totalB = 0;
            var count = 0;

            for (var i = 0; i < pixels.length; i += 4) {
                var r = pixels[i];
                var g = pixels[i + 1];
                var b = pixels[i + 2];
                var a = pixels[i + 3];

                if (a < 128) {
                    continue;
                }

                var luma = 0.299 * r + 0.587 * g + 0.114 * b;
                // Abaikan piksel terlalu gelap atau terlalu terang (latar putih/hitam)
                if (luma < 26 || luma > 224) {
                    continue;
                }

                totalR += r;
                totalG += g;
                totalB += b;
                count++;
            }

            if (count === 0) {
                return DEFAULT_GLOW;
            }

            var avgR = totalR / count;
            var avgG = totalG / count;
            var avgB = totalB / count;

            // Turunkan kecerahan agar tetap gelap dan selaras dengan kartu hero
            var avgLuma = 0.299 * avgR + 0.587 * avgG + 0.114 * avgB;
            if (avgLuma > 118) {
                var dimScale = 118 / avgLuma;
                avgR *= dimScale;
                avgG *= dimScale;
                avgB *= dimScale;
            } else if (avgLuma < 52) {
                var liftScale = 52 / Math.max(avgLuma, 1);
                avgR *= liftScale;
                avgG *= liftScale;
                avgB *= liftScale;
            }

            // Campurkan sedikit (25%) nada cokelat hangat agar tetap harmonis di latar #1A1210
            var warmR = Math.round(avgR * 0.75 + DEFAULT_GLOW.r * 0.25);
            var warmG = Math.round(avgG * 0.75 + DEFAULT_GLOW.g * 0.25);
            var warmB = Math.round(avgB * 0.75 + DEFAULT_GLOW.b * 0.25);

            return {
                r: Math.max(24, Math.min(155, warmR)),
                g: Math.max(18, Math.min(135, warmG)),
                b: Math.max(16, Math.min(125, warmB))
            };
        } catch (err) {
            return DEFAULT_GLOW;
        }
    }

    function applyGlowForProduct(product, imageElement) {
        var cacheKey = product && product.id ? 'id_' + String(product.id) : (product && product.gambarUrl ? product.gambarUrl : '');
        if (cacheKey && glowCache[cacheKey]) {
            animateGlowTo(glowCache[cacheKey]);
            return;
        }

        var glowRgb = extractDominantGlow(imageElement);
        if (cacheKey) {
            glowCache[cacheKey] = glowRgb;
        }
        animateGlowTo(glowRgb);
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
                imgEl.onload = null;
                imgEl.removeAttribute('src');
            }
            showFallbackAvatar(initials);
            animateGlowTo(DEFAULT_GLOW);
            return;
        }

        imgEl.dataset.productId = prodId;
        imgEl.alt = product.nama || 'Menu Starbuck';

        imgEl.onload = function () {
            if (imgEl.dataset.productId === prodId) {
                applyGlowForProduct(product, imgEl);
            }
        };

        imgEl.onerror = function () {
            if (imgEl.dataset.productId === prodId) {
                showFallbackAvatar(initials);
                animateGlowTo(DEFAULT_GLOW);
            }
        };

        if (fallbackEl) {
            fallbackEl.textContent = initials;
            fallbackEl.classList.add('d-none');
        }
        imgEl.classList.remove('d-none');
        imgEl.src = gambarUrl;

        if (imgEl.complete) {
            if (imgEl.naturalWidth > 0) {
                applyGlowForProduct(product, imgEl);
            } else {
                showFallbackAvatar(initials);
                animateGlowTo(DEFAULT_GLOW);
            }
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

    // Parallax halus maksimal 8px pada foto produk (desktop fine pointer, nonaktif saat reduced-motion)
    function initParallax() {
        var parallaxTargets = [imgEl, fallbackEl].filter(Boolean);
        if (parallaxTargets.length === 0) {
            return;
        }

        var parallaxRafId = null;
        var targetX = 0;
        var targetY = 0;

        function canParallax() {
            var isDesktop = window.innerWidth >= 992;
            var hasFinePointer = !window.matchMedia || window.matchMedia('(pointer: fine)').matches;
            return isDesktop && hasFinePointer && !prefersReducedMotion();
        }

        function applyParallaxOffset(x, y) {
            if (window.gsap && typeof window.gsap.to === 'function') {
                window.gsap.to(parallaxTargets, {
                    x: x,
                    y: y,
                    duration: 0.45,
                    ease: 'power2.out',
                    overwrite: 'auto'
                });
            } else {
                var transformVal = 'translate3d(' + x.toFixed(2) + 'px, ' + y.toFixed(2) + 'px, 0)';
                parallaxTargets.forEach(function (el) {
                    el.style.transform = transformVal;
                });
            }
        }

        heroEl.addEventListener('mousemove', function (e) {
            if (!canParallax()) {
                return;
            }
            var rect = heroEl.getBoundingClientRect();
            if (rect.width <= 0 || rect.height <= 0) {
                return;
            }

            var normX = (e.clientX - rect.left) / rect.width - 0.5;
            var normY = (e.clientY - rect.top) / rect.height - 0.5;

            targetX = Math.max(-8, Math.min(8, normX * 16));
            targetY = Math.max(-8, Math.min(8, normY * 16));

            if (!parallaxRafId) {
                parallaxRafId = window.requestAnimationFrame(function () {
                    parallaxRafId = null;
                    applyParallaxOffset(targetX, targetY);
                });
            }
        });

        heroEl.addEventListener('mouseleave', function () {
            if (parallaxRafId) {
                window.cancelAnimationFrame(parallaxRafId);
                parallaxRafId = null;
            }
            applyParallaxOffset(0, 0);
        });
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

    // Ekstrak warna glow untuk produk pertama saat halaman pertama kali dibuka
    if (products[0]) {
        var firstProd = products[0];
        if (imgEl && firstProd.gambarUrl) {
            imgEl.dataset.productId = String(firstProd.id || '');
            imgEl.addEventListener('load', function onFirstLoad() {
                if (currentIndex === 0) {
                    applyGlowForProduct(firstProd, imgEl);
                }
            }, { once: true });

            if (imgEl.complete && imgEl.naturalWidth > 0) {
                applyGlowForProduct(firstProd, imgEl);
            }
        }
    }

    initParallax();
    preloadAllImages();
})();
