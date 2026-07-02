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

	const config = window.vmConfig || { breakpointDesktop: 1024, i18n: {} };

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

/* ---------------------------------------------------------------------------
 * DMF live search — debounced fetch against /dmf/v1/search with an
 * accessible results dropdown. Enhances [data-dmf-search] forms.
 * ------------------------------------------------------------------------ */
(function () {
	'use strict';

	const forms = document.querySelectorAll('[data-dmf-search]');
	if (!forms.length) return;

	forms.forEach((form) => {
		const input = form.querySelector('input[type="search"]');
		const results = form.querySelector('[data-dmf-search-results]');
		const endpoint = form.dataset.endpoint;
		if (!input || !results || !endpoint) return;

		let timer = null;
		let controller = null;

		function close() {
			results.hidden = true;
			results.innerHTML = '';
		}

		function render(items) {
			if (!items.length) {
				results.innerHTML = '<p class="dmf-search__empty">' +
					((window.vmConfig && window.vmConfig.i18n.noResults) || 'No results.') + '</p>';
				results.hidden = false;
				return;
			}

			results.innerHTML = items.map((item) => (
				'<a class="dmf-search__result" role="option" href="' + item.url + '">' +
				(item.image ? '<img src="' + item.image + '" alt="" loading="lazy">' : '') +
				'<span>' + item.title +
				(item.terms && item.terms[0] ? '<small>' + item.terms[0].name + '</small>' : '') +
				'</span></a>'
			)).join('');
			results.hidden = false;
		}

		input.addEventListener('input', () => {
			clearTimeout(timer);
			const q = input.value.trim();

			if (q.length < 2) { close(); return; }

			timer = setTimeout(() => {
				if (controller) controller.abort();
				controller = new AbortController();

				const url = new URL(endpoint);
				url.searchParams.set('q', q);
				url.searchParams.set('per_page', '6');
				if (form.dataset.types) url.searchParams.set('type', form.dataset.types);

				fetch(url, { signal: controller.signal })
					.then((r) => r.json())
					.then((data) => render(data.items || []))
					.catch(() => {});
			}, 220);
		});

		document.addEventListener('click', (event) => {
			if (!form.contains(event.target)) close();
		});
		form.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') { close(); input.focus(); }
		});
	});
})();

/* ---------------------------------------------------------------------------
 * DMF inquiry form — REST submission with inline feedback.
 * Enhances [data-dmf-inquiry]; without JS the form degrades to plain POST.
 * ------------------------------------------------------------------------ */
(function () {
	'use strict';

	document.querySelectorAll('[data-dmf-inquiry]').forEach((form) => {
		form.addEventListener('submit', (event) => {
			event.preventDefault();

			const i18n = (window.vmConfig && window.vmConfig.i18n) || {};
			const feedback = form.querySelector('[data-dmf-inquiry-feedback]');
			const submit = form.querySelector('[type="submit"]');
			const payload = Object.fromEntries(new FormData(form).entries());

			if (submit) submit.disabled = true;
			if (feedback) {
				feedback.textContent = i18n.sending || 'Sending…';
				feedback.className = 'dmf-inquiry__feedback';
			}

			fetch(form.dataset.endpoint, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-DMF-Nonce': form.dataset.nonce
				},
				body: JSON.stringify(payload)
			})
				.then((r) => r.json().then((data) => ({ ok: r.ok, data })))
				.then(({ ok, data }) => {
					if (feedback) {
						feedback.textContent = ok
							? (i18n.sent || 'Thank you!')
							: (data.message || i18n.error || 'Error.');
						feedback.classList.add(ok ? 'is-success' : 'is-error');
					}
					if (ok) form.reset();
				})
				.catch(() => {
					if (feedback) {
						feedback.textContent = i18n.error || 'Error.';
						feedback.classList.add('is-error');
					}
				})
				.finally(() => {
					if (submit) submit.disabled = false;
				});
		});
	});
})();
