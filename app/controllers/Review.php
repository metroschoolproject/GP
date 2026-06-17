<?php

require_once APPROOT . '/traits/JsonResponseTrait.php';

class Review extends Controller
{
    use JsonResponseTrait;

    private ReviewModel $reviewModel;
    private ?int $userId;

    public function __construct()
    {
        $this->reviewModel = $this->model('ReviewModel');
        $this->userId = $_SESSION['session_uid'] ?? null;
    }

    /* ─── Helpers ─────────────────────────────────────────────────── */

    private function ensureCustomer(): void
    {
        if (!$this->userId) {
            redirect('users/auth');
            exit;
        }
    }

    private function h(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    }

    /* ─── Submit ──────────────────────────────────────────────────── */

    public function submit(int $bookingId): void
    {
        $this->ensureCustomer();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('booking/detail/' . $bookingId);
            return;
        }

        if (!$this->reviewModel->canReview($this->userId, $bookingId)) {
            $_SESSION['review_error'] = 'You are not eligible to review this booking.';
            redirect('booking/detail/' . $bookingId);
            return;
        }

        $rating  = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $_SESSION['review_error'] = 'Please select a rating between 1 and 5 stars.';
            redirect('booking/detail/' . $bookingId);
            return;
        }

        if (strlen($comment) < 10 || strlen($comment) > 2000) {
            $_SESSION['review_error'] = 'Your review must be between 10 and 2000 characters.';
            redirect('booking/detail/' . $bookingId);
            return;
        }

        $this->reviewModel->create($bookingId, $this->userId, $rating, $comment);

        $_SESSION['review_success'] = 'Thank you! Your review has been submitted.';
        redirect('booking/detail/' . $bookingId);
    }

    /* ─── Update ──────────────────────────────────────────────────── */

    public function update(int $reviewId): void
    {
        $this->ensureCustomer();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $rating  = (int)($payload['rating'] ?? $_POST['rating'] ?? 0);
        $comment = trim($payload['comment'] ?? $_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $this->jsonResponse(['error' => 'Rating must be 1–5.'], 422);
            return;
        }

        if (strlen($comment) < 10 || strlen($comment) > 2000) {
            $this->jsonResponse(['error' => 'Comment must be 10–2000 characters.'], 422);
            return;
        }

        if (!$this->reviewModel->isWithinEditWindow($reviewId)) {
            $this->jsonResponse(['error' => 'The 7-day edit window has passed.'], 403);
            return;
        }

        $ok = $this->reviewModel->update($reviewId, $this->userId, $rating, $comment);
        if (!$ok) {
            $this->jsonResponse(['error' => 'Could not update review.'], 400);
            return;
        }

        $this->jsonResponse(['status' => 'success']);
    }

    /* ─── Delete ──────────────────────────────────────────────────── */

    public function delete(int $reviewId): void
    {
        $this->ensureCustomer();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('booking/myBookings');
            return;
        }

        $this->reviewModel->delete($reviewId, $this->userId);

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        if ($bookingId) {
            $_SESSION['review_success'] = 'Your review has been removed.';
            redirect('booking/detail/' . $bookingId);
        } else {
            redirect('review/my');
        }
    }

    /* ─── My Reviews ──────────────────────────────────────────────── */

    public function my(): void
    {
        $this->ensureCustomer();

        $submitted = $this->reviewModel->getByCustomer($this->userId);
        $pending   = $this->reviewModel->getPendingBookings($this->userId);

        $enrichedSubmitted = array_map(function ($r) {
            return array_merge($r, [
                'can_edit' => $this->reviewModel->isWithinEditWindow((int)$r['id']),
            ]);
        }, $submitted);

        $this->view('review/my', [
            'submitted' => $enrichedSubmitted,
            'pending'   => $pending,
        ]);
    }

    /* ─── AJAX: Reviews for a service ────────────────────────────── */

    public function service(int $serviceId): void
    {
        $sort   = $_GET['sort']   ?? 'recent';
        $offset = max(0, (int)($_GET['offset'] ?? 0));
        $limit  = min(20, max(1, (int)($_GET['limit'] ?? 4)));

        $reviews = $this->reviewModel->getByService($serviceId, $sort, $limit, $offset);

        $html = '';
        foreach ($reviews as $idx => $review) {
            $name    = $this->h((string)($review['customer_name'] ?? 'Customer'));
            $date    = $this->h(date('Y.m.d', strtotime($review['created_at'] ?? 'now')));
            $comment = $this->h((string)($review['comment'] ?? ''));
            $rating  = number_format((float)($review['rating'] ?? 0), 1);
            $initial = mb_strtoupper(mb_substr($name, 0, 1));
            $html .= '<article class="review-item">'
                . '<div class="review-avatar" style="background:var(--plum);color:#fff;font-weight:700;">' . $initial . '</div>'
                . '<div class="review-text"><strong>' . $name . '</strong>'
                . '<span>' . $date . '</span>'
                . '<p>' . $comment . '</p></div>'
                . '<strong class="review-score">&#9733; ' . $rating . '</strong>'
                . '</article>';
        }

        $this->jsonResponse([
            'html'     => $html,
            'count'    => count($reviews),
            'has_more' => count($reviews) === $limit,
        ]);
    }
}
