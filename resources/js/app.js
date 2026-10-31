import './admin-products';
const modal = document.querySelector('#auth-modal');

if (modal) {
    let opener = null;
    let pending = false;
    const errors = modal.querySelector('#auth-errors');
    const forms = [...modal.querySelectorAll('[data-auth-form]')];
    const tabs = [...modal.querySelectorAll('[data-auth-tab]')];

    const clearErrors = () => {
        errors.replaceChildren();
        errors.hidden = true;
        modal.querySelectorAll('.field-error').forEach((element) => { element.textContent = ''; });
        modal.querySelectorAll('[aria-invalid]').forEach((element) => element.removeAttribute('aria-invalid'));
    };

    const clearPasswords = () => {
        modal.querySelectorAll('input[name^="password"]').forEach((input) => {
            input.value = '';
            input.type = 'password';
        });
        modal.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.setAttribute('aria-pressed', 'false');
            button.textContent = 'نمایش';
        });
    };

    const switchMode = (mode, preserveErrors = false) => {
        if (pending) return;
        if (mode !== modal.dataset.mode) {
            const previousPhone = modal.querySelector(`#${modal.dataset.mode}-phone`).value;
            modal.querySelector(`#${mode}-phone`).value = previousPhone;
            clearPasswords();
        }
        modal.dataset.mode = mode;
        tabs.forEach((tab) => {
            const selected = tab.dataset.authTab === mode;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
            modal.querySelector(`#panel-${tab.dataset.authTab}`).hidden = !selected;
        });
        if (!preserveErrors) clearErrors();
        if (modal.open) modal.querySelector(`#tab-${mode}`).focus();
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-auth-open]');
        if (!trigger) return;
        event.preventDefault();
        opener = trigger;
        switchMode(trigger.dataset.authOpen === 'register' ? 'register' : 'login');
        if (!modal.open) modal.showModal();
        modal.querySelector(`#panel-${modal.dataset.mode} input:not([type="hidden"])`).focus();
    });

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => switchMode(tab.dataset.authTab));
        tab.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key) || pending) return;
            event.preventDefault();
            const next = event.key === 'Home' ? tabs[0] : event.key === 'End' ? tabs[1] : tabs.find((candidate) => candidate !== tab);
            switchMode(next.dataset.authTab);
            next.focus();
        });
    });

    modal.querySelector('[data-auth-close]').addEventListener('click', () => modal.close());
    modal.addEventListener('click', (event) => {
        const bounds = modal.getBoundingClientRect();
        if (event.target === modal && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) modal.close();
    });
    modal.addEventListener('close', () => {
        if (modal.open) return;
        document.body.classList.remove('modal-open');
        clearPasswords();
        opener?.focus();
    });
    new MutationObserver(() => document.body.classList.toggle('modal-open', modal.open)).observe(modal, { attributes: true, attributeFilter: ['open'] });

    modal.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(visible));
            button.textContent = visible ? 'پنهان' : 'نمایش';
        });
    });

    const normalizePhone = (value) => value
        .replace(/[۰-۹]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)))
        .replace(/[٠-٩]/g, (digit) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)))
        .replace(/[\s()\-]+/g, '')
        .replace(/^(?:0098|\+98|98|0)(9[0-9]{9})$/, '+98$1');

    const showErrors = (messages, mode) => {
        clearErrors();
        Object.entries(messages).forEach(([field, values]) => {
            const message = Array.isArray(values) ? values[0] : values;
            const line = document.createElement('p');
            line.textContent = message;
            errors.append(line);
            const input = document.getElementById(`${mode}-${field}`);
            const fieldError = document.getElementById(`${mode}-${field}-error`);
            if (input) input.setAttribute('aria-invalid', 'true');
            if (fieldError) fieldError.textContent = message;
        });
        errors.hidden = false;
        if (modal.open) errors.focus();
    };

    forms.forEach((form) => {
        // Normalize before the browser's native constraint validation runs.
        form.querySelector('[name="phone"]').addEventListener('input', (event) => {
            event.target.value = normalizePhone(event.target.value);
        });
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (pending) return;
            const mode = form.dataset.authForm;
            const confirmation = form.querySelector('[name="password_confirmation"]');
            if (confirmation && confirmation.value !== form.elements.password.value) {
                showErrors({ password_confirmation: ['تکرار رمز عبور مطابقت ندارد.'] }, mode);
                return;
            }
            clearErrors();
            const payload = new FormData(form);
            pending = true;
            form.setAttribute('aria-busy', 'true');
            form.querySelector('[data-auth-progress]').hidden = false;
            const controls = [...modal.querySelectorAll('input, button:not([data-auth-close])')];
            controls.forEach((control) => { control.disabled = true; });
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: payload,
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': payload.get('_token') },
                });
                if (response.status === 419) {
                    showErrors({ session: ['نشست شما منقضی شده است. صفحه را تازه کنید و دوباره تلاش کنید.'] }, mode);
                    return;
                }
                if (response.status === 429) {
                    showErrors({ phone: ['تلاش‌های بیش از حد. لطفاً یک دقیقه دیگر تلاش کنید.'] }, mode);
                    return;
                }
                const result = await response.json();
                if (response.ok && result.redirect) {
                    const destination = new URL(result.redirect, window.location.origin);
                    if (destination.origin !== window.location.origin) throw new Error('Invalid destination');
                    modal.close();
                    document.querySelector('#auth-notice').textContent = result.message;
                    window.location.assign(destination.href);
                } else if (response.status === 422 && result.errors) {
                    showErrors(result.errors, mode);
                } else {
                    showErrors({ server: ['در حال حاضر امکان ورود نیست. لطفاً دوباره تلاش کنید.'] }, mode);
                }
            } catch {
                showErrors({ network: ['ارتباط برقرار نشد. اتصال اینترنت را بررسی کنید و دوباره تلاش کنید.'] }, mode);
            } finally {
                pending = false;
                form.removeAttribute('aria-busy');
                form.querySelector('[data-auth-progress]').hidden = true;
                controls.forEach((control) => { control.disabled = false; });
            }
        });
    });

    if (modal.hasAttribute('data-auto-open')) {
        modal.close();
        modal.showModal();
        if (!errors.hidden) errors.focus();
        else modal.querySelector(`#panel-${modal.dataset.mode} input:not([type="hidden"])`).focus();
    }
}
