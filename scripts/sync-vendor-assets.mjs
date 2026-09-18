// Copies the pieces of our npm dependencies we actually ship, into public/,
// so the running app needs zero external network access (no CDNs).
import { copyFileSync, mkdirSync, readdirSync, existsSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = dirname(dirname(fileURLToPath(import.meta.url)));
const nm = join(root, 'node_modules');

function ensureDir(p) {
  mkdirSync(p, { recursive: true });
}

function copy(src, dest) {
  ensureDir(dirname(dest));
  copyFileSync(src, dest);
}

// --- Chart.js ---
// Lives in public/vendor (not public/build) because `vite build` empties its
// own output directory, which would delete anything else stored there.
// Alpine is no longer vendored here: Livewire 3 ships and boots its own Alpine.
copy(join(nm, 'chart.js/dist/chart.umd.min.js'), join(root, 'public/vendor/chart.js'));

// --- Cairo + IBM Plex Mono fonts (Arabic-supporting weights only) ---
const fontsDir = join(root, 'public/fonts');
ensureDir(fontsDir);
const cairoSrc = join(nm, '@fontsource/cairo/files');
const wantedCairo = ['cairo-arabic-400-normal.woff2', 'cairo-arabic-600-normal.woff2', 'cairo-arabic-700-normal.woff2', 'cairo-latin-400-normal.woff2', 'cairo-latin-600-normal.woff2', 'cairo-latin-700-normal.woff2'];
for (const f of wantedCairo) {
  const src = join(cairoSrc, f);
  if (existsSync(src)) copy(src, join(fontsDir, f));
}

const monoSrc = join(nm, '@fontsource/ibm-plex-mono/files');
const wantedMono = ['ibm-plex-mono-latin-400-normal.woff2', 'ibm-plex-mono-latin-600-normal.woff2'];
for (const f of wantedMono) {
  const src = join(monoSrc, f);
  if (existsSync(src)) copy(src, join(fontsDir, f));
}

// --- Lucide icons we use across the app (curated subset, inlined as SVG) ---
const icons = [
  'layout-dashboard', 'users', 'graduation-cap', 'book-open', 'users-round', 'clipboard-list',
  'calendar-check', 'calendar-days', 'wallet', 'receipt', 'banknote', 'bar-chart-3', 'line-chart',
  'bell', 'settings', 'chevron-down', 'chevron-left', 'chevron-right', 'search', 'menu', 'x',
  'plus', 'more-horizontal', 'more-vertical', 'eye', 'pencil', 'trash-2', 'filter', 'log-out',
  'phone', 'mail', 'map-pin', 'clock', 'check', 'check-check', 'circle-check', 'circle-x', 'triangle-alert',
  'trending-up', 'trending-down', 'arrow-up-right', 'arrow-down-right', 'download', 'upload',
  'user-round-plus', 'file-text', 'star', 'building-2', 'shield-check', 'globe', 'palette',
  'chevron-up', 'sliders-horizontal', 'inbox', 'wifi-off', 'server-crash', 'loader-circle',
  'credit-card', 'wallet-cards', 'sun', 'moon', 'sparkles', 'timer', 'hash', 'id-card',
  'square-check-big', 'square', 'minus', 'circle-dot', 'lightbulb', 'megaphone', 'wrench',
  'droplet', 'zap', 'package', 'briefcase-business', 'notebook-pen', 'lock', 'languages',
  'layers', 'list-checks', 'percent', 'arrow-left', 'arrow-right', 'pause', 'play', 'shield-off',
];
// lucide-static renamed a few icons; map our stable template-facing names to the current file.
const aliases = {
  'bar-chart-3': 'chart-column',
  'line-chart': 'chart-line',
  'more-horizontal': 'ellipsis',
  'more-vertical': 'ellipsis-vertical',
};

const iconsDest = join(root, 'public/icons');
ensureDir(iconsDest);
let missing = [];
for (const icon of icons) {
  const sourceName = aliases[icon] ?? icon;
  const src = join(nm, 'lucide-static/icons', `${sourceName}.svg`);
  if (existsSync(src)) {
    copy(src, join(iconsDest, `${icon}.svg`));
  } else {
    missing.push(icon);
  }
}
if (missing.length) {
  console.warn('Missing lucide icons:', missing.join(', '));
}

console.log('Vendor assets synced.');
