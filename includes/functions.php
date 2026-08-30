<?php
require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}

function sanitize($conn, $str) {
    return htmlspecialchars(trim($str));
}

function generateInvoiceNo() {
    return 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
}

function flash($name, $message = '', $class = 'success') {
    if ($message) {
        $_SESSION['flash'][$name] = ['message' => $message, 'class' => $class];
    } elseif (isset($_SESSION['flash'][$name])) {
        $f = $_SESSION['flash'][$name];
        unset($_SESSION['flash'][$name]);
        echo '<div class="alert alert-' . $f['class'] . ' alert-dismissible fade show" role="alert">'
            . $f['message'] .
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}
