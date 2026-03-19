import './bootstrap';
import Alpine from 'alpinejs';
import Swiper from 'swiper/bundle';
import { Notyf } from 'notyf';
import TomSelect from 'tom-select';
import 'swiper/css/bundle';
import 'notyf/notyf.min.css';
import 'tom-select/dist/css/tom-select.css';

window.Alpine = Alpine;
window.Swiper = Swiper;
window.Notyf = Notyf;
window.TomSelect = TomSelect;
window.notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'bottom' },
    dismissible: true,
    types: [
        {
            type: 'success',
            background: '#0284c7',
        },
    ],
});
Alpine.start();

function smoothScrollTo(targetY, duration = 400) {
    const startY = window.pageYOffset || document.documentElement.scrollTop;
    const distance = targetY - startY;
    const startTime = performance.now();

    const easeInOutQuad = (t) => (t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t);

    function step(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = easeInOutQuad(progress);
        const nextY = startY + distance * eased;

        window.scrollTo(0, nextY);

        if (elapsed < duration) {
            requestAnimationFrame(step);
        }
    }

    requestAnimationFrame(step);
}

function initTomSelects() {
    document.querySelectorAll('select[data-enhance="tom-select"]').forEach((el) => {
        if (el.tomselect) return;
        const opts = {
            allowEmptyOption: !!el.querySelector('option[value=""]'),
            dropdownParent: 'body',
            onChange(value) {
                if (el.dataset.redirectOnChange !== undefined && value) {
                    window.location.href = value;
                } else if (el.dataset.submitOnChange !== undefined && el.form) {
                    el.form.submit();
                }
            },
        };
        el.tomselect = new TomSelect(el, opts);
    });
}

function initLightbox() {
    let overlay = null;
    let overlayImg = null;
    let state = { scale: 1, rotate: 0 };
    let currentGroup = [];
    let currentIndex = 0;

    function ensureOverlay() {
        if (overlay) return;
        overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[1000] bg-black/80 flex items-center justify-center px-4';
        overlay.innerHTML = `
            <button type="button" data-action="prev" class="hidden md:flex absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 w-11 h-11 sm:w-12 sm:h-12 lg:w-14 lg:h-14 items-center justify-center rounded-full bg-black/70 text-white hover:bg-black shadow-xl border border-white/40 z-[1010] text-2xl">
                ‹
            </button>
            <button type="button" data-action="next" class="hidden md:flex absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 w-11 h-11 sm:w-12 sm:h-12 lg:w-14 lg:h-14 items-center justify-center rounded-full bg-black/70 text-white hover:bg-black shadow-xl border border-white/40 z-[1010] text-2xl">
                ›
            </button>
            <div class="relative max-h-[90vh] max-w-full w-full sm:w-auto flex items-center justify-center">
                <img class="max-h-[90vh] max-w-full shadow-2xl border border-white/10 transition-transform duration-150 ease-out select-none bg-black/10" alt="">
            </div>
            <div class="pointer-events-none absolute inset-x-0 bottom-4 flex items-center justify-center px-4 sm:px-6">
                <div class="pointer-events-auto inline-flex items-center gap-2 sm:gap-3 text-[11px] sm:text-xs text-stone-100">
                    <button
                        type="button"
                        data-action="zoom-out"
                        class="w-9 h-9 sm:w-11 sm:h-11 flex items-center justify-center rounded-full bg-black/90 hover:bg-black text-white shadow-lg"
                        title="Уменьшить"
                    >
                        −
                    </button>
                    <button
                        type="button"
                        data-action="zoom-in"
                        class="w-9 h-9 sm:w-11 sm:h-11 flex items-center justify-center rounded-full bg-black/90 hover:bg-black text-white shadow-lg"
                        title="Увеличить"
                    >
                        +
                    </button>
                    <button
                        type="button"
                        data-action="rotate"
                        class="w-9 h-9 sm:w-11 sm:h-11 flex items-center justify-center rounded-full bg-black/90 hover:bg-black text-white text-sm shadow-lg"
                        title="Повернуть"
                    >
                        ⟲
                    </button>
                    <button
                        type="button"
                        data-action="reset"
                        class="hidden sm:inline-flex h-9 sm:h-11 items-center justify-center rounded-full bg-black/90 hover:bg-black text-white px-3 text-[11px] shadow-lg"
                        title="Сбросить"
                    >
                        Сброс
                    </button>
                    <div class="mx-1 h-6 w-px bg-white/40 hidden sm:block"></div>
                    <div class="min-w-[40px] text-center" data-lightbox-counter></div>
                    <button
                        type="button"
                        data-action="close"
                        class="w-9 h-9 sm:w-11 sm:h-11 flex items-center justify-center rounded-full bg-black/90 hover:bg-black text-white shadow-lg"
                        title="Закрыть"
                    >
                        ✕
                    </button>
                </div>
            </div>
        `;
        overlayImg = overlay.querySelector('img');

        overlay.addEventListener('click', () => {
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay?.remove(), 200);
        });

        overlay.querySelectorAll('button[data-action]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const action = btn.getAttribute('data-action');
                if (action === 'close') {
                    overlay.click();
                    return;
                }
                if (action === 'prev' || action === 'next') {
                    if (!currentGroup.length) return;
                    const delta = action === 'prev' ? -1 : 1;
                    currentIndex = (currentIndex + delta + currentGroup.length) % currentGroup.length;
                    const item = currentGroup[currentIndex];
                    if (!item) return;
                    state = { scale: 1, rotate: 0 };
                    overlayImg.src = item.src;
                    overlayImg.style.transform = 'scale(1) rotate(0deg)';
                    updateCounter();
                    return;
                }
                if (!overlayImg) return;

                if (action === 'zoom-in') {
                    state.scale = Math.min(state.scale + 0.25, 3);
                } else if (action === 'zoom-out') {
                    state.scale = Math.max(state.scale - 0.25, 0.5);
                } else if (action === 'rotate') {
                    state.rotate = (state.rotate + 90) % 360;
                } else if (action === 'reset') {
                    state = { scale: 1, rotate: 0 };
                }

                overlayImg.style.transform = `scale(${state.scale}) rotate(${state.rotate}deg)`;
                overlayImg.style.cursor = state.scale > 1 ? 'grab' : 'default';
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && overlay?.parentNode) {
                overlay.click();
                return;
            }
            if (!overlay?.parentNode || !currentGroup.length) return;
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                const prevBtn = overlay.querySelector('button[data-action="prev"]');
                if (prevBtn) prevBtn.click();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                const nextBtn = overlay.querySelector('button[data-action="next"]');
                if (nextBtn) nextBtn.click();
            }
        });
    }

    function updateCounter() {
        const counter = overlay?.querySelector('[data-lightbox-counter]');
        if (!counter) return;
        if (!currentGroup.length) {
            counter.textContent = '';
            return;
        }
        counter.textContent = `${currentIndex + 1} / ${currentGroup.length}`;
    }

    function collectGroup(link, href) {
        const groupName = link.dataset.lightboxGroup;
        if (!groupName) {
            currentGroup = [{ src: href, el: link }];
            currentIndex = 0;
            return;
        }
        const all = Array.from(
            document.querySelectorAll(`[data-lightbox="image"][data-lightbox-group="${groupName}"]`),
        );
        currentGroup = all
            .map((el) => {
                const url = el.getAttribute('href') || el.dataset.src;
                if (!url) return null;
                return { src: url, el };
            })
            .filter(Boolean);
        if (!currentGroup.length) {
            currentGroup = [{ src: href, el: link }];
        }
        currentIndex = currentGroup.findIndex((item) => item && item.el === link);
        if (currentIndex === -1) currentIndex = 0;
    }

    document.body.addEventListener('click', (e) => {
        const clicked = e.target;
        if (!(clicked instanceof Element)) return;

        const link = clicked.closest('[data-lightbox="image"]');
        if (!link) return;

        const href = link.getAttribute('href') || link.dataset.src;
        if (!href) return;

        e.preventDefault();
        state = { scale: 1, rotate: 0 };
        collectGroup(link, href);
        ensureOverlay();
        overlayImg.src = href;
        overlayImg.style.transform = 'scale(1) rotate(0deg)';
        updateCounter();

        const arrows = overlay.querySelectorAll('button[data-action="prev"], button[data-action="next"]');
        if (currentGroup.length > 1) {
            arrows.forEach((btn) => btn.classList.remove('hidden'));
        } else {
            arrows.forEach((btn) => btn.classList.add('hidden'));
        }

        overlay.classList.remove('opacity-0');
        document.body.appendChild(overlay);
    });
}

function initAdminImagePreview() {
    document.querySelectorAll('[data-image-preview]').forEach((wrap) => {
        const input = wrap.querySelector('input[type="file"]');
        const img = wrap.querySelector('img[data-image-preview-target]');
        const link = wrap.querySelector('a[data-lightbox="image"]');
        if (!input || !img || !link) return;

        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            img.src = url;
            link.href = url;
            wrap.classList.remove('hidden');
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTomSelects();
    initLightbox();
    initAdminImagePreview();

    // Smooth scroll to reviews (and other hash links) with offset support
    document.body.addEventListener('click', (e) => {
        const clicked = e.target;
        if (!(clicked instanceof Element)) return;

        const target = clicked.closest('a[href^="#"]');
        if (!target) return;

        const hash = target.getAttribute('href');
        if (!hash || hash === '#') return;

        const el = document.querySelector(hash);
        if (!el) return;

        e.preventDefault();

        // Respect Tailwind offset via scroll-mt-* if present
        const rect = el.getBoundingClientRect();
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        const computedStyle = getComputedStyle(el);
        const scrollMarginTop = parseFloat(computedStyle.scrollMarginTop || '0');
        const targetY = rect.top + currentScroll - scrollMarginTop;

        smoothScrollTo(targetY, 450);
    });

    document.body.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form.matches('[data-ajax-cart-add]')) return;

        e.preventDefault();
        const btn = form.querySelector('.cart-add-btn') || form.querySelector('button[type="submit"]');
        const cartUrl = form.dataset.cartUrl || '/cart';
        const originalText = btn?.innerHTML;

        if (btn) {
            btn.disabled = true;
            if (btn.innerHTML.includes('В корзину')) btn.innerHTML = '<span class="animate-pulse">...</span>';
        }

        try {
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                },
            });

            const data = await res.json().catch(() => ({}));

            if (res.status === 429) {
                window.notyf.error('Слишком много запросов. Подождите минуту.');
                if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
                return;
            }

            if (res.ok && data.success) {
                document.querySelectorAll('[data-cart-count]').forEach((el) => {
                    el.textContent = data.cartCount ?? 0;
                    el.classList.toggle('!hidden', !(data.cartCount > 0));
                });
                window.notyf.success(data.message || 'Товар добавлен в корзину');

                const tpl = document.getElementById('cart-in-button-tpl');
                if (tpl?.content) {
                    const link = tpl.content.cloneNode(true);
                    link.querySelector('a').href = cartUrl;
                    form.replaceWith(link);
                }
            } else {
                const msg = (data.errors && data.errors.quantity && data.errors.quantity[0]) || data.message || 'Ошибка добавления в корзину';
                window.notyf.error(msg);
                if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
            }
        } catch (err) {
            window.notyf.error('Ошибка соединения');
            if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
        }
    });

    // AJAX submit for cart update/remove/clear
    document.body.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form.matches('[data-ajax-cart-update],[data-ajax-cart-remove],[data-ajax-cart-clear]')) return;

        e.preventDefault();

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const btn = form.querySelector('button[type="submit"]');
        const confirmMessage = form.dataset.confirmMessage;

        if (btn) btn.disabled = true;

        const doAjax = async () => {
            try {
                const formData = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                    },
                });

                const data = await res.json().catch(() => ({}));

                if (res.status === 429) {
                    window.notyf.error('Слишком много запросов. Подождите минуту.');
                    return;
                }

                if (res.ok && data?.result && data?.html) {
                    const container = document.getElementById('cart-root');
                    if (container) {
                        container.outerHTML = data.html;
                    } else {
                        document.body.insertAdjacentHTML('beforeend', data.html);
                    }

                    document.querySelectorAll('[data-cart-count]').forEach((el) => {
                        el.textContent = data.cartCount ?? 0;
                        el.classList.toggle('!hidden', !(data.cartCount > 0));
                    });

                    if (data?.message) window.notyf.success(data.message);
                } else {
                    window.notyf.error(data?.message || 'Ошибка обновления корзины');
                }
            } catch (err) {
                window.notyf.error('Ошибка соединения');
            } finally {
                if (btn) btn.disabled = false;
            }
        };

        if (confirmMessage && typeof window.ulplayConfirm === 'function' && (form.matches('[data-ajax-cart-remove],[data-ajax-cart-clear]'))) {
            window.ulplayConfirm(confirmMessage, (ok) => {
                if (!ok) {
                    if (btn) btn.disabled = false;
                    return;
                }
                doAjax();
            });
            return;
        }

        await doAjax();
    });

    // AJAX submit for comments on news page
    document.body.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form.matches('[data-ajax-comments-store]')) return;

        e.preventDefault();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const btn = form.querySelector('button[type="submit"]');

        // Clear previous inline errors
        form.querySelectorAll('[data-ajax-comments-error]').forEach((el) => {
            el.textContent = '';
            el.classList.add('hidden');
        });

        if (btn) btn.disabled = true;

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                },
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok && data?.result && data?.html) {
                const container = document.getElementById('comments');
                if (container) {
                    container.outerHTML = data.html;
                } else {
                    document.body.insertAdjacentHTML('beforeend', data.html);
                }

                const newContainer = document.getElementById('comments');
                if (window.Alpine?.initTree && newContainer) window.Alpine.initTree(newContainer);
                if (btn) btn.disabled = false;
                return;
            }

            if (res.status === 422 && data?.errors) {
                const errBody = data.errors?.body?.[0] || '';
                const errEl = form.querySelector('[data-ajax-comments-error="body"]');
                if (errEl) {
                    errEl.textContent = errBody;
                    errEl.classList.remove('hidden');
                }
            } else {
                window.notyf.error(data?.message || 'Ошибка отправки комментария');
            }
        } catch (err) {
            window.notyf.error('Ошибка соединения');
        } finally {
            if (btn) btn.disabled = false;
        }
    });

    // AJAX submit for marking comments as helpful
    document.body.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form.matches('[data-ajax-comment-helpful]')) return;

        e.preventDefault();

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const btn = form.querySelector('button[type="submit"]');
        const commentId = form.dataset.commentHelpfulCommentId;

        if (btn) btn.disabled = true;

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                },
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok && data?.result) {
                const countEl = commentId
                    ? document.querySelector(`[data-comment-helpful-count="${commentId}"]`)
                    : null;

                if (countEl && typeof data.count !== 'undefined') {
                    countEl.textContent = data.count ?? 0;
                }

                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    const outlineIcon = btn.querySelector('.comment-helpful-icon-outline');
                    const filledIcon = btn.querySelector('.comment-helpful-icon-filled');
                    outlineIcon && outlineIcon.classList.add('hidden');
                    filledIcon && filledIcon.classList.remove('hidden');
                }
                window.notyf.success(data?.message || 'Отмечено как полезное');
            } else {
                window.notyf.error(data?.message || 'Ошибка отметки');
                if (btn) btn.disabled = false;
            }
        } catch (err) {
            window.notyf.error('Ошибка соединения');
            if (btn) btn.disabled = false;
        }
    });

    // AJAX submit for reviews (product/service pages)
    document.body.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form.matches('[data-ajax-review-store]')) return;

        e.preventDefault();

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const btn = form.querySelector('button[type="submit"]');

        // Clear previous inline errors
        form.querySelectorAll('[data-ajax-review-error]').forEach((el) => {
            el.textContent = '';
            el.classList.add('hidden');
        });

        if (btn) btn.disabled = true;

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                },
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok && data?.result && data?.html) {
                const container = document.getElementById('reviews');
                if (container) {
                    container.outerHTML = data.html;
                } else {
                    document.body.insertAdjacentHTML('beforeend', data.html);
                }

                const newContainer = document.getElementById('reviews');
                if (window.Alpine?.initTree && newContainer) window.Alpine.initTree(newContainer);
                window.notyf.success(data?.message || 'Отзыв добавлен');
                if (btn) btn.disabled = false;
                return;
            }

            if (res.status === 422 && data?.errors) {
                const fieldToKey = {
                    rating: 'rating',
                    body: 'body',
                    images: 'images',
                };

                Object.keys(fieldToKey).forEach((field) => {
                    const errEl = form.querySelector(`[data-ajax-review-error="${field}"]`);
                    const errMsg = data.errors?.[field]?.[0] || '';
                    if (errEl) {
                        if (errMsg) {
                            errEl.textContent = errMsg;
                            errEl.classList.remove('hidden');
                        } else {
                            errEl.textContent = '';
                            errEl.classList.add('hidden');
                        }
                    }
                });
            } else {
                window.notyf.error(data?.message || 'Ошибка отправки отзыва');
            }
        } catch (err) {
            window.notyf.error('Ошибка соединения');
        } finally {
            if (btn) btn.disabled = false;
        }
    });
});

// Global loader: скрываем после полной загрузки страницы
window.addEventListener('load', () => {
    const loader = document.querySelector('.ulplay-loader');
    if (!loader) return;
    setTimeout(() => {
        loader.style.display = 'none';
    }, 700);
});

// Fix dynamic "star fill" widths without inline Blade in `style=""` (to avoid IDE CSS parser errors)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-star-fill]').forEach((el) => {
        if (!(el instanceof HTMLElement)) return;
        const fill = el.getAttribute('data-star-fill');
        if (!fill) return;

        el.style.width = `${fill}%`;

        const inner = el.querySelector('[data-inner-star-fill]');
        if (inner instanceof HTMLElement) {
            const innerFill = el.getAttribute('data-inner-star-fill')
                ?? inner.getAttribute('data-inner-star-fill');
            if (innerFill) inner.style.width = `${innerFill}%`;
        }
    });
});