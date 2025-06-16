
<?php
$currentPage = getCurrentPage();
?>
<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">Library Management</h1>
                </div>
                <div class="hidden md:ml-6 md:flex md:space-x-8">
                    <a href="?page=dashboard" class="<?= isCurrentPage('dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Dashboard
                    </a>
                    <a href="?page=members" class="<?= isCurrentPage('members') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Members
                    </a>
                    <a href="?page=books" class="<?= isCurrentPage('books') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Books
                    </a>
                    <a href="?page=borrowers" class="<?= isCurrentPage('borrowers') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Borrowers
                    </a>
                    <a href="?page=returns" class="<?= isCurrentPage('returns') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Returns
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
