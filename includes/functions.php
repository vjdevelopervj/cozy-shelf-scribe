
<?php
function generateId() {
    return uniqid();
}

function loadData($filename) {
    if (file_exists($filename)) {
        $content = file_get_contents($filename);
        return json_decode($content, true) ?: [];
    }
    return [];
}

function saveData($filename, $data) {
    return file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
}

function redirect($page = '') {
    $url = $_SERVER['PHP_SELF'];
    if ($page) {
        $url .= '?page=' . urlencode($page);
    }
    header('Location: ' . $url);
    exit;
}

function getCurrentPage() {
    return $_GET['page'] ?? 'dashboard';
}

function isCurrentPage($page) {
    return getCurrentPage() === $page;
}
?>
