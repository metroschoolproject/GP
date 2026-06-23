<?php


class Controller
{
    protected function requireRole(string $role, bool $json = false, string $loginUrl = 'users/auth'): int
    {
        $userId = (int)($_SESSION['session_uid'] ?? 0);
        if ($userId > 0 && user_has_role($userId, $role)) {
            return $userId;
        }

        if ($json) {
            http_response_code(403);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        // Preserve the requested URL so user returns here after login
        $returnUrl = trim((string)($_GET['url'] ?? ''), '/');
        $separator = str_contains($loginUrl, '?') ? '&' : '?';
        $redirectUrl = $returnUrl !== '' ? $loginUrl . $separator . 'redirect=' . urlencode($returnUrl) : $loginUrl;
        redirect($redirectUrl);
    }

    protected function requireCsrf(bool $json = true): void
    {
        if (valid_csrf_token()) {
            return;
        }

        if ($json) {
            http_response_code(419);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['error' => 'Your session token expired. Refresh the page and try again.']);
            exit;
        }

        http_response_code(419);
        exit('Invalid CSRF token.');
    }

    public function view($view, $data = [])
    {
        $viewPath = APPROOT . "/views/" . $view . ".php";

        if (file_exists($viewPath)) {
            if (!empty($data)) {
                extract($data);
            }
            require $viewPath;
        } else {
            die("View file didn't exit");
        }

    }
    public function model($model)
    {
        $modelPath = APPROOT . "/models/" . ucwords($model) . ".php";

        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        } else {
            die("Model file didn't exit");
        }
    }



}


?>
