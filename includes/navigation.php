
<?php
$currentPage = getCurrentPage();
?>
<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <i data-lucide="book-open" class="h-8 w-8 text-blue-600 mr-3"></i>
                    <h1 class="text-xl font-bold text-gray-900">Manajemen Perpustakaan</h1>
                </div>
                <div class="hidden md:ml-6 md:flex md:space-x-8">
                    <a href="?page=dashboard" class="<?= isCurrentPage('dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Dashboard
                    </a>
                    <a href="?page=members" class="<?= isCurrentPage('members') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Anggota
                    </a>
                    <a href="?page=books" class="<?= isCurrentPage('books') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Buku
                    </a>
                    <a href="?page=borrowers" class="<?= isCurrentPage('borrowers') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Peminjaman
                    </a>
                    <a href="?page=returns" class="<?= isCurrentPage('returns') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Pengembalian
                    </a>
                    <a href="?page=fines" class="<?= isCurrentPage('fines') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Denda
                    </a>
                    <a href="?page=payments" class="<?= isCurrentPage('payments') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Pembayaran
                    </a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">Selamat datang, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="logout.php" class="text-red-600 hover:text-red-700 flex items-center">
                    <i data-lucide="log-out" class="h-4 w-4 mr-1"></i>
                    Keluar
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
    lucide.createIcons();
</script>
