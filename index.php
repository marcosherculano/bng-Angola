<?php

// Redireciona o acesso à raiz do projecto para o directório "public" do Laravel.
// Isto permite abrir http://localhost/bng-Angola/ sem precisar de /public.

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$queryString = $_SERVER['QUERY_STRING'] ?? '';

$target = rtrim($uri, '/');
$target = $target === '' ? '/' : $target;

// Se já estiver em /public, não redireciona.
if (preg_match('~/(public)(/|$)~', $target)) {
    require __DIR__ . '/public/index.php';
    exit;
}

$redirectTo = rtrim($target, '/') . '/public/';
if ($queryString !== '') {
    $redirectTo .= '?' . $queryString;
}

header('Location: ' . $redirectTo, true, 302);
exit;
