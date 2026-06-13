#!/usr/bin/env node
/**
 * Golden Promise — Playwright driver script.
 *
 * Launches headless Chromium against a local XAMPP-hosted instance,
 * navigates routes, takes screenshots, and exits.
 *
 * Usage:
 *   node driver.mjs                        # default: http://localhost/GP
 *   URL=http://192.168.1.5/GP node driver.mjs
 *   node driver.mjs --nav /login --ss /tmp/login.png
 *
 * Commands (passed as --cmd "nav /login" --cmd "ss login.png"):
 *   nav <path>        Navigate to <path> relative to base URL
 *   wait <sel>        Wait for selector
 *   click <sel>       Click on element matching selector
 *   fill <sel> <text> Fill an input field
 *   ss [file]         Take screenshot (default: shot.png in driver dir)
 *   html [sel]        Dump inner HTML of <sel> (or body) to stdout
 *   console           Dump browser console log
 *   errors            Dump browser console errors only
 *   eval <expr>       Evaluate JS in page context
 *   quit              Close browser
 */

import { chromium } from 'playwright';

const BASE = process.env.URL || 'http://localhost/GP';
const SCREENSHOT_DIR = process.env.SS_DIR || '/tmp/gp-shots';
const args = process.argv.slice(2);

async function run() {
  // Parse commands
  const cmds = [];
  for (let i = 0; i < args.length; i++) {
    if (args[i] === '--nav') cmds.push({ type: 'nav', val: args[++i] });
    else if (args[i] === '--wait') cmds.push({ type: 'wait', val: args[++i] });
    else if (args[i] === '--click') cmds.push({ type: 'click', val: args[++i] });
    else if (args[i] === '--fill') { cmds.push({ type: 'fill', sel: args[++i], text: args[++i] }); }
    else if (args[i] === '--ss') cmds.push({ type: 'ss', val: args[++i] });
    else if (args[i] === '--html') cmds.push({ type: 'html', val: args[++i] || 'body' });
    else if (args[i] === '--console') cmds.push({ type: 'console' });
    else if (args[i] === '--errors') cmds.push({ type: 'errors' });
    else if (args[i] === '--eval') cmds.push({ type: 'eval', val: args[++i] });
  }

  // If no commands, do a default sequence
  if (cmds.length === 0) {
    cmds.push({ type: 'nav', val: '/' });
    cmds.push({ type: 'wait', val: 'body' });
    cmds.push({ type: 'ss', val: 'home.png' });
    cmds.push({ type: 'nav', val: '/login' });
    cmds.push({ type: 'wait', val: 'body' });
    cmds.push({ type: 'ss', val: 'login.png' });
    cmds.push({ type: 'errors' });
  }

  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
  });

  const context = await browser.newContext({
    viewport: { width: 1280, height: 900 },
  });
  const page = await context.newPage();

  page.on('console', msg => {
    if (msg.type() === 'error') {
      console.error(`[CONSOLE ERROR] ${msg.text()}`);
    }
  });
  page.on('pageerror', err => {
    console.error(`[PAGE ERROR] ${err.message}`);
  });

  const { mkdir } = await import('fs');
  mkdir(SCREENSHOT_DIR, { recursive: true }, () => {});

  // Default screenshot path
  let shotCounter = 0;
  const nextShot = (name) => {
    if (name) return `${SCREENSHOT_DIR}/${name}`;
    shotCounter++;
    return `${SCREENSHOT_DIR}/shot_${shotCounter}.png`;
  };

  try {
    for (const cmd of cmds) {
      switch (cmd.type) {
        case 'nav': {
          const url = cmd.val.startsWith('http') ? cmd.val : `${BASE}${cmd.val}`;
          console.error(`→ NAV ${url}`);
          await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
          break;
        }
        case 'wait': {
          console.error(`→ WAIT ${cmd.val}`);
          await page.waitForSelector(cmd.val, { timeout: 10000 });
          break;
        }
        case 'click': {
          console.error(`→ CLICK ${cmd.val}`);
          await page.click(cmd.val);
          break;
        }
        case 'fill': {
          console.error(`→ FILL ${cmd.sel} = "${cmd.text}"`);
          await page.fill(cmd.sel, cmd.text);
          break;
        }
        case 'ss': {
          const path = nextShot(cmd.val);
          console.error(`→ SCREENSHOT ${path}`);
          await page.screenshot({ path, fullPage: false });
          console.log(`SCREENSHOT:${path}`);
          break;
        }
        case 'html': {
          const el = page.locator(cmd.val);
          const html = await el.innerHTML();
          console.log(html);
          break;
        }
        case 'console': {
          // Console messages already logged via event handler
          console.error('→ CONSOLE (errors reported above)');
          break;
        }
        case 'errors': {
          // Already captured via events above
          console.error('→ ERRORS (captured from page console)');
          break;
        }
        case 'eval': {
          console.error(`→ EVAL ${cmd.val}`);
          const result = await page.evaluate(cmd.val);
          if (result !== undefined) console.log(result);
          break;
        }
      }
    }
  } finally {
    await browser.close();
  }
}

run().catch(err => {
  console.error('DRIVER ERROR:', err);
  process.exit(1);
});
