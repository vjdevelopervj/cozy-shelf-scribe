
<?php
require_once 'config.php';
require_once 'includes/functions.php';

class DataAccess {
    public static function getMembers() {
        return loadData(MEMBERS_FILE);
    }
    
    public static function getBooks() {
        return loadData(BOOKS_FILE);
    }
    
    public static function getBorrowers() {
        return loadData(BORROWERS_FILE);
    }
    
    public static function getReturns() {
        return loadData(RETURNS_FILE);
    }
    
    public static function saveMembers($members) {
        return saveData(MEMBERS_FILE, $members);
    }
    
    public static function saveBooks($books) {
        return saveData(BOOKS_FILE, $books);
    }
    
    public static function saveBorrowers($borrowers) {
        return saveData(BORROWERS_FILE, $borrowers);
    }
    
    public static function saveReturns($returns) {
        return saveData(RETURNS_FILE, $returns);
    }
}
?>
