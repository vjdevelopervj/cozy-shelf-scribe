
import { useState, useEffect, createContext, useContext } from 'react';

interface User {
  id: string;
  username: string;
  fullname: string;
  email: string;
  role: 'admin' | 'visitor';
  profilePicture?: string;
}

interface AuthContextType {
  user: User | null;
  login: (user: User) => void;
  logout: () => void;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(() => {
    const savedUser = localStorage.getItem('current_user');
    return savedUser ? JSON.parse(savedUser) : null;
  });

  const login = (userData: User) => {
    setUser(userData);
    localStorage.setItem('current_user', JSON.stringify(userData));
  };

  const logout = () => {
    setUser(null);
    localStorage.removeItem('current_user');
  };

  // Initialize with admin user if no users exist
  useEffect(() => {
    const users = JSON.parse(localStorage.getItem('library_users') || '[]');
    if (users.length === 0) {
      const adminUser = {
        id: 'admin-001',
        username: 'admin',
        password: 'admin123',
        fullname: 'Administrator',
        email: 'admin@perpus.com',
        role: 'admin',
        profilePicture: '',
        createdAt: new Date().toISOString()
      };
      localStorage.setItem('library_users', JSON.stringify([adminUser]));
    }
  }, []);

  return (
    <AuthContext.Provider value={{ 
      user, 
      login, 
      logout, 
      isAuthenticated: !!user 
    }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
