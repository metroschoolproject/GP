<?php
$assignments = $assignments ?? [];
$pendingAssignments = $pendingAssignments ?? [];
$activeAssignments = $activeAssignments ?? [];
$replacementInvitations = $replacementInvitations ?? [];
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
$dashboardContent = function () use ($assignments, $pendingAssignments, $activeAssignments, $replacementInvitations, $h, $money, $formatDate, $daysUntil) {
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/supplier-bookings.css?v=<?= filemtime(APPROOT . '/../public/css/supplier-bookings.css') ?>">
<script src="<?= URLROOT ?>/public/js/supplier-toast.js"></script>

<div class="asn-page">
    <header class="asn-header">
        <div class="asn-header-left">
            <p class="asn-kicker">Assignments</p>
            <h1 class="asn-title">My Assignments</h1>
            <?php $totalCount = count($assignments) + count($replacementInvitations); ?>
            <span class="asn-count">
                <span class="asn-count-dot"></span>
                <?= $totalCount ?> assignment<?= $totalCount === 1 ? '' : 's' ?>
            </span>
        </div>
        <div class="bk-nav-group">
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

    <?php if (empty($assignments) && empty($replacementInvitations)): ?>
        <div class="asn-empty">
            <span class="asn-empty-icon"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="11" rx="1"/><path d="M5 2v2M11 2v2M2 7h12"/></svg></span>
            <h2>No assignments yet</h2>
            <p>When a customer books your services, you'll see assignments here to accept or manage.</p>
        </div>
    <?php else: ?>

        <?php if (!empty($replacementInvitations)): ?>
        <section class="asn-section">
            <div class="asn-section-head">
                <h2 class="asn-section-title">Replacement Invitations</h2>
                <span class="asn-section-count asn-section-count--amber"><?= count($replacementInvitations) ?></span>
            </div>
            <div class="asn-grid">
            <?php foreach ($replacementInvitations as $inv):
                $bookingId = (int)($inv['booking_id'] ?? 0);
                $status = (string)($inv['status'] ?? 'invited');
                $eventDate = $inv['event_date'] ?? '';
                $delta = (float)($inv['price_delta'] ?? 0);
            ?>
                <article class="asn-card" data-invitation-id="<?= (int)$inv['id'] ?>">
                    <div class="asn-card-top">
                        <span class="asn-card-ref"><?= $h($inv['booking_ref'] ?? ('BK-' . str_pad((string)$bookingId, 3, '0', STR_PAD_LEFT))) ?></span>
                        <span class="asn-card-customer"><?= $h($inv['customer_name'] ?? 'Customer') ?></span>
                        <span class="asn-countdown asn-countdown--soon">Replacement option</span>
                    </div>
                    <div class="asn-card-meta">
                        <span><strong><?= $h($formatDate($eventDate)) ?></strong></span>
                        <?php if (!empty($inv['venue'])): ?>
                        <span class="asn-card-meta-sep">·</span>
                        <span><?= $h($inv['venue']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="asn-services-list">
                        <div class="asn-service-row">
                            <div class="asn-service-info">
                                <div class="asn-service-name-row">
                                    <span class="asn-service-name"><?= $h($inv['service_name'] ?? 'Replacement service') ?></span>
                                    <span class="asn-service-cat"><?= $h($inv['category_name'] ?? '') ?></span>
                                </div>
                                <div class="asn-service-replacement">
                                    Replacement for <?= $h($inv['original_supplier_name'] ?? 'original supplier') ?>
                                    <?= !empty($inv['original_service_name']) ? ' · ' . $h($inv['original_service_name']) : '' ?>
                                </div>
                                <div class="asn-service-replacement">
                                    <?= $money($inv['price'] ?? 0) ?> · <?= $delta > 0 ? '+' . $money($delta) . ' over original' : 'No extra cost to customer' ?>
                                </div>
                            </div>
                            <?php if ($status === 'invited'): ?>
                            <div class="asn-service-actions">
                                <button type="button" class="asn-response-btn asn-response-btn--accept asn-invite-respond-btn" data-action="accept" data-invitation-id="<?= (int)$inv['id'] ?>">Accept</button>
                                <button type="button" class="asn-response-btn asn-response-btn--decline asn-invite-respond-btn" data-action="decline" data-invitation-id="<?= (int)$inv['id'] ?>">Decline</button>
                            </div>
                            <?php else: ?>
                            <span class="asn-service-status <?= $status === 'accepted' || $status === 'chosen' ? 'asn-service-status--confirmed' : 'asn-service-status--pending' ?>"><?= $h(ucfirst($status)) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="asn-card-bottom">
                        <span class="asn-response-pill asn-response-pill--awaiting">Admin will show accepted options to the customer.</span>
                        <a href="<?= URLROOT ?>/supplier/bookingDetail/<?= $bookingId ?>" class="asn-view-btn">View details</a>
                    </div>
                </article>
            <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

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
                $ref = $a['booking_ref'] ?? ('BK-' . str_pad($bookingId, 3, '0', STR_PAD_LEFT));
                $customer = trim((string)($a['customer_name'] ?? 'Customer'));
                $eventDate = $a['event_date'] ?? '';
                $venue = trim((string)($a['venue'] ?? ''));
                $totalAmount = (float)($a['total_amount'] ?? 0);
                $deadline = $a['supplier_response_deadline'] ?? '';
                $services = $a['services'] ?? [];

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
                <article class="asn-card" data-booking-id="<?= $bookingId ?>">
                    <div class="asn-card-top">
                        <span class="asn-card-ref"><?= $h($ref) ?></span>
                        <span class="asn-card-customer"><?= $h($customer) ?></span>
                        <?php if ($countdownLabel !== ''): ?>
                        <span class="asn-countdown <?= $countdownClass ?>">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l3 2"/></svg>
                            <?= $countdownLabel ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="asn-card-meta">
                        <span><strong><?= $h($formatDate($eventDate)) ?></strong></span>
                        <?php if ($venue !== ''): ?>
                        <span class="asn-card-meta-sep">·</span>
                        <span><?= $h($venue) ?></span>
                        <?php endif; ?>
                        <span class="asn-card-meta-sep">·</span>
                        <span><?= $money($totalAmount) ?></span>
                    </div>

                    <div class="asn-services-list">
                        <?php foreach ($services as $svc):
                            $svcBsid = $svc['booking_supplier_id'];
                            $svcStatus = $svc['supplier_status'] ?? 'pending';
                            $isRepl = $svc['replacement_id'] > 0;
                            $isPending = $svcStatus === 'pending';
                        ?>
                        <div class="asn-service-row" data-bsid="<?= $svcBsid ?>">
                            <div class="asn-service-info">
                                <div class="asn-service-name-row">
                                    <span class="asn-service-name"><?= $h($svc['service_name']) ?></span>
                                    <span class="asn-service-cat"><?= $h($svc['category_name']) ?></span>
                                </div>
                                <?php if ($isRepl && $svc['original_supplier_name'] !== ''): ?>
                                <div class="asn-service-replacement">
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width:10px;height:10px"><path d="M2 8a6 6 0 0110-4.5M14 8a6 6 0 01-10 4.5"/><path d="M12 2v3H9M4 14v-3h3"/></svg>
                                    Replacement for <?= $h($svc['original_supplier_name']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($isPending): ?>
                            <div class="asn-service-actions">
                                <button type="button" class="asn-response-btn asn-response-btn--accept asn-respond-btn" data-action="accept" data-bsid="<?= $svcBsid ?>">
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l3.5 3.5L13 5"/></svg>
                                    Accept
                                </button>
                                <?php if (!empty($a['decline_blocked'])): ?>
                                <button type="button" class="asn-response-btn asn-response-btn--request-decline asn-respond-btn" data-action="request_decline" data-bsid="<?= $svcBsid ?>">
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1v6M8 11h.01"/><circle cx="8" cy="8" r="6"/></svg>
                                    Request decline
                                </button>
                                <?php else: ?>
                                <button type="button" class="asn-response-btn asn-response-btn--decline asn-respond-btn" data-action="decline" data-bsid="<?= $svcBsid ?>">
                                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>
                                    Decline
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php elseif ($svcStatus === 'decline_requested'): ?>
                            <span class="asn-service-status asn-service-status--decline-requested">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width:13px;height:13px"><path d="M8 1v6M8 11h.01"/><circle cx="8" cy="8" r="6"/></svg>
                                Decline requested
                            </span>
                            <?php else: ?>
                            <span class="asn-service-status <?= $svcStatus === 'confirmed' ? 'asn-service-status--confirmed' : 'asn-service-status--pending' ?>"><?= $h(ucfirst($svcStatus)) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="asn-card-bottom">
                        <div class="asn-card-bottom-left">
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
                            <span class="asn-response-pill asn-response-pill--awaiting">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width:11px;height:11px"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l3 2"/></svg>
                                Awaiting response
                            </span>
                        </div>
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
                $paymentStatus = strtolower((string)($a['payment_status'] ?? 'pending'));
                $totalAmount = (float)($a['total_amount'] ?? 0);
                $paidAmount = (float)($a['paid_amount'] ?? 0);
                $services = $a['services'] ?? [];

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
            ?>
                <article class="asn-card" data-booking-id="<?= $bookingId ?>">
                    <div class="asn-card-top">
                        <span class="asn-card-ref"><?= $h($ref) ?></span>
                        <span class="asn-card-customer"><?= $h($customer) ?></span>
                        <?php if ($countdownLabel !== ''): ?>
                        <span class="asn-countdown <?= $countdownClass ?>">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l3 2"/></svg>
                            <?= $countdownLabel ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="asn-card-meta">
                        <span><strong><?= $h($formatDate($eventDate)) ?></strong></span>
                        <?php if ($venue !== ''): ?>
                        <span class="asn-card-meta-sep">·</span>
                        <span><?= $h($venue) ?></span>
                        <?php endif; ?>
                        <span class="asn-card-meta-sep">·</span>
                        <span><?= $money($totalAmount) ?></span>
                    </div>

                    <div class="asn-services-list">
                        <?php foreach ($services as $svc):
                            $svcStatus = $svc['supplier_status'] ?? 'confirmed';
                            $statusClass = $svcStatus === 'confirmed' ? 'asn-service-status--confirmed' : 'asn-service-status--pending';
                        ?>
                        <div class="asn-service-row">
                            <div class="asn-service-info">
                                <div class="asn-service-name-row">
                                    <span class="asn-service-name"><?= $h($svc['service_name']) ?></span>
                                    <span class="asn-service-cat"><?= $h($svc['category_name']) ?></span>
                                </div>
                            </div>
                            <span class="asn-service-status <?= $statusClass ?>"><?= $h(ucfirst($svcStatus)) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="asn-card-bottom">
                        <div class="asn-card-bottom-left">
                            <span class="asn-payment-pill <?= $paymentClass ?>"><?= $paymentLabel ?></span>
                        </div>
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
                <p style="font-size:13px;color:#7b5c69;margin-bottom:14px;line-height:1.5">
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
            var bsid = button.dataset.bsid; // Get bsid from the button itself
            var action = button.dataset.action;

            if (!bookingId || !bsid) return;

            /* For decline or request_decline, open modal instead of confirm() */
            if (action === 'decline' || action === 'request_decline') {
                pendingDeclineBtn = button;
                declineModal.dataset.bookingId = bookingId;
                declineModal.dataset.bsid = bsid;
                declineReason.value = '';
                charCount.textContent = '0 / 500';
                /* Update modal title for request_decline */
                var title = document.getElementById('asn-decline-title');
                if (title) title.textContent = action === 'request_decline' ? 'Request Decline' : 'Decline Assignment';
                var submitBtn = declineForm.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.textContent = action === 'request_decline' ? 'Submit request' : 'Confirm decline';
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
                var action = pendingDeclineBtn.dataset.action || 'decline';
                await submitResponse(pendingDeclineBtn, declineModal.dataset.bookingId, declineModal.dataset.bsid, action, reason);
            }
        });
    }

    async function submitResponse(button, bookingId, bsid, action, reason) {
        button.disabled = true;
        var original = button.innerHTML;
        var loadingText = action === 'accept' ? 'Accepting…' : (action === 'request_decline' ? 'Submitting…' : 'Declining…');
        button.innerHTML = loadingText;

        var formData = new FormData();
        formData.append('booking_id', bookingId);
        formData.append('booking_supplier_id', bsid || '');
        formData.append('action', action);
        if (reason) formData.append('reason', reason);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

        try {
            var resp = await fetch('<?= URLROOT ?>/supplier/bookingRespond', { method: 'POST', body: formData });
            var data = await resp.json().catch(function() { return {}; });

            /* 409 = already handled — show resolved state instead of error */
            if (resp.status === 409) {
                markServiceResolved(button, action);
                return;
            }

            if (data.success) {
                var msg = action === 'accept' ? 'Assignment accepted!' : (action === 'request_decline' ? 'Decline request submitted!' : 'Assignment declined.');
                supToastSuccess(msg);
                markServiceResolved(button, action);
                return;
            }
            supToastError(data.error || 'Could not update. Please try again.');
        } catch (err) {
            supToastError('Network error. Please try again.');
        }
        button.disabled = false;
        button.innerHTML = original;
    }

    document.querySelectorAll('.asn-invite-respond-btn').forEach(function(button) {
        button.addEventListener('click', async function() {
            var action = button.dataset.action || '';
            var invitationId = button.dataset.invitationId || '';
            button.disabled = true;
            var original = button.innerHTML;
            button.innerHTML = action === 'accept' ? 'Accepting…' : 'Declining…';

            var formData = new FormData();
            formData.append('invitation_id', invitationId);
            formData.append('action', action);
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

            try {
                var resp = await fetch('<?= URLROOT ?>/supplier/replacementInvitationRespond', { method: 'POST', body: formData });
                var data = await resp.json().catch(function() { return {}; });
                if (data.success) {
                    supToastSuccess(data.message || 'Replacement invitation updated.');
                    markServiceResolved(button, action);
                    return;
                }
                supToastError(data.error || 'Could not update invitation.');
            } catch (err) {
                supToastError('Network error. Please try again.');
            }
            button.disabled = false;
            button.innerHTML = original;
        });
    });

    function markServiceResolved(button, action) {
        var serviceRow = button.closest('.asn-service-row');
        var card = button.closest('.asn-card');
        var section = button.closest('.asn-section');

        /* Replace action buttons with a resolved badge */
        var actionsWrap = button.closest('.asn-service-actions');
        if (actionsWrap) {
            var badge = document.createElement('span');
            if (action === 'request_decline') {
                badge.className = 'asn-service-status asn-service-status--decline-requested';
                badge.innerHTML = '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width:13px;height:13px"><path d="M8 1v6M8 11h.01"/><circle cx="8" cy="8" r="6"/></svg> Decline requested';
            } else {
                badge.className = 'asn-service-status ' + (action === 'accept' ? 'asn-service-status--confirmed' : 'asn-service-status--pending');
                badge.textContent = action === 'accept' ? 'Accepted' : 'Declined';
            }
            actionsWrap.replaceWith(badge);
            return;
        }

        /* Fallback: fade out the row if no actions wrapper found */
        if (serviceRow) {
            serviceRow.style.transition = 'opacity .25s';
            serviceRow.style.opacity = '0';
            setTimeout(function() { serviceRow.remove(); }, 260);
        }

        setTimeout(function() {
            if (card && !card.querySelector('.asn-service-row')) {
                card.style.transition = 'opacity .3s, transform .3s';
                card.style.opacity = '0';
                card.style.transform = 'translateY(-8px)';
                setTimeout(function() { card.remove(); updateCounts(section); }, 310);
            } else {
                updateCounts(section);
            }
        }, 300);
    }

    function updateCounts(section) {
        /* Update section count badge */
        if (section) {
            var grid = section.querySelector('.asn-grid');
            var remaining = grid ? grid.querySelectorAll('.asn-card').length : 0;
            var badge = section.querySelector('.asn-section-count');
            if (badge) badge.textContent = remaining;
            if (remaining === 0) section.remove();
        }
        /* Update page-level assignment count */
        var countEl = document.querySelector('.asn-count');
        if (countEl) {
            var totalCards = document.querySelectorAll('.asn-grid .asn-card').length;
            countEl.innerHTML = '<span class="asn-count-dot"></span> ' + totalCards + ' assignment' + (totalCards === 1 ? '' : 's');
        }
    }
})();
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php $pageTitle = 'Assignments — Golden Promise'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
  <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
