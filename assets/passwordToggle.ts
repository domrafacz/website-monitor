export function initPasswordToggles(): void {
    const toggles = Array.from(document.querySelectorAll<HTMLElement>('.js-password-toggle'));

    toggles.forEach((btn) => {
        const container = btn.closest('.relative') || btn.parentElement;
        const input = container?.querySelector<HTMLInputElement>('.js-password');
        const iconShow = btn.querySelector<HTMLElement>('.js-password-icon-show');
        const iconHide = btn.querySelector<HTMLElement>('.js-password-icon-hide');

        if (!input) return;

        btn.addEventListener('click', () => {
            const wasPassword = input.type === 'password';
            input.type = wasPassword ? 'text' : 'password';

            if (iconShow) iconShow.classList.toggle('hidden', !wasPassword);
            if (iconHide) iconHide.classList.toggle('hidden', wasPassword);
        });
    });
}

export default initPasswordToggles;
