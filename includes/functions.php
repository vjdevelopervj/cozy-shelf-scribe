
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

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: visitor.php');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function uploadFile($file, $directory, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedTypes)) {
        return false;
    }
    
    $fileName = generateId() . '.' . $fileExtension;
    $filePath = $directory . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return $fileName;
    }
    
    return false;
}

function calculateFine($dueDate, $returnDate, $finePerDay) {
    $due = new DateTime($dueDate);
    $return = new DateTime($returnDate);
    
    if ($return <= $due) {
        return 0;
    }
    
    $daysDiff = $due->diff($return)->days;
    return $daysDiff * $finePerDay;
}

function getTopActiveReaders($limit = 5) {
    $borrowers = loadData(BORROWERS_FILE);
    $users = loadData(USERS_FILE);
    
    $readerStats = [];
    
    foreach ($borrowers as $borrow) {
        if (!isset($readerStats[$borrow['memberId']])) {
            $readerStats[$borrow['memberId']] = 0;
        }
        $readerStats[$borrow['memberId']]++;
    }
    
    arsort($readerStats);
    $topReaders = array_slice($readerStats, 0, $limit, true);
    
    $result = [];
    foreach ($topReaders as $userId => $count) {
        $user = array_filter($users, function($u) use ($userId) {
            return $u['id'] === $userId;
        });
        
        if ($user) {
            $user = array_values($user)[0];
            $result[] = [
                'user' => $user,
                'borrow_count' => $count
            ];
        }
    }
    
    return $result;
}

function getTopPopularBooks($limit = 5) {
    $borrowers = loadData(BORROWERS_FILE);
    $books = loadData(BOOKS_FILE);
    
    $bookStats = [];
    
    foreach ($borrowers as $borrow) {
        if (!isset($bookStats[$borrow['bookId']])) {
            $bookStats[$borrow['bookId']] = 0;
        }
        $bookStats[$borrow['bookId']]++;
    }
    
    arsort($bookStats);
    $topBooks = array_slice($bookStats, 0, $limit, true);
    
    $result = [];
    foreach ($topBooks as $bookId => $count) {
        $book = array_filter($books, function($b) use ($bookId) {
            return $b['id'] === $bookId;
        });
        
        if ($book) {
            $book = array_values($book)[0];
            $result[] = [
                'book' => $book,
                'borrow_count' => $count
            ];
        }
    }
    
    return $result;
}
?>
