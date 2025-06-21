
import React from 'react';
import VisitorDashboard from './VisitorDashboard';
import BookCatalog from './BookCatalog';
import MyBorrows from './MyBorrows';
import VisitorProfile from './VisitorProfile';

interface VisitorViewRendererProps {
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

const VisitorViewRenderer: React.FC<VisitorViewRendererProps> = ({
  currentView,
  user,
  members,
  books,
  borrowers,
  returns,
  onNavigate,
  onAddBorrow,
  onAddReturn,
}) => {
  switch (currentView) {
    case 'dashboard':
      return (
        <VisitorDashboard 
          user={user}
          borrowers={borrowers} 
          books={books} 
          returns={returns} 
        />
      );
    case 'books':
      return (
        <BookCatalog
          books={books}
          borrowers={borrowers}
          user={user}
          onAddBorrow={onAddBorrow}
        />
      );
    case 'my-borrows':
      return (
        <MyBorrows
          user={user}
          borrowers={borrowers}
          returns={returns}
          onAddReturn={onAddReturn}
        />
      );
    case 'profile':
      return (
        <VisitorProfile
          user={user}
        />
      );
    default:
      return (
        <VisitorDashboard 
          user={user}
          borrowers={borrowers} 
          books={books} 
          returns={returns} 
        />
      );
  }
};

export default VisitorViewRenderer;
