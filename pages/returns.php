
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $returns = DataAccess::getReturns();
        $borrowers = DataAccess::getBorrowers();
        $books = DataAccess::getBooks();
        
        switch ($_POST['action']) {
            case 'add':
                $borrowRecord = array_filter($borrowers, function($b) { return $b['id'] === $_POST['borrowId']; });
                if ($borrowRecord) {
                    $borrowRecord = array_values($borrowRecord)[0];
                    
                    $newReturn = [
                        'id' => generateId(),
                        'borrowId' => $_POST['borrowId'],
                        'memberName' => $borrowRecord['memberName'],
                        'bookTitle' => $borrowRecord['bookTitle'],
                        'returnDate' => $_POST['returnDate'],
                        'fine' => (float)$_POST['fine']
                    ];
                    $returns[] = $newReturn;
                    DataAccess::saveReturns($returns);
                    
                    // Update borrower status
                    $borrowers = array_map(function($b) use ($borrowRecord) {
                        if ($b['id'] === $borrowRecord['id']) {
                            $b['status'] = 'Returned';
                        }
                        return $b;
                    }, $borrowers);
                    DataAccess::saveBorrowers($borrowers);
                    
                    // Update book stock
                    $books = array_map(function($b) use ($borrowRecord) {
                        if ($b['id'] === $borrowRecord['bookId']) {
                            $b['stock']++;
                        }
                        return $b;
                    }, $books);
                    DataAccess::saveBooks($books);
                }
                break;
                
            case 'delete':
                $returnRecord = array_filter($returns, function($r) { return $r['id'] === $_POST['id']; });
                if ($returnRecord) {
                    $returnRecord = array_values($returnRecord)[0];
                    
                    // Update borrower status back to borrowed
                    $borrowers = array_map(function($b) use ($returnRecord) {
                        if ($b['id'] === $returnRecord['borrowId']) {
                            $b['status'] = 'Borrowed';
                        }
                        return $b;
                    }, $borrowers);
                    DataAccess::saveBorrowers($borrowers);
                    
                    // Reduce book stock
                    $borrowRecord = array_filter($borrowers, function($b) { return $b['id'] === $returnRecord['borrowId']; });
                    if ($borrowRecord) {
                        $borrowRecord = array_values($borrowRecord)[0];
                        $books = array_map(function($b) use ($borrowRecord) {
                            if ($b['id'] === $borrowRecord['bookId']) {
                                $b['stock'] = max(0, $b['stock'] - 1);
                            }
                            return $b;
                        }, $books);
                        DataAccess::saveBooks($books);
                    }
                }
                
                $returns = array_filter($returns, function($returnRec) {
                    return $returnRec['id'] !== $_POST['id'];
                });
                DataAccess::saveReturns(array_values($returns));
                break;
        }
        redirect('returns');
    }
}

$returns = DataAccess::getReturns();
$borrowers = DataAccess::getBorrowers();
$activeBorrows = array_filter($borrowers, function($b) { return $b['status'] === 'Borrowed'; });
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Returns Management</h2>
        <button onclick="document.getElementById('addReturnModal').classList.remove('hidden')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            Process Return
        </button>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fine</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($returns as $return): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($return['memberName']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($return['bookTitle']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($return['returnDate']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$<?= number_format($return['fine'], 2) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this return record?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $return['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Return Modal -->
<div id="addReturnModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Process Return</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Active Borrow</label>
                <select name="borrowId" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Borrow Record</option>
                    <?php foreach ($activeBorrows as $borrow): ?>
                        <option value="<?= $borrow['id'] ?>">
                            <?= htmlspecialchars($borrow['memberName']) ?> - <?= htmlspecialchars($borrow['bookTitle']) ?>
                            (Due: <?= $borrow['dueDate'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Return Date</label>
                <input type="date" name="returnDate" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Fine ($)</label>
                <input type="number" name="fine" min="0" step="0.01" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="document.getElementById('addReturnModal').classList.add('hidden')" class="mr-3 px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Process Return
                </button>
            </div>
        </form>
    </div>
</div>
