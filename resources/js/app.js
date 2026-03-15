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

document.addEventListener('DOMContentLoaded', () => {
    initTomSelects();
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
});