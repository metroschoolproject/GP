const { execFileSync } = require('node:child_process');
const { existsSync, readFileSync, readdirSync, statSync } = require('node:fs');
const { join, resolve } = require('node:path');

const root = resolve(__dirname, '..');
const php = existsSync('/Applications/XAMPP/xamppfiles/bin/php')
  ? '/Applications/XAMPP/xamppfiles/bin/php'
  : 'php';

function filesUnder(directory, predicate) {
  const output = [];
  const visit = (path) => {
    for (const entry of readdirSync(path)) {
      const fullPath = join(path, entry);
      const stats = statSync(fullPath);
      if (stats.isDirectory()) {
        if (!['vendor', 'node_modules', 'uploads'].includes(entry)) visit(fullPath);
      } else if (predicate(fullPath)) {
        output.push(fullPath);
      }
    }
  };
  visit(join(root, directory));
  return output;
}

function run(command, args) {
  execFileSync(command, args, { cwd: root, stdio: 'pipe' });
}

function assertContains(file, text) {
  const contents = readFileSync(join(root, file), 'utf8');
  if (!contents.includes(text)) {
    throw new Error(`${file} is missing required text: ${text}`);
  }
}

const phpFiles = ['app', 'database', 'public']
  .flatMap((directory) => filesUnder(directory, (file) => file.endsWith('.php')));
for (const file of phpFiles) run(php, ['-l', file]);

for (const file of filesUnder('public/js', (path) => path.endsWith('.js'))) {
  run(process.execPath, ['--check', file]);
}

assertContains('package.json', '"test": "node tests/run-static-checks.js"');
assertContains('app/services/PayoutService.php', 'FOR UPDATE');
assertContains('app/services/PayoutService.php', 'payout_batch_id');
assertContains('database/migrations/2026_08_payout_lifecycle.sql', "'processing'");
assertContains('database/migrations/2026_08_payout_lifecycle.sql', "'refunded'");
assertContains('.env.example', 'PAYMENT_GATEWAY_SECRET=');

console.log(`Static checks passed: ${phpFiles.length} PHP files and public JavaScript syntax.`);
