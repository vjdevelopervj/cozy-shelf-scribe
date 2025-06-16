
<?php
// Application configuration
define('DATA_DIR', 'data/');
define('MEMBERS_FILE', DATA_DIR . 'members.json');
define('BOOKS_FILE', DATA_DIR . 'books.json');
define('BORROWERS_FILE', DATA_DIR . 'borrowers.json');
define('RETURNS_FILE', DATA_DIR . 'returns.json');

// Create data directory if it doesn't exist
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0777, true);
}

// Initialize empty JSON files if they don't exist
$files = [MEMBERS_FILE, BOOKS_FILE, BORROWERS_FILE, RETURNS_FILE];
foreach ($files as $file) {
    if (!file_exists($file)) {
        file_put_contents($file, '[]');
    }
}
?>
