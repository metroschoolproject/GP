<?php
/**
 * Reusable pagination partial.
 *
 * Expected variables:
 *   $currentPage  int   Current page number (1-based)
 *   $totalPages   int   Total number of pages
 *   $totalCount   int   Total record count
 *   $perPage      int   Items per page
 *   $baseParams   string  Existing query string (e.g. 'status=pending&search=foo')
 *   $classPrefix  string  'admin' (default) or 'supplier' — controls CSS class set
 *   $showSinglePage bool  Render the footer even when there is only one page
 *   $h            callable  HTML-escape function (optional, defaults to htmlspecialchars)
 *
 * CSS classes required on page:
 *   admin:    .pagination, .page-info, .page-btns, .page-btn, .page-btn.active, .page-btn:disabled
 *   supplier: .bk-pagination, .bk-page-info, .bk-page-btns, .bk-page-btn, .bk-page-btn-cur, .bk-page-btn-disabled
 */

if (!isset($totalPages) || ($totalPages <= 1 && empty($showSinglePage))) {
    return;
}

$classPrefix = $classPrefix ?? 'admin';
$baseParams  = $baseParams ?? '';
$h           = $h ?? 'htmlspecialchars';
$prevText    = $prevText ?? '';
$nextText    = $nextText ?? '';

// Choose CSS class set
if ($classPrefix === 'supplier') {
    $cls = [
        'wrap'     => 'bk-pagination',
        'info'     => 'bk-page-info',
        'btns'     => 'bk-page-btns',
        'btn'      => 'bk-page-btn',
        'btnCur'   => 'bk-page-btn-cur',
        'btnDis'   => 'bk-page-btn-disabled',
    ];
} elseif ($classPrefix === 'customer') {
    $cls = [
        'wrap'     => 'gp-pagination',
        'info'     => 'gp-pagination-info',
        'btns'     => 'gp-pagination-btns',
        'btn'      => 'gp-pagination-btn',
        'btnCur'   => 'gp-pagination-btn-cur',
        'btnDis'   => 'gp-pagination-btn-disabled',
    ];
} else {
    $cls = [
        'wrap'     => 'pagination',
        'info'     => 'page-info',
        'btns'     => 'page-btns',
        'btn'      => 'page-btn',
        'btnCur'   => 'active',
        'btnDis'   => '',  // admin uses :disabled pseudo, style inline
    ];
}

// Strip existing page= from baseParams
$paramStr = trim($baseParams);
if ($paramStr !== '') {
    $paramStr = preg_replace('/&?page=\d+/', '', $paramStr);
    $paramStr = ltrim($paramStr, '&');
    if ($paramStr !== '') {
        $paramStr = '&' . $paramStr;
    }
}

$start = (($currentPage - 1) * $perPage) + 1;
$end   = min($currentPage * $perPage, $totalCount);
?>

<div class="<?= $cls['wrap'] ?>">
    <span class="<?= $cls['info'] ?>">
        Showing <?= $start ?>–<?= $end ?> of <?= $totalCount ?>
    </span>

    <div class="<?= $cls['btns'] ?>">
        <?php /* Prev */ ?>
        <?php if ($currentPage > 1): ?>
        <a href="?page=<?= $currentPage - 1 . $h($paramStr) ?>"
           class="<?= $cls['btn'] ?>" aria-label="Previous page">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18 9 12l6-6"/></svg>
            <?php if ($prevText !== ''): ?><span class="page-btn-label"><?= $h($prevText) ?></span><?php endif; ?>
        </a>
        <?php else: ?>
        <span class="<?= $cls['btn'] ?><?= $cls['btnDis'] ? ' ' . $cls['btnDis'] : '' ?>"
              style="<?= $cls['btnDis'] ? '' : 'opacity:.35;pointer-events:none' ?>"
              aria-disabled="true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18 9 12l6-6"/></svg>
            <?php if ($prevText !== ''): ?><span class="page-btn-label"><?= $h($prevText) ?></span><?php endif; ?>
        </span>
        <?php endif; ?>

        <?php /* Page numbers */ ?>
        <?php
        for ($p = 1; $p <= $totalPages; $p++):
            $showPage = ($p === 1)
                || ($p === $totalPages)
                || ($p >= $currentPage - 1 && $p <= $currentPage + 1);
            $isEllipsisBefore = ($p === 2 && $currentPage > 3);
            $isEllipsisAfter  = ($p === $totalPages - 1 && $currentPage < $totalPages - 2);
        ?>
            <?php if ($showPage): ?>
                <?php if ($p === $currentPage): ?>
                <span class="<?= $cls['btn'] ?><?= $cls['btnCur'] ? ' ' . $cls['btnCur'] : '' ?>" aria-current="page"><?= $p ?></span>
                <?php else: ?>
                <a href="?page=<?= $p . $h($paramStr) ?>" class="<?= $cls['btn'] ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php elseif ($isEllipsisBefore || $isEllipsisAfter): ?>
                <span style="padding:0 4px;color:var(--muted,#9ca3af);font-size:12px">…</span>
            <?php endif; ?>
        <?php endfor; ?>

        <?php /* Next */ ?>
        <?php if ($currentPage < $totalPages): ?>
        <a href="?page=<?= $currentPage + 1 . $h($paramStr) ?>"
           class="<?= $cls['btn'] ?>" aria-label="Next page">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            <?php if ($nextText !== ''): ?><span class="page-btn-label"><?= $h($nextText) ?></span><?php endif; ?>
        </a>
        <?php else: ?>
        <span class="<?= $cls['btn'] ?><?= $cls['btnDis'] ? ' ' . $cls['btnDis'] : '' ?>"
              style="<?= $cls['btnDis'] ? '' : 'opacity:.35;pointer-events:none' ?>"
              aria-disabled="true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            <?php if ($nextText !== ''): ?><span class="page-btn-label"><?= $h($nextText) ?></span><?php endif; ?>
        </span>
        <?php endif; ?>
    </div>
</div>
