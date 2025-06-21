
import React from 'react';
import AdminDashboard from './AdminDashboard';
import MembersAdmin from './MembersAdmin';
import BooksAdmin from './BooksAdmin';
import BorrowersAdmin from './BorrowersAdmin';
import ReturnsAdmin from './ReturnsAdmin';
import Fines from './Fines';
import Payments from './Payments';

interface AdminViewRendererProps {
  currentView: string;
  user: any;
  members: any[];
  books: any[];
  borrowers: any[];
  returns: any[];
  onNavigate: (view: string) => void;
  onAddMember: (data: any) => void;
  onEditMember: (id: string, data: any) => void;
  onDeleteMember: (id: string) => void;
  onAddBook: (data: any) => void;
  onEditBook: (id: string, data: any) => void;
  onDeleteBook: (id: string) => void;
  onAddBorrow: (data: any) => void;
  onDeleteBorrow: (id: string) => void;
  onAddReturn: (data: any) => void;
  onDeleteReturn: (id: string) => void;
}

const AdminViewRenderer: React.FC<AdminViewRendererProps> = ({
  currentView,
  user,
  members,
  books,
  borrowers,
  returns,
  onNavigate,
  onAddMember,
  onEditMember,
  onDeleteMember,
  onAddBook,
  onEditBook,
  onDeleteBook,
  onAddBorrow,
  onDeleteBorrow,
  onAddReturn,
  onDeleteReturn,
}) => {
  switch (currentView) {
    case 'dashboard':
      return (
        <AdminDashboard 
          members={members} 
          books={books} 
          borrowers={borrowers} 
          returns={returns} 
          onNavigate={onNavigate} 
        />
      );
    case 'members':
      return (
        <MembersAdmin
          members={members}
          onAddMember={onAddMember}
          onEditMember={onEditMember}
          onDeleteMember={onDeleteMember}
        />
      );
    case 'books':
      return (
        <BooksAdmin
          books={books}
          onAddBook={onAddBook}
          onEditBook={onEditBook}
          onDeleteBook={onDeleteBook}
        />
      );
    case 'borrowers':
      return (
        <BorrowersAdmin
          borrowers={borrowers}
          members={members}
          books={books}
          onAddBorrow={onAddBorrow}
          onDeleteBorrow={onDeleteBorrow}
        />
      );
    case 'returns':
      return (
        <ReturnsAdmin
          returns={returns}
          borrowers={borrowers}
          onAddReturn={onAddReturn}
          onDeleteReturn={onDeleteReturn}
        />
      );
    case 'fines':
      return (
        <Fines
          borrowers={borrowers}
          returns={returns}
          books={books}
        />
      );
    case 'payments':
      return (
        <Payments
          returns={returns}
          borrowers={borrowers}
        />
      );
    default:
      return (
        <AdminDashboard 
          members={members} 
          books={books} 
          borrowers={borrowers} 
          returns={returns} 
          onNavigate={onNavigate} 
        />
      );
  }
};

export default AdminViewRenderer;
