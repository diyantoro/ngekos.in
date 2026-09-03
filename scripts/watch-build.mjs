import { spawn } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import chokidar from 'chokidar';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

const WATCH = [
  path.join(root, 'resources/views/**/*.blade.php'),
  path.join(root, 'resources/css/**/*.css'),
  path.join(root, 'resources/js/**/*.js'),
  path.join(root, 'tailwind.config.js'),
  path.join(root, 'vite.config.js'),
];

const IGNORE = new Set([path.join(root, 'public/build')]);

function debounce(fn, wait) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), wait);
  };
}

let building = false;
let queued = false;

function runBuild() {
  if (building) {
    queued = true;
    return;
  }
  building = true;
  const started = Date.now();
  console.log(`\n[watch-build] change detected -> npm run build ...`);
  const npmCmd = process.platform === 'win32' ? 'npm.cmd' : 'npm';
  const child = spawn(npmCmd, ['run', 'build'], {
    cwd: root,
    stdio: 'inherit',
  });
  child.on('close', (code) => {
    building = false;
    const secs = ((Date.now() - started) / 1000).toFixed(1);
    console.log(`[watch-build] build finished (${secs}s, exit ${code})` + (queued ? ' // running again...' : ''));
    if (queued) {
      queued = false;
      runBuild();
    }
  });
}

const rebuild = debounce(runBuild, 400);

const watcher = chokidar.watch(WATCH, {
  ignoreInitial: true,
  ignored: (p) => {
    const rel = path.resolve(p);
    for (const i of IGNORE) {
      if (rel === i || rel.startsWith(i + path.sep)) return true;
    }
    return false;
  },
});

watcher
  .on('add', (p) => rebuild(`add: ${p}`))
  .on('change', (p) => rebuild(`change: ${p}`))
  .on('unlink', (p) => rebuild(`unlink: ${p}`));

console.log('[watch-build] Watching for changes...');
console.log('Watching: ' + WATCH.join('\n  '));
console.log('\nPress Ctrl+C to stop.\n');

process.on('SIGINT', () => {
  watcher.close();
  process.exit(0);
});
