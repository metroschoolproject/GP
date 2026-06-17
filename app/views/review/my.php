<?php
$submitted = $submitted ?? [];
$pending   = $pending ?? [];
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$stars = fn($n) => str_repeat('★', max(0, min(5, (int)$n))) . str_repeat('☆', 5 - max(0, min(5, (int)$n)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Reviews — Golden Promise</title>
<?php $v = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $v ?>">
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#f2e4d4;--card:#fff;--rule:rgba(178,143,110,0.22);--rule-strong:rgba(178,143,110,0.45);--plum:#6b4459;--plum-dk:#4e3141;--plum-lt:#9b7289;--gold:#b8924a;--muted:#a08878;--text:#1a1118;--text2:#5c4a54;--danger:#b94b4b;--r-sm:8px;--r-md:14px;--r-lg:20px;--font-d:'Playfair Display',Georgia,serif;--font-b:'Poppins',system-ui,sans-serif;--pad-x:clamp(20px,5vw,72px)}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:var(--font-b);font-size:14px;-webkit-font-smoothing:antialiased;min-height:100vh;display:flex;flex-direction:column}
a{color:inherit;text-decoration:none}
.gp-header{position:sticky;top:0;z-index:100;display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:24px;padding:0 var(--pad-x);height:68px;border-bottom:1px solid var(--rule);background:rgba(242,228,212,0.82);backdrop-filter:blur(24px)}
.gp-brand{display:flex;align-items:center;gap:12px;font-size:17px;font-weight:800}
.gp-brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:13px;font-weight:700}
.gp-header-nav{display:flex;align-items:center;gap:2px}
.gp-header-nav a{padding:7px 16px;border-radius:999px;font-size:13px;font-weight:600;color:var(--text2);transition:all .22s}
.gp-header-nav a:hover,.gp-header-nav a.active{color:var(--plum);background:rgba(107,68,89,0.08)}
.gp-page{flex:1;padding:40px var(--pad-x) 80px;max-width:900px;margin:0 auto;width:100%}
.gp-page-title{font-family:var(--font-d);font-size:28px;font-weight:600;margin-bottom:6px}
.gp-page-sub{font-size:13px;color:var(--muted);margin-bottom:32px}
.gp-btn-sm{display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:999px;border:1px solid var(--rule-strong);font-size:11px;font-weight:600;color:var(--text2);transition:all .2s;text-decoration:none;cursor:pointer;background:none}
.gp-btn-sm:hover{border-color:var(--plum);color:var(--plum)}
.gp-btn-sm.primary{background:var(--plum);color:#fff;border-color:var(--plum)}
.gp-btn-sm.primary:hover{background:var(--plum-dk)}
.gp-btn-sm.danger{color:var(--danger);border-color:rgba(185,75,75,0.2)}
.gp-btn-sm.danger:hover{background:var(--danger);color:#fff}
.gp-section-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--gold);margin-bottom:4px}
.gp-section-title{font-family:var(--font-d);font-size:20px;font-weight:600;margin-bottom:16px}
.gp-card{background:var(--card);border:1px solid var(--rule);border-radius:var(--r-lg);overflow:hidden;margin-bottom:12px}
.gp-card-b{padding:16px 18px}
.gp-review-card{display:grid;grid-template-columns:1fr auto;gap:12px;align-items:start}
.gp-review-stars{color:#d6a72d;font-size:18px;letter-spacing:-1px;margin-bottom:4px}
.gp-review-comment{font-size:13px;color:var(--text2);line-height:1.6;margin-bottom:8px}
.gp-review-meta{font-size:11px;color:var(--muted)}
.gp-review-actions{display:flex;gap:6px;flex-shrink:0}
.gp-empty{text-align:center;padding:40px 20px;color:var(--muted);font-size:13px}
.gp-pending-card{display:flex;justify-content:space-between;align-items:center;gap:16px}
.gp-pending-info{flex:1}
.gp-pending-ref{font-size:12px;font-weight:600;color:var(--plum);margin-bottom:2px}
.gp-pending-date{font-size:11px;color:var(--muted)}
.gp-section+.gp-section{margin-top:40px}
.gp-edit-form{margin-top:12px;display:none}
.gp-star-picker{display:flex;gap:6px;margin-bottom:10px}
.gp-star-btn{background:none;border:none;cursor:pointer;font-size:24px;color:var(--rule-strong);padding:0;line-height:1;transition:color .15s}
.gp-star-btn.active{color:#d6a72d}
.gp-review-textarea{width:100%;padding:10px 12px;border-radius:var(--r-sm);border:1px solid var(--rule-strong);background:#faf7f2;font-family:var(--font-b);font-size:13px;color:var(--text);resize:vertical;min-height:80px;outline:none;transition:border-color .2s}
.gp-review-textarea:focus{border-color:var(--plum)}
@media(max-width:640px){.gp-header-nav{display:none}:root{--pad-x:16px}.gp-review-card{grid-template-columns:1fr}.gp-review-actions{margin-top:6px}}
</style>
</head>
<body>
<header class="gp-header">
  <a class="gp-brand" href="<?= URLROOT ?>/main/index"><span class="gp-brand-mark">G</span>Golden Promise</a>
  <nav class="gp-header-nav">
    <a href="<?= URLROOT ?>/main/index">Home</a>
    <a href="<?= URLROOT ?>/booking/myBookings">Bookings</a>
    <a href="<?= URLROOT ?>/review/my" class="active">Reviews</a>
  </nav>
  <div style="display:flex;align-items:center;gap:10px;">
    <?php require APPROOT . '/views/dashboardLayout/customerNotification.php'; ?>
  </div>
</header>

<main class="gp-page">
  <div class="gp-page-title">My Reviews</div>
  <p class="gp-page-sub">Manage reviews you've written and bookings awaiting your feedback.</p>

  <!-- Pending Reviews -->
  <section class="gp-section">
    <div class="gp-section-label">Awaiting Your Feedback</div>
    <div class="gp-section-title">Pending Reviews (<?= count($pending) ?>)</div>

    <?php if (empty($pending)): ?>
      <div class="gp-empty">No completed bookings are waiting for your review.</div>
    <?php else: ?>
      <?php foreach ($pending as $p): ?>
        <div class="gp-card">
          <div class="gp-card-b">
            <div class="gp-pending-card">
              <div class="gp-pending-info">
                <div class="gp-pending-ref">Booking #<?= (int)$p['booking_id'] ?></div>
                <div class="gp-pending-date">
                  <?php if (!empty($p['event_date'])): ?>
                    Event: <?= $h(date('d M Y', strtotime($p['event_date']))) ?>
                  <?php endif; ?>
                </div>
              </div>
              <a class="gp-btn-sm primary" href="<?= URLROOT ?>/booking/detail/<?= (int)$p['booking_id'] ?>">Write a Review</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <!-- Submitted Reviews -->
  <section class="gp-section">
    <div class="gp-section-label">Your Reviews</div>
    <div class="gp-section-title">Submitted Reviews (<?= count($submitted) ?>)</div>

    <?php if (empty($submitted)): ?>
      <div class="gp-empty">You haven't submitted any reviews yet.</div>
    <?php else: ?>
      <?php foreach ($submitted as $r): ?>
        <div class="gp-card" id="reviewCard_<?= (int)$r['id'] ?>">
          <div class="gp-card-b">
            <div class="gp-review-card">
              <div>
                <div class="gp-review-stars"><?= $stars((int)$r['rating']) ?></div>
                <div class="gp-review-comment" id="reviewComment_<?= (int)$r['id'] ?>"><?= $h($r['comment'] ?? '') ?></div>
                <div class="gp-review-meta">
                  Booking #<?= (int)$r['booking_id'] ?>
                  <?php if (!empty($r['event_date'])): ?> · Event: <?= $h(date('d M Y', strtotime($r['event_date']))) ?><?php endif; ?>
                  · Submitted <?= $h(date('d M Y', strtotime($r['created_at'] ?? 'now'))) ?>
                  <?php if (!empty($r['updated_at'])): ?> · Edited <?= $h(date('d M Y', strtotime($r['updated_at']))) ?><?php endif; ?>
                </div>
              </div>
              <div class="gp-review-actions">
                <?php if ($r['can_edit']): ?>
                  <button class="gp-btn-sm" type="button" onclick="toggleEditCard(<?= (int)$r['id'] ?>, <?= (int)$r['rating'] ?>)">Edit</button>
                <?php endif; ?>
                <form method="POST" action="<?= URLROOT ?>/review/delete/<?= (int)$r['id'] ?>" style="display:inline" onsubmit="return confirm('Remove this review?')">
                  <input type="hidden" name="booking_id" value="<?= (int)$r['booking_id'] ?>">
                  <button class="gp-btn-sm danger" type="submit">Delete</button>
                </form>
              </div>
            </div>

            <?php if ($r['can_edit']): ?>
            <div class="gp-edit-form" id="editForm_<?= (int)$r['id'] ?>">
              <div class="gp-star-picker" id="editPicker_<?= (int)$r['id'] ?>" data-value="<?= (int)$r['rating'] ?>">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                  <button class="gp-star-btn<?= $s <= (int)$r['rating'] ? ' active' : '' ?>" type="button" data-val="<?= $s ?>" onclick="setEditStar(<?= (int)$r['id'] ?>, <?= $s ?>)">★</button>
                <?php endfor; ?>
              </div>
              <textarea class="gp-review-textarea" id="editText_<?= (int)$r['id'] ?>" maxlength="2000"><?= $h($r['comment'] ?? '') ?></textarea>
              <div style="display:flex;gap:8px;margin-top:8px">
                <button class="gp-btn-sm primary" type="button" onclick="submitEditCard(<?= (int)$r['id'] ?>)">Save</button>
                <button class="gp-btn-sm" type="button" onclick="toggleEditCard(<?= (int)$r['id'] ?>)">Cancel</button>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<script>
function setEditStar(id, val) {
  const picker = document.getElementById('editPicker_' + id);
  if (picker) picker.dataset.value = val;
  document.querySelectorAll('#editPicker_' + id + ' .gp-star-btn').forEach(b => {
    b.classList.toggle('active', parseInt(b.dataset.val) <= val);
  });
}
function toggleEditCard(id) {
  const form = document.getElementById('editForm_' + id);
  if (!form) return;
  form.style.display = form.style.display === 'block' ? 'none' : 'block';
}
function submitEditCard(id) {
  const picker = document.getElementById('editPicker_' + id);
  const rating = picker ? parseInt(picker.dataset.value) : 0;
  const comment = (document.getElementById('editText_' + id)?.value || '').trim();
  if (!rating || rating < 1 || rating > 5) { alert('Please select a rating.'); return; }
  if (comment.length < 10) { alert('Comment must be at least 10 characters.'); return; }
  fetch('<?= URLROOT ?>/review/update/' + id, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({rating, comment})
  }).then(r => r.json()).then(d => {
    if (d.status === 'success') {
      const stars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
      document.querySelector('#reviewCard_' + id + ' .gp-review-stars').textContent = stars;
      document.getElementById('reviewComment_' + id).textContent = comment;
      document.getElementById('editForm_' + id).style.display = 'none';
    } else {
      alert(d.error || 'Could not update review.');
    }
  }).catch(() => alert('Network error.'));
}
</script>
</body>
</html>
