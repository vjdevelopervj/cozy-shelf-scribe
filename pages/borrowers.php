
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $borrowers = DataAccess::getBorrowers();
        $books = DataAccess::getBooks();
        $members = DataAccess::getMembers();
        
        switch ($_POST['action']) {
            case 'add':
                $member = array_filter($members, function($m) { return $m['id'] === $_POST['memberId']; });
                $book = array_filter($books, function($b) { return $b['id'] === $_POST['bookId']; });
                
                if ($member && $book) {
                    $member = array_values($member)[0];
                    $book = array_values($book)[0];
                    
                    if ($book['stock'] > 0) {
                        $newBorrow = [
                            'id' => generateId(),
                            'memberId' => $_POST['memberId'],
                            'memberName' => $member['name'],
                            'bookId' => $_POST['bookId'],
                            'bookTitle' => $book['title'],
                            'borrowDate' => $_POST['borrowDate'],
                            'dueDate' => $_POST['dueDate'],
                            'status' => 'Borrowed'
                        ];
                        $borrowers[] = $newBorrow;
                        DataAccess::saveBorrowers($borrowers);
                        
                        // Update book stock
                        $books = array_map(function($b) use ($book) {
                            if ($b['id'] === $book['id']) {
                                $b['stock']--;
                            }
                            return $b;
                        }, $books);
                        DataAccess::saveBooks($books);
                    }
                }
                break;
                
            case 'delete':
                $borrowRecord = array_filter($borrowers, function($b) { return $b['id'] === $_POST['id']; });
                if ($borrowRecord) {
                    $borrowRecord = array_values($borrowRecord)[0];
                    if ($borrowRecord['status'] === 'Borrowed') {
                        // Return book to stock
                        $books = array_map(function($b) use ($borrowRecord) {
                            if ($b['id'] === $borrowRecord['bookId']) {
                                $b['stock']++;
                            }
                            return $b;
                        }, $books);
                        DataAccess::saveBooks($books);
                    }
                }
                
                $borrowers = array_filter($borrowers, function($borrow) {
                    return $borrow['id'] !== $_POST['id'];
                });
                DataAccess::saveBorrowers(array_values($borrowers));
                break;
        }
        redirect('borrowers');
    }
}

$borrowers = DataAccess::getBorrowers();
$members = DataAccess::getMembers();
$books = DataAccess::getBooks();
$availableBooks = array_filter($books, function($book) { return $book['stock'] > 0; });
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Borrowers Management</h2>
        <button onclick="document.getElementById('addBorrowModal').classList.remove('hidden')" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
            Add New Borrow
        </button>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrow Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($borrowers as $borrower): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($borrower['memberName']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($borrower['bookTitle']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($borrower['borrowDate']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($borrower['dueDate']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $borrower['status'] === 'Borrowed' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                <?= $borrower['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this borrow record?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $borrower['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Borrow Modal -->
<div id="addBorrowModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Add New Borrow</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Member</label>
                <select name="memberId" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Select Member</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Book</label>
                <select name="bookId" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Select Book</option>
                    <?php foreach ($availableBooks as $book): ?>
                        <option value="<?= $book['id'] ?>"><?= htmlspecialchars($book['title']) ?> (Stock: <?= $book['stock'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Borrow Date</label>
                <input type="date" name="borrowDate" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Due Date</label>
                <input type="date" name="dueDate" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="document.getElementById('addBorrowModal').classList.add('hidden')" class="mr-3 px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                    Add Borrow
                </button>
            </div>
        </form>
    </div>
</div>
