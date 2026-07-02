/**
 * Marrakech Convention Bureau — theme behaviour.
 *
 * Vanilla ES2017+, no dependencies, ~2 KB gzipped. Loaded once, deferred.
 * Each feature is an isolated init function that exits quietly when its
 * markup is absent, so any page loads only pay for what it uses.
 *
 * Brikk/Routiz scripts (maps, filters, galleries, dashboard) are untouched.
 */
(function () {
	'use strict';

	const config = window.mcbConfig || { breakpointDesktop: 1024, i18n: {} };

	/* -----------------------------------------------------------------------
	 * Sticky header state — adds .is-stuck for the shadow.
	 * -------------------------------------------------------------------- */
	function initStickyHeader() {
		const header = document.querySelector('[data-mcb-header]');
		if (!header) return;

		const sentinel = document.createElement('span');
		sentinel.setAttribute('aria-hidden', 'true');
		header.before(sentinel);

		new IntersectionObserver(([entry]) => {
			header.classList.toggle('is-stuck', !entry.isIntersecting);
		}).observe(sentinel);
	}

	/* -----------------------------------------------------------------------
	 * Mobile drawer: open/close, focus trap, ESC, submenu accordions.
	 * -------------------------------------------------------------------- */
	function initMobileNav() {
		const drawer = document.querySelector('[data-mcb-mobile-nav]');
		const toggle = document.querySelector('[data-mcb-nav-toggle]');
		if (!drawer || !toggle) return;

		const panel = drawer.querySelector('.mcb-mobile-nav__panel');
		let lastFocus = null;

		function setOpen(open) {
			drawer.hidden = !open;
			toggle.setAttribute('aria-expanded', String(open));
			toggle.setAttribute('aria-label', open ? config.i18n.closeMenu : config.i18n.openMenu);
			document.documentElement.style.overflow = open ? 'hidden' : '';

			if (open) {
				lastFocus = document.activeElement;
				(panel.querySelector('a, button') || panel).focus();
			} else if (lastFocus) {
				lastFocus.focus();
			}
		}

		toggle.addEventListener('click', () => setOpen(drawer.hidden));

		drawer.addEventListener('click', (event) => {
			if (event.target.closest('[data-mcb-nav-close]')) setOpen(false);
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && !drawer.hidden) setOpen(false);
		});

		// Submenu accordions: tapping a parent link's row toggles its children.
		drawer.querySelectorAll('.mcb-mobile-nav__menu .menu-item-has-children > a').forEach((link) => {
			link.addEventListener('click', (event) => {
				const li = link.parentElement;
				if (!li.classList.contains('is-open')) {
					event.preventDefault(); // first tap opens, second tap follows the link
				}
				li.classList.toggle('is-open');
			});
		});
	}

	/* -----------------------------------------------------------------------
	 * Desktop dropdown/mega menu keyboard support (hover is pure CSS).
	 * -------------------------------------------------------------------- */
	function initDesktopNav() {
		document.querySelectorAll('.mcb-nav__item--parent > a, .mcb-nav__item--mega > a').forEach((link) => {
			link.addEventListener('click', (event) => {
				// On touch/keyboard, first activation opens the panel.
				const li = link.parentElement;
				if (window.innerWidth >= config.breakpointDesktop && !li.classList.contains('is-open')) {
					event.preventDefault();
					closeAllPanels();
					li.classList.add('is-open');
					link.setAttribute('aria-expanded', 'true');
				}
			});
		});

		function closeAllPanels() {
			document.querySelectorAll('.mcb-nav__item.is-open').forEach((li) => {
				li.classList.remove('is-open');
				const link = li.querySelector(':scope > a');
				if (link) link.setAttribute('aria-expanded', 'false');
			});
		}

		document.addEventListener('click', (event) => {
			if (!event.target.closest('.mcb-nav')) closeAllPanels();
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') closeAllPanels();
		});
	}

	/* -----------------------------------------------------------------------
	 * Stat counters — count up when scrolled into view.
	 * -------------------------------------------------------------------- */
	function initCounters() {
		const counters = document.querySelectorAll('[data-mcb-counter]');
		if (!counters.length) return;

		const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		const formatter = new Intl.NumberFormat(document.documentElement.lang || undefined);

		const observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return;
				observer.unobserve(entry.target);

				const target = parseFloat(entry.target.dataset.mcbCounter) || 0;
				if (reduceMotion) {
					entry.target.textContent = formatter.format(target);
					return;
				}

				const duration = 1400;
				const start = performance.now();

				(function tick(now) {
					const progress = Math.min((now - start) / duration, 1);
					const eased = 1 - Math.pow(1 - progress, 3);
					entry.target.textContent = formatter.format(Math.round(target * eased));
					if (progress < 1) requestAnimationFrame(tick);
				})(start);
			});
		}, { threshold: 0.4 });

		counters.forEach((el) => observer.observe(el));
	}

	/* -----------------------------------------------------------------------
	 * FAQ accordion — exclusive open within a [data-mcb-accordion] group.
	 * -------------------------------------------------------------------- */
	function initAccordions() {
		document.querySelectorAll('[data-mcb-accordion]').forEach((group) => {
			group.addEventListener('toggle', (event) => {
				if (!event.target.open) return;
				group.querySelectorAll('details[open]').forEach((item) => {
					if (item !== event.target) item.open = false;
				});
			}, true);
		});
	}

	function init() {
		initStickyHeader();
		initMobileNav();
		initDesktopNav();
		initCounters();
		initAccordions();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
