
import React, { useState } from 'react';
import { useAuth } from '../hooks/useAuth';
import Login from '../components/Login';
import Register from '../components/Register';
import AdminNavigation from '../components/AdminNavigation';
import VisitorNavigation from '../components/VisitorNavigation';
import AdminViewRenderer from '../components/AdminViewRenderer';
import VisitorViewRenderer from '../components/VisitorViewRenderer';
import { useLibraryData } from '../hooks/useLibraryData';

const Index = () => {
  const { user, login, logout, isAuthenticated } = useAuth();
  const [showRegister, setShowRegister] = useState(false);
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

  if (!isAuthenticated) {
    if (showRegister) {
      return <Register onSwitchToLogin={() => setShowRegister(false)} />;
    }
    return (
      <Login 
        onLogin={login} 
        onSwitchToRegister={() => setShowRegister(true)} 
      />
    );
  }

  const NavigationComponent = user?.role === 'admin' ? AdminNavigation : VisitorNavigation;
  const ViewRendererComponent = user?.role === 'admin' ? AdminViewRenderer : VisitorViewRenderer;

  return (
    <div className="min-h-screen bg-background">
      <NavigationComponent 
        currentView={currentView} 
        onViewChange={setCurrentView}
        user={user}
        onLogout={logout}
      />
      <ViewRendererComponent
        currentView={currentView}
        user={user}
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
