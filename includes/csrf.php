<?php
declare(strict_types=1);

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $submitted = (string) ($_POST['csrf_token'] ?? '');
    if ($submitted === '' || !hash_equals((string) ($_SESSION['csrf'] ?? ''), $submitted)) {
        http_response_code(419);
        flash('error', 'নিরাপত্তা যাচাই করা যায়নি। পেজটি রিফ্রেশ করে আবার চেষ্টা করুন।');
        redirect(requestPath());
    }
}
