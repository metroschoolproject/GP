<?php
$assignments = $assignments ?? [];
$pendingAssignments = $pendingAssignments ?? [];
$activeAssignments = $activeAssignments ?? [];
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$formatDate = function ($value) {
    if (empty($value)) return '—';
    $time = strtotime((string)$value);
    return $time ? date('d M Y', $time) : '—';
};

$daysUntil = function ($date) {
    if (empty($date)) return null;
    $ts = new DateTimeImmutable($date);
    $now = new DateTimeImmutable('today');
    return (int)$now->diff($ts)->format('%r%a');
};

$dashboardTitle = 'Assignments';
$dashboardCrumb = 'My assignments';
$dashboardContentClass = 'bg-[#F4F1EE] px-0 py-0 overflow-y-auto';
$dashboardContent = function () use ($assignments, $pendingAssignments, $activeAssignments, $h, $money, $formatDate, $daysUntil) {
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/supplier-bookings.css?v=<?= filemtime(APPROOT . '/../public/css/supplier-bookings.css') ?>">
<script src="<?= URLROOT ?>/public/js/supplier-toast.js"></script>

<div class="asn-page">
    <header class="asn-header">
        <p class="asn-kicker">Supplier workspace</p>
        <h1 class="asn-title">My Assignments</h1>
        <p class="asn-subtitle">Bookings where your services are assigned. Respond to new requests and prepare for upcoming events.</p>
        <?php $totalCount = count($assignments); ?>
        <span class="asn-count">
            <span class="asn-count-dot"></span>
            <?= $totalCount ?> assignment<?= $totalCount === 1 ? '' : 's' ?>
        </span>
        <div class="bk-nav-group" style="margin-top:12px">
            <a href="<?= URLROOT ?>/supplier/bookings" class="bk-nav-link">
                <i data-lucide="list"></i>
                List view
            </a>
            <a href="<?= URLROOT ?>/supplier/calendar" class="bk-nav-link">
                <i data-lucide="calendar-days"></i>
                Calendar
            </a>
        </div>
    </header>

    <?php if (empty($assignments)): ?>
        <div class="asn-empty">
            <span class="asn-empty-icon"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="11" rx="1"/><path d="M5 2v2M11 2v2M2 7h12"/></svg></span>
            <h2>No assignments yet</h2>
            <p>When a customer books your services, you'll see assignments here to accept or manage.</p>
        </div>
    <?php else: ?>

        <!-- ── Pending: Action Required ── -->
        <?php if (!empty($pendingAssignments)): ?>
        <section class="asn-section">
            <div class="asn-section-head">
                <h2 class="asn-section-title">Action Required</h2>
                <span class="asn-section-count asn-section-count--amber"><?= count($pendingAssignments) ?></span>
            </div>
            <div class="asn-grid">
            <?php foreach ($pendingAssignments as $a):
                $bookingId = (int)($a['booking_id'] ?? 0);
                $bookingSupplierId = (int)($a['booking_supplier_id'] ?? 0);
                $ref = $a['booking_ref'] ?? ('BK-' . str_pad($bookingId, 3, '0', STR_PAD_LEFT));
                $customer = trim((string)($a['customer_name'] ?? 'Customer'));
                $eventDate = $a['event_date'] ?? '';
                $venue = trim((string)($a['venue'] ?? ''));
                $services = $a['assigned_service_name'] ?? '';
                $categoryName = $a['category_name'] ?? '';
                $totalAmount = (float)($a['total_amount'] ?? 0);
                $deadline = $a['supplier_response_deadline'] ?? '';

                // Replacement context
                $replacementId = (int)($a['replacement_id'] ?? 0);
                $isReplacement = $replacementId > 0;
                $origSupplier = trim((string)($a['original_supplier_name'] ?? ''));
                $origService = trim((string)($a['original_service_name'] ?? ''));
                $priceDelta = (float)($a['price_delta'] ?? 0);
                $needsApproval = !empty($a['requires_customer_approval']);

                $days = $daysUntil($eventDate);
                $countdownClass = 'asn-countdown--ok';
                $countdownLabel = '';
                if ($days !== null) {
                    if ($days < 0) { $countdownClass = 'asn-countdown--past'; $countdownLabel = 'Event passed'; }
                    elseif ($days === 0) { $countdownClass = 'asn-countdown--urgent'; $countdownLabel = 'Today'; }
                    elseif ($days <= 7) { $countdownClass = 'asn-countdown--urgent'; $countdownLabel = $days . ' day' . ($days === 1 ? '' : 's') . ' away'; }
                    elseif ($days <= 21) { $countdownClass = 'asn-countdown--soon'; $countdownLabel = $days . ' days away'; }
                    else { $countdownLabel = $days . ' days away'; }
                }

                // Deadline display
                $deadlineDays = null;
                if ($deadline !== '') {
                    $deadlineTs = strtotime($deadline);
                    if ($deadlineTs) {
                        $deadlineDays = max(0, (int)ceil(($deadlineTs - time()) / 86400));
                    }
                }
            ?>
                <article class="asn-card asn-card--pending" data-booking-id="<?= $bookingId ?>" data-bsid="<?= $bookingSupplierId ?>">
                    <div class="asn-card-left">
                        <div class="asn-card-ref"><?= $h($ref) ?></div>
                        <div class="asn-card-customer"><?= $h($customer) ?></div>
                        <div class="asn-card-facts">
                            <span class="asn-fact">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="11" rx="1"/><path d="M5 2v2M11 2v2M2 7h12"/></svg>
                                <strong><?= $h($formatDate($eventDate)) ?></strong>
                            </span>
                            <?php if ($venue !== ''): ?>
                            <span class="asn-fact">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 14s-5-4.5-5-7.5a5 5 0 0110 0C13 9.5 8 14 8 14z"/><circle cx="8" cy="6.5" r="1.5"/></svg>
                                <?= $h($venue) ?>
                            </span>
                            <?php endif; ?>
                            <span class="asn-fact">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="10" rx="1"/><path d="M2 6h12"/></svg>
                                <?= $money($totalAmount) ?>
                            </span>
                        </div>
                        <?php if ($services !== ''): ?>
                        <div class="asn-services">
                            <span class="asn-service-tag"><?= $h($services) ?></span>
                            <?php if ($categoryName !== ''): ?>
                                <span class="asn-service-tag"><?= $h($categoryName) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($isReplacement): ?>
                        <div class="asn-replacement-info">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 8a6 6 0 0110-4.5M14 8a6 6 0 01-10 4.5"/><path d="M12 2v3H9M4 14v-3h3"/></svg>
                            <div>
                                <strong>You've been chosen as a replacement.</strong>
                                <?php if ($origSupplier !== ''): ?>
                                    <?= $h($origSupplier) ?><?php if ($origService !== ''): ?>'s <?= $h($origService) ?><?php endif; ?> is no longer available for this booking.
                                <?php else: ?>
                                    A previous supplier declined this booking and admin has selected you as the replacement.
                                <?php endif; ?>
                                <?php if ($priceDelta > 0): ?>
                                    <div class="asn-price-delta">
                                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width:11px;height:11px"><circle cx="8" cy="8" r="6"/><path d="M8 5v3M8 11h.01"/></svg>
                                        +<?= $money($priceDelta) ?> price difference<?= $needsApproval ? ' — awaiting customer approval' : '' ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Accept / Decline actions -->
                        <div class="asn-response-actions">
                            <button type="button" class="asn-response-btn asn-response-btn--accept asn-respond-btn" data-action="accept">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l3.5 3.5L13 5"/></svg>
                                Accept
                            </button>
                            <button type="button" class="asn-response-btn asn-response-btn--decline asn-respond-btn" data-action="decline">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>
                                Decline
                            </button>
                            <?php if ($deadlineDays !== null): ?>
                            <span class="asn-response-deadline">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l3 2"/></svg>
                                <?php if ($deadlineDays <= 0): ?>
                                    Response overdue
                                <?php else: ?>
                                    <?= $deadlineDays ?> day<?= $deadlineDays === 1 ? '' : 's' ?> to respond
                                <?php endif; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="asn-card-right">
                        <?php if ($countdownLabel !== ''): ?>
                        <span class="asn-countdown <?= $countdownClass ?>">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l3 2"/></svg>
                            <?= $countdownLabel ?>
                        </span>
                        <?php endif; ?>
                        <span class="asn-response-pill asn-response-pill--awaiting">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width:11px;height:11px"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l3 2"/></svg>
                            Awaiting response
                        </span>
                        <a href="<?= URLROOT ?>/supplier/bookingDetail/<?= $bookingId ?>" class="asn-view-btn">
                            View details
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 4l4 4-4 4"/></svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- ── Active / Confirmed Assignments ── -->
        <?php if (!empty($activeAssignments)): ?>
        <section class="asn-section">
            <div class="asn-section-head">
                <h2 class="asn-section-title">Confirmed Assignments</h2>
                <span class="asn-section-count asn-section-count--green"><?= count($activeAssignments) ?></span>
            </div>
            <div class="asn-grid">
            <?php foreach ($activeAssignments as $a):
                $bookingId = (int)($a['booking_id'] ?? 0);
                $ref = $a['booking_ref'] ?? ('BK-' . str_pad($bookingId, 3, '0', STR_PAD_LEFT));
                $customer = trim((string)($a['customer_name'] ?? 'Customer'));
                $eventDate = $a['event_date'] ?? '';
                $venue = trim((string)($a['venue'] ?? ''));
                $services = $a['assigned_service_name'] ?? $a['service_names'] ?? '';
                $paymentStatus = strtolower((string)($a['payment_status'] ?? 'pending'));
                $totalAmount = (float)($a['total_amount'] ?? 0);
                $paidAmount = (float)($a['paid_amount'] ?? 0);

                // Replacement context (for accepted replacements)
                $replacementId = (int)($a['replacement_id'] ?? 0);
                $origSupplier = trim((string)($a['original_supplier_name'] ?? ''));

                $days = $daysUntil($eventDate);
                $countdownClass = 'asn-countdown--ok';
                $countdownLabel = '';
                if ($days !== null) {
                    if ($days < 0) { $countdownClass = 'asn-countdown--past'; $countdownLabel = 'Event passed'; }
                    elseif ($days === 0) { $countdownClass = 'asn-countdown--urgent'; $countdownLabel = 'Today'; }
                    elseif ($days <= 7) { $countdownClass = 'asn-countdown--urgent'; $countdownLabel = $days . ' day' . ($days === 1 ? '' : 's') . ' away'; }
                    elseif ($days <= 21) { $countdownClass = 'asn-countdown--soon'; $countdownLabel = $days . ' days away'; }
                    else { $countdownLabel = $days . ' days away'; }
                }

                $paymentClass = 'asn-payment-pill--pending';
                $paymentLabel = 'Pending';
                if ($paymentStatus === 'paid') { $paymentClass = 'asn-payment-pill--paid'; $paymentLabel = 'Fully paid'; }
                elseif ($paymentStatus === 'partial' || $paidAmount > 0) { $paymentClass = 'asn-payment-pill--partial'; $paymentLabel = 'Deposit paid'; }

                $serviceList = $services !== '' ? array_map('trim', explode(',', $services)) : [];
            ?>
                <article class="asn-card">
                    <div class="asn-card-left">
                        <div class="asn-card-ref"><?= $h($ref) ?></div>
                        <div class="asn-card-customer"><?= $h($customer) ?></div>
                        <div class="asn-card-facts">
                            <span class="asn-fact">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="11" rx="1"/><path d="M5 2v2M11 2v2M2 7h12"/></svg>
                                <strong><?= $h($formatDate($eventDate)) ?></strong>
                            </span>
                            <?php if ($venue !== ''): ?>
                            <span class="asn-fact">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 14s-5-4.5-5-7.5a5 5 0 0110 0C13 9.5 8 14 8 14z"/><circle cx="8" cy="6.5" r="1.5"/></svg>
                                <?= $h($venue) ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($a['guest_count'])): ?>
                            <span class="asn-fact">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="5" r="3"/><path d="M2 14a6 6 0 0112 0"/></svg>
                                <?= number_format((int)$a['guest_count']) ?> guests
                            </span>
                            <?php endif; ?>
                            <span class="asn-fact">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="10" rx="1"/><path d="M2 6h12"/></svg>
                                <?= $money($totalAmount) ?>
                            </span>
                        </div>
                        <?php if (!empty($serviceList)): ?>
                        <div class="asn-services">
                            <?php foreach (array_slice($serviceList, 0, 3) as $svc): ?>
                                <span class="asn-service-tag"><?= $h($svc) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($serviceList) > 3): ?>
                                <span class="asn-service-tag">+<?= count($serviceList) - 3 ?> more</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($replacementId > 0 && $origSupplier !== ''): ?>
                        <div class="asn-replacement-info" style="margin-top:8px;padding:8px 11px">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 8a6 6 0 0110-4.5M14 8a6 6 0 01-10 4.5"/><path d="M12 2v3H9M4 14v-3h3"/></svg>
                            <div>Replacement for <?= $h($origSupplier) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="asn-card-right">
                        <?php if ($countdownLabel !== ''): ?>
                        <span class="asn-countdown <?= $countdownClass ?>">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l3 2"/></svg>
                            <?= $countdownLabel ?>
                        </span>
                        <?php endif; ?>
                        <span class="asn-payment-pill <?= $paymentClass ?>"><?= $paymentLabel ?></span>
                        <a href="<?= URLROOT ?>/supplier/bookingDetail/<?= $bookingId ?>" class="asn-view-btn">
                            View details
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 4l4 4-4 4"/></svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    <?php endif; ?>
</div>

<!-- Decline modal -->
<div id="asn-decline-modal" class="asn-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="asn-decline-title">
    <div class="asn-modal">
        <div class="asn-modal-head">
            <h2 id="asn-decline-title" class="asn-modal-title">Decline Assignment</h2>
            <button type="button" class="asn-modal-close" data-decline-close aria-label="Close">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4l8 8M12 4l-8 8"/></svg>
            </button>
        </div>
        <form id="asn-decline-form">
            <div class="asn-modal-body">
                <p style="font-size:12px;color:#7b5c69;margin-bottom:14px;line-height:1.5">
                    The customer and admin will be notified. Please provide a reason for declining.
                </p>
                <div class="asn-field" style="margin-bottom:0">
                    <label class="asn-label" for="asn-dec-reason">Reason <span style="color:#991b1b">*</span></label>
                    <textarea id="asn-dec-reason" class="asn-textarea" rows="3" required
                              placeholder="e.g., Already booked for this date, schedule conflict"
                              minlength="10" maxlength="500"></textarea>
                    <span class="sup-char-count" id="asn-dec-char-count">0 / 500</span>
                </div>
            </div>
            <div class="asn-modal-foot">
                <button type="button" class="asn-cancel-btn" data-decline-close>Cancel</button>
                <button type="submit" class="asn-confirm-btn">Confirm decline</button>
            </div>
        </form>
    </div>
</div>

<!-- Accept/Decline AJAX -->
<script>
(function() {
    var declineModal = document.getElementById('asn-decline-modal');
    var declineForm = document.getElementById('asn-decline-form');
    var declineReason = document.getElementById('asn-dec-reason');
    var charCount = document.getElementById('asn-dec-char-count');
    var pendingDeclineBtn = null;

    /* Character counter */
    if (declineReason && charCount) {
        declineReason.addEventListener('input', function() {
            var len = declineReason.value.length;
            charCount.textContent = len + ' / 500';
            charCount.classList.toggle('is-over', len > 500);
        });
    }

    /* Close modal */
    document.querySelectorAll('[data-decline-close]').forEach(function(btn) {
        btn.addEventListener('click', function() { declineModal.classList.remove('is-open'); });
    });
    if (declineModal) {
        declineModal.addEventListener('click', function(e) { if (e.target === declineModal) declineModal.classList.remove('is-open'); });
    }

    /* Accept/Decline buttons */
    document.querySelectorAll('.asn-respond-btn').forEach(function(button) {
        button.addEventListener('click', async function() {
            var card = button.closest('.asn-card');
            var bookingId = card?.dataset.bookingId;
            var bsid = card?.dataset.bsid;
            var action = button.dataset.action;

            if (!bookingId) return;

            /* For decline, open modal instead of confirm() */
            if (action === 'decline') {
                pendingDeclineBtn = button;
                declineModal.dataset.bookingId = bookingId;
                declineModal.dataset.bsid = bsid || '';
                declineReason.value = '';
                charCount.textContent = '0 / 500';
                declineModal.classList.add('is-open');
                declineReason.focus();
                return;
            }

            await submitResponse(button, bookingId, bsid, 'accept', '');
        });
    });

    /* Decline form submit */
    if (declineForm) {
        declineForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            var reason = (declineReason.value || '').trim();
            if (reason.length < 10) {
                supToastWarning('Please provide a reason (at least 10 characters).');
                return;
            }
            declineModal.classList.remove('is-open');
            if (pendingDeclineBtn) {
                await submitResponse(pendingDeclineBtn, declineModal.dataset.bookingId, declineModal.dataset.bsid, 'decline', reason);
            }
        });
    }

    async function submitResponse(button, bookingId, bsid, action, reason) {
        button.disabled = true;
        var original = button.innerHTML;
        button.innerHTML = action === 'accept' ? 'Accepting…' : 'Declining…';

        var formData = new FormData();
        formData.append('booking_id', bookingId);
        formData.append('booking_supplier_id', bsid || '');
        formData.append('action', action);
        if (reason) formData.append('reason', reason);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

        try {
            var resp = await fetch('<?= URLROOT ?>/supplier/bookingRespond', { method: 'POST', body: formData });
            var data = await resp.json().catch(function() { return {}; });
            if (data.success) {
                supToastSuccess(action === 'accept' ? 'Assignment accepted!' : 'Assignment declined.');
                setTimeout(function() { window.location.reload(); }, 1200);
                return;
            }
            supToastError(data.error || 'Could not update. Please try again.');
        } catch (err) {
            supToastError('Network error. Please try again.');
        }
        button.disabled = false;
        button.innerHTML = original;
    }
})();
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
  <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
