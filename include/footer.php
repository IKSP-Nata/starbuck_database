    </main>

    <!-- Bootstrap 5.3 JS Bundle via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    (function () {
        var STORAGE_KEY = 'starbuck_prev_nav';
        var navContainer = document.querySelector('.sb-sidebar .sidebar-nav');
        var indicator = navContainer ? navContainer.querySelector('.nav-curve-indicator') : null;
        var navLinks = navContainer ? navContainer.querySelectorAll('.nav-link[data-nav-index]') : [];

        if (!navContainer || !indicator || !navLinks.length) {
            return;
        }

        function getTargetY(link) {
            var indicatorHeight = indicator.offsetHeight || 58;
            return link.offsetTop + (link.offsetHeight - indicatorHeight) / 2;
        }

        function positionIndicator(animateFromPrev) {
            var activeLink = navContainer.querySelector('.nav-link.active');
            if (!activeLink || window.innerWidth < 992) {
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
                indicator.style.transition = 'none';
                indicator.style.transform = 'translateY(' + prevY + 'px)';
                indicator.style.opacity = '1';
                void indicator.offsetHeight;

                requestAnimationFrame(function () {
                    indicator.style.transition = '';
                    indicator.style.transform = 'translateY(' + targetY + 'px)';
                });
            } else {
                indicator.style.transition = 'none';
                indicator.style.transform = 'translateY(' + targetY + 'px)';
                indicator.style.opacity = '1';
                void indicator.offsetHeight;
                indicator.style.transition = '';
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
                navLinks.forEach(function (item) {
                    item.classList.remove('active', 'is-active');
                });
                link.classList.add('active', 'is-active');
                indicator.style.transform = 'translateY(' + getTargetY(link) + 'px)';
            });
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                positionIndicator(false);
            }
        });

        positionIndicator(true);
    })();
    </script>
</body>

</html>
