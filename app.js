(function () {
    const header = document.querySelector('[data-site-header]');
    const toggle = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-primary-nav]');
    const phoneQuery = window.matchMedia('(max-width: 720px), (pointer: coarse) and (max-width: 900px)');

    const updatePhoneClass = () => {
        document.body.classList.toggle('is-phone', phoneQuery.matches);
    };

    updatePhoneClass();

    if (typeof phoneQuery.addEventListener === 'function') {
        phoneQuery.addEventListener('change', updatePhoneClass);
    } else if (typeof phoneQuery.addListener === 'function') {
        phoneQuery.addListener(updatePhoneClass);
    }

    if (toggle && nav) {
        const setOpen = (isOpen) => {
            document.body.classList.toggle('nav-open', isOpen);
            nav.classList.toggle('is-open', isOpen);
            toggle.classList.toggle('is-open', isOpen);
            toggle.setAttribute('aria-expanded', String(isOpen));
            toggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
        };

        toggle.addEventListener('click', () => {
            setOpen(!nav.classList.contains('is-open'));
        });

        nav.addEventListener('click', (event) => {
            if (event.target.matches('a')) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });
    }

    const updateHeader = () => {
        if (!header) return;
        header.classList.toggle('is-scrolled', window.scrollY > 12);
    };

    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    document.querySelectorAll('table').forEach((table) => {
        if (table.closest('.table-shell')) return;

        const shell = document.createElement('div');
        shell.className = 'table-shell';
        table.parentNode.insertBefore(shell, table);
        shell.appendChild(table);

        const headers = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
        table.querySelectorAll('tbody tr').forEach((row) => {
            Array.from(row.children).forEach((cell, index) => {
                if (headers[index]) {
                    cell.setAttribute('data-label', headers[index]);
                }
            });
        });
    });

    document.querySelectorAll('input, textarea, select').forEach((field) => {
        const update = () => field.classList.toggle('has-value', Boolean(field.value));
        update();
        field.addEventListener('input', update);
        field.addEventListener('change', update);
    });

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const revealTargets = document.querySelectorAll(
        '.hero, .admin-hero, .auth-layout, .category-link-grid, .content-band, .panel, .product-card, .product-showcase, .detail-panel, .empty-state, .breadcrumb-row'
    );

    if (revealTargets.length) {
        revealTargets.forEach((target, index) => {
            target.classList.add('reveal-item');

            if (target.classList.contains('product-card')) {
                target.style.transitionDelay = `${Math.min(index % 6, 5) * 45}ms`;
            }
        });

        if (!reduceMotion && 'IntersectionObserver' in window) {
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.14, rootMargin: '0px 0px -8% 0px' });

            revealTargets.forEach((target) => revealObserver.observe(target));
        } else {
            revealTargets.forEach((target) => target.classList.add('is-visible'));
        }
    }

    document.querySelectorAll('.size-chip').forEach((chip) => {
        chip.setAttribute('aria-pressed', 'false');

        chip.addEventListener('click', () => {
            const group = chip.closest('.size-list');

            if (group) {
                group.querySelectorAll('.size-chip').forEach((item) => {
                    item.classList.remove('is-selected');
                    item.setAttribute('aria-pressed', 'false');
                });
            }

            chip.classList.add('is-selected');
            chip.setAttribute('aria-pressed', 'true');
        });
    });

    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    if (!reduceMotion && finePointer) {
        document.querySelectorAll('[data-product-card]').forEach((card) => {
            card.addEventListener('pointermove', (event) => {
                const rect = card.getBoundingClientRect();
                const x = (event.clientX - rect.left) / rect.width;
                const y = (event.clientY - rect.top) / rect.height;
                const tiltX = (0.5 - y) * 5;
                const tiltY = (x - 0.5) * 6;

                card.style.setProperty('--tilt-x', `${tiltX.toFixed(2)}deg`);
                card.style.setProperty('--tilt-y', `${tiltY.toFixed(2)}deg`);
                card.style.setProperty('--shine-x', `${(x * 100).toFixed(1)}%`);
                card.style.setProperty('--shine-y', `${(y * 100).toFixed(1)}%`);
            });

            card.addEventListener('pointerleave', () => {
                card.style.setProperty('--tilt-x', '0deg');
                card.style.setProperty('--tilt-y', '0deg');
                card.style.setProperty('--shine-x', '50%');
                card.style.setProperty('--shine-y', '20%');
            });
        });
    }

    const status = document.querySelector('.floating-status');
    if (status) {
        window.setTimeout(() => {
            status.classList.add('is-hiding');
        }, 2600);
    }
})();
