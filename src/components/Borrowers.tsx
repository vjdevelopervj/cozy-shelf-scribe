
import React, { useState } from 'react';
import { Plus } from 'lucide-react';
import Table from './Table';

interface BorrowRecord {
  id: string;
  memberId: string;
  memberName: string;
  bookId: string;
  bookTitle: string;
  borrowDate: string;
  dueDate: string;
  status: 'Borrowed' | 'Returned';
}

interface BorrowersProps {
  borrowers: BorrowRecord[];
  members: any[];
  books: any[];
  onAddBorrow: (borrow: Omit<BorrowRecord, 'id' | 'memberName' | 'bookTitle'>) => void;
  onDeleteBorrow: (id: string) => void;
}

const Borrowers: React.FC<BorrowersProps> = ({ borrowers, members, books, onAddBorrow, onDeleteBorrow }) => {
  const [showForm, setShowForm] = useState(false);
  const [formData, setFormData] = useState({
    memberId: '',
    bookId: '',
    borrowDate: new Date().toISOString().split('T')[0],
    dueDate: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onAddBorrow({
      ...formData,
      status: 'Borrowed' as const,
    });
    setFormData({
      memberId: '',
      bookId: '',
      borrowDate: new Date().toISOString().split('T')[0],
      dueDate: '',
    });
    setShowForm(false);
  };

  const handleDelete = (borrow: BorrowRecord) => {
    if (window.confirm('Are you sure you want to delete this borrow record?')) {
      onDeleteBorrow(borrow.id);
    }
  };

  const availableBooks = books.filter(book => book.stock > 0);

  const columns = [
    { key: 'id', label: 'Borrow ID' },
    { key: 'memberName', label: 'Member Name' },
    { key: 'bookTitle', label: 'Book Title' },
    { key: 'borrowDate', label: 'Borrow Date' },
    { key: 'dueDate', label: 'Due Date' },
    { 
      key: 'status', 
      label: 'Status',
      render: (value: string, row: BorrowRecord) => {
        const isOverdue = row.status === 'Borrowed' && new Date(row.dueDate) < new Date();
        return (
          <span className={`px-2 py-1 rounded-full text-xs ${
            value === 'Returned' 
              ? 'bg-green-100 text-green-800' 
              : isOverdue
              ? 'bg-red-100 text-red-800'
              : 'bg-yellow-100 text-yellow-800'
          }`}>
            {isOverdue && value === 'Borrowed' ? 'Overdue' : value}
          </span>
        )
      }
    },
  ];

  return (
    <div className="p-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-foreground">Borrowers Management</h2>
        <button
          onClick={() => setShowForm(true)}
          className="bg-primary text-primary-foreground px-4 py-2 rounded-lg flex items-center hover:bg-primary/90 transition-colors"
        >
          <Plus className="h-4 w-4 mr-2" />
          New Borrow
        </button>
      </div>

      {showForm && (
        <div className="bg-card border border-border rounded-lg p-6 mb-6">
          <h3 className="text-lg font-semibold mb-4">Add New Borrow Record</h3>
          <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Member</label>
              <select
                value={formData.memberId}
                onChange={(e) => setFormData({ ...formData, memberId: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              >
                <option value="">Select a member</option>
                {members.map((member) => (
                  <option key={member.id} value={member.id}>
                    {member.name}
                  </option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Book</label>
              <select
                value={formData.bookId}
                onChange={(e) => setFormData({ ...formData, bookId: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              >
                <option value="">Select a book</option>
                {availableBooks.map((book) => (
                  <option key={book.id} value={book.id}>
                    {book.title} (Stock: {book.stock})
                  </option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Borrow Date</label>
              <input
                type="date"
                value={formData.borrowDate}
                onChange={(e) => setFormData({ ...formData, borrowDate: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Due Date</label>
              <input
                type="date"
                value={formData.dueDate}
                onChange={(e) => setFormData({ ...formData, dueDate: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              />
            </div>
            <div className="md:col-span-2 flex space-x-2">
              <button
                type="submit"
                className="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 transition-colors"
              >
                Add Borrow Record
              </button>
              <button
                type="button"
                onClick={() => {
                  setShowForm(false);
                  setFormData({
                    memberId: '',
                    bookId: '',
                    borrowDate: new Date().toISOString().split('T')[0],
                    dueDate: '',
                  });
                }}
                className="bg-secondary text-secondary-foreground px-4 py-2 rounded-md hover:bg-secondary/90 transition-colors"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      )}

      <Table
        columns={columns}
        data={borrowers}
        onDelete={handleDelete}
        onEdit={undefined}
      />
    </div>
  );
};

export default Borrowers;
