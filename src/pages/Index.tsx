
import React, { useState } from 'react';
import Navigation from '../components/Navigation';
import ViewRenderer from '../components/ViewRenderer';
import { useLibraryData } from '../hooks/useLibraryData';

const Index = () => {
  const [currentView, setCurrentView] = useState('dashboard');
  const {
    members,
    books,
    borrowers,
    returns,
    handleAddMember,
    handleEditMember,
    handleDeleteMember,
    handleAddBook,
    handleEditBook,
    handleDeleteBook,
    handleAddBorrow,
    handleDeleteBorrow,
    handleAddReturn,
    handleDeleteReturn,
  } = useLibraryData();

  return (
    <div className="min-h-screen bg-background">
      <Navigation currentView={currentView} onViewChange={setCurrentView} />
      <ViewRenderer
        currentView={currentView}
        members={members}
        books={books}
        borrowers={borrowers}
        returns={returns}
        onNavigate={setCurrentView}
        onAddMember={handleAddMember}
        onEditMember={handleEditMember}
        onDeleteMember={handleDeleteMember}
        onAddBook={handleAddBook}
        onEditBook={handleEditBook}
        onDeleteBook={handleDeleteBook}
        onAddBorrow={handleAddBorrow}
        onDeleteBorrow={handleDeleteBorrow}
        onAddReturn={handleAddReturn}
        onDeleteReturn={handleDeleteReturn}
      />
    </div>
  );
};

export default Index;
