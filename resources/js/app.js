document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggle() {
            this.dark = !this.dark;
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            this.apply();
        },
        apply() {
            document.documentElement.classList.toggle('dark', this.dark);
            const meta = document.querySelector('meta[name="theme-color"]');
            if (meta) meta.setAttribute('content', this.dark ? '#0f172a' : '#0d9488');
        },
        init() {
            this.apply();
        }
    });

    // Counter statistik: hitung naik saat elemen terlihat (menggunakan nilai target yang dilewatkan).
    Alpine.data('statCounter', (target) => ({
        display: 0,
        run() {
            const selesai = Date.now() + 1200;
            const tick = () => {
                const sisa = selesai - Date.now();
                if (sisa <= 0) {
                    this.display = Number(target || 0);
                    return;
                }
                const p = 1 - sisa / 1200;
                this.display = Math.floor(Number(target || 0) * (1 - Math.pow(1 - p, 3)));
                requestAnimationFrame(tick);
            };
            tick();
        },
        init() {
            if (!('IntersectionObserver' in window)) { this.run(); return; }
            const io = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        obs.disconnect();
                        this.run();
                    }
                });
            }, { threshold: 0.4 });
            io.observe(this.$el);
        }
    }));

    // Navigasi Livewire/Alpine mengganti DOM dan menghapus class "dark" dari <html>
    // (elemen html dari server dikirim tanpa class). Terapkan ulang setelah navigasi selesai.
    const reapplyTheme = () => Alpine.store('theme')?.apply();
    document.addEventListener('livewire:navigated', reapplyTheme);
    document.addEventListener('alpine:navigated', reapplyTheme);

    Alpine.store('theme').init();
});

// Reveal on scroll: elemen dengan class `.reveal` muncul halus saat masuk viewport.
const initReveal = () => {
    const els = document.querySelectorAll('.reveal:not(.is-revealed)');
    if (!('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-revealed'));
        return;
    }
    els.forEach((el) => {
        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                el.classList.add('is-revealed');
                obs.disconnect();
                setTimeout(() => el.classList.remove('reveal'), 800);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        io.observe(el);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReveal);
} else {
    initReveal();
}
document.addEventListener('livewire:navigated', initReveal);
document.addEventListener('alpine:navigated', initReveal);