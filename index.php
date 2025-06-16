
<?php
session_start();

// Data file paths
$dataDir = 'data/';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

$membersFile = $dataDir . 'members.json';
$booksFile = $dataDir . 'books.json';
$borrowersFile = $dataDir . 'borrowers.json';
$returnsFile = $dataDir . 'returns.json';

// Helper functions
function loadData($file) {
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

function saveData($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function generateId() {
    return uniqid();
}

// Load data
$members = loadData($membersFile);
$books = loadData($booksFile);
$borrowers = loadData($borrowersFile);
$returns = loadData($returnsFile);

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_member':
            $newMember = [
                'id' => generateId(),
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address'],
                'registrationDate' => date('Y-m-d')
            ];
            $members[] = $newMember;
            saveData($membersFile, $members);
            $_SESSION['message'] = 'Member added successfully!';
            break;
            
        case 'edit_member':
            $id = $_POST['id'];
            foreach ($members as &$member) {
                if ($member['id'] === $id) {
                    $member['name'] = $_POST['name'];
                    $member['email'] = $_POST['email'];
                    $member['phone'] = $_POST['phone'];
                    $member['address'] = $_POST['address'];
                    break;
                }
            }
            saveData($membersFile, $members);
            $_SESSION['message'] = 'Member updated successfully!';
            break;
            
        case 'delete_member':
            $id = $_POST['id'];
            $members = array_filter($members, function($member) use ($id) {
                return $member['id'] !== $id;
            });
            saveData($membersFile, $members);
            $_SESSION['message'] = 'Member deleted successfully!';
            break;
            
        case 'add_book':
            $newBook = [
                'id' => generateId(),
                'title' => $_POST['title'],
                'author' => $_POST['author'],
                'publisher' => $_POST['publisher'],
                'year' => $_POST['year'],
                'isbn' => $_POST['isbn'],
                'stock' => (int)$_POST['stock']
            ];
            $books[] = $newBook;
            saveData($booksFile, $books);
            $_SESSION['message'] = 'Book added successfully!';
            break;
            
        case 'edit_book':
            $id = $_POST['id'];
            foreach ($books as &$book) {
                if ($book['id'] === $id) {
                    $book['title'] = $_POST['title'];
                    $book['author'] = $_POST['author'];
                    $book['publisher'] = $_POST['publisher'];
                    $book['year'] = $_POST['year'];
                    $book['isbn'] = $_POST['isbn'];
                    $book['stock'] = (int)$_POST['stock'];
                    break;
                }
            }
            saveData($booksFile, $books);
            $_SESSION['message'] = 'Book updated successfully!';
            break;
            
        case 'delete_book':
            $id = $_POST['id'];
            $books = array_filter($books, function($book) use ($id) {
                return $book['id'] !== $id;
            });
            saveData($booksFile, $books);
            $_SESSION['message'] = 'Book deleted successfully!';
            break;
            
        case 'add_borrow':
            $memberId = $_POST['memberId'];
            $bookId = $_POST['bookId'];
            
            $member = array_filter($members, function($m) use ($memberId) {
                return $m['id'] === $memberId;
            });
            $member = reset($member);
            
            $book = null;
            foreach ($books as &$b) {
                if ($b['id'] === $bookId && $b['stock'] > 0) {
                    $book = &$b;
                    break;
                }
            }
            
            if ($member && $book) {
                $newBorrow = [
                    'id' => generateId(),
                    'memberId' => $memberId,
                    'bookId' => $bookId,
                    'memberName' => $member['name'],
                    'bookTitle' => $book['title'],
                    'borrowDate' => $_POST['borrowDate'],
                    'dueDate' => $_POST['dueDate'],
                    'status' => 'Borrowed'
                ];
                $borrowers[] = $newBorrow;
                $book['stock']--;
                saveData($borrowersFile, $borrowers);
                saveData($booksFile, $books);
                $_SESSION['message'] = 'Book borrowed successfully!';
            }
            break;
            
        case 'delete_borrow':
            $id = $_POST['id'];
            $borrowRecord = null;
            foreach ($borrowers as $b) {
                if ($b['id'] === $id) {
                    $borrowRecord = $b;
                    break;
                }
            }
            
            if ($borrowRecord && $borrowRecord['status'] === 'Borrowed') {
                foreach ($books as &$book) {
                    if ($book['title'] === $borrowRecord['bookTitle']) {
                        $book['stock']++;
                        break;
                    }
                }
                saveData($booksFile, $books);
            }
            
            $borrowers = array_filter($borrowers, function($borrow) use ($id) {
                return $borrow['id'] !== $id;
            });
            saveData($borrowersFile, $borrowers);
            $_SESSION['message'] = 'Borrow record deleted successfully!';
            break;
            
        case 'add_return':
            $borrowId = $_POST['borrowId'];
            $fine = (float)$_POST['fine'];
            
            $borrowRecord = null;
            foreach ($borrowers as &$borrow) {
                if ($borrow['id'] === $borrowId) {
                    $borrowRecord = &$borrow;
                    break;
                }
            }
            
            if ($borrowRecord) {
                $newReturn = [
                    'id' => generateId(),
                    'borrowId' => $borrowId,
                    'memberName' => $borrowRecord['memberName'],
                    'bookTitle' => $borrowRecord['bookTitle'],
                    'returnDate' => $_POST['returnDate'],
                    'fine' => $fine
                ];
                $returns[] = $newReturn;
                $borrowRecord['status'] = 'Returned';
                
                foreach ($books as &$book) {
                    if ($book['title'] === $borrowRecord['bookTitle']) {
                        $book['stock']++;
                        break;
                    }
                }
                
                saveData($returnsFile, $returns);
                saveData($borrowersFile, $borrowers);
                saveData($booksFile, $books);
                $_SESSION['message'] = 'Return processed successfully!';
            }
            break;
            
        case 'delete_return':
            $id = $_POST['id'];
            $returnRecord = null;
            foreach ($returns as $r) {
                if ($r['id'] === $id) {
                    $returnRecord = $r;
                    break;
                }
            }
            
            if ($returnRecord) {
                foreach ($borrowers as &$borrow) {
                    if ($borrow['id'] === $returnRecord['borrowId']) {
                        $borrow['status'] = 'Borrowed';
                        break;
                    }
                }
                
                foreach ($books as &$book) {
                    if ($book['title'] === $returnRecord['bookTitle']) {
                        $book['stock'] = max(0, $book['stock'] - 1);
                        break;
                    }
                }
                
                saveData($borrowersFile, $borrowers);
                saveData($booksFile, $books);
            }
            
            $returns = array_filter($returns, function($return) use ($id) {
                return $return['id'] !== $id;
            });
            saveData($returnsFile, $returns);
            $_SESSION['message'] = 'Return record deleted successfully!';
            break;
    }
    
    header('Location: ' . $_SERVER['PHP_SELF'] . '?view=' . ($_GET['view'] ?? 'dashboard'));
    exit;
}

// Get current view
$view = $_GET['view'] ?? 'dashboard';
$editItem = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    switch ($view) {
        case 'members':
            $editItem = array_filter($members, function($m) use ($editId) {
                return $m['id'] === $editId;
            });
            $editItem = reset($editItem);
            break;
        case 'books':
            $editItem = array_filter($books, function($b) use ($editId) {
                return $b['id'] === $editId;
            });
            $editItem = reset($editItem);
            break;
    }
}

// Calculate stats for dashboard
$activeBorrows = array_filter($borrowers, function($b) {
    return $b['status'] === 'Borrowed';
});
$overdueBorrows = array_filter($activeBorrows, function($b) {
    return strtotime($b['dueDate']) < time();
});
$availableBooks = array_sum(array_column($books, 'stock'));
$totalFines = array_sum(array_column($returns, 'fine'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        border: "hsl(214.3 31.8% 91.4%)",
                        input: "hsl(214.3 31.8% 91.4%)",
                        ring: "hsl(222.2 84% 4.9%)",
                        background: "hsl(0 0% 100%)",
                        foreground: "hsl(222.2 84% 4.9%)",
                        primary: {
                            DEFAULT: "hsl(222.2 47.4% 11.2%)",
                            foreground: "hsl(210 40% 98%)"
                        },
                        secondary: {
                            DEFAULT: "hsl(210 40% 96%)",
                            foreground: "hsl(222.2 84% 4.9%)"
                        },
                        destructive: {
                            DEFAULT: "hsl(0 84.2% 60.2%)",
                            foreground: "hsl(210 40% 98%)"
                        },
                        muted: {
                            DEFAULT: "hsl(210 40% 96%)",
                            foreground: "hsl(215.4 16.3% 46.9%)"
                        },
                        accent: {
                            DEFAULT: "hsl(210 40% 96%)",
                            foreground: "hsl(222.2 84% 4.9%)"
                        },
                        popover: {
                            DEFAULT: "hsl(0 0% 100%)",
                            foreground: "hsl(222.2 84% 4.9%)"
                        },
                        card: {
                            DEFAULT: "hsl(0 0% 100%)",
                            foreground: "hsl(222.2 84% 4.9%)"
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .modal {
            display: none;
        }
        .modal.show {
            display: flex;
        }
    </style>
</head>
<body class="bg-background text-foreground">
    <!-- Navigation -->
    <nav class="bg-card border-b border-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <svg class="h-8 w-8 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h1 class="text-xl font-bold text-foreground">Library Management</h1>
                </div>
                <div class="flex space-x-4">
                    <a href="?view=dashboard" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= $view === 'dashboard' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 12 2-2m0 0 7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>
                    <a href="?view=members" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= $view === 'members' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Members
                    </a>
                    <a href="?view=books" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= $view === 'books' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Books
                    </a>
                    <a href="?view=borrowers" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= $view === 'borrowers' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Borrowers
                    </a>
                    <a href="?view=returns" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors <?= $view === 'returns' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Returns
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-green-800"><?= $_SESSION['message'] ?></p>
                </div>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if ($view === 'dashboard'): ?>
            <!-- Dashboard View -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-foreground mb-2">Library Dashboard</h2>
                <p class="text-muted-foreground">Manage your library operations efficiently</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-card rounded-lg border border-border p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">Total Members</p>
                            <p class="text-2xl font-bold text-foreground"><?= count($members) ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-card rounded-lg border border-border p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-green-50 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">Available Books</p>
                            <p class="text-2xl font-bold text-foreground"><?= $availableBooks ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-card rounded-lg border border-border p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-purple-50 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">Active Borrows</p>
                            <p class="text-2xl font-bold text-foreground"><?= count($activeBorrows) ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-card rounded-lg border border-border p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-indigo-50 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">Total Returns</p>
                            <p class="text-2xl font-bold text-foreground"><?= count($returns) ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-card rounded-lg border border-border p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-red-50 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.232 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">Overdue Books</p>
                            <p class="text-2xl font-bold text-foreground"><?= count($overdueBorrows) ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-card rounded-lg border border-border p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="bg-yellow-50 p-3 rounded-lg">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">Total Fines</p>
                            <p class="text-2xl font-bold text-foreground">$<?= number_format($totalFines, 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (count($overdueBorrows) > 0): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.232 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-red-800">Overdue Books Alert</h3>
                    </div>
                    <p class="text-red-700 mt-1">
                        There are <?= count($overdueBorrows) ?> overdue book(s) that need attention.
                    </p>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="bg-card rounded-lg border border-border p-6">
                <h3 class="text-xl font-semibold text-foreground mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="?view=members" class="flex items-center justify-center p-4 border border-border rounded-lg hover:bg-accent transition-colors">
                        <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Add Member
                    </a>
                    <a href="?view=books" class="flex items-center justify-center p-4 border border-border rounded-lg hover:bg-accent transition-colors">
                        <svg class="h-5 w-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Add Book
                    </a>
                    <a href="?view=borrowers" class="flex items-center justify-center p-4 border border-border rounded-lg hover:bg-accent transition-colors">
                        <svg class="h-5 w-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        New Borrow
                    </a>
                    <a href="?view=returns" class="flex items-center justify-center p-4 border border-border rounded-lg hover:bg-accent transition-colors">
                        <svg class="h-5 w-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Process Return
                    </a>
                </div>
            </div>

        <?php elseif ($view === 'members'): ?>
            <!-- Members View -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-foreground">Members Management</h2>
                <button onclick="showModal('memberModal')" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg flex items-center hover:bg-primary/90 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Member
                </button>
            </div>

            <div class="bg-card border border-border rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-muted">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Registration Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <?php foreach ($members as $member): ?>
                                <tr class="hover:bg-accent transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($member['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($member['name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($member['email']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($member['phone']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($member['address']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($member['registrationDate']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                        <div class="flex space-x-2">
                                            <a href="?view=members&edit=<?= $member['id'] ?>" class="text-blue-600 hover:text-blue-800 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_member">
                                                <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-800 transition-colors" onclick="return confirm('Are you sure?')">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($members)): ?>
                        <div class="text-center py-8 text-muted-foreground">
                            No data available
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Member Modal -->
            <div id="memberModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
                <div class="bg-card rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-semibold mb-4"><?= $editItem ? 'Edit Member' : 'Add New Member' ?></h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="<?= $editItem ? 'edit_member' : 'add_member' ?>">
                        <?php if ($editItem): ?>
                            <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Name</label>
                                <input type="text" name="name" value="<?= $editItem['name'] ?? '' ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Email</label>
                                <input type="email" name="email" value="<?= $editItem['email'] ?? '' ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Phone</label>
                                <input type="tel" name="phone" value="<?= $editItem['phone'] ?? '' ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Address</label>
                                <input type="text" name="address" value="<?= $editItem['address'] ?? '' ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                        </div>
                        <div class="flex space-x-2 mt-6">
                            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 transition-colors">
                                <?= $editItem ? 'Update' : 'Add' ?> Member
                            </button>
                            <button type="button" onclick="hideModal('memberModal')" class="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-secondary/90 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($view === 'books'): ?>
            <!-- Books View -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-foreground">Books Management</h2>
                <button onclick="showModal('bookModal')" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg flex items-center hover:bg-primary/90 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Book
                </button>
            </div>

            <div class="bg-card border border-border rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-muted">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Author</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Publisher</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">ISBN</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <?php foreach ($books as $book): ?>
                                <tr class="hover:bg-accent transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($book['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($book['title']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($book['author']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($book['publisher']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($book['year']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($book['isbn']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                        <span class="px-2 py-1 rounded-full text-xs <?= $book['stock'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $book['stock'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                        <div class="flex space-x-2">
                                            <a href="?view=books&edit=<?= $book['id'] ?>" class="text-blue-600 hover:text-blue-800 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_book">
                                                <input type="hidden" name="id" value="<?= $book['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-800 transition-colors" onclick="return confirm('Are you sure?')">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($books)): ?>
                        <div class="text-center py-8 text-muted-foreground">
                            No data available
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Book Modal -->
            <div id="bookModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
                <div class="bg-card rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-semibold mb-4"><?= $editItem ? 'Edit Book' : 'Add New Book' ?></h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="<?= $editItem ? 'edit_book' : 'add_book' ?>">
                        <?php if ($editItem): ?>
                            <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Title</label>
                                <input type="text" name="title" value="<?= $editItem['title'] ?? '' ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Author</label>
                                <input type="text" name="author" value="<?= $editItem['author'] ?? '' ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Publisher</label>
                                <input type="text" name="publisher" value="<?= $editItem['publisher'] ?? '' ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Year</label>
                                <input type="text" name="year" value="<?= $editItem['year'] ?? '' ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">ISBN</label>
                                <input type="text" name="isbn" value="<?= $editItem['isbn'] ?? '' ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Stock</label>
                                <input type="number" name="stock" value="<?= $editItem['stock'] ?? '' ?>" min="0" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                        </div>
                        <div class="flex space-x-2 mt-6">
                            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 transition-colors">
                                <?= $editItem ? 'Update' : 'Add' ?> Book
                            </button>
                            <button type="button" onclick="hideModal('bookModal')" class="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-secondary/90 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($view === 'borrowers'): ?>
            <!-- Borrowers View -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-foreground">Borrowers Management</h2>
                <button onclick="showModal('borrowModal')" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg flex items-center hover:bg-primary/90 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New Borrow
                </button>
            </div>

            <div class="bg-card border border-border rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-muted">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Borrow ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Member Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Book Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Borrow Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <?php foreach ($borrowers as $borrower): ?>
                                <?php 
                                $isOverdue = $borrower['status'] === 'Borrowed' && strtotime($borrower['dueDate']) < time();
                                ?>
                                <tr class="hover:bg-accent transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($borrower['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($borrower['memberName']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($borrower['bookTitle']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($borrower['borrowDate']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($borrower['dueDate']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                        <span class="px-2 py-1 rounded-full text-xs <?= 
                                            $borrower['status'] === 'Returned' ? 'bg-green-100 text-green-800' : 
                                            ($isOverdue ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') 
                                        ?>">
                                            <?= $isOverdue && $borrower['status'] === 'Borrowed' ? 'Overdue' : $borrower['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_borrow">
                                            <input type="hidden" name="id" value="<?= $borrower['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800 transition-colors" onclick="return confirm('Are you sure?')">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($borrowers)): ?>
                        <div class="text-center py-8 text-muted-foreground">
                            No data available
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Borrow Modal -->
            <div id="borrowModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
                <div class="bg-card rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-semibold mb-4">Add New Borrow Record</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_borrow">
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Member</label>
                                <select name="memberId" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                                    <option value="">Select a member</option>
                                    <?php foreach ($members as $member): ?>
                                        <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Book</label>
                                <select name="bookId" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                                    <option value="">Select a book</option>
                                    <?php foreach ($books as $book): ?>
                                        <?php if ($book['stock'] > 0): ?>
                                            <option value="<?= $book['id'] ?>"><?= htmlspecialchars($book['title']) ?> (Stock: <?= $book['stock'] ?>)</option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Borrow Date</label>
                                <input type="date" name="borrowDate" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Due Date</label>
                                <input type="date" name="dueDate" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                        </div>
                        <div class="flex space-x-2 mt-6">
                            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 transition-colors">
                                Add Borrow Record
                            </button>
                            <button type="button" onclick="hideModal('borrowModal')" class="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-secondary/90 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($view === 'returns'): ?>
            <!-- Returns View -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-foreground">Returns Management</h2>
                <button onclick="showModal('returnModal')" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg flex items-center hover:bg-primary/90 transition-colors" <?= empty($activeBorrows) ? 'disabled' : '' ?>>
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Process Return
                </button>
            </div>

            <?php if (empty($activeBorrows)): ?>
                <div class="bg-muted border border-border rounded-lg p-4 mb-6">
                    <p class="text-muted-foreground">No active borrows available for return.</p>
                </div>
            <?php endif; ?>

            <div class="bg-card border border-border rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-muted">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Return ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Borrow ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Member Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Book Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Return Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Fine</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <?php foreach ($returns as $return): ?>
                                <tr class="hover:bg-accent transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($return['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($return['borrowId']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($return['memberName']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($return['bookTitle']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><?= htmlspecialchars($return['returnDate']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                        <span class="px-2 py-1 rounded-full text-xs <?= $return['fine'] > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                            $<?= number_format($return['fine'], 2) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_return">
                                            <input type="hidden" name="id" value="<?= $return['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800 transition-colors" onclick="return confirm('Are you sure?')">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($returns)): ?>
                        <div class="text-center py-8 text-muted-foreground">
                            No data available
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Return Modal -->
            <div id="returnModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
                <div class="bg-card rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-semibold mb-4">Process Book Return</h3>
                    <form method="POST" id="returnForm">
                        <input type="hidden" name="action" value="add_return">
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Select Borrow Record</label>
                                <select name="borrowId" id="borrowSelect" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required onchange="calculateFine()">
                                    <option value="">Select a borrow record</option>
                                    <?php foreach ($activeBorrows as $borrow): ?>
                                        <option value="<?= $borrow['id'] ?>" data-due-date="<?= $borrow['dueDate'] ?>">
                                            <?= htmlspecialchars($borrow['memberName']) ?> - <?= htmlspecialchars($borrow['bookTitle']) ?>
                                            (Due: <?= $borrow['dueDate'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Return Date</label>
                                <input type="date" name="returnDate" id="returnDate" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required onchange="calculateFine()">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Fine ($)</label>
                                <input type="number" name="fine" id="fineAmount" value="0" min="0" step="0.01" class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground" required>
                            </div>
                            <div id="fineInfo" class="bg-muted p-3 rounded-md hidden">
                                <p id="fineText" class="text-sm text-muted-foreground"></p>
                            </div>
                        </div>
                        <div class="flex space-x-2 mt-6">
                            <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 transition-colors">
                                Process Return
                            </button>
                            <button type="button" onclick="hideModal('returnModal')" class="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-secondary/90 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <script>
        function showModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function hideModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Auto-show modal if editing
        <?php if ($editItem): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const modalId = '<?= $view === 'members' ? 'memberModal' : 'bookModal' ?>';
                showModal(modalId);
            });
        <?php endif; ?>

        function calculateFine() {
            const borrowSelect = document.getElementById('borrowSelect');
            const returnDate = document.getElementById('returnDate');
            const fineAmount = document.getElementById('fineAmount');
            const fineInfo = document.getElementById('fineInfo');
            const fineText = document.getElementById('fineText');

            if (borrowSelect.value && returnDate.value) {
                const selectedOption = borrowSelect.options[borrowSelect.selectedIndex];
                const dueDate = selectedOption.getAttribute('data-due-date');
                
                if (dueDate) {
                    const due = new Date(dueDate);
                    const returned = new Date(returnDate.value);
                    const diffTime = returned.getTime() - due.getTime();
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    const fine = diffDays > 0 ? diffDays * 1 : 0;
                    
                    fineAmount.value = fine.toFixed(2);
                    
                    if (fine > 0) {
                        fineText.textContent = `Late fee: $${fine.toFixed(2)} (${diffDays} days late)`;
                    } else {
                        fineText.textContent = 'No late fee';
                    }
                    
                    fineInfo.classList.remove('hidden');
                } else {
                    fineInfo.classList.add('hidden');
                }
            } else {
                fineInfo.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
