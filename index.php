
<?php
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/data-access.php';

requireAdmin();

$currentPage = getCurrentPage();
$allowedPages = ['dashboard', 'members', 'books', 'borrowers', 'returns', 'fines', 'payments'];

if (!in_array($currentPage, $allowedPages)) {
    $currentPage = 'dashboard';
}

include 'includes/header.php';
include 'includes/navigation.php';

switch ($currentPage) {
    case 'members':
        include 'pages/members.php';
        break;
    case 'books':
        include 'pages/books.php';
        break;
    case 'borrowers':
        include 'pages/borrowers.php';
        break;
    case 'returns':
        include 'pages/returns.php';
        break;
    case 'fines':
        include 'pages/fines.php';
        break;
    case 'payments':
        include 'pages/payments.php';
        break;
    default:
        include 'pages/dashboard.php';
        break;
}

include 'includes/footer.php';
?>
