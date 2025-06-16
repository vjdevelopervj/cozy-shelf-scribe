
<?php
$members = DataAccess::getMembers();
$books = DataAccess::getBooks();
$borrowers = DataAccess::getBorrowers();
$returns = DataAccess::getReturns();

$activeBorrows = array_filter($borrowers, function($b) { return $b['status'] === 'Borrowed'; });
$overdueBorrows = array_filter($activeBorrows, function($b) { return strtotime($b['dueDate']) < time(); });
$availableBooks = array_sum(array_column($books, 'stock'));
$totalFines = array_sum(array_column($returns, 'fine'));

$stats = [
    ['title' => 'Total Members', 'value' => count($members), 'icon' => 'users', 'color' => 'text-blue-600', 'bgColor' => 'bg-blue-50'],
    ['title' => 'Available Books', 'value' => $availableBooks, 'icon' => 'book', 'color' => 'text-green-600', 'bgColor' => 'bg-green-50'],
    ['title' => 'Active Borrows', 'value' => count($activeBorrows), 'icon' => 'book-open', 'color' => 'text-purple-600', 'bgColor' => 'bg-purple-50'],
    ['title' => 'Total Returns', 'value' => count($returns), 'icon' => 'rotate-ccw', 'color' => 'text-indigo-600', 'bgColor' => 'bg-indigo-50'],
    ['title' => 'Overdue Books', 'value' => count($overdueBorrows), 'icon' => 'alert-circle', 'color' => 'text-red-600', 'bgColor' => 'bg-red-50'],
    ['title' => 'Total Fines', 'value' => '$' . number_format($totalFines, 2), 'icon' => 'check-circle', 'color' => 'text-yellow-600', 'bgColor' => 'bg-yellow-50']
];
?>

<div class="p-6">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Library Dashboard</h2>
        <p class="text-gray-600">Manage your library operations efficiently</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
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

    <?php if (count($overdueBorrows) > 0): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 mr-2"></i>
                <h3 class="text-lg font-semibold text-red-800">Overdue Books Alert</h3>
            </div>
            <p class="text-red-700 mt-1">
                There are <?= count($overdueBorrows) ?> overdue book(s) that need attention.
            </p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="?page=members" class="flex items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <i data-lucide="users" class="h-5 w-5 mr-2 text-blue-600"></i>
                Add Member
            </a>
            <a href="?page=books" class="flex items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <i data-lucide="book" class="h-5 w-5 mr-2 text-green-600"></i>
                Add Book
            </a>
            <a href="?page=borrowers" class="flex items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <i data-lucide="book-open" class="h-5 w-5 mr-2 text-purple-600"></i>
                New Borrow
            </a>
            <a href="?page=returns" class="flex items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <i data-lucide="rotate-ccw" class="h-5 w-5 mr-2 text-indigo-600"></i>
                Process Return
            </a>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
