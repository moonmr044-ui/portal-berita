// File: resources/js/app.js
import Alpine from 'alpinejs';

// Daftarkan Alpine ke window agar dapat dipakai komponen inline di Blade.
window.Alpine = Alpine;

/*
|--------------------------------------------------------------------------
| Komponen Alpine
|--------------------------------------------------------------------------
*/

/**
 * Marquee Breaking News.
 *
 * Menjeda animasi saat kursor berada di atas elemen (ditangani CSS) dan
 * menghormati prefers-reduced-motion.
 */
Alpine.data('breakingNewsTicker', () => ({
    paused: false,

    init() {
        const media = window.matchMedia('(prefers-reduced-motion: reduce)');

        if (media.matches) {
            this.paused = true;
        }

        media.addEventListener('change', (event) => {
            this.paused = event.matches;
        });
    },
}));

/**
 * Toggle dropdown menu akun.
 */
Alpine.data('accountMenu', () => ({
    open: false,

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    },
}));

Alpine.start();
