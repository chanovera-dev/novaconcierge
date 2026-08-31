/**
 * Nova Concierge - Property Interactive Filters & AJAX Pagination
 *
 * Handles real-time search, filter change events, accordion submenus, steppers,
 * mobile drawer toggling, dynamic pagination, and URL history state.
 *
 * @package NovaConcierge
 */

document.addEventListener('DOMContentLoaded', () => {
	const resultsContainer = document.querySelector('#nc-properties-list-container, .properties--list');
	const filterForm = document.querySelector('#nc-property-filter-form, .property-filter-form');
	const sidebarContainer = document.querySelector('#nc-properties-sidebar, .properties--filter');
	const toggleFiltersBtn = document.getElementById('nc-toggle-filters-btn');
	const filterDrawer = document.getElementById('nc-filter-drawer');
	const searchInput = document.getElementById('filter-search-input');
	const resetBtn = document.getElementById('nc-reset-filters-btn');
	const activeCountBadge = document.getElementById('nc-active-filters-count');
	const priceRangeSlider = document.getElementById('price_range');
	const priceRangeValue = document.getElementById('price-range-value');
	const minPriceInput = document.getElementById('filter-min-price');
	const maxPriceInput = document.getElementById('filter-max-price');

	if (!resultsContainer || !filterForm) {
		return;
	}

	const ajaxUrl = (typeof novaconcierge_ajax !== 'undefined' && novaconcierge_ajax.ajaxurl) ? novaconcierge_ajax.ajaxurl : '/wp-admin/admin-ajax.php';
	let currentPage = parseInt((new URL(window.location)).searchParams.get('paged')) || 1;
	let debounceTimer = null;

	// Number formatting helper
	const formatNumber = (num) => {
		return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
	};

	// 1. Mobile Filter Drawer Toggle
	if (toggleFiltersBtn && filterDrawer) {
		toggleFiltersBtn.addEventListener('click', (e) => {
			e.preventDefault();
			const isOpen = filterDrawer.classList.toggle('is-open');
			if (sidebarContainer) {
				sidebarContainer.classList.toggle('is-expanded', isOpen);
			}
			toggleFiltersBtn.classList.toggle('is-active', isOpen);
			toggleFiltersBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});
	}

	// 2. Primary Accordion Menus (Location, Rooms, Price, Size)
	const accordionButtons = filterForm.querySelectorAll('.filter-navigation.menu > .menu-item-has-children > .button-for-submenu');
	accordionButtons.forEach(btn => {
		btn.addEventListener('click', (e) => {
			e.preventDefault();
			const parentLi = btn.closest('li');
			const subMenu = parentLi ? parentLi.querySelector(':scope > .sub-menu') : null;
			if (!subMenu) return;

			const isCurrentlyOpen = subMenu.classList.contains('is-open');
			subMenu.classList.toggle('is-open', !isCurrentlyOpen);
			btn.classList.toggle('rotate', !isCurrentlyOpen);
			btn.setAttribute('aria-expanded', (!isCurrentlyOpen) ? 'true' : 'false');
		});
	});

	// 3. Nested City Submenus
	const nestedToggles = filterForm.querySelectorAll('.nested-toggle');
	nestedToggles.forEach(btn => {
		btn.addEventListener('click', (e) => {
			e.preventDefault();
			const stateLi = btn.closest('.state-item');
			const nestedMenu = stateLi ? stateLi.querySelector('.nested-cities') : null;
			if (!nestedMenu) return;

			const isNestedOpen = nestedMenu.classList.contains('is-open');
			nestedMenu.classList.toggle('is-open', !isNestedOpen);
			btn.classList.toggle('rotate', !isNestedOpen);
			btn.setAttribute('aria-expanded', (!isNestedOpen) ? 'true' : 'false');
		});
	});

	// 4. Stepper Buttons (+ / -) for Bedrooms and Bathrooms
	const increaseButtons = filterForm.querySelectorAll('.btn-increase');
	const decreaseButtons = filterForm.querySelectorAll('.btn-decrease');

	increaseButtons.forEach(btn => {
		btn.addEventListener('click', (e) => {
			e.preventDefault();
			const targetId = btn.getAttribute('data-target');
			const input = document.getElementById(targetId);
			if (input) {
				const val = parseFloat(input.value) || 0;
				input.value = val + 1;
				input.dispatchEvent(new Event('change', { bubbles: true }));
			}
		});
	});

	decreaseButtons.forEach(btn => {
		btn.addEventListener('click', (e) => {
			e.preventDefault();
			const targetId = btn.getAttribute('data-target');
			const input = document.getElementById(targetId);
			if (input) {
				const val = parseFloat(input.value) || 0;
				if (val > 0) {
					input.value = (val - 1) > 0 ? (val - 1) : '';
					input.dispatchEvent(new Event('change', { bubbles: true }));
				}
			}
		});
	});

	// 5. Price Range Slider Synchronization
	if (priceRangeSlider && priceRangeValue) {
		priceRangeSlider.addEventListener('input', () => {
			priceRangeValue.textContent = '$' + formatNumber(priceRangeSlider.value);
			if (minPriceInput) {
				minPriceInput.value = priceRangeSlider.value;
			}
		});

		priceRangeSlider.addEventListener('change', () => {
			fetchProperties(1, true);
		});
	}

	if (minPriceInput && priceRangeSlider) {
		minPriceInput.addEventListener('input', () => {
			if (minPriceInput.value && priceRangeValue) {
				priceRangeSlider.value = minPriceInput.value;
				priceRangeValue.textContent = '$' + formatNumber(minPriceInput.value);
			}
		});
	}

	// 6. Update Active Filters Counter
	const updateActiveFilterCount = () => {
		if (!activeCountBadge) return;
		let count = 0;

		// Checkboxes (Operation, Type, State, City)
		const checkedBoxes = filterForm.querySelectorAll('input[type="checkbox"]:checked');
		count += checkedBoxes.length;

		// Number inputs
		const numberInputs = filterForm.querySelectorAll('input[type="number"]');
		numberInputs.forEach(input => {
			if (input.value && parseFloat(input.value) > 0) {
				count += 1;
			}
		});

		// Select dropdowns (Location)
		const selectInputs = filterForm.querySelectorAll('select');
		selectInputs.forEach(sel => {
			if (sel.value && sel.value.trim().length > 0) {
				count += 1;
			}
		});

		// Search input
		if (searchInput && searchInput.value.trim().length > 0) {
			count += 1;
		}

		if (count > 0) {
			activeCountBadge.textContent = count;
			activeCountBadge.style.display = 'inline-flex';
		} else {
			activeCountBadge.style.display = 'none';
		}
	};

	// 7. Reset Filters
	if (resetBtn) {
		resetBtn.addEventListener('click', (e) => {
			e.preventDefault();
			filterForm.reset();
			if (searchInput) searchInput.value = '';

			// Reset range slider display
			if (priceRangeSlider && priceRangeValue) {
				priceRangeSlider.value = priceRangeSlider.min || 0;
				priceRangeValue.textContent = '$' + formatNumber(priceRangeSlider.value);
			}

			// Reset custom selects
			const selectInputs = filterForm.querySelectorAll('select');
			selectInputs.forEach(sel => {
				sel.dispatchEvent(new Event('reset-custom'));
			});

			updateActiveFilterCount();
			fetchProperties(1, true);
		});
	}

	// Build FormData
	const buildFormData = (extra = {}) => {
		const fd = new FormData(filterForm);
		Object.keys(extra).forEach(key => fd.append(key, extra[key]));
		if (!fd.get('action')) {
			fd.append('action', 'novaconcierge_filter_properties');
		}
		return fd;
	};

	// 8. Fetch & Render Filtered Properties
	const fetchProperties = async (paged = 1, pushState = true) => {
		try {
			resultsContainer.classList.add('is-loading');
			updateActiveFilterCount();

			const fd = buildFormData({ paged });

			const response = await fetch(ajaxUrl, {
				method: 'POST',
				body: fd
			});

			if (!response.ok) {
				throw new Error('Network response was not ok');
			}

			const html = await response.text();

			// Replace results container with smooth transition
			resultsContainer.innerHTML = html;
			bindPaginationLinks();

			// Scroll smoothly to top of results on pagination
			if (paged > 1) {
				const topOffset = resultsContainer.getBoundingClientRect().top + window.pageYOffset - 120;
				window.scrollTo({ top: topOffset, behavior: 'smooth' });
			}

			// Push URL state without refreshing
			if (pushState && window.history.pushState) {
				const url = new URL(window.location.href);
				if (paged > 1) {
					url.searchParams.set('paged', paged);
				} else {
					url.searchParams.delete('paged');
				}
				window.history.pushState({}, '', url.toString());
			}

			// Re-initialize card slideshows
			if (typeof window.initNovaConciergeCardSlideshows === 'function') {
				window.initNovaConciergeCardSlideshows(resultsContainer);
			}

			// Update URL history
			if (pushState) {
				const url = new URL(window.location);
				if (currentPage > 1) {
					url.searchParams.set('paged', currentPage);
				} else {
					url.searchParams.delete('paged');
				}
				window.history.pushState({ paged: currentPage }, '', url);
			}

			// Smooth scroll to top of results
			const containerTop = resultsContainer.getBoundingClientRect().top + window.pageYOffset - 100;
			window.scrollTo({ top: Math.max(0, containerTop), behavior: 'smooth' });

		} catch (err) {
			console.error('NovaConcierge: Error fetching properties:', err);
		} finally {
			resultsContainer.classList.remove('is-loading');
		}
	};

	// Parse page number from link
	const parsePageFromLink = (link) => {
		try {
			const url = new URL(link.href, window.location.origin);
			const p = url.searchParams.get('paged');
			if (p) return parseInt(p);

			const match = url.pathname.match(/page\/(\d+)\/?/);
			if (match) return parseInt(match[1]);
		} catch (e) {
			// fallback
		}

		const text = link.textContent.trim();
		const num = parseInt(text);
		if (!isNaN(num)) return num;

		return 1;
	};

	// Attach click listeners to pagination links
	const bindPaginationLinks = () => {
		const links = resultsContainer.querySelectorAll('.pagination a, .nav-links a, .page-numbers a');
		links.forEach(link => {
			link.addEventListener('click', (e) => {
				e.preventDefault();
				const page = parsePageFromLink(link) || 1;
				fetchProperties(page, true);
			});
		});
	};

	// Form change listener (checkboxes, number inputs)
	filterForm.addEventListener('change', (e) => {
		if (e.target.id !== 'filter-search-input') {
			fetchProperties(1, true);
		}
	});

	// Form submit listener
	filterForm.addEventListener('submit', (e) => {
		e.preventDefault();
		fetchProperties(1, true);
	});

	// Debounced search input listener
	if (searchInput) {
		searchInput.addEventListener('input', () => {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(() => {
				fetchProperties(1, true);
			}, 400);
		});
	}

	// Browser back/forward navigation support
	window.addEventListener('popstate', (e) => {
		const paged = (e.state && e.state.paged) ? parseInt(e.state.paged) : (new URL(window.location)).searchParams.get('paged');
		const page = parseInt(paged) || 1;
		fetchProperties(page, false);
	});

	// 9. Scroll Mask & Drag-to-Scroll for Tags / Chips
	const updateTagsScrollMask = (el) => {
		if (!el) return;
		const scrollLeft = el.scrollLeft;
		const scrollWidth = el.scrollWidth;
		const clientWidth = el.clientWidth;
		const maxScroll = scrollWidth - clientWidth;

		if (maxScroll <= 2) {
			if (el.dataset.scrollState !== 'none') el.dataset.scrollState = 'none';
			return;
		}

		let state = 'middle';
		if (scrollLeft <= 3) {
			state = 'start';
		} else if (scrollLeft >= maxScroll - 3) {
			state = 'end';
		}

		if (el.dataset.scrollState !== state) {
			el.dataset.scrollState = state;
		}
	};

	const initFilterTagsScroll = () => {
		const tagContainers = filterForm.querySelectorAll('.post--tags, .menu-flex--operation, .menu-flex--type');
		tagContainers.forEach(container => {
			const recalculate = () => updateTagsScrollMask(container);
			recalculate();
			setTimeout(recalculate, 100);
			setTimeout(recalculate, 400);

			container.addEventListener('scroll', recalculate, { passive: true });

			// Horizontal wheel scrolling
			container.addEventListener('wheel', (e) => {
				if (e.deltaY !== 0 && container.scrollWidth > container.clientWidth) {
					e.preventDefault();
					container.scrollLeft += e.deltaY;
					recalculate();
				}
			}, { passive: false });

			// Mouse drag-to-scroll
			let isDown = false;
			let startX = 0;
			let initialScroll = 0;
			let hasDragged = false;

			container.addEventListener('mousedown', (e) => {
				isDown = true;
				hasDragged = false;
				startX = e.pageX;
				initialScroll = container.scrollLeft;
			});

			document.addEventListener('mouseup', () => {
				if (isDown) {
					isDown = false;
				}
			});

			document.addEventListener('mousemove', (e) => {
				if (!isDown) return;
				const walk = (e.pageX - startX) * 1.5;
				if (Math.abs(walk) > 4) {
					hasDragged = true;
					container.scrollLeft = initialScroll - walk;
					recalculate();
				}
			});

			container.addEventListener('click', (e) => {
				if (hasDragged) {
					e.preventDefault();
					e.stopPropagation();
					hasDragged = false;
				}
			}, true);
		});
	};

	window.addEventListener('resize', () => {
		const tagContainers = filterForm.querySelectorAll('.post--tags, .menu-flex--operation, .menu-flex--type');
		tagContainers.forEach(updateTagsScrollMask);
	}, { passive: true });

	// 10. Modern Custom Select Component Builder
	const escapeHtml = (str) => {
		const div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	};

	const initModernCustomSelects = () => {
		const selects = filterForm.querySelectorAll('.filter-location-select, .nc-modern-select');
		selects.forEach(select => {
			if (select.dataset.customSelectInit) return;
			select.dataset.customSelectInit = 'true';
			select.style.display = 'none';

			const wrapper = select.closest('.filter-select-wrapper') || select.parentElement;

			const customContainer = document.createElement('div');
			customContainer.className = 'nc-custom-select';

			// Get current selected option
			const selectedOption = select.options[select.selectedIndex] || select.options[0];
			const initialText = selectedOption ? selectedOption.text : 'Seleccionar...';

			const hasIconInWrapper = wrapper.closest('.filter-location-field') || (wrapper.parentElement && wrapper.parentElement.querySelector(':scope > svg'));
			const iconHtml = hasIconInWrapper ? '' : `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c4-4.5 7-8.5 7-12A7 7 0 0 0 5 9c0 3.5 3 7.5 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>`;

			const trigger = document.createElement('button');
			trigger.type = 'button';
			trigger.className = 'nc-custom-select-trigger';
			trigger.setAttribute('aria-haspopup', 'listbox');
			trigger.setAttribute('aria-expanded', 'false');
			trigger.innerHTML = `
				<div class="nc-custom-select-label-wrap">
					${iconHtml}
					<span class="nc-custom-select-label">${escapeHtml(initialText)}</span>
				</div>
				<span class="nc-custom-select-arrow">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
				</span>
			`;

			const dropdown = document.createElement('div');
			dropdown.className = 'nc-custom-select-dropdown';

			// Search Box inside dropdown if multiple options
			let searchInputEl = null;
			if (select.options.length > 6) {
				const searchWrap = document.createElement('div');
				searchWrap.className = 'nc-custom-select-search-wrap';
				searchWrap.innerHTML = `<input type="text" class="nc-custom-select-search" placeholder="Buscar ubicación..." autocomplete="off">`;
				dropdown.appendChild(searchWrap);
				searchInputEl = searchWrap.querySelector('input');
			}

			const optionsContainer = document.createElement('div');
			optionsContainer.className = 'nc-custom-select-options';

			const createOptionElement = (option, parent) => {
				const item = document.createElement('div');
				item.className = 'nc-custom-select-option' + (option.selected ? ' is-selected' : '');
				item.dataset.value = option.value;
				item.textContent = option.text;

				item.addEventListener('click', (e) => {
					e.stopPropagation();
					select.value = option.value;
					trigger.querySelector('.nc-custom-select-label').textContent = option.text;

					optionsContainer.querySelectorAll('.nc-custom-select-option').forEach(el => el.classList.remove('is-selected'));
					item.classList.add('is-selected');

					closeDropdown();
					select.dispatchEvent(new Event('change', { bubbles: true }));
				});

				parent.appendChild(item);
			};

			// Build options list from optgroups and options
			Array.from(select.children).forEach(child => {
				if (child.tagName === 'OPTGROUP') {
					const groupHeader = document.createElement('div');
					groupHeader.className = 'nc-custom-select-group-header';
					groupHeader.textContent = child.label;
					optionsContainer.appendChild(groupHeader);

					Array.from(child.children).forEach(opt => {
						createOptionElement(opt, optionsContainer);
					});
				} else if (child.tagName === 'OPTION') {
					createOptionElement(child, optionsContainer);
				}
			});

			dropdown.appendChild(optionsContainer);
			customContainer.appendChild(trigger);
			customContainer.appendChild(dropdown);
			wrapper.appendChild(customContainer);

			// Search filtering inside dropdown
			if (searchInputEl) {
				searchInputEl.addEventListener('input', (e) => {
					const query = e.target.value.toLowerCase().trim();
					let visibleCount = 0;
					optionsContainer.querySelectorAll('.nc-custom-select-option').forEach(opt => {
						const text = opt.textContent.toLowerCase();
						const isVisible = text.includes(query);
						opt.style.display = isVisible ? 'flex' : 'none';
						if (isVisible) visibleCount++;
					});

					// Hide/show optgroup headers
					optionsContainer.querySelectorAll('.nc-custom-select-group-header').forEach(header => {
						let next = header.nextElementSibling;
						let hasVisibleChild = false;
						while (next && next.classList.contains('nc-custom-select-option')) {
							if (next.style.display !== 'none') {
								hasVisibleChild = true;
								break;
							}
							next = next.nextElementSibling;
						}
						header.style.display = hasVisibleChild ? 'block' : 'none';
					});

					let noResults = optionsContainer.querySelector('.nc-custom-select-no-results');
					if (visibleCount === 0) {
						if (!noResults) {
							noResults = document.createElement('div');
							noResults.className = 'nc-custom-select-no-results';
							noResults.textContent = 'No se encontraron resultados';
							optionsContainer.appendChild(noResults);
						}
						noResults.style.display = 'block';
					} else if (noResults) {
						noResults.style.display = 'none';
					}
				});
			}

			// Open / Close Dropdown
			const openDropdown = () => {
				document.querySelectorAll('.nc-custom-select.is-open').forEach(el => {
					if (el !== customContainer) el.classList.remove('is-open');
				});
				customContainer.classList.add('is-open');
				trigger.setAttribute('aria-expanded', 'true');
				if (searchInputEl) {
					setTimeout(() => searchInputEl.focus(), 50);
				}
			};

			const closeDropdown = () => {
				customContainer.classList.remove('is-open');
				trigger.setAttribute('aria-expanded', 'false');
				if (searchInputEl) {
					searchInputEl.value = '';
					searchInputEl.dispatchEvent(new Event('input'));
				}
			};

			trigger.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				if (customContainer.classList.contains('is-open')) {
					closeDropdown();
				} else {
					openDropdown();
				}
			});

			document.addEventListener('click', (e) => {
				if (!customContainer.contains(e.target)) {
					closeDropdown();
				}
			});

			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape' && customContainer.classList.contains('is-open')) {
					closeDropdown();
				}
			});

			// Reset event listener for form clear
			select.addEventListener('reset-custom', () => {
				const defaultOption = select.options[0];
				if (defaultOption) {
					trigger.querySelector('.nc-custom-select-label').textContent = defaultOption.text;
					optionsContainer.querySelectorAll('.nc-custom-select-option').forEach((el, idx) => {
						el.classList.toggle('is-selected', idx === 0);
					});
				}
			});
		});
	};

	// Initial binding
	bindPaginationLinks();
	updateActiveFilterCount();
	initModernCustomSelects();
	initFilterTagsScroll();
});
