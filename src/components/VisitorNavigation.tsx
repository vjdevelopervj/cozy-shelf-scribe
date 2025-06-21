
import React, { useState } from 'react';
import { BookOpen, LogOut, User, Menu, X } from 'lucide-react';

interface VisitorNavigationProps {
  currentView: string;
  onViewChange: (view: string) => void;
  user: any;
  onLogout: () => void;
}

const VisitorNavigation: React.FC<VisitorNavigationProps> = ({ 
  currentView, 
  onViewChange, 
  user, 
  onLogout 
}) => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const menuItems = [
    { id: 'dashboard', title: 'Dashboard' },
    { id: 'books', title: 'Katalog Buku' },
    { id: 'my-borrows', title: 'Peminjaman Saya' },
    { id: 'profile', title: 'Profil' }
  ];

  const handleMenuClick = (viewId: string) => {
    onViewChange(viewId);
    setIsMobileMenuOpen(false);
  };

  return (
    <nav className="bg-white shadow-sm border-b border-gray-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-16">
          {/* Logo and Brand */}
          <div className="flex items-center">
            <div className="flex-shrink-0 flex items-center">
              <BookOpen className="h-8 w-8 text-green-600 mr-3" />
              <h1 className="text-xl font-bold text-gray-900 hidden sm:block">Perpustakaan Digital</h1>
              <h1 className="text-lg font-bold text-gray-900 sm:hidden">Perpustakaan</h1>
            </div>
          </div>

          {/* Desktop Menu */}
          <div className="hidden md:flex md:items-center md:space-x-8">
            {menuItems.map((item) => (
              <button
                key={item.id}
                onClick={() => onViewChange(item.id)}
                className={`${
                  currentView === item.id
                    ? 'border-green-500 text-gray-900'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                } whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors`}
              >
                {item.title}
              </button>
            ))}
          </div>

          {/* User Menu */}
          <div className="flex items-center space-x-4">
            <div className="hidden sm:flex items-center space-x-2">
              {user.profilePicture ? (
                <img
                  src={user.profilePicture}
                  alt="Profile"
                  className="w-8 h-8 rounded-full object-cover"
                />
              ) : (
                <User className="h-8 w-8 text-gray-600" />
              )}
              <span className="text-gray-700 hidden lg:inline">Halo, {user.fullname}</span>
              <span className="text-gray-700 lg:hidden">{user.fullname.split(' ')[0]}</span>
            </div>
            
            <button
              onClick={onLogout}
              className="text-red-600 hover:text-red-700 flex items-center"
            >
              <LogOut className="h-4 w-4 mr-1" />
              <span className="hidden sm:inline">Keluar</span>
            </button>

            {/* Mobile menu button */}
            <button
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100"
            >
              {isMobileMenuOpen ? (
                <X className="h-6 w-6" />
              ) : (
                <Menu className="h-6 w-6" />
              )}
            </button>
          </div>
        </div>

        {/* Mobile Menu */}
        {isMobileMenuOpen && (
          <div className="md:hidden">
            <div className="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-gray-50 border-t border-gray-200">
              {/* User info on mobile */}
              <div className="flex items-center space-x-3 px-3 py-2 border-b border-gray-200 mb-2">
                {user.profilePicture ? (
                  <img
                    src={user.profilePicture}
                    alt="Profile"
                    className="w-10 h-10 rounded-full object-cover"
                  />
                ) : (
                  <User className="h-10 w-10 text-gray-600" />
                )}
                <span className="text-gray-900 font-medium">{user.fullname}</span>
              </div>
              
              {menuItems.map((item) => (
                <button
                  key={item.id}
                  onClick={() => handleMenuClick(item.id)}
                  className={`${
                    currentView === item.id
                      ? 'bg-green-100 border-green-500 text-green-700'
                      : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                  } block w-full text-left px-3 py-2 rounded-md text-base font-medium border-l-4 border-transparent transition-colors`}
                >
                  {item.title}
                </button>
              ))}
            </div>
          </div>
        )}
      </div>
    </nav>
  );
};

export default VisitorNavigation;
