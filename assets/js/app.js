(function () {
    'use strict';

    // 1. Toast Notifikasi (kanan atas, hilang otomatis 3 detik)
    function ensureToastContainer() {
        var container = document.getElementById('sb-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'sb-toast-container';
            container.className = 'sb-toast-container';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
        }
        return container;
    }

    function showToast(message, type) {
        if (!message) {
            return;
        }
        var container = ensureToastContainer();
        var toastType = type || 'success';

        var toastEl = document.createElement('div');
        toastEl.className = 'sb-toast sb-toast-' + toastType;
        toastEl.setAttribute('role', toastType === 'danger' ? 'alert' : 'status');

        var textSpan = document.createElement('span');
        textSpan.textContent = String(message);

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'sb-toast-close';
        closeBtn.setAttribute('aria-label', 'Tutup notifikasi');
        closeBtn.innerHTML = '&times;';

        toastEl.appendChild(textSpan);
        toastEl.appendChild(closeBtn);
        container.appendChild(toastEl);

        requestAnimationFrame(function () {
            toastEl.classList.add('is-visible');
        });

        var removed = false;
        function removeToast() {
            if (removed) {
                return;
            }
            removed = true;
            toastEl.classList.remove('is-visible');
            window.setTimeout(function () {
                if (toastEl.parentNode) {
                    toastEl.parentNode.removeChild(toastEl);
                }
            }, 220);
        }

        closeBtn.addEventListener('click', removeToast);
        window.setTimeout(removeToast, 3000);
    }

    window.StarbuckUI = window.StarbuckUI || {};
    window.StarbuckUI.showToast = showToast;

    if (window.SB_FLASH_TOAST && window.SB_FLASH_TOAST.message) {
        showToast(window.SB_FLASH_TOAST.message, window.SB_FLASH_TOAST.type || 'success');
    }

    // 2. Modal Konfirmasi Hapus (Bootstrap 5.3)
    var modalEl = document.getElementById('sbConfirmModal');
    var modalMessageEl = document.getElementById('sbConfirmModalMessage');
    var modalOkBtn = document.getElementById('sbConfirmModalOk');
    var pendingTarget = null;
    var bsModal = null;

    if (modalEl && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        bsModal = new window.bootstrap.Modal(modalEl);
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-confirm-delete]');
        if (!trigger) {
            return;
        }

        var msg = trigger.getAttribute('data-confirm-message') || 'Apakah Anda yakin ingin menghapus data ini?';

        if (!bsModal || !modalOkBtn) {
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
            return;
        }

        e.preventDefault();
        pendingTarget = trigger;
        if (modalMessageEl) {
            modalMessageEl.textContent = msg;
        }
        bsModal.show();
    });

    if (modalOkBtn) {
        modalOkBtn.addEventListener('click', function () {
            if (!pendingTarget) {
                return;
            }
            var target = pendingTarget;
            pendingTarget = null;
            if (bsModal) {
                bsModal.hide();
            }

            if (target.tagName === 'A' && target.href) {
                window.location.href = target.href;
            } else {
                var form = target.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }

    // 3. Indikator Curved Protruding Sidebar + Idle Animation (menggunakan sessionStorage)
    var STORAGE_KEY = 'starbuck_prev_nav';
    var navContainer = document.querySelector('.sb-sidebar .sidebar-nav');
    var indicator = navContainer ? navContainer.querySelector('.nav-curve-indicator') : null;
    var navLinks = navContainer ? navContainer.querySelectorAll('.nav-link[data-nav-index]') : [];
    var idleTimer = null;

    if (!navContainer || !indicator || !navLinks.length) {
        return;
    }

    function getTargetY(link) {
        var indicatorHeight = indicator.offsetHeight || 56;
        return link.offsetTop + (link.offsetHeight - indicatorHeight) / 2;
    }

    function stopIdle() {
        if (idleTimer) {
            window.clearTimeout(idleTimer);
            idleTimer = null;
        }
        indicator.classList.remove('is-idle');
        navLinks.forEach(function (item) {
            item.classList.remove('is-idle');
        });
    }

    function startIdle(activeLink) {
        stopIdle();
        if (!activeLink || window.innerWidth < 992) {
            return;
        }
        indicator.classList.add('is-idle');
        activeLink.classList.add('is-idle');
    }

    function scheduleIdleAfterSlide(activeLink) {
        stopIdle();
        idleTimer = window.setTimeout(function () {
            startIdle(activeLink);
        }, 420);
    }

    function positionIndicator(animateFromPrev) {
        var activeLink = navContainer.querySelector('.nav-link.active');
        if (!activeLink || window.innerWidth < 992) {
            stopIdle();
            return;
        }

        var currentIdx = activeLink.getAttribute('data-nav-index');
        var targetY = getTargetY(activeLink);
        var prevIdx = null;

        try {
            prevIdx = sessionStorage.getItem(STORAGE_KEY);
        } catch (e) {}

        var prevLink = (animateFromPrev && prevIdx !== null)
            ? navContainer.querySelector('.nav-link[data-nav-index="' + prevIdx + '"]')
            : null;

        if (prevLink && prevLink !== activeLink) {
            var prevY = getTargetY(prevLink);
            stopIdle();
            indicator.style.transition = 'none';
            indicator.style.transform = 'translateY(' + prevY + 'px)';
            indicator.style.opacity = '1';
            void indicator.offsetHeight;

            requestAnimationFrame(function () {
                indicator.style.transition = '';
                indicator.style.transform = 'translateY(' + targetY + 'px)';
                scheduleIdleAfterSlide(activeLink);
            });
        } else {
            indicator.style.transition = 'none';
            indicator.style.transform = 'translateY(' + targetY + 'px)';
            indicator.style.opacity = '1';
            void indicator.offsetHeight;
            indicator.style.transition = '';
            startIdle(activeLink);
        }

        try {
            if (currentIdx !== null) {
                sessionStorage.setItem(STORAGE_KEY, currentIdx);
            }
        } catch (e) {}
    }

    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) {
                return;
            }
            stopIdle();
            navLinks.forEach(function (item) {
                item.classList.remove('active', 'is-active');
            });
            link.classList.add('active', 'is-active');
            indicator.style.transform = 'translateY(' + getTargetY(link) + 'px)';
            scheduleIdleAfterSlide(link);
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            positionIndicator(false);
        } else {
            stopIdle();
        }
    });

    positionIndicator(true);
})();
