import 'cropperjs/dist/cropper.min.css';
import Cropper from 'cropperjs';
window.Cropper = Cropper;

// Loader Google Maps — key dibaca dari <meta name="gmaps-key">.
// Skrip Maps JS dimuat sekali (lazy) dan callback antrean dipanggil saat siap.
window.loadNgekosMaps = (() => {
    const key = (document.querySelector('meta[name="gmaps-key"]') || {}).content || '';
    let booted = false;
    const antre = [];

    window.ngekosMapsBoot = () => {
        booted = true;
        antre.splice(0).forEach((fn) => {
            try { fn(); } catch (e) { console.error('Inisialisasi peta gagal:', e); }
        });
    };

    return function loadNgekosMaps(fn) {
        if (!key) return;
        if (booted && window.google && window.google.maps) { fn(); return; }
        antre.push(fn);
        if (document.getElementById('ngekos-gmaps-js')) return;
        const s = document.createElement('script');
        s.id = 'ngekos-gmaps-js';
        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(key)
            + '&v=weekly&libraries=places,geocoding&loading=async&callback=ngekosMapsBoot';
        s.async = true;
        s.defer = true;
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