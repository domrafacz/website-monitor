// Dark mode functionality
export function initDarkMode(): void {
    // Support multiple toggle buttons (header + login). Query all known IDs so
    // we can update icons and attach listeners consistently.
    const toggleIds = ['dark-mode-toggle', 'dark-mode-toggle-login'];
    const toggles: HTMLElement[] = toggleIds.map(id => document.getElementById(id)).filter(Boolean) as HTMLElement[];

    // Set initial theme based on localStorage or system preference
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    let isDark: boolean;

    if (savedTheme === 'dark') {
        isDark = true;
    } else if (savedTheme === 'light') {
        isDark = false;
    } else {
        // First visit - use system preference and save it
        isDark = systemPrefersDark;
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }

    // Apply the theme
    if (isDark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    // Helper to set SVG path for a button's <svg>
    function setSvgForButton(button: HTMLElement, dark: boolean) {
        const svg = button.querySelector('svg');
        if (!svg) return;
        if (dark) {
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
        } else {
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>';
        }
    }

    // Initialize icons for all toggles
    toggles.forEach((btn) => setSvgForButton(btn, isDark));

    // Attach listeners to each toggle so all update together
    toggles.forEach((btn) => {
        btn.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            const isNowDark = document.documentElement.classList.contains('dark');
            localStorage.setItem('theme', isNowDark ? 'dark' : 'light');
            toggles.forEach((b) => setSvgForButton(b, isNowDark));
        });
    });
}
