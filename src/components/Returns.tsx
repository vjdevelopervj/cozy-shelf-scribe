
import React, { useState } from 'react';
import { Plus } from 'lucide-react';
import Table from './Table';

interface ReturnRecord {
  id: string;
  borrowId: string;
  memberName: string;
  bookTitle: string;
  returnDate: string;
  fine: number;
}

interface ReturnsProps {
  returns: ReturnRecord[];
  borrowers: any[];
  onAddReturn: (returnRecord: Omit<ReturnRecord, 'id'>) => void;
  onDeleteReturn: (id: string) => void;
}

const Returns: React.FC<ReturnsProps> = ({ returns, borrowers, onAddReturn, onDeleteReturn }) => {
  const [showForm, setShowForm] = useState(false);
  const [selectedBorrow, setSelectedBorrow] = useState('');
  const [returnDate, setReturnDate] = useState(new Date().toISOString().split('T')[0]);

  const activeBorrows = borrowers.filter(b => b.status === 'Borrowed');

  const calculateFine = (dueDate: string, returnDate: string) => {
    const due = new Date(dueDate);
    const returned = new Date(returnDate);
    const diffTime = returned.getTime() - due.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays * 1 : 0; // $1 per day late
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const borrowRecord = borrowers.find(b => b.id === selectedBorrow);
    if (borrowRecord) {
      const fine = calculateFine(borrowRecord.dueDate, returnDate);
      onAddReturn({
        borrowId: selectedBorrow,
        memberName: borrowRecord.memberName,
        bookTitle: borrowRecord.bookTitle,
        returnDate,
        fine,
      });
      setSelectedBorrow('');
      setReturnDate(new Date().toISOString().split('T')[0]);
      setShowForm(false);
    }
  };

  const handleDelete = (returnRecord: ReturnRecord) => {
    if (window.confirm('Are you sure you want to delete this return record?')) {
      onDeleteReturn(returnRecord.id);
    }
  };

  const columns = [
    { key: 'id', label: 'Return ID' },
    { key: 'borrowId', label: 'Borrow ID' },
    { key: 'memberName', label: 'Member Name' },
    { key: 'bookTitle', label: 'Book Title' },
    { key: 'returnDate', label: 'Return Date' },
    { 
      key: 'fine', 
      label: 'Fine',
      render: (value: number) => (
        <span className={`px-2 py-1 rounded-full text-xs ${
          value > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
        }`}>
          ${value.toFixed(2)}
        </span>
      )
    },
  ];

  return (
    <div className="p-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-foreground">Returns Management</h2>
        <button
          onClick={() => setShowForm(true)}
          className="bg-primary text-primary-foreground px-4 py-2 rounded-lg flex items-center hover:bg-primary/90 transition-colors"
          disabled={activeBorrows.length === 0}
        >
          <Plus className="h-4 w-4 mr-2" />
          Process Return
        </button>
      </div>

      {activeBorrows.length === 0 && !showForm && (
        <div className="bg-muted border border-border rounded-lg p-4 mb-6">
          <p className="text-muted-foreground">No active borrows available for return.</p>
        </div>
      )}

      {showForm && (
        <div className="bg-card border border-border rounded-lg p-6 mb-6">
          <h3 className="text-lg font-semibold mb-4">Process Book Return</h3>
          <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Select Borrow Record</label>
              <select
                value={selectedBorrow}
                onChange={(e) => setSelectedBorrow(e.target.value)}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              >
                <option value="">Select a borrow record</option>
                {activeBorrows.map((borrow) => (
                  <option key={borrow.id} value={borrow.id}>
                    {borrow.memberName} - {borrow.bookTitle} (Due: {borrow.dueDate})
                  </option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Return Date</label>
              <input
                type="date"
                value={returnDate}
                onChange={(e) => setReturnDate(e.target.value)}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              />
            </div>
            {selectedBorrow && (
              <div className="md:col-span-2">
                <div className="bg-muted p-3 rounded-md">
                  <p className="text-sm text-muted-foreground">
                    {(() => {
                      const borrowRecord = borrowers.find(b => b.id === selectedBorrow);
                      if (borrowRecord) {
                        const fine = calculateFine(borrowRecord.dueDate, returnDate);
                        return fine > 0 
                          ? `Late fee: $${fine.toFixed(2)} (${Math.ceil((new Date(returnDate).getTime() - new Date(borrowRecord.dueDate).getTime()) / (1000 * 60 * 60 * 24))} days late)`
                          : 'No late fee';
                      }
                      return '';
                    })()}
                  </p>
                </div>
              </div>
            )}
            <div className="md:col-span-2 flex space-x-2">
              <button
                type="submit"
                className="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 transition-colors"
              >
                Process Return
              </button>
              <button
                type="button"
                onClick={() => {
                  setShowForm(false);
                  setSelectedBorrow('');
                  setReturnDate(new Date().toISOString().split('T')[0]);
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
        data={returns}
        onDelete={handleDelete}
        onEdit={undefined}
      />
    </div>
  );
};

export default Returns;
