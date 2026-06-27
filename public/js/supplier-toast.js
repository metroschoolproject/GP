/* ── Supplier Toast Notification System ── */
(function () {
  'use strict';

  var CONTAINER_ID = 'sup-toast-container';
  var DURATION = 4000;
  var FADE_MS = 280;

  function ensureContainer() {
    if (document.getElementById(CONTAINER_ID)) return;

    var style = document.createElement('style');
    style.textContent =
      '#' + CONTAINER_ID + '{position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;max-width:380px;width:100%}' +
      '.sup-toast{pointer-events:auto;display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-radius:12px;border:1px solid transparent;font-family:Poppins,system-ui,sans-serif;font-size:13px;font-weight:600;line-height:1.45;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.12);opacity:0;transform:translateY(-12px) scale(.97);transition:opacity ' + FADE_MS + 'ms ease,transform ' + FADE_MS + 'ms ease;backdrop-filter:blur(8px)}' +
      '.sup-toast.is-visible{opacity:1;transform:translateY(0) scale(1)}' +
      '.sup-toast svg{width:18px;height:18px;flex-shrink:0;margin-top:1px}' +
      '.sup-toast-dismiss{margin-left:auto;background:none;border:none;cursor:pointer;color:currentColor;opacity:.7;padding:0;flex-shrink:0;display:flex}' +
      '.sup-toast-dismiss:hover{opacity:1}' +
      '.sup-toast-dismiss svg{width:14px;height:14px}' +
      '.sup-toast--success{background:#065f46;border-color:#059669}' +
      '.sup-toast--error{background:#991b1b;border-color:#dc2626}' +
      '.sup-toast--warning{background:#92400e;border-color:#d97706}' +
      '.sup-toast--info{background:#4f46a5;border-color:#6366f1}' +
      '@media(max-width:480px){#' + CONTAINER_ID + '{top:12px;right:12px;left:12px;max-width:none}}';
    document.head.appendChild(style);

    var container = document.createElement('div');
    container.id = CONTAINER_ID;
    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-atomic', 'false');
    document.body.appendChild(container);
  }

  var icons = {
    success: '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="6"/><path d="M5 8l2 2 4-4"/></svg>',
    error: '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="6"/><path d="M8 5v3M8 11h.01"/></svg>',
    warning: '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1.5L1 14h14L8 1.5z"/><path d="M8 6v3M8 11.5h.01"/></svg>',
    info: '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3M8 11h.01"/></svg>'
  };

  var closeIcon = '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>';

  function toast(message, type, opts) {
    type = type || 'info';
    opts = opts || {};
    var duration = opts.duration != null ? opts.duration : DURATION;
    var dismissible = opts.dismissible !== false;

    ensureContainer();
    var container = document.getElementById(CONTAINER_ID);

    var el = document.createElement('div');
    el.className = 'sup-toast sup-toast--' + type;
    el.setAttribute('role', 'status');

    var html = (icons[type] || icons.info) + '<span>' + escapeHtml(message) + '</span>';
    if (dismissible) {
      html += '<button class="sup-toast-dismiss" aria-label="Dismiss">' + closeIcon + '</button>';
    }
    el.innerHTML = html;

    var dismissBtn = el.querySelector('.sup-toast-dismiss');
    if (dismissBtn) {
      dismissBtn.addEventListener('click', function () { removeToast(el); });
    }

    container.appendChild(el);

    requestAnimationFrame(function () {
      requestAnimationFrame(function () { el.classList.add('is-visible'); });
    });

    if (duration > 0) {
      setTimeout(function () { removeToast(el); }, duration);
    }
    return el;
  }

  function removeToast(el) {
    if (!el || !el.parentNode) return;
    el.classList.remove('is-visible');
    setTimeout(function () {
      if (el.parentNode) el.parentNode.removeChild(el);
    }, FADE_MS);
  }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  window.supToast = toast;
  window.supToastSuccess = function (msg, opts) { return toast(msg, 'success', opts); };
  window.supToastError = function (msg, opts) { return toast(msg, 'error', opts); };
  window.supToastWarning = function (msg, opts) { return toast(msg, 'warning', opts); };
  window.supToastInfo = function (msg, opts) { return toast(msg, 'info', opts); };
})();
