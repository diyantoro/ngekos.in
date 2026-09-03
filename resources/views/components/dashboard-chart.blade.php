@props([
    'id' => 'chart',
    'type' => 'line',
    'title' => null,
    'height' => 'h-64',
    'labels' => [],
    'datasets' => [],
    'yCallback' => null,
])

@if ($title)
    <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ $title }}</h3>
@endif

<div {{ $attributes->merge(['class' => 'relative ' . $height]) }}
     x-data="{
         chart: null,
         init() {
             const wait = () => {
                 if (typeof Chart === 'undefined') { setTimeout(wait, 100); return; }
                 const ctx = $refs.canvas.getContext('2d');
                 this.chart = new Chart(ctx, {
                     type: @js($type),
                     data: {
                         labels: @js($labels),
                         datasets: @js($datasets)
                     },
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         animation: { duration: 1200, easing: 'easeOutQuart' },
                         interaction: { mode: 'index', intersect: false },
                         plugins: {
                             legend: {
                                 display: true,
                                 position: 'bottom',
                                 labels: {
                                     boxWidth: 10,
                                     boxHeight: 10,
                                     padding: 20,
                                     usePointStyle: true,
                                     pointStyle: 'circle',
                                     font: { size: 12, family: 'Inter, sans-serif' },
                                     color: '#6b7280'
                                 }
                             },
                             tooltip: {
                                 backgroundColor: 'rgba(255,255,255,0.96)',
                                 titleColor: '#111827',
                                 bodyColor: '#374151',
                                 titleFont: { size: 13, weight: '600', family: 'Inter, sans-serif' },
                                 bodyFont: { size: 12, family: 'Inter, sans-serif' },
                                 padding: { top: 12, bottom: 12, left: 16, right: 16 },
                                 cornerRadius: 12,
                                 borderColor: '#e5e7eb',
                                 borderWidth: 1,
                                 displayColors: true,
                                 boxWidth: 8,
                                 boxHeight: 8,
                                 boxPadding: 6,
                                 usePointStyle: true,
                                 shadow: { blur: 10, color: 'rgba(0,0,0,0.08)', offsetX: 0, offsetY: 4 }
                             }
                         },
                         scales: {
                             x: {
                                 grid: { display: false },
                                 border: { display: false },
                                 ticks: { font: { size: 11, family: 'Inter, sans-serif' }, color: '#9ca3af', padding: 8 }
                             },
                             y: {
                                 beginAtZero: true,
                                 grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                                 border: { display: false, dash: [4, 4] },
                                 ticks: {
                                     font: { size: 11, family: 'Inter, sans-serif' },
                                     color: '#9ca3af',
                                     padding: 12
                                     @if(!empty($yCallback)), callback: {!! $yCallback !!}@endif
                                 }
                             }
                         }
                     }
                 });
             };
             wait();
         }
     }">
    <canvas x-ref="canvas"></canvas>
</div>
