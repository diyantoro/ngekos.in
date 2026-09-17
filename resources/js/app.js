// Cropper.js hanya dipakai di form properti/kamar — dimuat malas agar bundle global ringan.
// window.ensureCropper() me-resolve constructor Cropper + menyuntik CSS sekali saja.
window.ensureCropper = (() => {
    let janji = null;
    return () => {
        if (window.Cropper) return Promise.resolve(window.Cropper);
        if (!janji) {
            janji = Promise.all([
                import('cropperjs'),
                import('cropperjs/dist/cropper.min.css'),
            ]).then(([m]) => (window.Cropper = m.default ?? m.Cropper ?? m));
        }
        return janji;
    };
})();

import { photoCropManager } from './photo-crop-manager.js';
window.photoCropManager = photoCropManager;

// Loader Google Maps — key dibaca dari <meta name="gmaps-key">.
// Skrip Maps JS dimuat sekali (lazy) dan callback antrean dipanggil saat siap.
// Tanpa key: callback tetap dipanggil async agar fallback OSM/embed jalan.
window.loadNgekosMaps = (() => {
    const bacaKey = () => ((document.querySelector('meta[name="gmaps-key"]') || {}).content || '').trim();
    let booted = false;
    const antre = [];

    window.ngekosMapsBoot = () => {
        booted = true;
        antre.splice(0).forEach((fn) => {
            try { fn(); } catch (e) { console.error('Inisialisasi peta gagal:', e); }
        });
    };

    return function loadNgekosMaps(fn) {
        if (typeof fn !== 'function') return;
        const key = bacaKey();
        if (!key) {
            setTimeout(fn, 0);
            return;
        }
        if (booted && window.google && window.google.maps) {
            try { fn(); } catch (e) { console.error('Inisialisasi peta gagal:', e); }
            return;
        }
        antre.push(fn);
        if (document.getElementById('ngekos-gmaps-js')) return;
        const s = document.createElement('script');
        s.id = 'ngekos-gmaps-js';
        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(key)
            + '&v=weekly&libraries=places,geocoding&loading=async&callback=ngekosMapsBoot';
        s.async = true;
        s.defer = true;
        s.onerror = () => {
            antre.splice(0).forEach((cb) => {
                try { cb(); } catch (e) { console.error('Inisialisasi peta gagal:', e); }
            });
        };
        document.head.appendChild(s);
    };
})();

// Embed Google Maps tanpa API key: <iframe src="...maps.google.com/maps?q=...&output=embed">.
// Dipakai saat GOOGLE_MAPS_API_KEY belum diisi agar peta tetap tampil.
window.pasangGoogleEmbed = function (el, lat, lng, zoom) {
    if (!el) return;
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
    el.innerHTML = '';
    const iframe = document.createElement('iframe');
    iframe.className = 'h-full w-full border-0';
    iframe.loading = 'lazy';
    iframe.allowFullscreen = true;
    iframe.referrerPolicy = 'no-referrer-when-downgrade';
    iframe.src = 'https://maps.google.com/maps?q=' + encodeURIComponent(lat + ',' + lng)
        + '&z=' + (zoom || 15) + '&hl=id&output=embed';
    el.appendChild(iframe);
};

// Embed OpenStreetMap tanpa API key sama sekali (paling andal).
// el: container, lat/lng: titik tengah, zoom diabaikan OSM (pakai bbox).
// daftar: opsional array [{lat,lng}] untuk banyak marker & bbox otomatis.
window.pasangOsmEmbed = function (el, lat, lng, zoom, daftar) {
    if (!el) return;
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
    const titik = Array.isArray(daftar) && daftar.length ? daftar.slice(0, 30) : [{ lat, lng }];
    let minLat = lat, maxLat = lat, minLng = lng, maxLng = lng;
    titik.forEach((m) => {
        if (!Number.isFinite(m.lat) || !Number.isFinite(m.lng)) return;
        minLat = Math.min(minLat, m.lat); maxLat = Math.max(maxLat, m.lat);
        minLng = Math.min(minLng, m.lng); maxLng = Math.max(maxLng, m.lng);
    });
    const pad = titik.length > 1 ? 0.08 : 0.01;
    const bbox = [minLng - pad, minLat - pad, maxLng + pad, maxLat + pad].join(',');
    const markers = titik
        .filter((m) => Number.isFinite(m.lat) && Number.isFinite(m.lng))
        .map((m) => '&marker=' + m.lat + ',' + m.lng)
        .join('');
    el.innerHTML = '';
    const iframe = document.createElement('iframe');
    iframe.className = 'h-full w-full border-0';
    iframe.loading = 'lazy';
    iframe.referrerPolicy = 'no-referrer-when-downgrade';
    iframe.title = 'Peta lokasi kos';
    iframe.src = 'https://www.openstreetmap.org/export/embed.html?bbox=' + bbox
        + '&layer=mapnik' + markers;
    el.appendChild(iframe);
};

// Clustering marker untuk peta dengan banyak titik (beranda & katalog).
// Memakai @googlemaps/markerclusterer (di-import dinamis agar masuk chunk terpisah).
window.pasangCluster = async (markers, map) => {
    if (!markers || !markers.length || !map) return null;
    const { MarkerClusterer } = await import('@googlemaps/markerclusterer');
    return new MarkerClusterer({ markers, map });
};

window.renderPendapatanChart = async (elementId, pendapatan, tagihan) => {
    const el = document.getElementById(elementId);
    if (!el) return;
    const { default: Chart } = await import('chart.js/auto');
    const labels = Object.keys(pendapatan).length ? Object.keys(pendapatan) : Object.keys(tagihan);
    if (!labels.length) return;
    new Chart(el, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Pendapatan', data: labels.map(m => pendapatan[m]?.total ?? 0), backgroundColor: 'rgba(20, 184, 166, .8)', borderRadius: 8 },
                { label: 'Tagihan Lunas', data: labels.map(m => tagihan[m]?.lunas ?? 0), backgroundColor: 'rgba(16, 185, 129, .8)', borderRadius: 8 },
                { label: 'Tagihan Belum', data: labels.map(m => tagihan[m]?.belum ?? 0), backgroundColor: 'rgba(244, 63, 94, .8)', borderRadius: 8 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp' + v.toLocaleString('id-ID') } } },
        },
    });
};

const chartRegistry = new Map();

window.destroyChart = (elementId) => {
    const chart = chartRegistry.get(elementId);
    if (chart) {
        chart.destroy();
        chartRegistry.delete(elementId);
    }
};

window.renderChart = async (elementId, config) => {
    const el = document.getElementById(elementId);
    if (!el) return;
    const { default: Chart } = await import('chart.js/auto');
    window.destroyChart(elementId);
    chartRegistry.set(elementId, new Chart(el, config));
};

// Grafik keuangan: line Pendapatan vs Pengeluaran vs Laba Bersih per bulan.
window.rekapKeuanganChart = (elementId, labels, pendapatan, pengeluaran, laba) => window.renderChart(elementId, {
    type: 'line',
    data: {
        labels,
        datasets: [
            { label: 'Pendapatan', data: pendapatan, borderColor: '#0d9488', backgroundColor: 'rgba(13, 148, 136, .1)', fill: true, tension: .4, borderWidth: 2, pointBackgroundColor: '#0d9488' },
            { label: 'Pengeluaran', data: pengeluaran, borderColor: '#f43f5e', backgroundColor: 'rgba(244, 63, 94, .1)', fill: true, tension: .4, borderWidth: 2, pointBackgroundColor: '#f43f5e' },
            { label: 'Laba Bersih', data: laba, borderColor: '#6366f1', backgroundColor: 'rgba(99, 102, 241, .1)', fill: true, tension: .4, borderWidth: 2, pointBackgroundColor: '#6366f1', borderDash: [6, 3] },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp' + Number(v).toLocaleString('id-ID') } } },
    },
});

// Bar chart nilai nominal Rupiah (transaksi, revenue, dst).
window.rupiahBarChart = (elementId, labels, values, label = 'Nilai') => window.renderChart(elementId, {
    type: 'bar',
    data: {
        labels,
        datasets: [{ label, data: values, backgroundColor: 'rgba(14, 165, 233, .85)', borderRadius: 6 }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp' + Number(v).toLocaleString('id-ID') } } },
    },
});

// Tren transaksi: bar jumlah transaksi + line total nilai (dual axis).
window.transactionTrendChart = (elementId, labels, counts, values) => window.renderChart(elementId, {
    data: {
        labels,
        datasets: [
            { type: 'bar', label: 'Jumlah Transaksi', data: counts, backgroundColor: 'rgba(20, 184, 166, .85)', borderRadius: 6, yAxisID: 'y' },
            { type: 'line', label: 'Nilai Transaksi', data: values, borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, .1)', fill: true, tension: .4, borderWidth: 2, pointBackgroundColor: '#f59e0b', yAxisID: 'y1' },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: v => 'Rp' + Number(v).toLocaleString('id-ID') } },
        },
    },
});

// Donut distribusi status (penyewaan/pembayaran) — warna fungsional kategori.
window.distributionDonutChart = (elementId, labels, values) => window.renderChart(elementId, {
    type: 'doughnut',
    data: {
        labels,
        datasets: [{
            data: values,
            backgroundColor: ['#0d9488', '#6366f1', '#f59e0b', '#f43f5e', '#0ea5e9', '#22c55e', '#8b5cf6', '#64748b'],
            borderWidth: 2,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 } } } },
    },
});

// Grafik horizontal: jumlah kos aktif per kota (persebaran kos di beranda).
// opsional onBarClick(index) dipanggil saat bar kota diklik.
window.kosPerKotaChart = (elementId, labels, values, { onBarClick } = {}) => {
    const gelap = document.documentElement.classList.contains('dark');
    return window.renderChart(elementId, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Kos Aktif',
                data: values,
                backgroundColor: 'rgba(13, 148, 136, .85)',
                hoverBackgroundColor: 'rgba(6, 182, 212, .9)',
                borderRadius: 5,
                barPercentage: 0.85,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            onClick: (e, elements) => {
                if (onBarClick && elements.length) onBarClick(elements[0].index);
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ctx.parsed.x.toLocaleString('id-ID') + ' kos aktif' } },
            },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0, color: gelap ? '#cbd5e1' : '#64748b' }, grid: { color: gelap ? 'rgba(148,163,184,.15)' : 'rgba(100,116,139,.12)' } },
                y: { grid: { display: false }, ticks: { color: gelap ? '#e2e8f0' : '#475569' } },
            },
        },
    });
};

// Grafik okupansi: tren tingkat hunian (%) per bulan.
window.rekapOkupansiChart = (elementId, labels, values) => window.renderChart(elementId, {
    type: 'line',
    data: {
        labels,
        datasets: [{
            label: 'Okupansi (%)',
            data: values,
            borderColor: '#0ea5e9',
            backgroundColor: 'rgba(14, 165, 233, .15)',
            fill: true,
            tension: .4,
            pointBackgroundColor: '#0ea5e9',
            borderWidth: 2,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } },
    },
});

// Grafik pengeluaran by kategori (donut).
window.rekapKategoriChart = (elementId, labels, values) => window.renderChart(elementId, {
    type: 'doughnut',
    data: {
        labels,
        datasets: [{
            data: values,
            backgroundColor: ['#0d9488', '#f43f5e', '#6366f1', '#f59e0b', '#0ea5e9', '#8b5cf6', '#22c55e', '#64748b'],
            borderWidth: 2,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 } } } },
    },
});

// Grafik pertumbuhan (multi-series, untuk admin & super admin).
window.growthLineChart = (elementId, labels, datasets) => window.renderChart(elementId, {
    type: 'line',
    data: { labels, datasets },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
    },
});

window.growthBarChart = (elementId, labels, datasets) => window.renderChart(elementId, {
    type: 'bar',
    data: { labels, datasets },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
        scales: { x: { stacked: false }, y: { beginAtZero: true, ticks: { precision: 0 } } },
    },
});

const terapkanTema = (gelap) => {
    document.documentElement.classList.toggle('dark', gelap);
    try { localStorage.setItem('theme', gelap ? 'dark' : 'light'); } catch (e) { /* abaikan */ }
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', gelap ? '#0f172a' : '#0d9488');
    try {
        if (window.Alpine && window.Alpine.store && window.Alpine.store('theme')) {
            window.Alpine.store('theme').dark = gelap;
        }
    } catch (e) { /* abaikan jika store belum siap */ }
    window.dispatchEvent(new CustomEvent('ngekos:theme-changed', { detail: { dark: gelap } }));
};

window.toggleNgekosTheme = () => {
    terapkanTema(!document.documentElement.classList.contains('dark'));
};

const daftarkanThemeStore = () => {
    try {
        if (!window.Alpine || !window.Alpine.store) return;
        if (!window.Alpine.store('theme')) {
            window.Alpine.store('theme', {
                dark: document.documentElement.classList.contains('dark'),
                toggle() {
                    terapkanTema(!document.documentElement.classList.contains('dark'));
                    this.dark = document.documentElement.classList.contains('dark');
                },
                apply() {
                    this.dark = document.documentElement.classList.contains('dark');
                },
                init() {
                    this.dark = document.documentElement.classList.contains('dark');
                },
            });
        } else {
            window.Alpine.store('theme').dark = document.documentElement.classList.contains('dark');
        }
    } catch (e) { /* abaikan jika Alpine belum siap */ }
};

if (window.Alpine) daftarkanThemeStore();
// Navigasi Livewire (wire:navigate) mengganti <body> tanpa reload <head>,
// sehingga class "dark" di <html> bisa hilang. Terapkan ulang dari localStorage.
const sinkronTemaNavigasi = () => {
    try {
        const t = localStorage.getItem('theme');
        const gelap = t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', gelap);
        if (window.Alpine && window.Alpine.store && window.Alpine.store('theme')) {
            window.Alpine.store('theme').dark = gelap;
        }
    } catch (e) { /* abaikan */ }
};
document.addEventListener('livewire:navigated', sinkronTemaNavigasi);
document.addEventListener('alpine:navigated', sinkronTemaNavigasi);
document.addEventListener('alpine:init', () => {
    daftarkanThemeStore();

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

const initPromo = () => {
    document.querySelectorAll('[data-promo]').forEach((root) => {
        if (root.dataset.promoDone === '1') return;
        root.dataset.promoDone = '1';
        const track = root.querySelector('[data-promo-track]');
        const slides = Array.from(root.querySelectorAll('[data-promo-slide]'));
        if (!track || slides.length < 2) return;
        let index = 0;
        let timer = null;
        const delay = 4500;
        const dotsWrap = root.querySelector('[data-promo-dots]');
        const count = root.querySelector('[data-promo-count]');
        const dots = [];
        if (dotsWrap) {
            dotsWrap.innerHTML = '';
            slides.forEach((_, i) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.setAttribute('aria-label', 'Ke promo ' + (i + 1));
                b.addEventListener('click', () => { go(i); restart(); });
                dotsWrap.appendChild(b);
                dots.push(b);
            });
        }
        const paint = () => dots.forEach((d, i) => {
            d.className = 'h-1.5 rounded-full transition-all duration-300 '
                + (i === index ? 'w-6 bg-white' : 'w-1.5 bg-white/50 hover:bg-white/80');
        });
        const render = () => {
            track.style.transform = 'translateX(-' + index * 100 + '%)';
            slides.forEach((s, i) => {
                const on = i === index;
                s.classList.toggle('promo-active', on);
                s.setAttribute('aria-hidden', on ? 'false' : 'true');
            });
            paint();
            if (count) count.textContent = (index + 1) + ' / ' + slides.length;
        };
        const next = () => { index = (index + 1) % slides.length; render(); };
        const prev = () => { index = (index - 1 + slides.length) % slides.length; render(); };
        const go = (i) => { index = ((i % slides.length) + slides.length) % slides.length; render(); };
        const start = () => { if (!timer) timer = setInterval(next, delay); };
        const stop = () => { if (timer) { clearInterval(timer); timer = null; } };
        const restart = () => { stop(); start(); };
        root.querySelector('[data-promo-next]')?.addEventListener('click', () => { next(); restart(); });
        root.querySelector('[data-promo-prev]')?.addEventListener('click', () => { prev(); restart(); });
        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);
        root.addEventListener('focusin', stop);
        root.addEventListener('focusout', start);
        let sx = null;
        root.addEventListener('touchstart', (e) => { sx = e.touches[0].clientX; stop(); }, { passive: true });
        root.addEventListener('touchend', (e) => {
            if (sx !== null) {
                const d = e.changedTouches[0].clientX - sx;
                if (Math.abs(d) > 40) {
                    if (d < 0) next(); else prev();
                }
                sx = null;
            }
            start();
        }, { passive: true });
        root.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') { next(); restart(); }
            else if (e.key === 'ArrowLeft') { prev(); restart(); }
        });
        render();
        start();
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPromo);
} else {
    initPromo();
}
document.addEventListener('livewire:navigated', initPromo);
document.addEventListener('alpine:navigated', initPromo);
if ('MutationObserver' in window) {
    new MutationObserver(() => initPromo()).observe(document.documentElement, { childList: true, subtree: true });
}