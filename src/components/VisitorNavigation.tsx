
import React from 'react';
import { BookOpen, LogOut, User } from 'lucide-react';

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
  const menuItems = [
    { id: 'dashboard', title: 'Dashboard' },
    { id: 'books', title: 'Katalog Buku' },
    { id: 'my-borrows', title: 'Peminjaman Saya' },
    { id: 'profile', title: 'Profil' }
  ];

  return (
    <nav className="bg-white shadow-sm border-b border-gray-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-16">
          <div className="flex">
            <div className="flex-shrink-0 flex items-center">
              <BookOpen className="h-8 w-8 text-green-600 mr-3" />
              <h1 className="text-xl font-bold text-gray-900">Perpustakaan Digital</h1>
            </div>
            <div className="hidden md:ml-6 md:flex md:space-x-8">
              {menuItems.map((item) => (
                <button
                  key={item.id}
                  onClick={() => onViewChange(item.id)}
                  className={`${
                    currentView === item.id
                      ? 'border-green-500 text-gray-900'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  } whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm`}
                >
                  {item.title}
                </button>
              ))}
            </div>
          </div>
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2">
              {user.profilePicture ? (
                <img
                  src={user.profilePicture}
                  alt="Profile"
                  className="w-8 h-8 rounded-full object-cover"
                />
              ) : (
                <User className="h-8 w-8 text-gray-600" />
              )}
              <span className="text-gray-700">Halo, {user.fullname}</span>
            </div>
            <button
              onClick={onLogout}
              className="text-red-600 hover:text-red-700 flex items-center"
            >
              <LogOut className="h-4 w-4 mr-1" />
              Keluar
            </button>
          </div>
        </div>
      </div>
    </nav>
  );
};

export default VisitorNavigation;
