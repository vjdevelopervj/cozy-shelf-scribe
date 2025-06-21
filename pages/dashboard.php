
<?php
$members = DataAccess::getMembers();
$books = DataAccess::getBooks();
$borrowers = DataAccess::getBorrowers();
$returns = DataAccess::getReturns();
$users = loadData(USERS_FILE);

$activeBorrows = array_filter($borrowers, function($b) { return $b['status'] === 'Borrowed'; });
$overdueBorrows = array_filter($activeBorrows, function($b) { return strtotime($b['dueDate']) < time(); });
$availableBooks = array_sum(array_column($books, 'stock'));
$totalFines = array_sum(array_column($returns, 'fine'));

$topReaders = getTopActiveReaders(5);
$topBooks = getTopPopularBooks(5);

$stats = [
    ['title' => 'Total Anggota', 'value' => count($users), 'icon' => 'users', 'color' => 'text-blue-600', 'bgColor' => 'bg-blue-50'],
    ['title' => 'Buku Tersedia', 'value' => $availableBooks, 'icon' => 'book', 'color' => 'text-green-600', 'bgColor' => 'bg-green-50'],
    ['title' => 'Peminjaman Aktif', 'value' => count($activeBorrows), 'icon' => 'book-open', 'color' => 'text-purple-600', 'bgColor' => 'bg-purple-50'],
    ['title' => 'Total Pengembalian', 'value' => count($returns), 'icon' => 'rotate-ccw', 'color' => 'text-indigo-600', 'bgColor' => 'bg-indigo-50'],
    ['title' => 'Buku Terlambat', 'value' => count($overdueBorrows), 'icon' => 'alert-circle', 'color' => 'text-red-600', 'bgColor' => 'bg-red-50'],
    ['title' => 'Total Denda', 'value' => 'Rp ' . number_format($totalFines, 0, ',', '.'), 'icon' => 'banknote', 'color' => 'text-yellow-600', 'bgColor' => 'bg-yellow-50']
];
?>

<div class="p-6">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Perpustakaan</h2>
        <p class="text-gray-600">Kelola operasi perpustakaan dengan efisien</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Side - Cards, Quick Actions, Charts -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($stats as $stat): ?>
                    <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="<?= $stat['bgColor'] ?> p-3 rounded-lg">
                                <i data-lucide="<?= $stat['icon'] ?>" class="h-6 w-6 <?= $stat['color'] ?>"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600"><?= $stat['title'] ?></p>
                                <p class="text-2xl font-bold text-gray-900"><?= $stat['value'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Tindakan Cepat</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="?page=members" class="flex items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <i data-lucide="users" class="h-5 w-5 mr-2 text-blue-600"></i>
                        Tambah Anggota
                    </a>
                    <a href="?page=books" class="flex items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <i data-lucide="book" class="h-5 w-5 mr-2 text-green-600"></i>
                        Tambah Buku
                    </a>
                    <a href="?page=borrowers" class="flex items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <i data-lucide="book-open" class="h-5 w-5 mr-2 text-purple-600"></i>
                        Peminjaman Baru
                    </a>
                    <a href="?page=returns" class="flex items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <i data-lucide="rotate-ccw" class="h-5 w-5 mr-2 text-indigo-600"></i>
                        Proses Pengembalian
                    </a>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Active Readers Chart -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Top 5 Pembaca Teraktif</h3>
                    <div class="relative h-64">
                        <canvas id="activeReadersChart"></canvas>
                    </div>
                </div>

                <!-- Top Popular Books Chart -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Top 5 Buku Terpopuler</h3>
                    <div class="relative h-64">
                        <canvas id="popularBooksChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Rankings -->
        <div class="space-y-6">
            <!-- Top Readers Table -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Ranking Pembaca Teraktif</h3>
                <div class="space-y-4">
                    <?php foreach ($topReaders as $index => $reader): ?>
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                                    #<?= $index + 1 ?>
                                </span>
                            </div>
                            <div class="flex-shrink-0">
                                <?php if ($reader['user']['profile_picture']): ?>
                                    <img src="uploads/profiles/<?= $reader['user']['profile_picture'] ?>" 
                                         alt="Profile" class="w-10 h-10 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                        <i data-lucide="user" class="h-5 w-5 text-gray-600"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?= htmlspecialchars($reader['user']['fullname']) ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?= $reader['borrow_count'] ?> peminjaman
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Books Table -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Ranking Buku Terpopuler</h3>
                <div class="space-y-4">
                    <?php foreach ($topBooks as $index => $book): ?>
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <span class="bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                                    #<?= $index + 1 ?>
                                </span>
                            </div>
                            <div class="flex-shrink-0">
                                <?php if ($book['book']['cover']): ?>
                                    <img src="uploads/books/<?= $book['book']['cover'] ?>" 
                                         alt="Cover" class="w-10 h-14 rounded object-cover">
                                <?php else: ?>
                                    <div class="w-10 h-14 bg-gray-300 rounded flex items-center justify-center">
                                        <i data-lucide="book" class="h-5 w-5 text-gray-600"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?= htmlspecialchars($book['book']['title']) ?>
                                </p>
                                <p class="text-sm text-gray-500 truncate">
                                    <?= htmlspecialchars($book['book']['author']) ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?= $book['borrow_count'] ?> kali dipinjam
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Alert -->
    <?php if (count($overdueBorrows) > 0): ?>
        <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 mr-2"></i>
                <h3 class="text-lg font-semibold text-red-800">Peringatan Buku Terlambat</h3>
            </div>
            <p class="text-red-700 mt-1">
                Ada <?= count($overdueBorrows) ?> buku yang terlambat dikembalikan dan memerlukan perhatian.
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
    lucide.createIcons();

    // Polar Area Chart for Top Active Readers
    const readersCtx = document.getElementById('activeReadersChart').getContext('2d');
    const readersData = <?= json_encode(array_map(function($r) { 
        return ['name' => $r['user']['fullname'], 'count' => $r['borrow_count']]; 
    }, $topReaders)) ?>;
    
    new Chart(readersCtx, {
        type: 'polarArea',
        data: {
            labels: readersData.map(r => r.name),
            datasets: [{
                data: readersData.map(r => r.count),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(139, 92, 246)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Radar Chart for Top Popular Books
    const booksCtx = document.getElementById('popularBooksChart').getContext('2d');
    const booksData = <?= json_encode(array_map(function($b) { 
        return ['title' => $b['book']['title'], 'count' => $b['borrow_count']]; 
    }, $topBooks)) ?>;
    
    new Chart(booksCtx, {
        type: 'radar',
        data: {
            labels: booksData.map(b => b.title),
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: booksData.map(b => b.count),
                backgroundColor: 'rgba(16, 185, 129, 0.2)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 2,
                pointBackgroundColor: 'rgb(16, 185, 129)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(16, 185, 129)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                r: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
