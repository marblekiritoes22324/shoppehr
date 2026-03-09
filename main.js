/**
 * Shopee HR - Main UI Logic
 * Handles sidebar, mobile menu, and shared UI interactions
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Toggle (Desktop)
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });

        // Restore sidebar state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }
    }

    // 2. Mobile Menu (Burger)
    const burger = document.querySelector('.burger');
    const overlay = document.querySelector('.overlay');
    const body = document.body;

    function setMenu(open) {
        if (!burger || !sidebar || !overlay) return;

        burger.classList.toggle('open', open);
        sidebar.classList.toggle('open', open);
        overlay.classList.toggle('open', open);
        body.classList.toggle('menu-open', open);
        burger.setAttribute('aria-expanded', open);
    }

    if (burger) {
        burger.addEventListener('click', () => {
            const isOpen = sidebar.classList.contains('open');
            setMenu(!isOpen);
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => setMenu(false));
    }

    // Escape key to close mobile menu
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setMenu(false);
    });

    // 3. Initialize Role Display
    if (typeof Auth !== 'undefined') {
        Auth.applyRoleNav();
        Auth.updateUserDisplay();
    }
});
