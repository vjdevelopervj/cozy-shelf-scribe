
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
                'year' => (int)$_POST['year'],
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
                    $book['year'] = (int)$_POST['year'];
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .nav-link.active {
            background-color: #0d6efd !important;
            color: white !important;
        }
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .table-actions {
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="?view=dashboard">
                <i class="fas fa-book"></i> Library Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= $view === 'dashboard' ? 'active' : '' ?>" href="?view=dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view === 'members' ? 'active' : '' ?>" href="?view=members">
                            <i class="fas fa-users"></i> Members
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view === 'books' ? 'active' : '' ?>" href="?view=books">
                            <i class="fas fa-book"></i> Books
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view === 'borrowers' ? 'active' : '' ?>" href="?view=borrowers">
                            <i class="fas fa-book-open"></i> Borrowers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view === 'returns' ? 'active' : '' ?>" href="?view=returns">
                            <i class="fas fa-undo"></i> Returns
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if ($view === 'dashboard'): ?>
            <!-- Dashboard View -->
            <div class="row mb-4">
                <div class="col">
                    <h2>Library Dashboard</h2>
                    <p class="text-muted">Manage your library operations efficiently</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4 col-lg-2 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <h4><?= count($members) ?></h4>
                            <small class="text-muted">Total Members</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-book fa-2x text-success mb-2"></i>
                            <h4><?= $availableBooks ?></h4>
                            <small class="text-muted">Available Books</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-book-open fa-2x text-info mb-2"></i>
                            <h4><?= count($activeBorrows) ?></h4>
                            <small class="text-muted">Active Borrows</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-undo fa-2x text-secondary mb-2"></i>
                            <h4><?= count($returns) ?></h4>
                            <small class="text-muted">Total Returns</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                            <h4><?= count($overdueBorrows) ?></h4>
                            <small class="text-muted">Overdue Books</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2 mb-3">
                    <div class="card stat-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-dollar-sign fa-2x text-warning mb-2"></i>
                            <h4>$<?= number_format($totalFines, 2) ?></h4>
                            <small class="text-muted">Total Fines</small>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (count($overdueBorrows) > 0): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Overdue Books Alert</strong><br>
                    There are <?= count($overdueBorrows) ?> overdue book(s) that need attention.
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="?view=members" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-plus"></i> Add Member
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="?view=books" class="btn btn-outline-success w-100">
                                <i class="fas fa-book-plus"></i> Add Book
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="?view=borrowers" class="btn btn-outline-info w-100">
                                <i class="fas fa-book-open"></i> New Borrow
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="?view=returns" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-undo"></i> Process Return
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($view === 'members'): ?>
            <!-- Members View -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Members Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#memberModal">
                    <i class="fas fa-plus"></i> Add Member
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($member['name']) ?></td>
                                        <td><?= htmlspecialchars($member['email']) ?></td>
                                        <td><?= htmlspecialchars($member['phone']) ?></td>
                                        <td><?= htmlspecialchars($member['address']) ?></td>
                                        <td><?= htmlspecialchars($member['registrationDate']) ?></td>
                                        <td class="table-actions">
                                            <a href="?view=members&edit=<?= $member['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_member">
                                                <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Member Modal -->
            <div class="modal fade" id="memberModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= $editItem ? 'Edit Member' : 'Add Member' ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="<?= $editItem ? 'edit_member' : 'add_member' ?>">
                                <?php if ($editItem): ?>
                                    <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= $editItem['name'] ?? '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= $editItem['email'] ?? '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="<?= $editItem['phone'] ?? '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" required><?= $editItem['address'] ?? '' ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary"><?= $editItem ? 'Update' : 'Add' ?> Member</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php elseif ($view === 'books'): ?>
            <!-- Books View -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Books Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookModal">
                    <i class="fas fa-plus"></i> Add Book
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Publisher</th>
                                    <th>Year</th>
                                    <th>ISBN</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($book['title']) ?></td>
                                        <td><?= htmlspecialchars($book['author']) ?></td>
                                        <td><?= htmlspecialchars($book['publisher']) ?></td>
                                        <td><?= htmlspecialchars($book['year']) ?></td>
                                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                                        <td>
                                            <span class="badge <?= $book['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $book['stock'] ?>
                                            </span>
                                        </td>
                                        <td class="table-actions">
                                            <a href="?view=books&edit=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_book">
                                                <input type="hidden" name="id" value="<?= $book['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Book Modal -->
            <div class="modal fade" id="bookModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= $editItem ? 'Edit Book' : 'Add Book' ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="<?= $editItem ? 'edit_book' : 'add_book' ?>">
                                <?php if ($editItem): ?>
                                    <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" value="<?= $editItem['title'] ?? '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Author</label>
                                    <input type="text" name="author" class="form-control" value="<?= $editItem['author'] ?? '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Publisher</label>
                                    <input type="text" name="publisher" class="form-control" value="<?= $editItem['publisher'] ?? '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Year</label>
                                    <input type="number" name="year" class="form-control" value="<?= $editItem['year'] ?? '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ISBN</label>
                                    <input type="text" name="isbn" class="form-control" value="<?= $editItem['isbn'] ?? '' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Stock</label>
                                    <input type="number" name="stock" class="form-control" value="<?= $editItem['stock'] ?? '' ?>" min="0" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary"><?= $editItem ? 'Update' : 'Add' ?> Book</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php elseif ($view === 'borrowers'): ?>
            <!-- Borrowers View -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Borrowers Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#borrowModal">
                    <i class="fas fa-plus"></i> Add Borrow
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Member Name</th>
                                    <th>Book Title</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowers as $borrower): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($borrower['memberName']) ?></td>
                                        <td><?= htmlspecialchars($borrower['bookTitle']) ?></td>
                                        <td><?= htmlspecialchars($borrower['borrowDate']) ?></td>
                                        <td><?= htmlspecialchars($borrower['dueDate']) ?></td>
                                        <td>
                                            <span class="badge <?= $borrower['status'] === 'Borrowed' ? 'bg-warning' : 'bg-success' ?>">
                                                <?= $borrower['status'] ?>
                                            </span>
                                        </td>
                                        <td class="table-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_borrow">
                                                <input type="hidden" name="id" value="<?= $borrower['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Borrow Modal -->
            <div class="modal fade" id="borrowModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Borrow</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add_borrow">
                                
                                <div class="mb-3">
                                    <label class="form-label">Member</label>
                                    <select name="memberId" class="form-select" required>
                                        <option value="">Select Member</option>
                                        <?php foreach ($members as $member): ?>
                                            <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Book</label>
                                    <select name="bookId" class="form-select" required>
                                        <option value="">Select Book</option>
                                        <?php foreach ($books as $book): ?>
                                            <?php if ($book['stock'] > 0): ?>
                                                <option value="<?= $book['id'] ?>"><?= htmlspecialchars($book['title']) ?> (Stock: <?= $book['stock'] ?>)</option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Borrow Date</label>
                                    <input type="date" name="borrowDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Due Date</label>
                                    <input type="date" name="dueDate" class="form-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add Borrow</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php elseif ($view === 'returns'): ?>
            <!-- Returns View -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Returns Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#returnModal">
                    <i class="fas fa-plus"></i> Process Return
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Member Name</th>
                                    <th>Book Title</th>
                                    <th>Return Date</th>
                                    <th>Fine</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($returns as $return): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($return['memberName']) ?></td>
                                        <td><?= htmlspecialchars($return['bookTitle']) ?></td>
                                        <td><?= htmlspecialchars($return['returnDate']) ?></td>
                                        <td>$<?= number_format($return['fine'], 2) ?></td>
                                        <td class="table-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_return">
                                                <input type="hidden" name="id" value="<?= $return['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Return Modal -->
            <div class="modal fade" id="returnModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Process Return</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add_return">
                                
                                <div class="mb-3">
                                    <label class="form-label">Borrow Record</label>
                                    <select name="borrowId" class="form-select" required>
                                        <option value="">Select Borrow Record</option>
                                        <?php foreach ($activeBorrows as $borrow): ?>
                                            <option value="<?= $borrow['id'] ?>">
                                                <?= htmlspecialchars($borrow['memberName']) ?> - <?= htmlspecialchars($borrow['bookTitle']) ?>
                                                (Due: <?= $borrow['dueDate'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Return Date</label>
                                    <input type="date" name="returnDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Fine ($)</label>
                                    <input type="number" name="fine" class="form-control" value="0" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Process Return</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-show modal if editing
        <?php if ($editItem): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.querySelector('#<?= $view === 'members' ? 'memberModal' : 'bookModal' ?>');
                if (modal) {
                    new bootstrap.Modal(modal).show();
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>
