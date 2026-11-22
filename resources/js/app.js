const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);

        return;
    }

    callback();
};

ready(() => {
    document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const panel = dropdown.querySelector('[data-dropdown-panel]');

        if (! trigger || ! panel) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = ! panel.classList.contains('hidden');

            document.querySelectorAll('[data-dropdown-panel]').forEach((openPanel) => {
                openPanel.classList.add('hidden');
            });

            panel.classList.toggle('hidden', isOpen);
            trigger.setAttribute('aria-expanded', String(! isOpen));
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-dropdown-panel]').forEach((panel) => {
            panel.classList.add('hidden');
        });

        document.querySelectorAll('[data-dropdown-trigger]').forEach((trigger) => {
            trigger.setAttribute('aria-expanded', 'false');
        });
    });

    const mobileNav = document.querySelector('[data-mobile-nav]');
    const mobileToggle = document.querySelector('[data-mobile-toggle]');
    const mobileClose = document.querySelector('[data-mobile-close]');

    const setMobileNav = (open) => {
        if (! mobileNav) {
            return;
        }

        mobileNav.classList.toggle('hidden', ! open);
        document.body.classList.toggle('overflow-hidden', open);
        mobileToggle?.setAttribute('aria-expanded', String(open));
    };

    mobileToggle?.addEventListener('click', () => setMobileNav(true));
    mobileClose?.addEventListener('click', () => setMobileNav(false));
    mobileNav?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setMobileNav(false));
    });

    const filterDrawer = document.querySelector('[data-filter-drawer]');
    const filterOpen = document.querySelector('[data-filter-open]');
    const filterClose = document.querySelector('[data-filter-close]');

    const setFilterDrawer = (open) => {
        if (! filterDrawer) {
            return;
        }

        filterDrawer.classList.toggle('hidden', ! open);
        document.body.classList.toggle('overflow-hidden', open);
    };

    filterOpen?.addEventListener('click', () => setFilterDrawer(true));
    filterClose?.addEventListener('click', () => setFilterDrawer(false));

    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copy ?? '');
                const original = button.textContent;
                button.textContent = 'Đã chép';
                window.setTimeout(() => {
                    button.textContent = original;
                }, 1500);
            } catch {
                // Clipboard blocked: the value is still visible on screen.
            }
        });
    });

    const withdrawMethod = document.querySelector('[data-withdraw-method]');
    const bankFields = document.querySelector('[data-bank-fields]');

    withdrawMethod?.addEventListener('change', () => {
        bankFields?.classList.toggle('hidden', withdrawMethod.value !== 'bank');
    });

    const depositBox = document.querySelector('[data-deposit]');

    if (depositBox && depositBox.dataset.open === '1') {
        const statusUrl = depositBox.dataset.statusUrl;
        const startedAt = Date.now();
        const maxPollingMs = 30 * 60 * 1000;

        const poll = async () => {
            try {
                const response = await fetch(statusUrl, { headers: { Accept: 'application/json' } });

                if (response.ok) {
                    const data = await response.json();
                    const statusText = depositBox.querySelector('[data-deposit-status]');
                    const balance = document.querySelector('[data-wallet-balance]');

                    if (balance && data.balance) {
                        balance.textContent = data.balance;
                    }

                    if (! data.open) {
                        if (statusText) {
                            statusText.textContent = data.successful ? `✓ ${data.label} — đã cộng vào số dư` : data.label;
                        }

                        const statusBox = depositBox.querySelector('[data-deposit-status-box]');
                        statusBox?.classList.toggle('border-emerald-500/40', data.successful);
                        statusBox?.classList.toggle('bg-emerald-500/10', data.successful);
                        depositBox.querySelector('[data-deposit-hint]')?.remove();
                        depositBox.querySelector('[data-deposit-qr]')?.classList.add('opacity-30');

                        return;
                    }

                    if (statusText && data.status === 'verifying') {
                        statusText.textContent = data.label;
                        const hint = depositBox.querySelector('[data-deposit-hint]');

                        if (hint) {
                            hint.textContent = 'Đã nhận chuyển khoản nhưng cần admin kiểm tra (sai số tiền hoặc nội dung).';
                        }
                    }
                }
            } catch {
                // Network hiccup: try again on the next tick.
            }

            if (Date.now() - startedAt < maxPollingMs) {
                window.setTimeout(poll, 5000);
            }
        };

        window.setTimeout(poll, 5000);
    }

    document.querySelectorAll('[data-chart]').forEach((chart) => {
        const box = chart.querySelector('[data-chart-tooltip-box]');
        const container = box?.parentElement;

        if (! box || ! container) {
            return;
        }

        const show = (target) => {
            box.textContent = target.dataset.chartTooltip ?? '';
            box.classList.remove('hidden');

            const containerRect = container.getBoundingClientRect();
            const targetRect = target.getBoundingClientRect();
            const center = targetRect.left - containerRect.left + targetRect.width / 2;
            const half = box.offsetWidth / 2;
            const left = Math.min(Math.max(center - half, 0), containerRect.width - box.offsetWidth);

            box.style.left = `${left}px`;
            box.style.top = '-8px';
        };

        const hide = () => box.classList.add('hidden');

        chart.querySelectorAll('[data-chart-tooltip]').forEach((target) => {
            target.addEventListener('mouseenter', () => show(target));
            target.addEventListener('focus', () => show(target));
            target.addEventListener('mouseleave', hide);
            target.addEventListener('blur', hide);
        });
    });

    const sitePopup = document.querySelector('[data-site-popup]');

    if (sitePopup) {
        const storageKey = `dismissed:${sitePopup.dataset.popupKey}`;
        const minutes = Number(sitePopup.dataset.popupMinutes) || 60;
        let dismissedUntil = 0;

        try {
            dismissedUntil = Number(window.localStorage.getItem(storageKey)) || 0;
        } catch {
            // Storage blocked (private mode): show the popup every time.
        }

        const closePopup = () => {
            sitePopup.classList.add('hidden');
            sitePopup.classList.remove('grid');
            document.body.classList.remove('overflow-hidden');
            sitePopup.querySelector('[data-site-popup-video]')?.pause();

            try {
                window.localStorage.setItem(storageKey, String(Date.now() + minutes * 60 * 1000));
            } catch {
                // Ignore storage errors.
            }

            document.removeEventListener('keydown', onKeydown);
        };

        const onKeydown = (event) => {
            if (event.key === 'Escape') {
                closePopup();
            }
        };

        if (Date.now() >= dismissedUntil) {
            sitePopup.classList.remove('hidden');
            sitePopup.classList.add('grid');
            document.body.classList.add('overflow-hidden');
            document.addEventListener('keydown', onKeydown);
            sitePopup.querySelector('[data-site-popup-close]')?.focus();
        }

        sitePopup.querySelectorAll('[data-site-popup-close]').forEach((button) => {
            button.addEventListener('click', closePopup);
        });

        sitePopup.addEventListener('click', (event) => {
            if (event.target === sitePopup) {
                closePopup();
            }
        });
    }

    const transactionList = document.querySelector('[data-recent-transactions]');

    if (transactionList) {
        const endpoint = transactionList.getAttribute('data-endpoint');
        const emptyState = document.querySelector('[data-recent-empty]');

        const renderTransactions = (items) => {
            if (! Array.isArray(items) || items.length === 0) {
                transactionList.replaceChildren();
                emptyState?.classList.remove('hidden');

                return;
            }

            emptyState?.classList.add('hidden');
            transactionList.replaceChildren();

            items.forEach((item) => {
                const row = document.createElement('article');
                row.className = 'grid grid-cols-2 gap-3 rounded-2xl border border-line bg-surface p-4 md:grid-cols-6 md:items-center';

                const fields = [
                    ['Mã', item.code ?? '', 'font-medium text-ink', ''],
                    ['Tài khoản / sản phẩm', item.product_name ?? '', 'font-medium text-ink', 'md:col-span-2'],
                    ['Khách hàng', item.masked_phone ?? '—', 'font-medium text-ink', ''],
                    ['Giá trị', item.amount ?? '', 'font-display font-semibold text-accent', ''],
                    ['Trạng thái', [item.status, item.completed_at].filter(Boolean).join(' · '), 'text-sm text-ink', ''],
                ];

                fields.forEach(([label, value, valueClass, wrapperClass]) => {
                    const cell = document.createElement('div');

                    if (wrapperClass) {
                        cell.className = wrapperClass;
                    }

                    const caption = document.createElement('p');
                    caption.className = 'text-xs uppercase tracking-wide text-muted';
                    caption.textContent = label;

                    const content = document.createElement('p');
                    content.className = valueClass;
                    content.textContent = value;

                    cell.append(caption, content);
                    row.append(cell);
                });

                transactionList.append(row);
            });
        };

        const refreshTransactions = async () => {
            if (! endpoint) {
                return;
            }

            try {
                const response = await fetch(endpoint, {
                    headers: { Accept: 'application/json' },
                });

                if (! response.ok) {
                    return;
                }

                const payload = await response.json();
                renderTransactions(payload.data ?? []);
            } catch {
                // Keep the last successful server-rendered state.
            }
        };

        window.setInterval(refreshTransactions, 30000);
    }
});
