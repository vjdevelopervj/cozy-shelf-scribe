
<?php
// Konfigurasi aplikasi
define('DATA_DIR', 'data/');
define('MEMBERS_FILE', DATA_DIR . 'members.json');
define('BOOKS_FILE', DATA_DIR . 'books.json');
define('BORROWERS_FILE', DATA_DIR . 'borrowers.json');
define('RETURNS_FILE', DATA_DIR . 'returns.json');
define('USERS_FILE', DATA_DIR . 'users.json');
define('FINES_FILE', DATA_DIR . 'fines.json');
define('PAYMENTS_FILE', DATA_DIR . 'payments.json');
define('SETTINGS_FILE', DATA_DIR . 'settings.json');

// Buat direktori data jika belum ada
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0777, true);
}

// Buat direktori untuk upload gambar
if (!file_exists('uploads/')) {
    mkdir('uploads/', 0777, true);
}
if (!file_exists('uploads/profiles/')) {
    mkdir('uploads/profiles/', 0777, true);
}
if (!file_exists('uploads/books/')) {
    mkdir('uploads/books/', 0777, true);
}
if (!file_exists('uploads/payments/')) {
    mkdir('uploads/payments/', 0777, true);
}

// Inisialisasi file JSON kosong jika belum ada
$files = [
    MEMBERS_FILE => [],
    BOOKS_FILE => [],
    BORROWERS_FILE => [],
    RETURNS_FILE => [],
    USERS_FILE => [
        [
            'id' => 'admin001',
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'fullname' => 'Administrator',
            'email' => 'admin@perpustakaan.com',
            'role' => 'admin',
            'profile_picture' => '',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ],
    FINES_FILE => [],
    PAYMENTS_FILE => [],
    SETTINGS_FILE => [
        'fine_per_day' => 1000,
        'bank_account' => [
            'bank_name' => 'Bank BCA',
            'account_number' => '1234567890',
            'account_name' => 'Perpustakaan Digital'
        ]
    ]
];

foreach ($files as $file => $defaultData) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($defaultData, JSON_PRETTY_PRINT));
    }
}

// Start session
session_start();
?>
