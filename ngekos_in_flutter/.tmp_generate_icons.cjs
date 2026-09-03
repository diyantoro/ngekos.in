const sharp = require('C:/Users/Lenovo/AppData/Local/Temp/opencode/sharp_tmp/node_modules/sharp');
const path = require('path');
const fs = require('fs');

const PROJECT = 'C:/laragon/www/Ngekos.in/ngekos_in_flutter';
const TEAL = '#0D9488';
const TEAL_DARK = '#0F766E';
const WHITE = '#FFFFFF';

// Simple house SVG icon for the brand
function houseSvg(size, opts = {}) {
  const { bg = TEAL, fg = WHITE, padding = 0, borderRadius = 0 } = opts;
  const inner = size - padding * 2;
  const cx = size / 2;
  const cy = size / 2;
  
  // House proportions relative to inner size
  const roofH = inner * 0.3;
  const wallH = inner * 0.35;
  const wallW = inner * 0.55;
  const doorW = inner * 0.15;
  const doorH = inner * 0.22;
  
  const wallTop = cy + roofH * 0.15;
  const wallBottom = wallTop + wallH;
  const wallLeft = cx - wallW / 2;
  const wallRight = cx + wallW / 2;
  
  const roofTop = wallTop - roofH;
  const roofLeft = cx - wallW * 0.7;
  const roofRight = cx + wallW * 0.7;

  const mask = borderRadius > 0 
    ? `<rect width="${size}" height="${size}" rx="${borderRadius}" ry="${borderRadius}" fill="${bg}"/>`
    : `<rect width="${size}" height="${size}" fill="${bg}"/>`;

  return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
  <defs>
    <clipPath id="c">
      ${borderRadius > 0 
        ? `<rect width="${size}" height="${size}" rx="${borderRadius}" ry="${borderRadius}"/>`
        : `<rect width="${size}" height="${size}"/>`}
    </clipPath>
  </defs>
  <g clip-path="url(#c)">
    <rect width="${size}" height="${size}" fill="${bg}"/>
    <!-- Roof -->
    <polygon points="${cx},${roofTop} ${roofLeft},${wallTop} ${roofRight},${wallTop}" fill="${fg}"/>
    <!-- Walls -->
    <rect x="${wallLeft}" y="${wallTop}" width="${wallW}" height="${wallH}" fill="${fg}"/>
    <!-- Door -->
    <rect x="${cx - doorW/2}" y="${wallBottom - doorH}" width="${doorW}" height="${doorH}" rx="${doorW * 0.15}" fill="${bg}"/>
    <!-- Window left -->
    <rect x="${wallLeft + wallW * 0.12}" y="${wallTop + wallH * 0.15}" width="${wallW * 0.22}" height="${wallW * 0.18}" rx="${wallW * 0.03}" fill="${bg}"/>
    <!-- Window right -->
    <rect x="${wallRight - wallW * 0.12 - wallW * 0.22}" y="${wallTop + wallH * 0.15}" width="${wallW * 0.22}" height="${wallW * 0.18}" rx="${wallW * 0.03}" fill="${bg}"/>
  </g>
</svg>`;
}

async function generateIcon(size, output, opts = {}) {
  const svg = houseSvg(size, opts);
  await sharp(Buffer.from(svg)).png({ compressionLevel: 9, adaptiveFiltering: true }).toFile(output);
  const stat = fs.statSync(output);
  console.log(`  ${path.basename(output)}: ${size}x${size} (${(stat.size/1024).toFixed(1)}KB)`);
}

async function main() {
  const tmpDir = path.join(PROJECT, '.tmp_icons');
  if (!fs.existsSync(tmpDir)) fs.mkdirSync(tmpDir, { recursive: true });

  console.log('=== Generating 1024x1024 base icon ===');
  const baseIcon = path.join(tmpDir, 'base_1024.png');
  await generateIcon(1024, baseIcon, { bg: TEAL, fg: WHITE, borderRadius: 200 });

  // --- WEB ICONS ---
  console.log('\n=== Web Icons ===');
  const webDir = path.join(PROJECT, 'web');
  const webIconsDir = path.join(webDir, 'icons');
  
  await generateIcon(16, path.join(webDir, 'favicon.png'), { bg: TEAL, fg: WHITE });
  await generateIcon(192, path.join(webIconsDir, 'Icon-192.png'), { bg: TEAL, fg: WHITE, borderRadius: 36 });
  await generateIcon(512, path.join(webIconsDir, 'Icon-512.png'), { bg: TEAL, fg: WHITE, borderRadius: 96 });
  // Maskable: add 10% safe zone padding
  await generateIcon(192, path.join(webIconsDir, 'Icon-maskable-192.png'), { bg: TEAL, fg: WHITE, padding: 19 });
  await generateIcon(512, path.join(webIconsDir, 'Icon-maskable-512.png'), { bg: TEAL, fg: WHITE, padding: 51 });

  // --- ANDROID ICONS ---
  console.log('\n=== Android Icons ===');
  const densities = [
    { name: 'mipmap-mdpi', size: 48 },
    { name: 'mipmap-hdpi', size: 72 },
    { name: 'mipmap-xhdpi', size: 96 },
    { name: 'mipmap-xxhdpi', size: 144 },
    { name: 'mipmap-xxxhdpi', size: 192 },
  ];
  
  for (const d of densities) {
    const dir = path.join(PROJECT, 'android/app/src/main/res', d.name);
    await generateIcon(d.size, path.join(dir, 'ic_launcher.png'), { bg: TEAL, fg: WHITE, borderRadius: Math.round(d.size * 0.22) });
    await generateIcon(d.size, path.join(dir, 'ic_launcher_round.png'), { bg: TEAL, fg: WHITE });
  }

  // Android adaptive icons (anydpi-v26)
  const anydpiDir = path.join(PROJECT, 'android/app/src/main/res/mipmap-anydpi-v26');
  if (!fs.existsSync(anydpiDir)) fs.mkdirSync(anydpiDir, { recursive: true });

  // Generate foreground for adaptive icon (108dp * 3 = 324px for xxxhdpi, but we'll use a clean vector approach)
  // For adaptive icons, we generate vector drawable XML files
  const adaptiveIconXml = `<?xml version="1.0" encoding="utf-8"?>
<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android">
    <background android:drawable="@color/ic_launcher_background"/>
    <foreground android:drawable="@drawable/ic_launcher_foreground"/>
</adaptive-icon>`;
  
  const adaptiveIconRoundXml = `<?xml version="1.0" encoding="utf-8"?>
<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android">
    <background android:drawable="@color/ic_launcher_background"/>
    <foreground android:drawable="@drawable/ic_launcher_foreground"/>
</adaptive-icon>`;
  
  fs.writeFileSync(path.join(anydpiDir, 'ic_launcher.xml'), adaptiveIconXml);
  fs.writeFileSync(path.join(anydpiDir, 'ic_launcher_round.xml'), adaptiveIconRoundXml);

  // Generate foreground drawable (vector)
  const drawableDir = path.join(PROJECT, 'android/app/src/main/res/drawable');
  const foregroundXml = `<?xml version="1.0" encoding="utf-8"?>
<vector xmlns:android="http://schemas.android.com/apk/res/android"
    android:width="108dp"
    android:height="108dp"
    android:viewportWidth="108"
    android:viewportHeight="108">
    <!-- House icon centered in 108dp viewport (safe zone is center 72dp) -->
    <!-- Roof -->
    <path
        android:pathData="M54,28 L30,48 L78,48 Z"
        android:fillColor="#FFFFFF"/>
    <!-- Walls -->
    <path
        android:pathData="M34,48 L34,72 L74,72 L74,48 Z"
        android:fillColor="#FFFFFF"/>
    <!-- Door -->
    <path
        android:pathData="M49,72 L49,60 L59,60 L59,72 Z"
        android:fillColor="#0D9488"/>
    <!-- Window left -->
    <path
        android:pathData="M37,52 L37,58 L43,58 L43,52 Z"
        android:fillColor="#0D9488"/>
    <!-- Window right -->
    <path
        android:pathData="M65,52 L65,58 L71,58 L71,52 Z"
        android:fillColor="#0D9488"/>
</vector>`;
  fs.writeFileSync(path.join(drawableDir, 'ic_launcher_foreground.xml'), foregroundXml);

  // Android colors.xml for background
  const valuesDir = path.join(PROJECT, 'android/app/src/main/res/values');
  const colorsPath = path.join(valuesDir, 'colors.xml');
  let colorsContent = '';
  if (fs.existsSync(colorsPath)) {
    colorsContent = fs.readFileSync(colorsPath, 'utf-8');
  }
  if (!colorsContent.includes('ic_launcher_background')) {
    const colorEntry = `\n    <color name="ic_launcher_background">${TEAL}</color>`;
    if (colorsContent.includes('<resources>')) {
      colorsContent = colorsContent.replace('<resources>', `<resources>${colorEntry}`);
    } else {
      colorsContent = `<?xml version="1.0" encoding="utf-8"?>\n<resources>${colorEntry}\n</resources>`;
    }
    fs.writeFileSync(colorsPath, colorsContent);
    console.log('  Added ic_launcher_background to colors.xml');
  }

  // --- iOS ICONS ---
  console.log('\n=== iOS App Icons ===');
  const iosIconDir = path.join(PROJECT, 'ios/Runner/Assets.xcassets/AppIcon.appiconset');
  const iosSizes = [
    { name: 'Icon-App-20x20@1x.png', size: 20 },
    { name: 'Icon-App-20x20@2x.png', size: 40 },
    { name: 'Icon-App-20x20@3x.png', size: 60 },
    { name: 'Icon-App-29x29@1x.png', size: 29 },
    { name: 'Icon-App-29x29@2x.png', size: 58 },
    { name: 'Icon-App-29x29@3x.png', size: 87 },
    { name: 'Icon-App-40x40@1x.png', size: 40 },
    { name: 'Icon-App-40x40@2x.png', size: 80 },
    { name: 'Icon-App-40x40@3x.png', size: 120 },
    { name: 'Icon-App-60x60@2x.png', size: 120 },
    { name: 'Icon-App-60x60@3x.png', size: 180 },
    { name: 'Icon-App-76x76@1x.png', size: 76 },
    { name: 'Icon-App-76x76@2x.png', size: 152 },
    { name: 'Icon-App-83.5x83.5@2x.png', size: 167 },
    { name: 'Icon-App-1024x1024@1x.png', size: 1024 },
  ];
  
  for (const i of iosSizes) {
    const borderRadius = i.size >= 1024 ? Math.round(i.size * 0.225) : Math.round(i.size * 0.22);
    await generateIcon(i.size, path.join(iosIconDir, i.name), { bg: TEAL, fg: WHITE, borderRadius });
  }

  // Clean up temp
  fs.rmSync(tmpDir, { recursive: true, force: true });

  console.log('\n=== All icons generated! ===');
}

main().catch(err => { console.error(err); process.exit(1); });
