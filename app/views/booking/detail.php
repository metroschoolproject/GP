<?php
$booking = $booking ?? [];
$bookingId = (int)($booking['id'] ?? 0);
$items = $items ?? [];
$eventDetails = $eventDetails ?? [];
$suppliers = $suppliers ?? [];
$logs = $logs ?? [];
$vouchers = $vouchers ?? [];
$bookingRef = $bookingRef ?? '';
$depositPercent = (int)($depositPercent ?? BOOKING_DEPOSIT_PERCENT);
$platformFeePercent = (float)($platformFeePercent ?? get_platform_fee_percent());
$canReview = $canReview ?? false;
$existingReview = $existingReview ?? null;
$canEditReview = $canEditReview ?? false;
$pendingReplacement = $pendingReplacement ?? null;
$replacementHistory = is_array($replacementHistory ?? null) ? $replacementHistory : [];

$statusLabels = ['draft'=>'Draft','pending_supplier_response'=>'Awaiting Supplier Response','pending_payment'=>'Pending Payment','payment_submitted'=>'Verifying Payment','paid'=>'Paid','pending_admin'=>'Pending Admin','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled','cancellation_requested'=>'Cancellation Requested'];
$money = fn($v) => number_format((float)$v,0) . ' MMK';
$plain = function($v){ $t=(string)$v; for($i=0;$i<10;$i++){$d=html_entity_decode($t,ENT_QUOTES|ENT_HTML5,'UTF-8');if($d===$t)break;$t=$d;}return $t; };
$h = fn($v)=>htmlspecialchars($plain($v),ENT_QUOTES,'UTF-8');
$depositPayment = $depositPayment ?? [];
$packageSchedules = $packageSchedules ?? [];
$eventDetailsByItem = [];
foreach ($eventDetails as $ed) {
    $eventDetailsByItem[(int)($ed['booking_item_id'] ?? 0)] = $ed;
}
$fallbackEventDetail = $eventDetails[0] ?? [];
$summaryTotalTop = (float)($booking['total_amount'] ?? 0);
$summaryPaidTop = (float)($booking['paid_amount'] ?? 0);
$summaryBalanceTop = max(0, $summaryTotalTop - $summaryPaidTop);
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Booking Detail — Golden Promise</title>
<?php $v=file_exists(APPROOT.'/../public/css/app.css')?filemtime(APPROOT.'/../public/css/app.css'):time();?>
<link rel="stylesheet" href="<?=URLROOT?>/public/css/app.css?v=<?=$v?>">
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#f2e4d4;--card:#fcf8f5;--rule:rgba(178,143,110,0.22);--rule-strong:rgba(178,143,110,0.45);--plum:#6b4459;--plum-dk:#4e3141;--plum-lt:#9b7289;--rose:#c27a8e;--gold:#b8924a;--muted:#a08878;--text:#1a1118;--text2:#5c4a54;--danger:#b94b4b;--r-sm:8px;--r-md:14px;--r-lg:20px;--font-d:'Playfair Display',Georgia,serif;--font-b:'Poppins',system-ui,sans-serif;--pad-x:clamp(20px,5vw,72px);--ease-expo:cubic-bezier(0.19,1,0.22,1);}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:var(--font-b);font-size:14px;-webkit-font-smoothing:antialiased;min-height:100vh;display:flex;flex-direction:column}
a{color:inherit;text-decoration:none}
.gp-header{position:sticky;top:0;z-index:100;display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:24px;padding:0 var(--pad-x);height:68px;border-bottom:1px solid var(--rule);background:rgba(242,228,212,0.82);backdrop-filter:blur(24px) saturate(1.4)}
.gp-brand{display:flex;align-items:center;gap:12px;font-size:17px;font-weight:800}
.gp-brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:13px;font-weight:700}
.gp-header-nav{display:flex;align-items:center;gap:2px}
.gp-header-nav a{padding:7px 16px;border-radius:999px;font-size:13px;font-weight:600;color:var(--text2);transition:all .22s}
.gp-header-nav a:hover{color:var(--plum);background:rgba(107,68,89,0.08)}
.gp-header-actions{display:flex;align-items:center;gap:10px}
.gp-btn-sm{display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:10px;border:1px solid var(--rule-strong);font-size:11px;font-weight:700;color:var(--text2);transition:all .2s;text-decoration:none}
.gp-btn-sm:hover{border-color:var(--plum);color:var(--plum)}
.gp-btn-sm.primary{background:var(--plum);color:#fcf8f5;border-color:var(--plum)}
.gp-btn-sm.primary:hover{background:var(--plum-dk)}
.gp-btn-sm.danger{color:var(--danger);border-color:rgba(185,75,75,0.2)}
.gp-btn-sm.danger:hover{background:var(--danger);color:#fcf8f5}
.gp-profile-dropdown{position:relative}
.gp-profile-btn{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border-radius:999px;border:1px solid var(--rule-strong);background:var(--card);cursor:pointer;transition:all .2s;color:var(--plum);font-family:var(--font-b);font-size:13px;font-weight:600}
.gp-profile-btn:hover{border-color:var(--plum);background:rgba(107,68,89,.06)}
.gp-profile-avatar{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:12px;font-weight:800;letter-spacing:.5px}
.gp-profile-name{white-space:nowrap;max-width:100px;overflow:hidden;text-overflow:ellipsis}
.gp-profile-chevron{opacity:.6;transition:transform .2s}
.gp-profile-btn[aria-expanded="true"] .gp-profile-chevron{transform:rotate(180deg)}
.gp-profile-menu{position:absolute;top:calc(100% + 8px);right:0;min-width:180px;padding:6px;border-radius:12px;border:1px solid var(--rule);background:var(--card);box-shadow:0 12px 35px rgba(15,23,42,.1);opacity:0;visibility:hidden;transform:translateY(-4px);transition:all .15s var(--ease-expo)}
.gp-profile-btn[aria-expanded="true"]+.gp-profile-menu,
.gp-profile-menu.show{opacity:1;visibility:visible;transform:translateY(0)}
.gp-profile-menu-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--text);transition:all .15s}
.gp-profile-menu-item:hover{background:rgba(107,68,89,.06)}
.gp-profile-menu-item--danger{color:var(--danger)}
.gp-profile-menu-item--danger:hover{background:rgba(185,75,75,.08)}

.gp-page{position:relative;z-index:1;flex:1;padding:24px var(--pad-x) 80px;max-width:1180px;margin:0 auto;width:100%}
.gp-back{margin-bottom:20px}
.gp-back a{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);transition:color .2s}
.gp-back a:hover{color:var(--plum)}

.gp-detail-topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:0 0 22px;padding-top:2px}
.gp-detail-topbar-left{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.gp-detail-topbar-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}
.gp-detail-topbar-actions .gp-btn-sm,.gp-detail-topbar-actions .gp-badge{background:rgba(252,248,245,.62)}
.gp-detail-topbar-actions .gp-btn-sm.primary{background:var(--plum);color:#fffaf5;border-color:var(--plum)}
.gp-detail-topbar-actions .gp-btn-sm.danger{background:rgba(252,248,245,.68)}
.gp-detail-topbar-actions .gp-btn-sm.danger:hover{background:var(--danger);color:#fcf8f5;border-color:var(--danger)}
.gp-detail-back{display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:8px 14px;border-radius:10px;border:1px solid rgba(178,143,110,.26);background:rgba(252,248,245,.52);color:var(--muted);font-size:12px;font-weight:700;transition:all .18s}
.gp-detail-back:hover{border-color:var(--plum);color:var(--plum);background:rgba(252,248,245,.84)}
.gp-head{display:none}
.gp-head h1{font-family:var(--font-d);font-size:28px;font-weight:600}
.gp-head em{font-style:italic;color:var(--plum-lt)}
.gp-badge{display:inline-flex;padding:4px 12px;border-radius:999px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em}

.gp-layout{display:grid;grid-template-columns:minmax(0,1fr) 420px;gap:20px;align-items:start}
.gp-col{display:flex;flex-direction:column;gap:16px}

.gp-card{background:var(--card);border:1px solid var(--rule);border-radius:var(--r-lg);overflow:hidden}
.gp-status-card{order:2}
.gp-event-card{order:1}
.gp-card-h{padding:12px 18px;border-bottom:1px solid var(--rule);font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--plum)}
.gp-card-b{padding:14px 18px;display:flex;flex-direction:column;gap:8px}
.gp-info-box{background:rgba(252,248,245,.78);border:1px solid rgba(184,146,74,.28);border-radius:14px;padding:16px 20px;display:flex;align-items:flex-start;gap:14px;margin-bottom:12px;box-shadow:0 10px 24px rgba(26,17,24,.035)}
.gp-info-icon{flex-shrink:0;display:grid;place-items:center;width:24px;height:24px;border-radius:8px;background:rgba(184,146,74,.14);color:var(--gold)}
.gp-info-title{font-weight:800;font-size:13px;color:var(--plum);margin-bottom:2px}
.gp-info-copy{font-size:12px;color:var(--text2);line-height:1.55}
.gp-info-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.gp-journey-card{margin:0 0 18px;padding:20px 24px 22px;background:var(--card);border:1px solid var(--rule);border-radius:12px;box-shadow:0 1px 3px rgba(52,35,43,.04)}
.gp-journey-card.is-cancel{background:rgba(255,247,246,.82);border-color:rgba(185,75,75,.16)}
.gp-journey-head{display:block;margin-bottom:18px}
.gp-journey-ref{font-family:var(--font-b);font-size:15px;font-weight:700;color:var(--text);line-height:1.25}
.gp-journey-flow{margin-top:4px;font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
.gp-journey-status{flex-shrink:0;display:inline-flex;align-items:center;margin-top:4px;padding:6px 12px;border-radius:999px;background:#efe2e3;color:#6b4459;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em}
.gp-journey-card.is-cancel .gp-journey-status{background:rgba(185,75,75,.09);color:var(--danger)}
.gp-journey-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:0;max-width:none;margin:0}
.gp-journey-step{position:relative;display:flex;flex-direction:column;align-items:center;text-align:center;min-width:0}
.gp-journey-step:not(:last-child)::after{content:'';position:absolute;left:calc(50% + 16px);right:calc(-50% + 16px);top:16px;height:1px;background:var(--rule);z-index:0}
.gp-journey-card.is-cancel .gp-journey-step:not(:last-child)::after{background:rgba(185,75,75,.16)}
.gp-journey-dot{position:relative;z-index:1;display:grid;place-items:center;flex:0 0 32px;width:32px;height:32px;border-radius:50%;border:2px solid var(--rule);background:var(--card);color:var(--muted);font-size:11px;font-weight:700}
.gp-journey-step.is-complete .gp-journey-dot{border-color:#6b9e7e;background:#3d6b4f;color:#fff}
.gp-journey-step.is-current .gp-journey-dot{border-color:var(--plum);background:var(--plum);color:#fff;box-shadow:0 0 0 4px rgba(107,68,89,.12)}
.gp-journey-card.is-cancel .gp-journey-step.is-complete .gp-journey-dot{border-color:#d79a92;background:#d79a92;color:#fff}
.gp-journey-card.is-cancel .gp-journey-step.is-current .gp-journey-dot{border-color:var(--danger);background:var(--danger);color:#fff;box-shadow:0 0 0 4px rgba(185,75,75,.12)}
.gp-journey-text{position:relative;z-index:1;min-width:0;padding-top:6px}
.gp-journey-label{font-size:11px;font-weight:600;color:var(--text);line-height:1.25}
.gp-journey-state{margin-top:1px;font-size:9px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
.gp-journey-step.is-current .gp-journey-label{color:var(--plum);font-weight:700}
.gp-journey-card.is-cancel .gp-journey-step.is-current .gp-journey-label{color:var(--danger)}
.gp-service-detail-panel{display:none}
.gp-service-detail-panel.active{display:flex;flex-direction:column;gap:12px}
.gp-service-detail-title{font-family:var(--font-d);font-size:20px;font-weight:700;color:var(--text);line-height:1.15}
.gp-service-detail-meta{font-size:12px;color:var(--plum-lt);font-weight:600}
.gp-service-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.gp-field{display:flex;flex-direction:column;gap:2px;min-width:0;padding:10px 12px;border:1px solid rgba(178,143,110,.18);border-radius:12px;background:rgba(255,250,247,.58)}
.gp-field-l{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)}
.gp-field-v{font-size:13px;color:var(--text)}
.gp-field-v.quote{font-style:italic;color:var(--text2);padding:8px 12px;background:rgba(107,68,89,0.04);border-radius:var(--r-sm);border-left:3px solid var(--plum-lt)}
.gp-package-plan{margin-top:8px;padding-top:12px;border-top:1px solid var(--rule)}
.gp-package-plan-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:10px}
.gp-package-plan-title{font-family:var(--font-d);font-size:16px;font-weight:600;color:var(--plum)}
.gp-package-plan-copy{font-size:10px;color:var(--muted);margin-top:2px}
.gp-package-plan-count{flex-shrink:0;padding:3px 8px;border-radius:999px;background:rgba(184,146,74,.12);color:#8a682d;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
.gp-event-plan-list{display:flex;flex-direction:column}
.gp-event-plan-item{display:grid;grid-template-columns:62px 1fr;gap:10px;position:relative;padding:8px 0}
.gp-event-plan-item:not(:last-child)::after{content:'';position:absolute;left:30px;top:33px;bottom:-7px;width:1px;background:var(--rule-strong)}
.gp-event-plan-time{position:relative;z-index:1;align-self:start;padding:4px 5px;border:1px solid rgba(107,68,89,.14);border-radius:8px;background:#fffaf7;color:var(--plum);font-size:9px;font-weight:800;text-align:center;white-space:nowrap}
.gp-event-plan-service{font-size:11px;font-weight:700;color:var(--text);line-height:1.35}
.gp-event-plan-meta{font-size:9px;color:var(--muted);line-height:1.45;margin-top:2px}
.gp-event-plan-hall{display:inline-flex;margin-top:4px;padding:2px 6px;border-radius:999px;background:rgba(107,68,89,.06);color:var(--plum-lt);font-size:9px;font-weight:700}

.gp-timeline{display:flex;flex-direction:column;gap:12px}
.gp-tl-item{display:grid;grid-template-columns:48px 1fr;gap:14px;align-items:center;padding:13px 14px;position:relative;border-radius:16px;border:1px solid rgba(178,143,110,.14);background:rgba(252,248,245,.78);box-shadow:0 10px 24px rgba(26,17,24,.035)}
.gp-tl-item:nth-child(2n){background:rgba(184,146,74,.08)}
.gp-tl-item:nth-child(3n){background:rgba(194,122,142,.08)}
.gp-tl-item:nth-child(4n){background:rgba(107,68,89,.06)}
.gp-tl-dot{width:42px;height:42px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:0;flex-shrink:0;background:rgba(107,68,89,.10);color:var(--plum)}
.gp-tl-dot svg{width:21px;height:21px;display:block}
.gp-tl-dot.current{background:var(--plum);color:#fffaf3;animation:none}
.gp-tl-c{padding-top:0;min-width:0}
.gp-tl-t{font-size:14px;font-weight:800;color:var(--text);line-height:1.3}
.gp-tl-s{font-size:12px;color:var(--plum-lt);line-height:1.4;margin-top:2px}
.gp-tl-item:nth-child(2n) .gp-tl-s{color:#8a682d}
.gp-tl-item:nth-child(3n) .gp-tl-s{color:var(--rose)}
.gp-tl-item:nth-child(4n) .gp-tl-s{color:var(--plum)}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.08)}}

.gp-items-table{width:100%;border-collapse:collapse}
.gp-items-table th{text-align:left;font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);padding:6px 8px;border-bottom:1px solid var(--rule)}
.gp-items-table td{padding:10px 8px;font-size:13px;border-bottom:1px solid var(--rule)}
.gp-items-table tr:last-child td{border-bottom:none}
.gp-item-name{font-family:var(--font-d);font-weight:600}
.gp-item-detail{font-size:11px;color:var(--muted);display:flex;align-items:center;gap:4px;margin-top:2px}
.gp-item-status{font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;white-space:nowrap}

.gp-order-list{display:flex;flex-direction:column;gap:10px}
.gp-order-item{width:100%;display:grid;grid-template-columns:62px minmax(0,1fr) auto;gap:14px;align-items:center;padding:12px;border:1px solid transparent;border-radius:14px;background:transparent;text-align:left;font-family:var(--font-b);color:var(--text);transition:background .18s,border-color .18s,box-shadow .18s}
.gp-order-item:hover{background:rgba(107,68,89,.04);border-color:rgba(107,68,89,.12)}
.gp-order-item.active{background:rgba(184,146,74,.12);border-color:rgba(107,68,89,.28);box-shadow:0 12px 28px rgba(107,68,89,.08)}
.gp-order-item.active .gp-order-thumb{background:var(--plum);color:#fffaf3}
.gp-order-item.active .gp-order-name{color:var(--plum)}
.gp-order-item.active .gp-order-price{color:var(--plum)}
.gp-order-thumb{width:62px;height:62px;border-radius:14px;overflow:hidden;background:#f1e6dc;display:grid;place-items:center;color:var(--plum);font-size:18px;font-weight:800}
.gp-order-thumb img{width:100%;height:100%;object-fit:cover}
.gp-order-name{display:block;font-size:14px;font-weight:800;line-height:1.25;color:var(--text)}
.gp-order-sub{display:block;margin-top:3px;font-size:11px;font-weight:600;color:var(--muted)}
.gp-order-price{font-size:14px;font-weight:800;color:var(--text);white-space:nowrap}
.gp-summary{padding:16px 18px;display:flex;flex-direction:column;gap:10px;border-top:1px solid var(--rule)}
.gp-summary-r{display:flex;justify-content:space-between;gap:20px;font-size:13px;color:var(--text2)}
.gp-summary-r.total{font-size:18px;font-weight:800;color:var(--text);font-family:var(--font-b);padding-top:12px;margin-top:4px;border-top:1px solid var(--rule-strong)}

.gp-bottom-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}

.gp-review-section{background:var(--card);border:1px solid var(--rule);border-radius:var(--r-lg);overflow:hidden;margin-top:24px}
.gp-review-section-h{padding:14px 18px;border-bottom:1px solid var(--rule);background:rgba(184,146,74,.10);font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--gold)}
.gp-review-section-b{padding:18px}
.gp-star-picker{display:flex;gap:6px;margin-bottom:12px}
.gp-star-btn{background:none;border:none;cursor:pointer;font-size:26px;color:var(--rule-strong);padding:0;line-height:1;transition:color .15s}
.gp-star-btn.active,.gp-star-btn:hover,.gp-star-btn.hover{color:#d6a72d}
.gp-review-textarea{width:100%;padding:10px 12px;border-radius:var(--r-sm);border:1px solid var(--rule-strong);background:#faf7f2;font-family:var(--font-b);font-size:13px;color:var(--text);resize:vertical;min-height:90px;outline:none;transition:border-color .2s}
.gp-review-textarea:focus{border-color:var(--plum)}
.gp-review-chars{font-size:10px;color:var(--muted);text-align:right;margin-top:3px}
.gp-review-existing{display:flex;flex-direction:column;gap:8px}
.gp-review-stars{color:#d6a72d;font-size:18px;letter-spacing:-1px}
.gp-review-body{font-size:13px;color:var(--text2);line-height:1.6}
.gp-review-meta{font-size:11px;color:var(--muted)}
.gp-review-actions{display:flex;gap:8px;margin-top:6px}
.gp-flash-success{background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:var(--r-sm);padding:10px 14px;font-size:13px;margin-bottom:16px}
.gp-flash-error{background:#fdecea;color:#c62828;border:1px solid #ef9a9a;border-radius:var(--r-sm);padding:10px 14px;font-size:13px;margin-bottom:16px}
.gp-edit-form{margin-top:12px;display:none}

/* ── Payment Proof Pill ── */
.gp-proof-pill{border:1px solid var(--rule);border-radius:10px;background:var(--card);overflow:hidden;box-shadow:0 1px 3px rgba(52,35,43,.04)}
.gp-proof-toggle{display:flex;align-items:center;gap:10px;width:100%;padding:12px 16px;border:0;background:transparent;font-family:var(--font-b);cursor:pointer;text-align:left;transition:background .15s}
.gp-proof-toggle:hover{background:rgba(107,68,89,.03)}
.gp-proof-icon{flex-shrink:0;display:grid;place-items:center;width:36px;height:36px;border-radius:10px;background:rgba(184,146,74,.12);color:var(--gold)}
.gp-proof-label{font-size:13px;font-weight:700;color:var(--text);white-space:nowrap}
.gp-proof-meta{font-size:12px;font-weight:500;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.gp-proof-chevron{flex-shrink:0;margin-left:auto;display:grid;place-items:center;color:var(--muted);transition:transform .2s}
.gp-proof-toggle[aria-expanded="true"] .gp-proof-chevron{transform:rotate(180deg)}
.gp-proof-detail{padding:0 16px 14px}
.gp-proof-detail-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px 16px}

@media(max-width:900px){.gp-layout{grid-template-columns:1fr}.gp-detail-topbar{align-items:flex-start;flex-direction:column}.gp-detail-topbar-actions{justify-content:flex-start}.gp-journey-steps{max-width:100%}}
@media(max-width:768px){.gp-service-detail-grid{grid-template-columns:1fr}.gp-order-item{grid-template-columns:54px minmax(0,1fr);align-items:start}.gp-order-price{grid-column:2;font-size:13px}}
@media(max-width:640px){.gp-header-nav{display:none}:root{--pad-x:16px}.gp-detail-topbar{margin-bottom:18px}.gp-journey-card{border-radius:12px;padding:18px 14px 20px}.gp-journey-head{grid-template-columns:1fr;margin-bottom:20px}.gp-journey-ref{font-size:16px}.gp-journey-steps{grid-template-columns:1fr;gap:12px}.gp-journey-step{align-items:flex-start;text-align:left;flex-direction:row}.gp-journey-step:not(:last-child)::after{left:16px;right:auto;top:32px;bottom:-20px;width:1px;height:auto}.gp-journey-text{padding-top:4px}.gp-order-item{padding:10px}.gp-order-thumb{width:54px;height:54px}}</style>
</head><body>

<?php $gpNavActive = 'bookings'; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<main class="gp-page">
  <?php
    $currentStatus = $booking['status'] ?? '';
    $heroHasRemainingBalance = in_array($currentStatus, ['paid', 'payment_verified', 'confirmed', 'pending_final_payment', 'finalized'], true) && $summaryBalanceTop > 0;

    $journeyStatus = strtolower((string)($booking['status'] ?? ''));
    $journeyIsCancellation = in_array($journeyStatus, ['cancellation_requested', 'cancelled'], true);
    $journeyIsReplacement = !empty($pendingReplacement);
    $journeyTone = $journeyIsCancellation ? 'cancel' : ($journeyIsReplacement ? 'replacement' : 'normal');
    $journeyStatusLabel = $statusLabels[$journeyStatus] ?? ucfirst(str_replace('_', ' ', $journeyStatus ?: 'booking placed'));

    if ($journeyIsReplacement) {
        $journeyFlowLabel = 'Replacement Booking Journey';
        $journeySteps = [
            'Booking Confirmed',
            'Supplier Replacement Proposed',
            'Difference Payment Required',
            'Replacement Approved',
            'Updated Booking Confirmed',
        ];
        $replacementStatus = strtolower((string)($pendingReplacement['status'] ?? ''));
        $replacementDelta = (float)($pendingReplacement['price_delta'] ?? 0);
        $journeyCurrent = 1;
        if ($replacementDelta > 0) $journeyCurrent = 2;
        if (in_array($replacementStatus, ['approved', 'accepted'], true)) $journeyCurrent = 3;
        if (in_array($replacementStatus, ['confirmed', 'completed'], true)) $journeyCurrent = 4;
        $journeyCopy = 'Your confirmed booking is being updated with a replacement supplier.';
    } elseif ($journeyIsCancellation) {
        $journeyFlowLabel = 'Cancellation Booking Journey';
        $supplierInitiatedJourney = false;
        foreach ($suppliers as $sup) {
            if (($sup['status'] ?? '') === 'supplier_cancellation_requested') { $supplierInitiatedJourney = true; break; }
        }
        $journeySteps = [
            'Booking Placed',
            $supplierInitiatedJourney ? 'Supplier Requests Cancellation' : 'Cancellation Requested',
            'Cancelled',
        ];
        $journeyCurrent = $journeyStatus === 'cancelled' ? 2 : 1;
        $journeyCopy = $supplierInitiatedJourney
            ? 'Your supplier has requested to cancel this booking. Admin will review and process your refund.'
            : 'Your cancellation progress is shown here in soft red for clarity.';
    } else {
        $journeyFlowLabel = 'Normal Booking Journey';
        $journeySteps = [
            'Booking Placed',
            'Deposit Paid',
            'Booking Confirmed',
            'Remaining Payment',
            'Fully Paid',
            'Service Completed',
        ];
        if ($journeyStatus === 'completed') {
            $journeyCurrent = 5;
        } elseif ($journeyStatus === 'finalized') {
            $journeyCurrent = 4;
        } elseif ($journeyStatus === 'pending_final_payment') {
            $journeyCurrent = 3;
        } elseif ($journeyStatus === 'confirmed') {
            $journeyCurrent = 3;
        } elseif (in_array($journeyStatus, ['payment_submitted', 'pending_admin', 'paid'], true)) {
            $journeyCurrent = 1;
        } elseif ($journeyStatus === 'pending_payment') {
            $journeyCurrent = 0;
        } else {
            $journeyCurrent = 0;
        }
        $journeyCopy = 'Follow each milestone from booking placement through completion.';
    }
  ?>

  <div class="gp-detail-topbar" aria-label="Booking actions">
      <div class="gp-detail-topbar-left">
        <a class="gp-detail-back" href="<?=URLROOT?>/booking/myBookings">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
          Back to Bookings
        </a>
      </div>
      <div class="gp-detail-topbar-actions">
        <?php if (in_array($booking['status']??'', ['draft', 'pending_payment']) && ($booking['payment_status'] ?? '') === 'unpaid'): ?>
          <a class="gp-btn-sm primary" href="<?=URLROOT?>/booking/pay/<?=(int)($booking['id']??0)?>">Proceed to Payment</a>
        <?php elseif (($booking['status']??'') === 'pending_supplier_response'): ?>
          <span class="gp-btn-sm" style="cursor:default;opacity:0.78;" title="Payment available after supplier confirms">Awaiting Supplier Response</span>
        <?php endif; ?>
        <?php if ($heroHasRemainingBalance && !($hasPendingRemaining ?? false)): ?>
          <a class="gp-btn-sm primary" href="<?=URLROOT?>/booking/payRemaining/<?= $bookingId ?>">Pay Remaining Balance</a>
        <?php elseif ($heroHasRemainingBalance && ($hasPendingRemaining ?? false)): ?>
          <span class="gp-btn-sm" style="cursor:default;opacity:0.78;">Remaining Payment Under Review</span>
        <?php endif; ?>
        <?php if (!in_array($booking['status']??'', ['cancelled','cancellation_requested','completed'])): ?>
          <a class="gp-btn-sm danger" href="<?=URLROOT?>/booking/cancel/<?=(int)($booking['id']??0)?>">Request Cancellation</a>
        <?php endif; ?>
      </div>
  </div>

  <section class="gp-journey-card<?= $journeyTone === 'cancel' ? ' is-cancel' : '' ?>" aria-label="Booking Journey">
    <div class="gp-journey-head">
      <div class="gp-journey-ref">#<?= $h($bookingRef) ?></div>
      <div class="gp-journey-flow"><?= $h($journeyFlowLabel) ?></div>
    </div>
    <div class="gp-journey-steps">
      <?php foreach ($journeySteps as $stepIndex => $stepLabel): ?>
        <?php
          $stepClass = $stepIndex < $journeyCurrent ? 'is-complete' : ($stepIndex === $journeyCurrent ? 'is-current' : 'is-upcoming');
          $stepState = $stepIndex < $journeyCurrent ? 'Completed' : ($stepIndex === $journeyCurrent ? 'Current' : 'Upcoming');
        ?>
        <div class="gp-journey-step <?= $stepClass ?>">
          <div class="gp-journey-dot" aria-hidden="true"><?= $stepIndex < $journeyCurrent ? '✓' : ($stepIndex + 1) ?></div>
          <div class="gp-journey-text">
            <div class="gp-journey-label"><?= $h($stepLabel) ?></div>
            <div class="gp-journey-state"><?= $h($stepState) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if (in_array($currentStatus, ['paid', 'confirmed'], true) && $summaryBalanceTop > 0): ?>
  <div class="gp-info-box">
    <div class="gp-info-icon" aria-hidden="true">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
    </div>
    <div>
      <div class="gp-info-title">Deposit paid — complete full payment</div>
      <div class="gp-info-copy">Your deposit has been paid. Remaining balance: <strong><?= $money($summaryBalanceTop) ?></strong>. You can pay now to complete the full payment.</div>
      <div class="gp-info-actions">
        <?php if (!($hasPendingRemaining ?? false)): ?>
          <a class="gp-btn-sm primary" href="<?=URLROOT?>/booking/payRemaining/<?= $bookingId ?>">Pay Remaining Balance (<?= $money($summaryBalanceTop) ?>)</a>
        <?php else: ?>
          <span class="gp-btn-sm" style="cursor:default;opacity:0.78;">Remaining Payment Under Review</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($replacementHistory)): ?>
    <?php
    $replStatusLabels = [
        'pending_admin' => 'Finding replacement',
        'declined_again' => 'Finding another replacement',
        'rejected_by_customer' => 'You declined this option',
        'pending_customer' => 'Waiting for your approval',
        'assigned' => 'New supplier assigned',
    ];
    $badgeColor = function ($status) {
        return match ($status) {
            'pending_customer' => 'background:#fef3c7;color:#92400e;border-color:#fcd34d',
            'assigned' => 'background:#dbeafe;color:#1e40af;border-color:#bfdbfe',
            'rejected_by_customer' => 'background:#fee2e2;color:#991b1b;border-color:#fecaca',
            'declined_again' => 'background:#fef3c7;color:#92400e;border-color:#fcd34d',
            default => 'background:#f3f4f6;color:#57534e;border-color:#e5e7eb',
        };
    };
    ?>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;margin-bottom:20px">
      <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6d4c5b" stroke-width="2"><path d="M16 3l5 5-5 5M21 8H8a5 5 0 0 0 0 10h1"/></svg>
        <strong style="font-size:14px;color:#1f2937">Service Replacement</strong>
      </div>
      <div style="padding:0 18px">
      <?php foreach ($replacementHistory as $idx => $repl):
        $isLast = $idx === count($replacementHistory) - 1;
        $status = (string)($repl['status'] ?? '');
        $oldService = $repl['old_service_name'] ?? 'A service';
        $oldSupplier = $repl['old_shop_name'] ?? $repl['old_supplier_name'] ?? 'Original supplier';
        $newSupplier = $repl['new_shop_name'] ?? $repl['new_service_name'] ?? 'New supplier';
        $newPrice = (float)($repl['new_price'] ?? 0);
        $oldPrice = (float)($repl['old_price'] ?? 0);
        $delta = (float)($repl['price_delta'] ?? 0);
        $reason = $repl['decline_reason'] ?? '';
        $proofSubmitted = !empty($repl['customer_approved_at']) && !empty($repl['delta_payment_slip']);
        $proposedAt = $repl['proposed_at'] ?? null;
        $createdAt = $repl['created_at'] ?? null;
        $deadline = $proposedAt ? date('M d', strtotime($proposedAt . ' + 3 days')) : null;
        $daysLeft = $proposedAt ? max(0, (int)((strtotime($proposedAt) + 3*86400) - time()) / 86400) : null;
      ?>
        <div style="display:flex;gap:12px;padding:14px 0;<?= $isLast ? '' : 'border-bottom:1px solid #f3f4f6' ?>">
          <!-- Timeline dot + line -->
          <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0">
            <div style="width:10px;height:10px;border-radius:50%;border:2px solid <?= $status === 'pending_customer' ? '#f59e0b' : ($status === 'rejected_by_customer' ? '#dc2626' : '#6d4c5b') ?>;background:<?= $status === 'pending_customer' ? '#fef3c7' : ($status === 'rejected_by_customer' ? '#fee2e2' : '#6d4c5b') ?>"></div>
            <?php if (!$isLast): ?><div style="width:2px;flex:1;min-height:20px;background:#e5e7eb"></div><?php endif; ?>
          </div>
          <!-- Content -->
          <div style="flex:1;min-width:0;padding-bottom:2px">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">
              <strong style="font-size:13px;color:#1f2937"><?= $h($oldService) ?> — <?= $h($oldSupplier) ?></strong>
              <span style="display:inline-flex;align-items:center;border:1px solid;border-radius:20px;padding:2px 8px;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;<?= $badgeColor($status) ?>"><?= $h($replStatusLabels[$status] ?? $status) ?></span>
            </div>
            <?php if ($reason !== ''): ?>
            <div style="font-size:12px;color:#991b1b;margin-bottom:4px">Reason: <?= $h($reason) ?></div>
            <?php endif; ?>
            <?php if ($status === 'pending_customer'): ?>
              <div style="font-size:12px;color:#7b5c69;margin-bottom:6px">
                <?= $h($newSupplier) ?> is available<?= $newPrice > 0 ? ' at ' . number_format($newPrice, 0) . ' MMK' : '' ?>.
                <?php if ($delta > 0): ?><strong style="color:#dc2626">+<?= number_format($delta, 0) ?> MMK more.</strong><?php endif; ?>
                <?php if ($deadline): ?>⏰ Respond by <strong><?= $deadline ?></strong><?php endif; ?>
              </div>
              <?php if (!$proofSubmitted): ?>
              <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="<?=URLROOT?>/booking/payReplacementDelta/<?= (int)$repl['id'] ?>" style="background:#6d4c5b;color:#fff;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:700;text-decoration:none">Approve &amp; Pay →</a>
                <form method="POST" action="<?=URLROOT?>/booking/rejectReplacement/<?= (int)$repl['id'] ?>" style="margin:0" onsubmit="return confirm('Decline this replacement? Admin will find another option.')">
                  <input type="hidden" name="csrf_token" value="<?= $h(csrf_token()) ?>">
                  <button type="submit" style="background:#fff;color:#991b1b;border:1px solid #fecaca;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer">Decline</button>
                </form>
              </div>
              <?php else: ?>
              <div style="font-size:12px;color:#92400e">⏳ Payment proof submitted — admin is verifying</div>
              <?php endif; ?>
            <?php elseif ($status === 'rejected_by_customer'): ?>
              <div style="font-size:12px;color:#7b5c69">You declined this replacement. Admin will find another option.</div>
            <?php elseif ($status === 'assigned'): ?>
              <div style="font-size:12px;color:#1e40af"><?= $h($newSupplier) ?> has been assigned. Waiting for them to confirm.</div>
            <?php else: ?>
              <div style="font-size:12px;color:#7b5c69">Admin is finding a replacement supplier.</div>
            <?php endif; ?>
            <?php if ($createdAt): ?><div style="font-size:10px;color:#9ca3af;margin-top:4px"><?= date('M d, H:i', strtotime((string)$createdAt)) ?></div><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="gp-head">
    <h1>Booking <?= $h($bookingRef) ?></h1>
  </div>

  <?php if ($currentStatus === 'pending_supplier_response'): ?>
  <div class="gp-info-box">
    <div class="gp-info-icon" aria-hidden="true">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
    </div>
    <div>
      <div class="gp-info-title">Waiting for supplier confirmation</div>
      <div class="gp-info-copy">Your booking request has been sent. The supplier has up to 48 hours to accept or decline. You will be notified when they respond.</div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($currentStatus === 'pending_payment' && ($booking['payment_status'] ?? '') === 'unpaid'): ?>
  <div class="gp-info-box">
    <div class="gp-info-icon" aria-hidden="true">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7 10 17l-5-5"/></svg>
    </div>
    <div>
      <div class="gp-info-title">Supplier accepted — please complete payment</div>
      <div class="gp-info-copy">Your booking request was accepted! Please pay your 20% deposit below to lock in your booking.</div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($currentStatus === 'payment_submitted'): ?>
  <?php
    $proofBank = !empty($depositPayment['bank_name']) ? $depositPayment['bank_name'] : '';
    $proofRef = !empty($depositPayment['transaction_ref']) ? $depositPayment['transaction_ref'] : '';
    $proofAmount = !empty($depositPayment['paid_amount']) ? number_format((float)$depositPayment['paid_amount'], 0) . ' MMK' : '';
    $proofDate = !empty($depositPayment['paid_at']) ? date('M j', strtotime($depositPayment['paid_at'])) : '';
    $proofMetaParts = array_filter([$proofBank, $proofAmount, $proofDate]);
    $proofMeta = !empty($proofMetaParts) ? implode(' · ', $proofMetaParts) : '';
  ?>
  <div class="gp-proof-pill" id="paymentProofPill" style="margin-bottom:4px;">
    <button class="gp-proof-toggle" type="button" onclick="toggleProofDetails()" aria-expanded="false">
      <span class="gp-proof-icon" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </span>
      <span class="gp-proof-label">Payment under review</span>
      <?php if ($proofMeta !== ''): ?>
      <span class="gp-proof-meta"><?= $h($proofMeta) ?></span>
      <?php endif; ?>
      <span class="gp-proof-chevron" aria-hidden="true">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </span>
    </button>
    <?php if (!empty($depositPayment)): ?>
    <div class="gp-proof-detail" id="proofDetail" style="display:none;">
      <div class="gp-proof-detail-grid">
        <?php if (!empty($depositPayment['paid_at'])): ?>
        <div><div class="gp-field-l">Transfer Date</div><div class="gp-field-v"><?= $h(date('d M Y, g:i A', strtotime($depositPayment['paid_at']))) ?></div></div>
        <?php endif; ?>
        <?php if (!empty($depositPayment['bank_name'])): ?>
        <div><div class="gp-field-l">Bank / Method</div><div class="gp-field-v"><?= $h($depositPayment['bank_name']) ?></div></div>
        <?php endif; ?>
        <?php if (!empty($depositPayment['transaction_ref'])): ?>
        <div><div class="gp-field-l">Reference</div><div class="gp-field-v" style="font-family:monospace;"><?= $h($depositPayment['transaction_ref']) ?></div></div>
        <?php endif; ?>
        <?php if (!empty($depositPayment['paid_amount'])): ?>
        <div><div class="gp-field-l">Amount Sent</div><div class="gp-field-v"><?= number_format((float)$depositPayment['paid_amount'], 0) ?> MMK</div></div>
        <?php endif; ?>
        <?php if ((float)($depositPayment['platform_fee'] ?? 0) > 0): ?>
        <div><div class="gp-field-l">Platform Fee (<?= rtrim(rtrim(number_format($platformFeePercent, 2), '0'), '.') ?>%)</div><div class="gp-field-v"><?= number_format((float)$depositPayment['platform_fee'], 0) ?> MMK</div></div>
        <?php endif; ?>
      </div>
      <div class="gp-info-copy" style="margin-top:10px;padding-top:10px;border-top:1px solid var(--rule);">Our team is reviewing your transfer. No action needed from you right now.</div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php
    // Remaining balance state — available after deposit is verified (paid, confirmed, or later)
    $isConfirmedOrLater = in_array($currentStatus, ['paid', 'payment_verified', 'confirmed', 'pending_final_payment', 'finalized'], true);
    $summaryBalanceEarly = max(0, (float)($booking['total_amount'] ?? 0) - (float)($booking['paid_amount'] ?? 0));
    $hasRemainingBalance = $isConfirmedOrLater && $summaryBalanceEarly > 0;
    $hasPendingRemaining = $hasPendingRemaining ?? false;
    $eventDateDisplay = $eventDate ?? null;
    $remainingPaymentsList = $remainingPayments ?? [];
  ?>

  <div class="gp-layout">
    <!-- LEFT: Timeline -->
    <div class="gp-col">
      <div class="gp-card gp-status-card">
        <div class="gp-card-h">Status History</div>
        <div class="gp-card-b">
          <?php if (!empty($logs)): ?>
          <div class="gp-timeline">
            <?php foreach ($logs as $l):
              $isLast = $l === end($logs);
              $isCurrent = $l['new_status'] === ($booking['status']??'');
              $logStatus = strtolower((string)($l['new_status'] ?? ''));
              if (str_contains($logStatus, 'payment')) {
                $statusIcon = '<path d="M3 7h18v10H3z"/><path d="M3 10h18"/><path d="M7 15h3"/>';
              } elseif (str_contains($logStatus, 'cancel')) {
                $statusIcon = '<circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/>';
              } elseif ($logStatus === 'completed') {
                $statusIcon = '<path d="M12 2l2.8 6.2 6.7.7-5 4.5 1.4 6.6L12 16.6 6.1 20l1.4-6.6-5-4.5 6.7-.7z"/>';
              } elseif (in_array($logStatus, ['confirmed', 'paid'], true)) {
                $statusIcon = '<path d="M20 7L10 17l-5-5"/>';
              } elseif (str_contains($logStatus, 'pending') || str_contains($logStatus, 'review')) {
                $statusIcon = '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>';
              } else {
                $statusIcon = '<path d="M8 3v4"/><path d="M16 3v4"/><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M4 10h16"/>';
              }
            ?>
            <div class="gp-tl-item">
              <div class="gp-tl-dot <?=$isCurrent?'current':($isLast?'past':'past')?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $statusIcon ?></svg>
              </div>
              <div class="gp-tl-c">
                <div class="gp-tl-t"><?=ucfirst(str_replace('_',' ',$l['new_status']??''))?></div>
                <div class="gp-tl-s"><?=date('d M, g:i A',strtotime($l['created_at']))?><?=$l['note']?' — '.$h($l['note']):''?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
            <div style="color:var(--muted);font-size:13px;padding:8px 0;">No status updates yet</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="gp-card gp-event-card">
        <div class="gp-card-h">Event Details</div>
        <div class="gp-card-b">
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $idx => $item): ?>
              <?php
                $itemId = (int)($item['id'] ?? 0);
                $ed = $eventDetailsByItem[$itemId] ?? ($eventDetails[$idx] ?? $fallbackEventDetail);
                $eventDateRaw = $ed['event_date'] ?? ($item['booking_date'] ?? null);
                $startRaw = $ed['start_time'] ?? ($item['start_time'] ?? null);
                $endRaw = $ed['end_time'] ?? ($item['end_time'] ?? null);
                $hallName = trim((string)($item['venue_room_name'] ?? ''));
                $venueName = trim((string)($item['venue_name'] ?? ''));
                $location = trim((string)($ed['location'] ?? ''));
                $venueText = $location !== '' ? $location : trim($hallName . ($venueName !== '' ? ' · ' . $venueName : ''));
                $category = trim((string)($item['category_name'] ?? 'Service'));
                $supplier = trim((string)($item['supplier_name'] ?? 'Golden Promise'));
                $packagePlan = $packageSchedules[$itemId] ?? [];
              ?>
              <section class="gp-service-detail-panel <?= $idx === 0 ? 'active' : '' ?>" data-service-panel="<?= $itemId ?>" aria-label="<?= $h($item['service_name'] ?? 'Service') ?> details">
                <div>
                  <div class="gp-service-detail-title"><?= $h($item['service_name'] ?? 'Wedding Service') ?></div>
                  <div class="gp-service-detail-meta"><?= $h($category) ?> · <?= $h($supplier) ?></div>
                </div>
                <div class="gp-service-detail-grid">
                  <?php if (!empty($eventDateRaw)): ?>
                  <div class="gp-field">
                    <span class="gp-field-l">Date</span>
                    <span class="gp-field-v"><?= $h(date('l, d M Y', strtotime((string)$eventDateRaw))) ?></span>
                  </div>
                  <?php endif; ?>
                  <?php if (!empty($startRaw) || !empty($endRaw)): ?>
                  <div class="gp-field">
                    <span class="gp-field-l">Time</span>
                    <span class="gp-field-v">
                      <?= !empty($startRaw) ? $h(date('g:i A', strtotime((string)$startRaw))) : 'TBD' ?>
                      <?php if (!empty($endRaw)): ?> — <?= $h(date('g:i A', strtotime((string)$endRaw))) ?><?php endif; ?>
                    </span>
                  </div>
                  <?php endif; ?>
                  <?php if (!empty($ed['guest_count'])): ?>
                  <div class="gp-field"><span class="gp-field-l">Guests</span><span class="gp-field-v"><?=(int)$ed['guest_count']?></span></div>
                  <?php endif; ?>
                  <?php if ($venueText !== ''): ?>
                  <div class="gp-field"><span class="gp-field-l">Venue</span><span class="gp-field-v"><?=$h($venueText)?></span></div>
                  <?php endif; ?>
                  <?php if (!empty($ed['contact_phone'])): ?>
                  <div class="gp-field"><span class="gp-field-l">Contact Phone</span><span class="gp-field-v"><?=$h($ed['contact_phone'])?></span></div>
                  <?php endif; ?>
                  <div class="gp-field"><span class="gp-field-l">Status</span><span class="gp-field-v"><?= $h(ucfirst($item['status'] ?? 'pending')) ?></span></div>
                </div>
                <?php if (!empty($ed['special_requests'])): ?>
                <div class="gp-field">
                  <span class="gp-field-l">Special Requests</span>
                  <span class="gp-field-v quote">"<?=$h($ed['special_requests'])?>"</span>
                </div>
                <?php endif; ?>

                <?php if (!empty($packagePlan)): ?>
                <section class="gp-package-plan" aria-label="<?= $h($item['service_name'] ?? 'Package') ?> event plan">
                  <div class="gp-package-plan-head">
                    <div>
                      <div class="gp-package-plan-title">Included service plan</div>
                      <div class="gp-package-plan-copy">Your included services are arranged for the selected event date.</div>
                    </div>
                    <span class="gp-package-plan-count"><?= count($packagePlan) ?> service<?= count($packagePlan) === 1 ? '' : 's' ?></span>
                  </div>
                  <div class="gp-event-plan-list">
                    <?php foreach ($packagePlan as $event): ?>
                      <?php
                        $start = !empty($event['start_time']) ? date('g:i A', strtotime($event['start_time'])) : 'TBD';
                        $end = !empty($event['end_time']) ? date('g:i A', strtotime($event['end_time'])) : '';
                        $hall = trim((string)($event['venue_room_name'] ?? ''));
                      ?>
                      <div class="gp-event-plan-item">
                        <div class="gp-event-plan-time"><?= $h($start) ?></div>
                        <div>
                          <div class="gp-event-plan-service"><?= $h($event['service_name'] ?? 'Package service') ?></div>
                          <div class="gp-event-plan-meta">
                            <?= $h($event['category_name'] ?? 'Service') ?>
                            · <?= $h($event['supplier_name'] ?? 'Golden Promise') ?>
                            <?php if ($end !== ''): ?> · Until <?= $h($end) ?><?php endif; ?>
                          </div>
                          <?php if ($hall !== ''): ?>
                            <span class="gp-event-plan-hall"><?= $h($hall) ?></span>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </section>
                <?php endif; ?>
              </section>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="color:var(--muted);font-size:13px;">No service details provided</div>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <!-- RIGHT: Items -->
    <div class="gp-col">
      <div class="gp-card">
        <div class="gp-card-h">Order Summary</div>
        <div class="gp-card-b">
          <div class="gp-order-list" role="listbox" aria-label="Booked services">
            <?php foreach ($items as $idx => $item):
              $p=(float)($item['price']??0);
              $itemId = (int)($item['id'] ?? 0);
              $thumb = trim((string)($item['thumbnail_url'] ?? ''));
              if ($thumb !== '' && !preg_match('#^(https?:)?//#', $thumb) && $thumb[0] !== '/') {
                  $thumb = URLROOT . '/' . ltrim($thumb, '/');
              }
            ?>
            <button class="gp-order-item <?= $idx === 0 ? 'active' : '' ?>" type="button" data-service-select="<?= $itemId ?>" role="option" aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>">
              <span class="gp-order-thumb">
                <?php if ($thumb !== ''): ?>
                  <img src="<?= $h($thumb) ?>" alt="">
                <?php else: ?>
                  <?= $idx + 1 ?>
                <?php endif; ?>
              </span>
              <span>
                <span class="gp-order-name"><?= $h($item['service_name'] ?? 'Service') ?></span>
                <span class="gp-order-sub">Qty: 1<?= !empty($item['addon_package_name']) ? ' · Add-on for ' . $h($item['addon_package_name']) : '' ?></span>
              </span>
              <span class="gp-order-price"><?= $money($p) ?></span>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="gp-summary">
          <?php
          $summaryTotal = (float)($booking['total_amount'] ?? 0);
          $summaryDeposit = round($summaryTotal * ($depositPercent / 100), 2);
          $summaryFee = round($summaryTotal * ($platformFeePercent / 100), 2);
          $summaryPaid = (float)($booking['paid_amount'] ?? 0);
          $summaryBalance = max(0, $summaryTotal - $summaryPaid);
          ?>
          <div class="gp-summary-r"><span>Deposit paid (<?=$depositPercent?>%)</span><span><?=$money($summaryPaid)?></span></div>
          <?php if ($summaryFee > 0): ?>
          <div class="gp-summary-r"><span>Platform fee (<?=rtrim(rtrim(number_format($platformFeePercent, 2), '0'), '.')?>%)</span><span><?=$money($summaryFee)?></span></div>
          <?php endif; ?>
          <div class="gp-summary-r balance"><span>Balance due</span><span><?=$money($summaryBalance)?></span></div>
          <div class="gp-summary-r total"><span>Total</span><span><?=$money($summaryTotal)?></span></div>
        </div>
      </div>

      <?php if ($hasRemainingBalance): ?>
      <div class="gp-info-box" style="margin-bottom:0;">
        <div class="gp-info-icon" aria-hidden="true">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div style="flex:1;min-width:0;">
          <div class="gp-info-title">Remaining Balance Details</div>
          <div class="gp-info-copy">Complete your final payment to finish this booking.</div>
          <div style="margin-top:10px;display:flex;flex-direction:column;gap:8px;">
          <?php if ($eventDateDisplay): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--rule);font-size:13px;">
            <span style="color:var(--muted);font-weight:600;">Due Date (Event Date)</span>
            <span style="font-weight:700;color:#b8924a;"><?= date('M d, Y', strtotime($eventDateDisplay)) ?></span>
          </div>
          <?php endif; ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--rule);font-size:13px;">
            <span style="color:var(--muted);font-weight:600;">Remaining Balance</span>
            <span style="font-weight:700;color:var(--plum);"><?= $money($summaryBalance) ?></span>
          </div>
          <?php if (!empty($remainingPaymentsList)): ?>
          <div style="padding-top:8px;">
            <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:8px;">Payment History</div>
            <?php foreach ($remainingPaymentsList as $rp): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--rule);font-size:12px;">
              <div>
                <span style="font-weight:600;"><?= date('M d, Y', strtotime($rp['created_at'])) ?></span>
                <?php if (($rp['bank_name'] ?? '') !== ''): ?>
                <span style="color:var(--muted);margin-left:8px;"><?= $h($rp['bank_name']) ?></span>
                <?php endif; ?>
              </div>
              <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-weight:700;"><?= $money($rp['paid_amount'] ?? $rp['amount']) ?></span>
                <?php
                  $rpStatus = $rp['status'] ?? 'pending';
                  $rpColors = [
                    'success' => ['bg' => '#f0fdf4', 'color' => '#166534', 'label' => 'Verified'],
                    'pending' => ['bg' => '#fffbeb', 'color' => '#92400e', 'label' => 'Under Review'],
                    'failed'  => ['bg' => '#fef2f2', 'color' => '#991b1b', 'label' => 'Rejected'],
                  ];
                  $rpSt = $rpColors[$rpStatus] ?? $rpColors['pending'];
                ?>
                <span style="background:<?= $rpSt['bg'] ?>;color:<?= $rpSt['color'] ?>;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;"><?= $rpSt['label'] ?></span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($vouchers)): ?>
      <div class="gp-card">
        <div class="gp-card-h">Vouchers</div>
        <div class="gp-card-b" style="gap:8px;">
          <?php foreach ($vouchers as $vc): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--rule);font-size:12px;">
            <span style="font-weight:500;"><?=$h($vc['service_name'])?></span>
            <span style="font-family:monospace;font-size:11px;color:var(--muted);"><?=$h($vc['voucher_number'])?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php $reviewFlashSuccess = $_SESSION['review_success'] ?? null; $reviewFlashError = $_SESSION['review_error'] ?? null; unset($_SESSION['review_success'], $_SESSION['review_error']); ?>
  <?php if ($canReview || $existingReview): ?>
  <div class="gp-review-section">
    <div class="gp-review-section-h"><?= $existingReview ? 'Your Review' : 'Leave a Review' ?></div>
    <div class="gp-review-section-b">
      <?php if ($reviewFlashSuccess): ?>
        <div class="gp-flash-success"><?= $h($reviewFlashSuccess) ?></div>
      <?php elseif ($reviewFlashError): ?>
        <div class="gp-flash-error"><?= $h($reviewFlashError) ?></div>
      <?php endif; ?>

      <!-- Completed services list -->
      <?php if (!empty($items)): ?>
      <div style="margin-bottom:16px">
        <p style="font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:8px">Services completed</p>
        <?php foreach ($items as $idx => $svc): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;<?= $idx > 0 ? 'border-top:1px solid var(--rule);' : '' ?>">
          <div style="width:32px;height:32px;border-radius:8px;background:var(--soft);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <span style="color:var(--muted);font-size:14px">✓</span>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:var(--text)"><?= $h($svc['service_name'] ?? 'Service') ?></div>
            <div style="font-size:11px;color:var(--muted)"><?= $h($svc['supplier_name'] ?? '') ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($existingReview): ?>
        <div class="gp-review-existing" id="reviewDisplay">
          <div class="gp-review-stars"><?= str_repeat('★', (int)($existingReview['rating'] ?? 0)) . str_repeat('☆', 5 - (int)($existingReview['rating'] ?? 0)) ?></div>
          <div class="gp-review-body" id="reviewCommentDisplay"><?= $h((string)($existingReview['comment'] ?? '')) ?></div>
          <div class="gp-review-actions">
            <?php if ($canEditReview): ?>
              <button class="gp-btn-sm" type="button" onclick="toggleEditReview()">Edit</button>
            <?php endif; ?>
            <form method="POST" action="<?=URLROOT?>/review/delete/<?=(int)$existingReview['id']?>" style="display:inline" onsubmit="return confirm('Remove your review?')">
              <input type="hidden" name="booking_id" value="<?=(int)($booking['id']??0)?>">
              <button class="gp-btn-sm danger" type="submit">Delete</button>
            </form>
          </div>
        </div>
        <?php if ($canEditReview): ?>
        <div class="gp-edit-form" id="editReviewForm">
          <div class="gp-star-picker" id="editStarPicker" data-value="<?=(int)($existingReview['rating']??5)?>">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <button class="gp-star-btn<?= $s <= (int)($existingReview['rating']??5) ? ' active' : '' ?>" type="button" data-val="<?=$s?>" onclick="setEditStar(<?=$s?>)">★</button>
            <?php endfor; ?>
          </div>
          <textarea class="gp-review-textarea" id="editCommentInput" maxlength="2000" oninput="updateCharCount('editCommentCount',this.value,2000)"><?= $h((string)($existingReview['comment'] ?? '')) ?></textarea>
          <div class="gp-review-chars"><span id="editCommentCount"><?=strlen($existingReview['comment']??'')?></span> / 2000</div>
          <div style="display:flex;gap:8px;margin-top:10px">
            <button class="gp-btn-sm primary" type="button" onclick="submitEditReview(<?=(int)$existingReview['id']?>)">Save</button>
            <button class="gp-btn-sm" type="button" onclick="toggleEditReview()">Cancel</button>
          </div>
        </div>
        <?php endif; ?>

      <?php elseif ($canReview): ?>
        <form method="POST" action="<?=URLROOT?>/review/submit/<?=(int)($booking['id']??0)?>">
          <p style="font-size:13px;color:var(--text2);margin-bottom:12px">How was your experience with these services?</p>
          <div class="gp-star-picker" id="submitStarPicker">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <button class="gp-star-btn" type="button" data-val="<?=$s?>" onclick="setSubmitStar(<?=$s?>)">★</button>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="rating" id="submitRatingInput" value="">
          <textarea class="gp-review-textarea" name="comment" placeholder="Share your experience with the services you received (min 10 characters)…" maxlength="2000" oninput="updateCharCount('submitCommentCount',this.value,2000)"></textarea>
          <div class="gp-review-chars"><span id="submitCommentCount">0</span> / 2000</div>
          <div style="margin-top:12px">
            <button class="gp-btn-sm primary" type="submit" onclick="return validateReviewForm()">Submit Review</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <?php elseif ($reviewFlashSuccess || $reviewFlashError): ?>
    <?php if ($reviewFlashSuccess): ?>
      <div class="gp-flash-success" style="margin-top:16px"><?= $h($reviewFlashSuccess) ?></div>
    <?php elseif ($reviewFlashError): ?>
      <div class="gp-flash-error" style="margin-top:16px"><?= $h($reviewFlashError) ?></div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="gp-bottom-actions">
    <?php if (!empty($vouchers)): ?>
    <a class="gp-btn-sm" href="<?=URLROOT?>/booking/vouchers">View All Vouchers</a>
    <?php endif; ?>

    <?php if (($booking['status'] ?? '') === 'cancellation_requested'): ?>
      <?php
      $supplierApproved = false;
      $supplierPending = false;
      $supplierInitiated = false;
      $supplierInitiatedName = '';
      foreach ($suppliers as $sup) {
        if (($sup['status'] ?? '') === 'cancellation_approved') $supplierApproved = true;
        if (($sup['status'] ?? '') === 'cancellation_pending') $supplierPending = true;
        if (($sup['status'] ?? '') === 'supplier_cancellation_requested') {
          $supplierInitiated = true;
          $supplierInitiatedName = $sup['shop_name'] ?? 'Your supplier';
        }
      }
      // Find cancellation reason from status logs
      $cancelReason = '';
      foreach (array_reverse($logs ?? []) as $log) {
        if (($log['new_status'] ?? '') === 'cancellation_requested' && !empty($log['note'])) {
          $cancelReason = $log['note'];
          break;
        }
      }
      ?>
      <?php if ($supplierInitiated): ?>
      <div style="background:#fff7ed;border:1px solid #fdba74;border-radius:12px;padding:16px 18px;margin-top:8px;color:#9a3412">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
          <span style="font-size:18px">⚠️</span>
          <div>
            <strong style="font-size:14px">Your supplier has requested to cancel this booking</strong>
            <div style="font-size:12px;color:#c2410c;margin-top:2px"><?= $h($supplierInitiatedName) ?> is unable to fulfill this booking. Our admin team will review the request and process your refund.</div>
          </div>
        </div>
        <?php if ($cancelReason): ?>
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid #fdba74;font-size:12px">
          <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.08em">Reason from supplier</span><br>
          <span style="font-weight:600"><?= $h($cancelReason) ?></span>
        </div>
        <?php endif; ?>
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid #fdba74;font-size:12px;color:#c2410c;display:flex;align-items:center;gap:6px">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          <span>You will be notified once the admin processes your refund. No action is required from you.</span>
        </div>
      </div>
      <?php elseif ($supplierPending): ?>
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;font-size:13px;color:#92400e;margin-top:8px">
        <strong>Cancellation under review</strong> — Your supplier is reviewing your cancellation request. You'll be notified once they respond.
        <?php if ($cancelReason): ?>
        <div style="margin-top:8px;padding-top:8px;border-top:1px solid #fde68a;font-size:12px;color:#a16207"><strong>Your reason:</strong> <?= $h($cancelReason) ?></div>
        <?php endif; ?>
      </div>
      <?php elseif ($supplierApproved): ?>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;color:#166534;margin-top:8px">
        <strong>Supplier approved</strong> — Your supplier has approved the cancellation. Admin will review and process your refund.
        <?php if ($cancelReason): ?>
        <div style="margin-top:8px;padding-top:8px;border-top:1px solid #bbf7d0;font-size:12px;color:#15803d"><strong>Your reason:</strong> <?= $h($cancelReason) ?></div>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 16px;font-size:13px;color:#1e40af;margin-top:8px">
        <strong>Cancellation requested</strong> — Your cancellation request is being reviewed.
        <?php if ($cancelReason): ?>
        <div style="margin-top:8px;padding-top:8px;border-top:1px solid #bfdbfe;font-size:12px;color:#1d4ed8"><strong>Your reason:</strong> <?= $h($cancelReason) ?></div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Refund Status (shown when booking is cancelled and refund exists) -->
    <?php
      $refund = $refund ?? null;
      $currentStatus2 = $booking['status'] ?? '';
    ?>
    <?php if ($refund): ?>
      <?php
        $refundStatus = (string)($refund['status'] ?? 'pending');
        $refundColors = [
          'pending'    => ['bg' => '#fffbeb', 'border' => '#fde68a', 'text' => '#92400e', 'icon' => '⏳'],
          'processing' => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1e40af', 'icon' => '⚙️'],
          'completed'  => ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'text' => '#166534', 'icon' => '✅'],
          'rejected'   => ['bg' => '#fef2f2', 'border' => '#fecaca', 'text' => '#991b1b', 'icon' => '❌'],
        ];
        $rc = $refundColors[$refundStatus] ?? $refundColors['pending'];
        $refundStatusText = [
          'pending'    => 'Refund requested — admin will process your refund shortly.',
          'processing' => 'Refund in progress — the admin has initiated the transfer to your account.',
          'completed'  => 'Refund completed — the money has been sent to your account.',
          'rejected'   => 'Refund request rejected.',
        ];
      ?>
      <div style="background:<?= $rc['bg'] ?>;border:1px solid <?= $rc['border'] ?>;border-radius:12px;padding:16px 18px;margin-top:12px;color:<?= $rc['text'] ?>">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
          <span style="font-size:18px"><?= $rc['icon'] ?></span>
          <strong style="font-size:14px"><?= $refundStatusText[$refundStatus] ?? '' ?></strong>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:12.5px">
          <div>
            <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.08em">Refund Amount</span><br>
            <span style="font-weight:700;font-size:15px"><?= number_format((float)($refund['amount'] ?? 0), 0) ?> MMK</span>
          </div>
          <?php if (!empty($refund['policy_reason'])): ?>
          <div>
            <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.08em">Policy Applied</span><br>
            <span><?= htmlspecialchars($refund['policy_reason'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <?php endif; ?>
          <?php if ($refundStatus === 'completed' && !empty($refund['completed_at'])): ?>
          <div>
            <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.08em">Completed On</span><br>
            <span><?= date('M j, Y', strtotime($refund['completed_at'])) ?></span>
          </div>
          <?php endif; ?>
          <?php if (!empty($refund['refund_transaction_ref'])): ?>
          <div>
            <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.08em">Transfer Reference</span><br>
            <span style="font-weight:600"><?= htmlspecialchars($refund['refund_transaction_ref'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php if (!empty($refund['refund_bank_name'])): ?>
              <span style="opacity:.7"> via <?= htmlspecialchars($refund['refund_bank_name'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <?php if (!empty($refund['refund_slip_path']) && in_array($refundStatus, ['processing', 'completed'], true)): ?>
          <div>
            <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.08em">Transfer Proof</span><br>
            <?php if (preg_match('/\.(jpe?g|png|webp)$/i', $refund['refund_slip_path'])): ?>
              <a href="<?= URLROOT . '/' . htmlspecialchars($refund['refund_slip_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" style="display:inline-block;margin-top:4px">
                <img src="<?= URLROOT . '/' . htmlspecialchars($refund['refund_slip_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Transfer proof" style="max-width:200px;max-height:120px;border-radius:8px;border:1px solid rgba(178,143,110,.3)">
              </a>
            <?php else: ?>
              <a href="<?= URLROOT . '/' . htmlspecialchars($refund['refund_slip_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" style="color:var(--plum);font-weight:600;text-decoration:underline;font-size:12px">
                📄 View document
              </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <?php if ($refundStatus === 'rejected' && !empty($refund['note'])): ?>
          <div>
            <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.08em">Reason</span><br>
            <span><?= htmlspecialchars($refund['note'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    <?php elseif ($currentStatus2 === 'cancelled' && !$refund): ?>
      <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:14px 18px;margin-top:12px;color:#991b1b;font-size:13px">
        <strong>Booking cancelled</strong> — No refund was issued for this booking based on the cancellation policy.
      </div>
    <?php endif; ?>
  </div>
</main>
<script>
document.addEventListener('click',(e)=>{const btn=e.target.closest('.gp-profile-btn');if(btn){const x=btn.getAttribute('aria-expanded')==='true';document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'));btn.setAttribute('aria-expanded',String(!x));return}document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'))});

function toggleProofDetails(){const btn=document.querySelector('.gp-proof-toggle');const detail=document.getElementById('proofDetail');if(!btn||!detail)return;const open=btn.getAttribute('aria-expanded')==='true';btn.setAttribute('aria-expanded',open?'false':'true');detail.style.display=open?'none':'block';}

document.querySelectorAll('[data-service-select]').forEach((button) => {
  button.addEventListener('click', () => {
    const id = button.dataset.serviceSelect;
    document.querySelectorAll('[data-service-select]').forEach((item) => {
      const active = item === button;
      item.classList.toggle('active', active);
      item.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    document.querySelectorAll('[data-service-panel]').forEach((panel) => {
      panel.classList.toggle('active', panel.dataset.servicePanel === id);
    });
  });
});

function updateCharCount(id, val, max) {
  const el = document.getElementById(id);
  if (el) el.textContent = val.length;
}
function setSubmitStar(val) {
  document.getElementById('submitRatingInput').value = val;
  document.querySelectorAll('#submitStarPicker .gp-star-btn').forEach(b => {
    b.classList.toggle('active', parseInt(b.dataset.val) <= val);
  });
}
function setEditStar(val) {
  const picker = document.getElementById('editStarPicker');
  if (picker) picker.dataset.value = val;
  document.querySelectorAll('#editStarPicker .gp-star-btn').forEach(b => {
    b.classList.toggle('active', parseInt(b.dataset.val) <= val);
  });
}
function validateReviewForm() {
  const rating = document.getElementById('submitRatingInput').value;
  if (!rating) { alert('Please select a star rating.'); return false; }
  return true;
}
function toggleEditReview() {
  const form = document.getElementById('editReviewForm');
  const display = document.getElementById('reviewDisplay');
  if (!form) return;
  const showing = form.style.display === 'block';
  form.style.display = showing ? 'none' : 'block';
  if (display) display.style.display = showing ? 'flex' : 'none';
}
function submitEditReview(reviewId) {
  const picker = document.getElementById('editStarPicker');
  const rating = picker ? parseInt(picker.dataset.value) : 0;
  const comment = (document.getElementById('editCommentInput')?.value || '').trim();
  if (!rating || rating < 1 || rating > 5) { alert('Please select a rating.'); return; }
  if (comment.length < 10) { alert('Comment must be at least 10 characters.'); return; }
  fetch('<?=URLROOT?>/review/update/' + reviewId, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({rating, comment})
  }).then(r => r.json()).then(d => {
    if (d.status === 'success') {
      document.getElementById('reviewCommentDisplay').textContent = comment;
      document.querySelectorAll('.gp-review-stars')[0].textContent = '★'.repeat(rating) + '☆'.repeat(5 - rating);
      toggleEditReview();
    } else {
      alert(d.error || 'Could not update review.');
    }
  }).catch(() => alert('Network error. Please try again.'));
}
</script>
</body></html>
