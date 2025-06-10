
import React, { useState } from 'react';
import { Plus } from 'lucide-react';
import Table from './Table';

interface Book {
  id: string;
  title: string;
  author: string;
  publisher: string;
  year: string;
  isbn: string;
  stock: number;
}

interface BooksProps {
  books: Book[];
  onAddBook: (book: Omit<Book, 'id'>) => void;
  onEditBook: (id: string, book: Omit<Book, 'id'>) => void;
  onDeleteBook: (id: string) => void;
}

const Books: React.FC<BooksProps> = ({ books, onAddBook, onEditBook, onDeleteBook }) => {
  const [showForm, setShowForm] = useState(false);
  const [editingBook, setEditingBook] = useState<Book | null>(null);
  const [formData, setFormData] = useState({
    title: '',
    author: '',
    publisher: '',
    year: '',
    isbn: '',
    stock: 0,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingBook) {
      onEditBook(editingBook.id, formData);
      setEditingBook(null);
    } else {
      onAddBook(formData);
    }
    setFormData({ title: '', author: '', publisher: '', year: '', isbn: '', stock: 0 });
    setShowForm(false);
  };

  const handleEdit = (book: Book) => {
    setEditingBook(book);
    setFormData({
      title: book.title,
      author: book.author,
      publisher: book.publisher,
      year: book.year,
      isbn: book.isbn,
      stock: book.stock,
    });
    setShowForm(true);
  };

  const handleDelete = (book: Book) => {
    if (window.confirm('Are you sure you want to delete this book?')) {
      onDeleteBook(book.id);
    }
  };

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'title', label: 'Title' },
    { key: 'author', label: 'Author' },
    { key: 'publisher', label: 'Publisher' },
    { key: 'year', label: 'Year' },
    { key: 'isbn', label: 'ISBN' },
    { 
      key: 'stock', 
      label: 'Stock',
      render: (value: number) => (
        <span className={`px-2 py-1 rounded-full text-xs ${
          value > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
        }`}>
          {value}
        </span>
      )
    },
  ];

  return (
    <div className="p-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-foreground">Books Management</h2>
        <button
          onClick={() => setShowForm(true)}
          className="bg-primary text-primary-foreground px-4 py-2 rounded-lg flex items-center hover:bg-primary/90 transition-colors"
        >
          <Plus className="h-4 w-4 mr-2" />
          Add Book
        </button>
      </div>

      {showForm && (
        <div className="bg-card border border-border rounded-lg p-6 mb-6">
          <h3 className="text-lg font-semibold mb-4">
            {editingBook ? 'Edit Book' : 'Add New Book'}
          </h3>
          <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Title</label>
              <input
                type="text"
                value={formData.title}
                onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Author</label>
              <input
                type="text"
                value={formData.author}
                onChange={(e) => setFormData({ ...formData, author: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Publisher</label>
              <input
                type="text"
                value={formData.publisher}
                onChange={(e) => setFormData({ ...formData, publisher: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Year</label>
              <input
                type="text"
                value={formData.year}
                onChange={(e) => setFormData({ ...formData, year: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">ISBN</label>
              <input
                type="text"
                value={formData.isbn}
                onChange={(e) => setFormData({ ...formData, isbn: e.target.value })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-foreground mb-1">Stock</label>
              <input
                type="number"
                value={formData.stock}
                onChange={(e) => setFormData({ ...formData, stock: parseInt(e.target.value) || 0 })}
                className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                min="0"
                required
              />
            </div>
            <div className="md:col-span-2 flex space-x-2">
              <button
                type="submit"
                className="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 transition-colors"
              >
                {editingBook ? 'Update' : 'Add'} Book
              </button>
              <button
                type="button"
                onClick={() => {
                  setShowForm(false);
                  setEditingBook(null);
                  setFormData({ title: '', author: '', publisher: '', year: '', isbn: '', stock: 0 });
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
        data={books}
        onEdit={handleEdit}
        onDelete={handleDelete}
      />
    </div>
  );
};

export default Books;
