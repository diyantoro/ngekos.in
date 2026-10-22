@props(['fasilitas' => [], 'editable' => false, 'selected' => [], 'field' => 'fasilitasTerpilih', 'scope' => 'semua', 'kompak' => false, 'tampilan' => 'chip', 'kolom' => null])

@php
    // Ikon SVG inline (Material Icons/Tabler/custom). Tiap fasilitas memakai
    // bentuk yang berbeda agar mudah dikenali; path yang sama dipakai di
    // aplikasi mobile.
    $ikonSvg = [
        'AC' => '<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2.6" y="5.4" width="18.8" height="7.6" rx="1.4"/><path d="M5.2 8.6h8.4"/><circle cx="18.2" cy="8.6" r="1.1"/><path d="M2.6 13v2.4a1.6 1.6 0 0 0 1.6 1.6h2.4"/><path d="M21.4 13v2.4a1.6 1.6 0 0 1-1.6 1.6h-2.4"/><path d="M7.4 15.6c0 1.6-.6 2.2-.6 3.7"/><path d="M10.4 15.6v4.1"/><path d="M13 15.6v4.1"/><path d="M16 15.6c0 1.6.6 2.2.6 3.7"/></g>',
        'Air' => '<path fill="currentColor" d="M12 2c-5.33 4.55-8 8.48-8 11.8 0 4.98 3.8 8.2 8 8.2s8-3.22 8-8.2c0-3.32-2.67-7.25-8-11.8zM7.83 14c.37 0 .67.26.74.62.41 2.22 2.28 2.98 3.64 2.87.43-.02.79.32.79.75 0 .4-.32.73-.72.75-2.13.13-4.62-1.09-5.19-4.12a.75.75 0 0 1 .74-.87z"/>',
        'Akses 24 Jam' => '<path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/><path fill="currentColor" d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>',
        'Aman 24 Jam' => '<path fill="currentColor" d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>',
        'Bantal' => '<path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M7 10.5c1.2 0 1.2 1.5 2.5 1.5s1.3-1.5 2.5-1.5 1.3 1.5 2.5 1.5 1.3-1.5 2.5-1.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
        'Cermin' => '<rect x="6" y="3" width="12" height="15" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M9 6l5 5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M12 18v3M9 21h6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'Cuci Setrika' => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6h7.459a3 3 0 0 1 2.959 2.507l.577 3.464l.81 4.865a1 1 0 0 1 -.985 1.164h-16.82a7 7 0 0 1 7 -7h9.8" /></g>',
        'Dapur' => '<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9.2 2.4c.8.8.8 1.6 0 2.4"/><path d="M12 1.9c.9 1 .9 2.1 0 3.1"/><path d="M14.8 2.4c.8.8.8 1.6 0 2.4"/><path d="M6.8 7.2h10.4"/><path d="M7.3 7.2c0 2.1 2.1 3.6 4.7 3.6s4.7-1.5 4.7-3.6"/><path d="M17.4 6.7 20 5.7"/><path d="M9.6 11.2v1.4"/><path d="M14.4 11.2v1.4"/><rect x="6.6" y="12.8" width="10.8" height="2" rx=".8"/><path d="M12 13.4v.9"/><path d="M4.4 15.7h15.2"/><path d="M4.4 15.7v5.9"/><path d="M19.6 15.7v5.9"/></g>',
        'Dapur Bersama' => '<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9.2 2.4c.8.8.8 1.6 0 2.4"/><path d="M12 1.9c.9 1 .9 2.1 0 3.1"/><path d="M14.8 2.4c.8.8.8 1.6 0 2.4"/><path d="M6.8 7.2h10.4"/><path d="M7.3 7.2c0 2.1 2.1 3.6 4.7 3.6s4.7-1.5 4.7-3.6"/><path d="M17.4 6.7 20 5.7"/><path d="M9.6 11.2v1.4"/><path d="M14.4 11.2v1.4"/><rect x="6.6" y="12.8" width="10.8" height="2" rx=".8"/><path d="M12 13.4v.9"/><path d="M4.4 15.7h15.2"/><path d="M4.4 15.7v5.9"/><path d="M19.6 15.7v5.9"/></g>',
        'Dilarang Bawa Hewan' => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 13.5c-1.1 -2 -1.441 -2.5 -2.7 -2.5c-1.259 0 -1.736 .755 -2.836 2.747c-.942 1.703 -2.846 1.845 -3.321 3.291c-.097 .265 -.145 .677 -.143 .962c0 1.176 .787 2 1.8 2c1.259 0 3 -1 4.5 -1s3.241 1 4.5 1c1.013 0 1.8 -.823 1.8 -2c0 -.285 -.049 -.697 -.146 -.962c-.475 -1.451 -2.512 -1.835 -3.454 -3.538" />
  <path d="M20.188 8.082a1.039 1.039 0 0 0 -.406 -.082h-.015c-.735 .012 -1.56 .75 -1.993 1.866c-.519 1.335 -.28 2.7 .538 3.052c.129 .055 .267 .082 .406 .082c.739 0 1.575 -.742 2.011 -1.866c.516 -1.335 .273 -2.7 -.54 -3.052l-.001 0" />
  <path d="M9.474 9c.055 0 .109 0 .163 -.011c.944 -.128 1.533 -1.346 1.32 -2.722c-.203 -1.297 -1.047 -2.267 -1.932 -2.267c-.055 0 -.109 0 -.163 .011c-.944 .128 -1.533 1.346 -1.32 2.722c.204 1.293 1.048 2.267 1.933 2.267" />
  <path d="M16.456 6.733c.214 -1.376 -.375 -2.594 -1.32 -2.722a1.164 1.164 0 0 0 -.162 -.011c-.885 0 -1.728 .97 -1.93 2.267c-.214 1.376 .375 2.594 1.32 2.722c.054 .007 .108 .011 .162 .011c.885 0 1.73 -.974 1.93 -2.267" />
  <path d="M5.69 12.918c.816 -.352 1.054 -1.719 .536 -3.052c-.436 -1.124 -1.271 -1.866 -2.009 -1.866c-.14 0 -.277 .027 -.407 .082c-.816 .352 -1.054 1.719 -.536 3.052c.436 1.124 1.271 1.866 2.009 1.866c.14 0 .277 -.027 .407 -.082" /><path d="M4 4l16 16" /></g>',
        'Dilarang Bawa Tamu lawan jenis' => '<path fill="currentColor" d="M8.65 5.82a3.999 3.999 0 1 1 5.53 5.53L8.65 5.82zM20 17.17c-.02-1.1-.63-2.11-1.61-2.62-.54-.28-1.13-.54-1.77-.76L20 17.17zm1.19 4.02L2.81 2.81 1.39 4.22l8.89 8.89c-1.81.23-3.39.79-4.67 1.45A2.97 2.97 0 0 0 4 17.22V20h13.17l2.61 2.61 1.41-1.42z"/>',
        'Dilarang Merokok' => '<path fill="currentColor" d="m2 6 6.99 7H2v3h9.99l7 7 1.26-1.25-17-17zm18.5 7H22v3h-1.5zM18 13h1.5v3H18zm.85-8.12c.62-.61 1-1.45 1-2.38h-1.5c0 1.02-.83 1.85-1.85 1.85v1.5c2.24 0 4 1.83 4 4.07V12H22V9.92c0-2.23-1.28-4.15-3.15-5.04zM14.5 8.7h1.53c1.05 0 1.97.74 1.97 2.05V12h1.5v-1.59c0-1.8-1.6-3.16-3.47-3.16H14.5c-1.02 0-1.85-.98-1.85-2s.83-1.75 1.85-1.75V2a3.35 3.35 0 0 0 0 6.7zm2.5 7.23V13h-2.93z"/>',
        'Dilarang Miras & Narkoba' => '<path fill="currentColor" d="M5.83 3H21v2l-6.2 6.97L9.83 7h6.74l1.78-2H7.83l-2-2zm13.95 19.61L18 20.83V21H6v-2h5v-5l-1.37-1.54-8.24-8.24L2.8 2.81 3 3l18.19 18.19-1.41 1.42zM16.17 19 13 15.83V19h3.17z"/>',
        'Ember' => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7a8 4 0 1 0 16 0a8 4 0 1 0 -16 0" />
  <path d="M4 7c0 .664 .088 1.324 .263 1.965l2.737 10.035c.5 1.5 2.239 2 5 2s4.5 -.5 5 -2c.333 -1 1.246 -4.345 2.737 -10.035a7.45 7.45 0 0 0 .263 -1.965" /></g>',
        'Gym' => '<path fill="currentColor" d="M20.57 14.86 22 13.43 20.57 12 17 15.57 8.43 7 12 3.43 10.57 2 9.14 3.43 7.71 2 5.57 4.14 4.14 2.71 2.71 4.14l1.43 1.43L2 7.71l1.43 1.43L2 10.57 3.43 12 7 8.43 15.57 17 12 20.57 13.43 22l1.43-1.43L16.29 22l2.14-2.14 1.43 1.43 1.43-1.43-1.43-1.43L22 16.29z"/>',
        'Jam Malam 22.00' => '<path fill="currentColor" d="M12.34 2.02C6.59 1.82 2 6.42 2 12c0 5.52 4.48 10 10 10 3.71 0 6.93-2.02 8.66-5.02-7.51-.25-12.09-8.43-8.32-14.96z"/>',
        'Jendela' => '<path fill="currentColor" d="M11 11V3H5c-1.1 0-2 .9-2 2v6h8zm2 0h8V5c0-1.1-.9-2-2-2h-6v8zm-2 2H3v6c0 1.1.9 2 2 2h6v-8zm2 0v8h6c1.1 0 2-.9 2-2v-6h-8z"/>',
        'Kamar Mandi Dalam' => '<circle fill="currentColor" cx="7" cy="7" r="2"/><path fill="currentColor" d="M20 13V4.83C20 3.27 18.73 2 17.17 2c-.75 0-1.47.3-2 .83l-1.25 1.25c-.16-.05-.33-.08-.51-.08-.4 0-.77.12-1.08.32l2.76 2.76c.2-.31.32-.68.32-1.08 0-.18-.03-.34-.07-.51l1.25-1.25a.828.828 0 0 1 1.41.59V13h-6.85c-.3-.21-.57-.45-.82-.72l-1.4-1.55c-.19-.21-.43-.38-.69-.5A2.251 2.251 0 0 0 5 12.25V13H2v6c0 1.1.9 2 2 2 0 .55.45 1 1 1h14c.55 0 1-.45 1-1 1.1 0 2-.9 2-2v-6h-2z"/>',
        'Kamar Mandi Luar' => '<g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h14v2.5a4.5 4.5 0 0 1-4.5 4.5H7.5A4.5 4.5 0 0 1 3 14.5V12Z"/><path d="M5 12V6.5A2.5 2.5 0 0 1 7.5 4"/><path d="M15 7h6M19 5l2 2-2 2"/></g>',
        'Kasur' => '<path fill="currentColor" d="M20 10V7c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v3c-1.1 0-2 .9-2 2v5h1.33L4 19h1l.67-2h12.67l.66 2h1l.67-2H22v-5c0-1.1-.9-2-2-2zm-9 0H6V7h5v3zm7 0h-5V7h5v3z"/>',
        'Kipas' => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 13a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
  <path d="M14.167 10.5c.722 -1.538 1.156 -3.043 1.303 -4.514c.22 -1.63 -.762 -2.986 -3.47 -2.986s-3.69 1.357 -3.47 2.986c.147 1.471 .581 2.976 1.303 4.514" />
  <path d="M13.169 16.751c.97 1.395 2.057 2.523 3.257 3.386c1.3 1 2.967 .833 4.321 -1.512c1.354 -2.345 .67 -3.874 -.85 -4.498c-1.348 -.608 -2.868 -.985 -4.562 -1.128" />
  <path d="M8.664 13c-1.693 .143 -3.213 .52 -4.56 1.128c-1.522 .623 -2.206 2.153 -.852 4.498s3.02 2.517 4.321 1.512c1.2 -.863 2.287 -1.991 3.258 -3.386" /></g>',
        'Kipas Angin' => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 13a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
  <path d="M14.167 10.5c.722 -1.538 1.156 -3.043 1.303 -4.514c.22 -1.63 -.762 -2.986 -3.47 -2.986s-3.69 1.357 -3.47 2.986c.147 1.471 .581 2.976 1.303 4.514" />
  <path d="M13.169 16.751c.97 1.395 2.057 2.523 3.257 3.386c1.3 1 2.967 .833 4.321 -1.512c1.354 -2.345 .67 -3.874 -.85 -4.498c-1.348 -.608 -2.868 -.985 -4.562 -1.128" />
  <path d="M8.664 13c-1.693 .143 -3.213 .52 -4.56 1.128c-1.522 .623 -2.206 2.153 -.852 4.498s3.02 2.517 4.321 1.512c1.2 -.863 2.287 -1.991 3.258 -3.386" /></g>',
        'Kloset Duduk' => '<path d="M4 4.5A1.5 1.5 0 0 1 5.5 3h5A1.5 1.5 0 0 1 12 4.5V11H4V4.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 11h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M8 18v3M16 18v3M6 21h12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="8" cy="6" r=".8" fill="currentColor"/>',
        'Kloset Jongkok' => '<rect x="3" y="7" width="18" height="11" rx="4" fill="none" stroke="currentColor" stroke-width="1.8"/><rect x="10" y="9" width="4" height="7" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/><ellipse cx="6" cy="12.5" rx="1.5" ry="2.2" fill="currentColor"/><ellipse cx="18" cy="12.5" rx="1.5" ry="2.2" fill="currentColor"/>',
        'Kolam Renang' => '<path fill="currentColor" d="M22 21c-1.11 0-1.73-.37-2.18-.64-.37-.22-.6-.36-1.15-.36-.56 0-.78.13-1.15.36-.46.27-1.07.64-2.18.64s-1.73-.37-2.18-.64c-.37-.22-.6-.36-1.15-.36-.56 0-.78.13-1.15.36-.46.27-1.08.64-2.19.64-1.11 0-1.73-.37-2.18-.64-.37-.23-.6-.36-1.15-.36s-.78.13-1.15.36c-.46.27-1.08.64-2.19.64v-2c.56 0 .78-.13 1.15-.36.46-.27 1.08-.64 2.19-.64s1.73.37 2.18.64c.37.23.59.36 1.15.36.56 0 .78-.13 1.15-.36.46-.27 1.08-.64 2.19-.64 1.11 0 1.73.37 2.18.64.37.22.6.36 1.15.36s.78-.13 1.15-.36c.45-.27 1.07-.64 2.18-.64s1.73.37 2.18.64c.37.23.59.36 1.15.36v2zm0-4.5c-1.11 0-1.73-.37-2.18-.64-.37-.22-.6-.36-1.15-.36-.56 0-.78.13-1.15.36-.45.27-1.07.64-2.18.64s-1.73-.37-2.18-.64c-.37-.22-.6-.36-1.15-.36-.56 0-.78.13-1.15.36-.45.27-1.07.64-2.18.64s-1.73-.37-2.18-.64c-.37-.22-.6-.36-1.15-.36s-.78.13-1.15.36c-.47.27-1.09.64-2.2.64v-2c.56 0 .78-.13 1.15-.36.45-.27 1.07-.64 2.18-.64s1.73.37 2.18.64c.37.22.6.36 1.15.36.56 0 .78-.13 1.15-.36.45-.27 1.07-.64 2.18-.64s1.73.37 2.18.64c.37.22.6.36 1.15.36s.78-.13 1.15-.36c.45-.27 1.07-.64 2.18-.64s1.73.37 2.18.64c.37.22.6.36 1.15.36v2zM8.67 12c.56 0 .78-.13 1.15-.36.46-.27 1.08-.64 2.19-.64 1.11 0 1.73.37 2.18.64.37.22.6.36 1.15.36s.78-.13 1.15-.36c.12-.07.26-.15.41-.23L10.48 5C8.93 3.45 7.5 2.99 5 3v2.5c1.82-.01 2.89.39 4 1.5l1 1-3.25 3.25c.31.12.56.27.77.39.37.23.59.36 1.15.36z"/><circle fill="currentColor" cx="16.5" cy="5.5" r="2.5"/>',
        'Kulkas' => '<path fill="currentColor" d="M20 9V4c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v5h16zM8 5h2v3H8V5zm-4 6v9c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-9H4zm6 6H8v-5h2v5z"/>',
        'Kursi' => '<path fill="currentColor" d="M7 11v2h10v-2c0-1.86 1.28-3.41 3-3.86V6c0-1.65-1.35-3-3-3H7C5.35 3 4 4.35 4 6v1.14c1.72.45 3 2 3 3.86z"/><path fill="currentColor" d="M21 9c-1.1 0-2 .9-2 2v4H5v-4c0-1.1-.9-2-2-2s-2 .9-2 2v5c0 1.65 1.35 3 3 3v1c0 .55.45 1 1 1s1-.45 1-1v-1h12v1c0 .55.45 1 1 1s1-.45 1-1v-1c1.65 0 3-1.35 3-3v-5c0-1.1-.9-2-2-2z"/>',
        'Laundry' => '<path fill="currentColor" d="M9.17 16.83a4.008 4.008 0 0 0 5.66 0 4.008 4.008 0 0 0 0-5.66l-5.66 5.66zM18 2.01 6 2c-1.11 0-2 .89-2 2v16c0 1.11.89 2 2 2h12c1.11 0 2-.89 2-2V4c0-1.11-.89-1.99-2-1.99zM10 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM7 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm5 16c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/>',
        'Lawan Jenis' => '<path fill="currentColor" d="M8.65 5.82a3.999 3.999 0 1 1 5.53 5.53L8.65 5.82zM20 17.17c-.02-1.1-.63-2.11-1.61-2.62-.54-.28-1.13-.54-1.77-.76L20 17.17zm1.19 4.02L2.81 2.81 1.39 4.22l8.89 8.89c-1.81.23-3.39.79-4.67 1.45A2.97 2.97 0 0 0 4 17.22V20h13.17l2.61 2.61 1.41-1.42z"/>',
        'Lemari' => '<path fill="currentColor" d="M20 19V5c0-1.1-.9-2-2-2h-5.25v16h-1.5V3H6c-1.1 0-2 .9-2 2v14H3v2h18v-2h-1zm-10-6H8v-2h2v2zm6 0h-2v-2h2v2z"/>',
        'Listrik' => '<path fill="currentColor" d="M11 21h-1l1-7H7.5c-.58 0-.57-.32-.38-.66.19-.34.05-.08.07-.12C8.48 10.94 10.42 7.54 13 3h1l-1 7h3.5c.49 0 .56.33.47.51l-.07.15C12.96 17.55 11 21 11 21z"/>',
        'Meja' => '<path fill="currentColor" d="M2 6v12h2V8h10v10h2v-2h4v2h2V6H2zm18 2v2h-4V8h4zm-4 6v-2h4v2h-4z"/>',
        'Meja & Kursi' => '<path fill="currentColor" d="m21.96 9.73-1.43-5a.996.996 0 0 0-.96-.73H4.43c-.45 0-.84.3-.96.73l-1.43 5c-.18.63.3 1.27.96 1.27h2.2L4 20h2l.67-5h10.67l.66 5h2l-1.2-9H21c.66 0 1.14-.64.96-1.27zM6.93 13l.27-2h9.6l.27 2H6.93z"/>',
        'Parkir' => '<path fill="currentColor" d="M13 3H6v18h4v-6h3c3.31 0 6-2.69 6-6s-2.69-6-6-6zm.2 8H10V7h3.2c1.1 0 2 .9 2 2s-.9 2-2 2z"/>',
        'Parkir Mobil' => '<path fill="currentColor" d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>',
        'Parkir Motor' => '<path fill="currentColor" d="M20 11c-.18 0-.36.03-.53.05L17.41 9H20V6l-3.72 1.86L13.41 5H9v2h3.59l2 2H11l-4 2-2-2H0v2h4c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4l2 2h3l3.49-6.1 1.01 1.01c-.91.73-1.5 1.84-1.5 3.09 0 2.21 1.79 4 4 4s4-1.79 4-4-1.79-4-4-4zM4 17c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm16 0c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>',
        'Parkir Motor & Sepeda' => '<path fill="currentColor" d="M20 11c-.18 0-.36.03-.53.05L17.41 9H20V6l-3.72 1.86L13.41 5H9v2h3.59l2 2H11l-4 2-2-2H0v2h4c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4l2 2h3l3.49-6.1 1.01 1.01c-.91.73-1.5 1.84-1.5 3.09 0 2.21 1.79 4 4 4s4-1.79 4-4-1.79-4-4-4zM4 17c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm16 0c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>',
        'Penjaga Kos' => '<path fill="currentColor" d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>',
        'Rak Baju' => '<path fill="currentColor" d="M21.6 18.2 13 11.75v-.91a3.496 3.496 0 0 0-.18-6.75A3.51 3.51 0 0 0 8.5 7.5h2c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5c0 .84-.69 1.52-1.53 1.5-.54-.01-.97.45-.97.99v1.76L2.4 18.2c-.77.58-.36 1.8.6 1.8h18c.96 0 1.37-1.22.6-1.8zM6 18l6-4.5 6 4.5H6z"/>',
        'Ruang Tamu' => '<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2.2 5.4 3 2.3h4l.8 3.1z"/><path d="M4.9 5.4v8"/><rect x="11.6" y="2.3" width="9.2" height="5.5" rx="1"/><path d="M13.2 5.9l1.7-1.4 1.4 1.2 1.3-1.2 1.8 1.4"/><path d="M5 13.4v-1.1a10 10 0 0 1 14 0v1.1"/><rect x="2.3" y="13.4" width="2.9" height="5.9" rx="1.3"/><rect x="18.8" y="13.4" width="2.9" height="5.9" rx="1.3"/><path d="M5.2 19.3h13.6"/><path d="M4.3 19.3v2"/><path d="M19.7 19.3v2"/><path d="M8.9 11.8h1.1"/><path d="M11.5 11.8h1.1"/><path d="M14.1 11.8h1.1"/><rect x="7.8" y="15.2" width="2.6" height="1.4" rx=".7"/><rect x="13.6" y="15.2" width="2.6" height="1.4" rx=".7"/></g>',
        'Shower' => '<g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><g transform="rotate(42 12 12.5)"><path d="M4.9 12.2a7.1 7.1 0 0 1 14.2 0"/><rect x="5.4" y="12.2" width="13.2" height="3" rx="1"/><path d="M12 5.1V3.4"/><rect x="10.4" y="1.3" width="3.2" height="2.1" rx=".7"/></g><path d="M3.4 14.2l1.3-1.3"/><path d="M6.4 17.2l1.3-1.3"/><path d="M2.9 19.2l1.3-1.3"/><path d="M9.6 20.2l1.3-1.3"/><path d="M12.4 17.4l1.3-1.3"/></g>',
        'Tamu' => '<path fill="currentColor" d="M12 12.75c1.63 0 3.07.39 4.24.9 1.08.48 1.76 1.56 1.76 2.73V18H6v-1.61c0-1.18.68-2.26 1.76-2.73 1.17-.52 2.61-.91 4.24-.91zM4 13c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm1.13 1.1c-.37-.06-.74-.1-1.13-.1-.99 0-1.93.21-2.78.58A2.01 2.01 0 0 0 0 16.43V18h4.5v-1.61c0-.83.23-1.61.63-2.29zM20 13c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm4 3.43c0-.81-.48-1.53-1.22-1.85A6.95 6.95 0 0 0 20 14c-.39 0-.76.04-1.13.1.4.68.63 1.46.63 2.29V18H24v-1.57zM12 6c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3z"/>',
        'Tempat Jemuran' => '<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M1.9 6.6 22.1 5.8"/><path d="M9.8 6.2v1.3l2.2 2.2 2.2-2.2V6.2"/><path d="M10.2 10.2 6.4 10.5 3.2 13.4 6.2 15.9 7.4 15V18.2Q7.4 19.2 8.4 19.2H15.6Q16.6 19.2 16.6 18.2V15L17.8 15.9 20.8 13.4 17.6 10.5 13.8 10.2"/><path d="M10.2 10.2c.6 1.4 3 1.4 3.6 0"/></g>',
        'Termasuk Listrik' => '<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M13.9 2.6 7.1 12.6h4.3l-1.9 8.8 7.6-10.7h-4.4z"/></g>',
        'Tidak Listrik' => '<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M13.9 2.6 7.1 12.6h4.3l-1.9 8.8 7.6-10.7h-4.4z"/><path d="M3.2 3.4 20.8 20.6"/></g>',
        'Tidak Termasuk Listrik' => '<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M13.9 2.6 7.1 12.6h4.3l-1.9 8.8 7.6-10.7h-4.4z"/><path d="M3.2 3.4 20.8 20.6"/></g>',
        'Toilet' => '<path d="M4 4.5A1.5 1.5 0 0 1 5.5 3h5A1.5 1.5 0 0 1 12 4.5V11H4V4.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 11h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M8 18v3M16 18v3M6 21h12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="8" cy="6" r=".8" fill="currentColor"/>',
        'Ventilasi' => '<path fill="currentColor" d="M14.5 17c0 1.65-1.35 3-3 3s-3-1.35-3-3h2c0 .55.45 1 1 1s1-.45 1-1-.45-1-1-1H2v-2h9.5c1.65 0 3 1.35 3 3zM19 6.5C19 4.57 17.43 3 15.5 3S12 4.57 12 6.5h2c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5S16.33 8 15.5 8H2v2h13.5c1.93 0 3.5-1.57 3.5-3.5zm-.5 4.5H2v2h16.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5v2c1.93 0 3.5-1.57 3.5-3.5S20.43 11 18.5 11z"/>',
        'WiFi' => '<g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="1.8" y="16.2" width="20.4" height="5.6" rx="1.6"/><path d="M4.9 10.9 9.6 16.2"/><path d="M19.1 10.9 14.4 16.2"/><path d="M5.4 10.4a6.6 6.6 0 0 1 13.2 0"/><path d="M7.4 10.9a4.6 4.6 0 0 1 9.2 0"/><path d="M9.4 11.3a2.6 2.6 0 0 1 5.2 0"/><circle cx="12" cy="12.7" r="1"/><circle cx="4.4" cy="19" r=".9"/><path d="M8.6 19h5.2"/><path d="M17 19h2.8"/></g>',
        'air' => '<path fill="currentColor" d="M12 2c-5.33 4.55-8 8.48-8 11.8 0 4.98 3.8 8.2 8 8.2s8-3.22 8-8.2c0-3.32-2.67-7.25-8-11.8zM7.83 14c.37 0 .67.26.74.62.41 2.22 2.28 2.98 3.64 2.87.43-.02.79.32.79.75 0 .4-.32.73-.72.75-2.13.13-4.62-1.09-5.19-4.12a.75.75 0 0 1 .74-.87z"/>',
        'kipas' => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 13a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
  <path d="M14.167 10.5c.722 -1.538 1.156 -3.043 1.303 -4.514c.22 -1.63 -.762 -2.986 -3.47 -2.986s-3.69 1.357 -3.47 2.986c.147 1.471 .581 2.976 1.303 4.514" />
  <path d="M13.169 16.751c.97 1.395 2.057 2.523 3.257 3.386c1.3 1 2.967 .833 4.321 -1.512c1.354 -2.345 .67 -3.874 -.85 -4.498c-1.348 -.608 -2.868 -.985 -4.562 -1.128" />
  <path d="M8.664 13c-1.693 .143 -3.213 .52 -4.56 1.128c-1.522 .623 -2.206 2.153 -.852 4.498s3.02 2.517 4.321 1.512c1.2 -.863 2.287 -1.991 3.258 -3.386" /></g>',
    ];

    // Daftar fasilitas yang punya file PNG. Key = label fasilitas (nama yang disimpan),
    // value = nama file PNG.
    $ikonPng = [
        'AC' => 'ac.png',
        'Kulkas' => 'kulkas.png',
        'Kasur' => 'kasur.png',
        'Lemari' => 'lemari.png',
        'Meja' => 'meja.png',
        'Meja & Kursi' => 'meja.png',
        'Kursi' => 'kursi.png',
        'Jendela' => 'jendela.png',
        'Bantal' => 'bantal.png',
        'Cermin' => 'cermin.png',
        'Rak Baju' => 'lemari.png',
        'Kamar Mandi Dalam' => 'kamar_mandi_dalam.png',
        'Kamar Mandi Luar' => 'kamar_mandi_luar.png',
        'Shower' => 'shower.png',
        'Ember' => 'ember.png',
        'WiFi' => 'wifi.png',
        'Dapur Bersama' => 'dapur.png',
        // Legacy (data lama): tetap tampil ikon yang sama agar tidak rusak.
        'Dapur' => 'dapur.png',
        'Ruang Tamu' => 'ruang_tamu.png',
        'Penjaga Kos' => 'penjaga_kos.png',
        'Dilarang Merokok' => 'dilarang_merokok.png',
        'Dilarang Bawa Tamu lawan jenis' => 'lawan_jenis.png',
        'Akses 24 Jam' => 'akses.png',
        'Akses' => 'akses.png',
        'Parkir Motor' => 'parkir_motor_sepeda.png',
        'Parkir Mobil' => 'parkir_mobil.png',
        // Legacy (data lama): tetap tampil ikon yang sama agar tidak rusak.
        'Parkir Motor & Sepeda' => 'parkir_motor_sepeda.png',
        'Parkir' => 'parkir_motor_sepeda.png',
        'Tempat Jemuran' => 'tempat_jemuran.png',
        'Ventilasi' => 'jendela.png',
        'Tamu' => 'tamu.png',
        'Lawan Jenis' => 'lawan_jenis.png',
        'Termasuk Listrik' => 'tidak_listrik.png',
        'Tidak Termasuk Listrik' => 'tidak_listrik.png',
        'Tidak Listrik' => 'tidak_listrik.png',
        'Kloset Duduk' => 'kamar_mandi_dalam.png',
        'Kloset Jongkok' => 'kamar_mandi_dalam.png',
    ];

    // Kelompok fasilitas per scope.
    // scope=kos   -> hanya fasilitas umum/bersama (form kos).
    // scope=kamar -> hanya fasilitas privat kamar + kamar mandi (form kamar).
    // scope=semua -> semua (tampilan detail / kompatibel data lama).
    $semuaKelompok = [
        'Fasilitas Kamar' => [
            'AC', 'Kipas Angin', 'Kulkas', 'Kasur', 'Lemari', 'Meja & Kursi',
            'Meja', 'Kursi', 'Rak Baju', 'Jendela', 'Bantal', 'Cermin', 'Ventilasi',
            'Termasuk Listrik', 'Tidak Termasuk Listrik',
        ],
        'Fasilitas Kamar Mandi' => [
            'Kamar Mandi Dalam', 'Kamar Mandi Luar', 'Kloset Duduk', 'Kloset Jongkok', 'Shower', 'Ember', 'Air',
        ],
        'Fasilitas Umum Kos' => [
            'WiFi', 'Dapur Bersama', 'Ruang Tamu', 'Penjaga Kos', 'Tempat Jemuran', 'Laundry',
        ],
        'Fasilitas Parkir Kos' => [
            'Parkir Motor', 'Parkir Mobil',
        ],
        'Peraturan Kos' => [
            'Dilarang Merokok', 'Dilarang Bawa Tamu lawan jenis', 'Akses 24 Jam', 'Jam Malam 22.00', 'Dilarang Bawa Hewan', 'Dilarang Miras & Narkoba',
        ],
    ];

    $kelompokFasilitas = match ($scope) {
        'kos' => [
            'Fasilitas Umum Kos' => $semuaKelompok['Fasilitas Umum Kos'],
            'Fasilitas Parkir Kos' => $semuaKelompok['Fasilitas Parkir Kos'],
            'Peraturan Kos' => $semuaKelompok['Peraturan Kos'],
        ],
        'kamar' => [
            'Fasilitas Kamar' => $semuaKelompok['Fasilitas Kamar'],
            'Fasilitas Kamar Mandi' => $semuaKelompok['Fasilitas Kamar Mandi'],
        ],
        default => $semuaKelompok,
    };

    // Fungsi menentukan apakah item punya PNG. Untuk mempermudah, buat closure.
    $assetFasilitas = function ($nama) use ($ikonPng) {
        return $ikonPng[$nama] ?? null;
    };
@endphp

@if ($editable)
    {{-- Widget checklist fasilitas: murni Livewire + CSS, tanpa Alpine.
        Setiap item memakai wire:model.live sehingga status ceklis selalu
        tersinkron ke server walau form di-render ulang (mis. habis validasi gagal).
        Pasangan bertentangan (AC vs Kipas Angin, dst.) berlaku seperti radio:
        mencentang satu otomatis melepas centang lawannya (JS di bawah +
        pengaman di server via FacilityHelper::EKSKLUSIF). Semua kotak selalu
        bisa diklik. --}}
    @php $grupEksklusif = \App\Support\FacilityHelper::EKSKLUSIF; @endphp
    <div class="space-y-7" data-fasilitas-eksklusif='@json($grupEksklusif)'>
        @foreach ($kelompokFasilitas as $judul => $items)
            <div>
                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                    {{ $judul }}
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach ($items as $nama)
                        @php $svg = $ikonSvg[\App\Support\FacilityHelper::normalize($nama)] ?? null; @endphp
                        <label data-fasilitas-label="{{ $nama }}" class="relative cursor-pointer select-none rounded-xl border-2 p-2.5 transition-all duration-200 border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600 dark:hover:bg-gray-700 has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50 has-[:checked]:ring-2 has-[:checked]:ring-teal-200 dark:has-[:checked]:border-teal-500 dark:has-[:checked]:bg-teal-500/10 dark:has-[:checked]:ring-teal-500/30 sm:p-3">
                            <input type="checkbox" value="{{ $nama }}" wire:model.live="{{ $field }}" @checked(in_array($nama, $selected)) data-fasilitas-input="{{ $nama }}" class="peer sr-only">
                            <span class="absolute -top-1.5 -right-1.5 hidden h-5 w-5 items-center justify-center rounded-full bg-teal-600 text-white peer-checked:flex">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </span>
                            <span class="flex min-w-0 items-center gap-2 sm:gap-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600 dark:bg-white/90 dark:text-teal-600 sm:h-9 sm:w-9 sm:rounded-xl">
                                    @if ($svg)
                                        <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" aria-hidden="true">{!! $svg !!}</svg>
                                    @else
                                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @endif
                                </span>
                                <span class="min-w-0 break-words text-[11px] font-semibold leading-snug text-gray-700 dark:text-gray-200 sm:text-xs">{{ $nama }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    @once
    <script>
    (function () {
        // Mencentang satu pilihan otomatis melepas centang lawannya dalam
        // grup eksklusif yang sama (perilaku seperti radio, tapi bisa
        // di-uncheck semua). Tidak memakai disabled agar tidak terkunci
        // dan tidak bentrok dengan render ulang Livewire.
        function lawanDari(grup, nama) {
            var lawan = [];
            grup.forEach(function (pasangan) {
                if (pasangan.indexOf(nama) !== -1) {
                    pasangan.forEach(function (item) {
                        if (item !== nama) lawan.push(item);
                    });
                }
            });
            return lawan;
        }

        document.addEventListener('change', function (e) {
            var input = e.target && e.target.closest ? e.target.closest('[data-fasilitas-input]') : null;
            if (!input || !input.checked) return;
            var wadah = input.closest('[data-fasilitas-eksklusif]');
            if (!wadah) return;
            var grup = [];
            try { grup = JSON.parse(wadah.getAttribute('data-fasilitas-eksklusif') || '[]'); } catch (err) { grup = []; }
            var lawan = lawanDari(grup, input.getAttribute('data-fasilitas-input'));
            if (!lawan.length) return;
            lawan.forEach(function (item) {
                var el = wadah.querySelector('[data-fasilitas-input="' + String(item).replace(/"/g, '') + '"]');
                if (el && el.checked) {
                    el.checked = false;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    })();
    </script>
    @endonce
@else
    @php
        $daftarFasilitas = is_array($fasilitas)
            ? \App\Support\FacilityHelper::normalizeList($fasilitas)
            : \App\Support\FacilityHelper::normalizeList(
                \App\Support\FacilityHelper::fromString((string) $fasilitas)
            );
        // Semua fasilitas selalu ditampilkan (tidak dipotong / disembunyikan).
        $daftarTampil = $daftarFasilitas;
        $kartu = $tampilan === 'kartu';
        $kelasGrid = (int) $kolom === 2 ? 'grid-cols-2' : 'grid-cols-2 sm:grid-cols-3';
        $jumlahKartu = count($daftarTampil);
        $kelasItemKartu = '';
        if ($jumlahKartu === 1) {
            $kelasItemKartu = (int) $kolom === 2 ? 'col-span-2' : 'col-span-2 sm:col-span-3';
        } elseif ($jumlahKartu % 2 === 1) {
            $kelasItemKartu = 'last:col-span-2';
        }
        $kelasChip = $kompak
            ? 'max-w-full gap-2 rounded-xl bg-white px-2.5 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-teal-100 dark:bg-gray-800 dark:text-gray-200 dark:ring-teal-500/20'
            : 'max-w-full gap-1.5 rounded-xl bg-teal-50 px-3 py-2 text-xs font-medium text-teal-700 ring-1 ring-inset ring-teal-200 dark:bg-teal-500/10 dark:text-teal-300 dark:ring-teal-500/30';
        $kelasKotakIkon = $kompak
            ? 'h-7 w-7 rounded-lg bg-teal-50 dark:bg-white/90'
            : 'h-7 w-7 rounded-lg bg-white/70 dark:bg-white/90';
        $kelasIkon = 'h-4 w-4';
    @endphp

    @if ($kartu)
        <div class="grid {{ $kelasGrid }} gap-2">
            @foreach ($daftarTampil as $f)
                @php
                    $tampil = \App\Support\FacilityHelper::normalize($f);
                    $svgIkon = $ikonSvg[$tampil] ?? null;
                @endphp
                <div class="relative flex min-h-[60px] items-center gap-2.5 rounded-xl bg-white/95 px-2.5 py-2 shadow-sm ring-1 ring-inset ring-teal-100/80 transition hover:shadow-md dark:bg-gray-800/90 dark:ring-teal-500/15 {{ $kelasItemKartu }}">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-50 to-emerald-100 text-teal-600 ring-1 ring-inset ring-teal-100/70 dark:from-white/95 dark:to-white/90 dark:text-teal-600 dark:ring-white/20">
                        @if ($svgIkon)
                            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">{!! $svgIkon !!}</svg>
                        @else
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @endif
                    </span>
                    <span class="min-w-0 flex-1 break-words text-[11.5px] font-semibold leading-snug text-slate-700 dark:text-gray-100">{{ $tampil }}</span>
                    <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-emerald-400/80" aria-hidden="true"></span>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-wrap gap-1.5 sm:gap-2">
            @foreach ($daftarTampil as $f)
                @php
                    $tampil = \App\Support\FacilityHelper::normalize($f);
                    $svgIkon = $ikonSvg[$tampil] ?? null;
                @endphp
                <span class="flex min-w-0 items-center {{ $kelasChip }}">
                    <span class="flex shrink-0 items-center justify-center {{ $kelasKotakIkon }} text-teal-600">
                        @if ($svgIkon)
                            <svg class="{{ $kelasIkon }}" viewBox="0 0 24 24" aria-hidden="true">{!! $svgIkon !!}</svg>
                        @else
                            <svg class="{{ $kelasIkon }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @endif
                    </span>
                    <span class="min-w-0 flex-1 text-left leading-snug">{{ $tampil }}</span>
                </span>
            @endforeach
        </div>
    @endif
@endif