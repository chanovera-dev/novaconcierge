/**
 * Nova Concierge - Property Gallery & Slideshow Interaction
 *
 * Uses robust global Event Delegation on document for seamless operation
 * across initial page loads and dynamic AJAX filter/pagination updates.
 *
 * @package NovaConcierge
 */

(function () {
	'use strict';

	/**
	 * Switch slide on a given slideshow container.
	 *
	 * @param {Element} slideshow The .stories-slideshow or .story-card container.
	 * @param {number} deltaOrIndex Step offset (+1, -1) or target 0-based index.
	 * @param {boolean} isAbsolute True if target is an exact index.
	 */
	function changeSlide(slideshow, deltaOrIndex, isAbsolute) {
		if (!slideshow) return;

		const targetSlideshow = slideshow.matches('.stories-slideshow, .property-slideshow')
			? slideshow
			: (slideshow.querySelector('.stories-slideshow, .property-slideshow') || slideshow);

		const slides = targetSlideshow.querySelectorAll('.slide-item');
		const dots = targetSlideshow.querySelectorAll('.dot-nav');
		const counterCurrent = targetSlideshow.querySelector('.slideshow-counter .current-slide');
		const lightboxTrigger = targetSlideshow.querySelector('.image-lightbox-trigger');
		const total = slides.length;

		if (total <= 1) return;

		let current = 0;
		slides.forEach((slide, idx) => {
			if (slide.classList.contains('is-active')) {
				current = idx;
			}
		});

		let nextIndex = isAbsolute ? deltaOrIndex : (current + deltaOrIndex);
		if (nextIndex < 0) nextIndex = total - 1;
		if (nextIndex >= total) nextIndex = 0;

		slides.forEach((slide, idx) => {
			slide.classList.toggle('is-active', idx === nextIndex);
		});

		dots.forEach((dot, idx) => {
			dot.classList.toggle('is-active', idx === nextIndex);
		});

		if (counterCurrent) {
			counterCurrent.textContent = nextIndex + 1;
		}

		if (lightboxTrigger) {
			lightboxTrigger.setAttribute('data-current-index', nextIndex);
		}
	}

	/**
	 * Global Delegated Click Handler.
	 * Handles previous/next slide, dots, and info-overlay toggle on ALL property cards,
	 * even those newly inserted via AJAX.
	 */
	document.addEventListener('click', function (e) {
		// 1. Previous slide button
		const prevBtn = e.target.closest('.prev-slide');
		if (prevBtn) {
			e.preventDefault();
			e.stopPropagation();
			const slideshow = prevBtn.closest('.stories-slideshow, .property-slideshow, .story-card');
			changeSlide(slideshow, -1, false);
			return;
		}

		// 2. Next slide button
		const nextBtn = e.target.closest('.next-slide');
		if (nextBtn) {
			e.preventDefault();
			e.stopPropagation();
			const slideshow = nextBtn.closest('.stories-slideshow, .property-slideshow, .story-card');
			changeSlide(slideshow, 1, false);
			return;
		}

		// 3. Dot navigation indicator
		const dot = e.target.closest('.dot-nav');
		if (dot) {
			e.preventDefault();
			e.stopPropagation();
			const targetIndex = parseInt(dot.getAttribute('data-slide-target'), 10);
			if (!isNaN(targetIndex)) {
				const slideshow = dot.closest('.stories-slideshow, .property-slideshow, .story-card');
				changeSlide(slideshow, targetIndex, true);
			}
			return;
		}

		// 4. Click on slide image opens lightbox
		const slideItem = e.target.closest('.slide-item');
		if (slideItem && !e.target.closest('.slideshow-bottom-bar, .post-top-actions, .info-overlay, .toggle-info-btn, .button__like, a, button')) {
			const slideshow = slideItem.closest('.stories-slideshow, .story-card');
			if (slideshow) {
				const trigger = slideshow.querySelector('.image-lightbox-trigger');
				if (trigger) {
					e.preventDefault();
					const slideIndex = parseInt(slideItem.getAttribute('data-slide-index'), 10) || 0;
					trigger.setAttribute('data-current-index', slideIndex);
					trigger.click();
				}
			}
		}
	});

	/**
	 * Global Delegated Touch & Swipe Gestures.
	 */
	let startX = 0;
	let startY = 0;
	let endX = 0;
	let endY = 0;
	let touchSlideshow = null;

	document.addEventListener('touchstart', function (e) {
		if (e.target.closest('.post-top-actions, .slideshow-bottom-bar, .info-overlay.is-visible, a, button')) {
			touchSlideshow = null;
			return;
		}
		touchSlideshow = e.target.closest('.stories-slideshow, .story-card');
		if (touchSlideshow) {
			const touch = e.changedTouches[0];
			startX = touch.clientX;
			startY = touch.clientY;
			endX = touch.clientX;
			endY = touch.clientY;
		}
	}, { passive: true });

	document.addEventListener('touchmove', function (e) {
		if (!touchSlideshow) return;
		const touch = e.changedTouches[0];
		endX = touch.clientX;
		endY = touch.clientY;
	}, { passive: true });

	document.addEventListener('touchend', function (e) {
		if (!touchSlideshow) return;
		const distX = endX - startX;
		const distY = endY - startY;
		const threshold = 35;
		const restraint = 75;

		if (Math.abs(distX) >= threshold && Math.abs(distX) > Math.abs(distY) && Math.abs(distY) <= restraint) {
			if (distX < 0) {
				changeSlide(touchSlideshow, 1, false);
			} else {
				changeSlide(touchSlideshow, -1, false);
			}
		}
		touchSlideshow = null;
	}, { passive: true });

	/**
	 * Initialize Single Property Detail Gallery.
	 */
	function initSingleGallery() {
		const galleryWrapper = document.getElementById('property-gallery-slider');
		if (!galleryWrapper) return;

		const slides = galleryWrapper.querySelectorAll('.single-gallery-slide');
		const thumbs = galleryWrapper.querySelectorAll('.thumb-btn');
		const prevBtn = galleryWrapper.querySelector('.gallery-nav-btn.prev-btn');
		const nextBtn = galleryWrapper.querySelector('.gallery-nav-btn.next-btn');
		const counterCurrent = galleryWrapper.querySelector('.gallery-counter-badge .current-img');

		let currentIndex = 0;
		const totalSlides = slides.length;

		if (totalSlides <= 1) return;

		const setActiveSlide = (index) => {
			if (index < 0) index = totalSlides - 1;
			if (index >= totalSlides) index = 0;
			currentIndex = index;

			slides.forEach((slide, i) => {
				slide.classList.toggle('is-active', i === currentIndex);
			});

			thumbs.forEach((thumb, i) => {
				const isActive = i === currentIndex;
				thumb.classList.toggle('is-active', isActive);
				if (isActive) {
					thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
				}
			});

			if (counterCurrent) {
				counterCurrent.textContent = currentIndex + 1;
			}
		};

		if (prevBtn) {
			prevBtn.addEventListener('click', () => setActiveSlide(currentIndex - 1));
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', () => setActiveSlide(currentIndex + 1));
		}

		thumbs.forEach((thumb, i) => {
			thumb.addEventListener('click', () => setActiveSlide(i));
		});

		// Clicking on the active slide or fullscreen button opens property lightbox
		const fsBtn = galleryWrapper.querySelector('.single-gallery-fullscreen-btn');
		slides.forEach((slide, i) => {
			slide.style.cursor = 'pointer';
			slide.addEventListener('click', (e) => {
				if (e.target.closest('.gallery-nav-btn')) return;
				if (fsBtn) {
					fsBtn.setAttribute('data-current-index', currentIndex);
					fsBtn.click();
				}
			});
		});

		if (fsBtn) {
			fsBtn.addEventListener('click', () => {
				fsBtn.setAttribute('data-current-index', currentIndex);
			});
		}
	}

	/**
	 * Initialize Avante 3D Flip Slideshow for Property Metadata with Counter.
	 */
	function initMetadataSlider() {
		const wrappers = document.querySelectorAll('.metadata-slideshow-container, .cert-container');

		wrappers.forEach((wrapper) => {
			const flipTarget = wrapper;
			const slideshow = wrapper.querySelector('.metadata-slider-track, .property--metadata--list, .slideshow');
			const slides = Array.from(slideshow ? slideshow.children : []);
			const counterCurrent = wrapper.querySelector('.counter-current');
			const counterTotal = wrapper.querySelector('.counter-total');
			const bulletsWrapper = wrapper.querySelector('.meta-slider-dots, .slideshow-bullets');

			if (!slideshow || slides.length === 0) return;

			const total = slides.length;
			let current = 0;
			let isAnimating = false;

			/* ── Initialize counter numbers ──────────────────────────────── */
			if (counterTotal) counterTotal.textContent = total;
			if (counterCurrent) counterCurrent.textContent = current + 1;

			/* ── Build bullets (if present) ──────────────────────────────── */
			if (bulletsWrapper) {
				bulletsWrapper.innerHTML = '';
				slides.forEach((_, i) => {
					const b = document.createElement('div');
					b.classList.add('bullet', 'meta-dot');
					if (i === 0) b.classList.add('active', 'is-active');
					b.dataset.index = i;
					b.setAttribute('aria-label', `Ir a especificación ${i + 1}`);
					bulletsWrapper.appendChild(b);
				});
			}

			/* ── Mark initial active slide ────────────────────────────────── */
			slides.forEach((s, idx) => {
				s.classList.toggle('active', idx === 0);
				s.classList.toggle('is-active', idx === 0);
			});

			function updateIndicators(idx) {
				if (counterCurrent) {
					counterCurrent.textContent = idx + 1;
				}
				if (bulletsWrapper) {
					const bullets = bulletsWrapper.querySelectorAll('.bullet, .meta-dot');
					bullets.forEach((b, i) => {
						b.classList.toggle('active', i === idx);
						b.classList.toggle('is-active', i === idx);
					});
				}
			}

			/* ── Core flip logic (Avante 2-Phase 3D Flip) ──────────────────── */
			function goToSlide(targetIdx) {
				if (isAnimating || targetIdx === current) return;
				isAnimating = true;

				// Determine direction before normalising targetIdx
				const direction = targetIdx > current ? 'next' : 'prev';

				// Normalise index with wrapping
				targetIdx = ((targetIdx % total) + total) % total;

				const FLIP_DURATION = 600; // ms — matches CSS 0.6s

				const phase1Angle = direction === 'next' ? 90 : -90;
				const phase2Angle = direction === 'next' ? -90 : 90;

				// Phase 1: rotate 0 → ±90° (card "falls away")
				flipTarget.style.transition = `transform ${FLIP_DURATION / 2}ms cubic-bezier(0.4, 0, 1, 1)`;
				flipTarget.style.transform = `rotateY(${phase1Angle}deg)`;

				setTimeout(() => {
					// At ±90° the card is edge-on and invisible — swap slides & counter
					slides[current].classList.remove('active', 'is-active');
					current = targetIdx;
					slides[current].classList.add('active', 'is-active');
					updateIndicators(current);

					// Instantly jump to ∓90° on the other side (no transition)
					flipTarget.style.transition = 'none';
					flipTarget.style.transform = `rotateY(${phase2Angle}deg)`;

					// Force reflow so the browser registers the position change
					void flipTarget.offsetWidth;

					// Phase 2: rotate ∓90° → 0° (card "comes back")
					flipTarget.style.transition = `transform ${FLIP_DURATION / 2}ms cubic-bezier(0, 0, 0.6, 1)`;
					flipTarget.style.transform = 'rotateY(0deg)';

					setTimeout(() => {
						flipTarget.style.transition = '';
						flipTarget.style.transform = '';
						isAnimating = false;
					}, FLIP_DURATION / 2);

				}, FLIP_DURATION / 2);
			}

			/* ── Navigation buttons ───────────────────────────────────────── */
			const prevBtn = wrapper.querySelector('.meta-prev-btn, .slideshow-prev');
			const nextBtn = wrapper.querySelector('.meta-next-btn, .slideshow-next');

			if (prevBtn) prevBtn.addEventListener('click', (e) => { e.preventDefault(); goToSlide(current - 1); });
			if (nextBtn) nextBtn.addEventListener('click', (e) => { e.preventDefault(); goToSlide(current + 1); });

			/* ── Bullet clicks ────────────────────────────────────────────── */
			if (bulletsWrapper) {
				bulletsWrapper.addEventListener('click', (e) => {
					const b = e.target.closest('.bullet, .meta-dot');
					if (!b) return;
					e.preventDefault();
					goToSlide(parseInt(b.dataset.index, 10));
				});
			}

			/* ── Touch / swipe ────────────────────────────────────────────── */
			let startX = 0;
			flipTarget.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; }, { passive: true });
			flipTarget.addEventListener('touchend', (e) => {
				const delta = e.changedTouches[0].clientX - startX;
				if (Math.abs(delta) > 40) {
					goToSlide(delta < 0 ? current + 1 : current - 1);
				}
			}, { passive: true });
		});
	}

	window.initNovaConciergeCardSlideshows = function () {
		// Safe no-op because global event delegation handles all nodes dynamically
	};

	document.addEventListener('DOMContentLoaded', () => {
		initSingleGallery();
		initMetadataSlider();
	});
})();
