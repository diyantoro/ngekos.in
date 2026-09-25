<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" <?php echo e($attributes->merge(['fill' => 'none'])); ?>>
    <defs>
        <linearGradient id="ngekos-gradient" x1="0" y1="0" x2="64" y2="64" gradientUnits="userSpaceOnUse">
            <stop stop-color="#0d9488" />
            <stop offset="1" stop-color="#047857" />
        </linearGradient>
        <filter id="ngekos-logo-shadow" x="-30%" y="-30%" width="160%" height="160%">
            <feDropShadow dx="0" dy="1.5" stdDeviation="2" flood-color="#052e2b" flood-opacity="0.30" />
        </filter>
    </defs>
    <rect width="64" height="64" rx="16" fill="url(#ngekos-gradient)" filter="url(#ngekos-logo-shadow)" />
    <path d="M13.5 30.5L32 13L50.5 30.5" stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
    <path d="M19 27.5V48.5H45V27.5" stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M26.5 48.5V37.5H37.5V48.5" stroke="#99f6e4" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" />
</svg><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\components\application-logo.blade.php ENDPATH**/ ?>