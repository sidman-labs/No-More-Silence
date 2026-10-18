<?php
declare(strict_types=1);

function render(string $title, string $view, array $data = [], array $meta = []): never
{
    extract($data, EXTR_SKIP);
    $pageTitle = $title;
    $pageMeta = $meta;
    $pageView = $view;
    ob_start();
    require dirname(__DIR__) . '/views/' . $view . '.php';
    $pageContent = ob_get_clean();
    require dirname(__DIR__) . '/views/layout.php';
    exit;
}

function renderAdmin(string $title, string $view, array $data = []): never
{
    extract($data, EXTR_SKIP);
    $pageTitle = $title;
    ob_start();
    require dirname(__DIR__) . '/views/admin/' . $view . '.php';
    $pageContent = ob_get_clean();
    require dirname(__DIR__) . '/views/admin/layout.php';
    exit;
}
