
import React from 'react';
import Dashboard from './Dashboard';
import Members from './Members';
import Books from './Books';
import Borrowers from './Borrowers';
import Returns from './Returns';

interface ViewRendererProps {
  currentView: string;
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

const ViewRenderer: React.FC<ViewRendererProps> = ({
  currentView,
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
      return <Dashboard members={members} books={books} borrowers={borrowers} returns={returns} onNavigate={onNavigate} />;
    case 'members':
      return (
        <Members
          members={members}
          onAddMember={onAddMember}
          onEditMember={onEditMember}
          onDeleteMember={onDeleteMember}
        />
      );
    case 'books':
      return (
        <Books
          books={books}
          onAddBook={onAddBook}
          onEditBook={onEditBook}
          onDeleteBook={onDeleteBook}
        />
      );
    case 'borrowers':
      return (
        <Borrowers
          borrowers={borrowers}
          members={members}
          books={books}
          onAddBorrow={onAddBorrow}
          onDeleteBorrow={onDeleteBorrow}
        />
      );
    case 'returns':
      return (
        <Returns
          returns={returns}
          borrowers={borrowers}
          onAddReturn={onAddReturn}
          onDeleteReturn={onDeleteReturn}
        />
      );
    default:
      return <Dashboard members={members} books={books} borrowers={borrowers} returns={returns} onNavigate={onNavigate} />;
  }
};

export default ViewRenderer;
