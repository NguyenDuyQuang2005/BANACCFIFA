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

    const formatNumber = (value) => Math.round(value).toLocaleString('vi-VN');

    const selectChip = (chips, selected) => {
        chips.forEach((chip) => {
            const isSelected = chip === selected;
            chip.setAttribute('aria-checked', String(isSelected));
            chip.classList.toggle('border-primary', isSelected);
            chip.classList.toggle('bg-primary/10', isSelected);
            chip.classList.toggle('text-primary', isSelected);
            chip.classList.toggle('border-line', ! isSelected);
            chip.classList.toggle('bg-canvas', ! isSelected);
            chip.classList.toggle('text-ink', ! isSelected);
        });
    };

    const taxBox = document.querySelector('[data-tax-calculator]');

    if (taxBox) {
        const rate = Number(taxBox.dataset.rate) || 0;
        const priceInput = taxBox.querySelector('[data-tax-price]');
        const perkChips = [...taxBox.querySelectorAll('[data-tax-perk]')];
        const couponChips = [...taxBox.querySelectorAll('[data-tax-coupon]')];
        let perk = perkChips.find((chip) => chip.getAttribute('aria-checked') === 'true');
        let coupon = couponChips.find((chip) => chip.getAttribute('aria-checked') === 'true');
        const out = (name) => taxBox.querySelector(`[data-tax-out="${name}"]`);

        const recalculate = () => {
            const price = Number((priceInput.value || '').replace(/\D/g, '')) || 0;
            const tax = Math.round(price * rate / 100);
            const perkRefund = Math.round(tax * (Number(perk?.dataset.taxPerk) || 0) / 100);
            const couponRefund = Math.round(tax * (Number(coupon?.dataset.taxCoupon) || 0) / 100);

            out('tax').textContent = `-${formatNumber(tax)} BP`;
            perkChips.forEach((chip) => {
                const target = out(`perk-${chip.dataset.taxPerkKey}`);
                if (target) {
                    target.textContent = `${chip === perk ? formatNumber(perkRefund) : 0} BP`;
                }
            });
            out('coupon').textContent = `+${formatNumber(couponRefund)} BP`;
            out('net').textContent = `${formatNumber(price - tax + perkRefund + couponRefund)} BP`;
        };

        priceInput.addEventListener('input', () => {
            const digits = priceInput.value.replace(/\D/g, '').slice(0, 15);
            priceInput.value = digits ? Number(digits).toLocaleString('vi-VN') : '';
            recalculate();
        });
        perkChips.forEach((chip) => chip.addEventListener('click', () => {
            perk = chip;
            selectChip(perkChips, chip);
            recalculate();
        }));
        couponChips.forEach((chip) => chip.addEventListener('click', () => {
            coupon = chip;
            selectChip(couponChips, chip);
            recalculate();
        }));
    }

    const ovrBox = document.querySelector('[data-ovr-calculator]');

    if (ovrBox) {
        const config = JSON.parse(ovrBox.querySelector('[data-ovr-config]').textContent);
        const positionSelect = ovrBox.querySelector('[data-ovr-position]');
        const rows = ovrBox.querySelector('[data-ovr-rows]');
        const stats = {};
        const training = {};

        const recalculate = () => {
            const weights = config.positions[positionSelect.value].weights;
            let total = 0;
            let before = 0;
            let after = 0;

            Object.entries(weights).forEach(([stat, weight]) => {
                const value = Number(stats[stat]) || 0;
                total += weight;
                before += value * weight;
                after += (value + (training[stat] || 0)) * weight;
            });

            ovrBox.querySelector('[data-ovr-before]').textContent = (total ? before / total : 0).toFixed(2);
            ovrBox.querySelector('[data-ovr-after]').textContent = (total ? after / total : 0).toFixed(2);
            ovrBox.querySelector('[data-ovr-used]').textContent = String(Object.values(training).filter((points) => points > 0).length);
        };

        const render = () => {
            const weights = config.positions[positionSelect.value].weights;
            Object.keys(training).forEach((stat) => {
                if (! (stat in weights)) {
                    delete training[stat];
                }
            });
            rows.replaceChildren();

            Object.keys(weights).forEach((stat) => {
                const row = document.createElement('tr');
                row.className = 'border-t border-line';

                const name = document.createElement('td');
                name.className = 'px-3 py-2 font-medium';
                name.textContent = config.statNames[stat] ?? stat;

                const valueCell = document.createElement('td');
                valueCell.className = 'px-3 py-2 text-center';
                const input = document.createElement('input');
                input.type = 'number';
                input.min = '0';
                input.max = '200';
                input.value = stats[stat] ?? 0;
                input.className = 'w-24 rounded-lg border border-line bg-canvas px-2 py-2 text-center';
                input.setAttribute('aria-label', `OVR ${name.textContent}`);
                input.addEventListener('input', () => {
                    stats[stat] = Math.max(0, Math.min(200, Number(input.value) || 0));
                    recalculate();
                });
                valueCell.append(input);

                const trainCell = document.createElement('td');
                trainCell.className = 'px-3 py-2';
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-center justify-center gap-2';
                const counter = document.createElement('span');
                counter.className = 'w-6 text-center font-semibold';
                const minus = document.createElement('button');
                const plus = document.createElement('button');

                const refresh = () => {
                    const points = training[stat] || 0;
                    const used = Object.values(training).filter((value) => value > 0).length;
                    counter.textContent = String(points);
                    counter.classList.toggle('text-primary', points > 0);
                    minus.disabled = points <= 0;
                    plus.disabled = points >= config.maxPerStat || (points === 0 && used >= config.maxTrained);
                };

                [[minus, '−', -1], [plus, '+', 1]].forEach(([button, label, step]) => {
                    button.type = 'button';
                    button.textContent = label;
                    button.className = 'h-8 w-8 rounded-lg border border-line bg-canvas font-bold disabled:opacity-30';
                    button.setAttribute('aria-label', `${step > 0 ? 'Tăng' : 'Giảm'} đào tạo ${name.textContent}`);
                    button.addEventListener('click', () => {
                        training[stat] = Math.max(0, Math.min(config.maxPerStat, (training[stat] || 0) + step));
                        rows.dispatchEvent(new Event('training-change'));
                        recalculate();
                    });
                });
                rows.addEventListener('training-change', refresh);
                refresh();

                wrapper.append(minus, counter, plus);
                trainCell.append(wrapper);
                row.append(name, valueCell, trainCell);
                rows.append(row);
            });

            recalculate();
        };

        positionSelect.addEventListener('change', render);
        render();
    }

    const skillLookup = document.querySelector('[data-skill-lookup]');

    if (skillLookup) {
        const search = skillLookup.querySelector('[data-skill-search]');
        const level = skillLookup.querySelector('[data-skill-level]');
        const cards = [...skillLookup.querySelectorAll('[data-skill-card]')];
        const modeButtons = [...skillLookup.querySelectorAll('[data-skill-mode]')];

        const filter = () => {
            const term = (search?.value || '').trim().toLowerCase();
            let visible = 0;

            cards.forEach((card) => {
                const matches = (! term || card.dataset.name.includes(term)) && (! level?.value || card.dataset.level === level.value);
                card.classList.toggle('hidden', ! matches);
                visible += matches ? 1 : 0;
            });

            skillLookup.querySelector('[data-skill-empty]')?.classList.toggle('hidden', visible > 0);
        };

        search?.addEventListener('input', filter);
        level?.addEventListener('change', filter);
        modeButtons.forEach((button) => button.addEventListener('click', () => {
            selectChip(modeButtons, button);
            modeButtons.forEach((other) => other.classList.toggle('text-muted', other !== button));
            skillLookup.querySelectorAll('[data-skill-keys]').forEach((keys) => {
                keys.classList.toggle('hidden', keys.dataset.skillKeys !== button.dataset.skillMode);
            });
        }));
    }

    const resetClock = document.querySelector('[data-reset-clock]');
    const countdowns = [...document.querySelectorAll('[data-reset-countdown]')];

    if (resetClock || countdowns.length > 0) {
        const vietnamNow = () => {
            const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', {
                timeZone: 'Asia/Ho_Chi_Minh', hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23',
            }).formatToParts(new Date()).map((part) => [part.type, part.value]));

            return { hour: Number(parts.hour), minute: Number(parts.minute), second: Number(parts.second) };
        };

        const tick = () => {
            if (resetClock) {
                const { hour, minute, second } = vietnamNow();
                const pad = (value) => String(value).padStart(2, '0');
                const isOdd = hour % 2 === 1;
                const minutesLeft = 60 - minute - (second > 0 ? 1 : 0);

                resetClock.querySelector('[data-reset-clock-time]').textContent = `${pad(hour)}:${pad(minute)}:${pad(second)}`;
                resetClock.querySelector('[data-reset-clock-note]').textContent = `Đang là giờ ${isOdd ? 'Lẻ' : 'Chẵn'} phút ${pad(minute)} — còn ~${Math.max(minutesLeft, 0)} phút đến giờ ${isOdd ? 'Chẵn' : 'Lẻ'}`;
            }

            const now = Date.now() / 1000;
            countdowns.forEach((element) => {
                const secondsLeft = Number(element.dataset.resetCountdown) - now;
                element.textContent = secondsLeft > 0
                    ? `còn ${Math.floor(secondsLeft / 60)} phút ${Math.floor(secondsLeft % 60)} giây`
                    : 'đang reset — tải lại trang để xem lần kế tiếp';
            });
        };

        tick();
        window.setInterval(tick, 1000);
    }

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
