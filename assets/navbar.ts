// Dark mode functionality
export function initDarkMode(): void {
    const darkModeToggle = document.getElementById('dark-mode-toggle');

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

    // Update icon to match current state
    const svg = darkModeToggle?.querySelector('svg');
    if (svg) {
        if (isDark) {
            // Sun icon
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
        } else {
            // Moon icon
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>';
        }
    }

    // Add click listener to toggle button
    darkModeToggle?.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        const isNowDark = document.documentElement.classList.contains('dark');
        localStorage.setItem('theme', isNowDark ? 'dark' : 'light');

        // Update icon
        if (svg) {
            if (isNowDark) {
                // Sun icon
                svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
            } else {
                // Moon icon
                svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>';
            }
        }
    });
}
