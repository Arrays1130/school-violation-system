// resources/js/theme.js
(() => {
  const STORAGE_KEY = 'theme';
  const html = document.documentElement;
  const icon = document.getElementById('themeIcon');
  const setIcon = (dark) => {
    if (!icon) return;
    icon.innerHTML = dark
      ? '<path d="M10 2a8 8 0 006.32 12.9 6 6 0 01-8.64-8.64A7.96 7.96 0 0010 2z" />' // moon
      : '<path d="M10 2a1 1 0 011 1v2a1 1 0 01-2 0V3a1 1 0 011-1zM4.22 4.22a1 1 0 011.42 0l1.42 1.42a1 1 0 01-1.42 1.42L4.22 5.64a1 1 0 010-1.42zM18 9a1 1 0 110 2h-2a1 1 0 110-2h2zM4 9a1 1 0 110 2H2a1 1 0 110-2h2zM15.66 15.66a1 1 0 010 1.42l-1.42 1.42a1 1 0 01-1.42-1.42l1.42-1.42a1 1 0 011.42 0zM5.64 15.66a1 1 0 011.42 0l1.42 1.42a1 1 0 01-1.42 1.42L5.64 17.08a1 1 0 010-1.42zM10 14a4 4 0 100-8 4 4 0 000 8z" />';
  };
  const applyTheme = (dark) => {
    html.classList.toggle('dark', dark);
    setIcon(dark);
    localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
  };
  // Initialise from storage or OS preference
  const saved = localStorage.getItem(STORAGE_KEY);
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const initialDark = saved ? saved === 'dark' : prefersDark;
  applyTheme(initialDark);

  // Click handler for toggle button
  const toggleBtn = document.getElementById('themeToggle');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const isDark = html.classList.contains('dark');
      applyTheme(!isDark);
    });
  }
})();
