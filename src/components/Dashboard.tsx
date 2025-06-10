import React from 'react';
import { Book, Users, BookOpen, RotateCcw, AlertCircle, CheckCircle } from 'lucide-react';

interface DashboardProps {
  members: any[];
  books: any[];
  borrowers: any[];
  returns: any[];
  onNavigate?: (view: string) => void;
}

const Dashboard: React.FC<DashboardProps> = ({ members, books, borrowers, returns, onNavigate }) => {
  const activeBorrows = borrowers.filter(b => b.status === 'Borrowed');
  const overdueBorrows = activeBorrows.filter(b => new Date(b.dueDate) < new Date());
  const availableBooks = books.reduce((sum, book) => sum + book.stock, 0);
  const totalFines = returns.reduce((sum, ret) => sum + (ret.fine || 0), 0);

  const stats = [
    {
      title: 'Total Members',
      value: members.length,
      icon: Users,
      color: 'text-blue-600',
      bgColor: 'bg-blue-50'
    },
    {
      title: 'Available Books',
      value: availableBooks,
      icon: Book,
      color: 'text-green-600',
      bgColor: 'bg-green-50'
    },
    {
      title: 'Active Borrows',
      value: activeBorrows.length,
      icon: BookOpen,
      color: 'text-purple-600',
      bgColor: 'bg-purple-50'
    },
    {
      title: 'Total Returns',
      value: returns.length,
      icon: RotateCcw,
      color: 'text-indigo-600',
      bgColor: 'bg-indigo-50'
    },
    {
      title: 'Overdue Books',
      value: overdueBorrows.length,
      icon: AlertCircle,
      color: 'text-red-600',
      bgColor: 'bg-red-50'
    },
    {
      title: 'Total Fines',
      value: `$${totalFines.toFixed(2)}`,
      icon: CheckCircle,
      color: 'text-yellow-600',
      bgColor: 'bg-yellow-50'
    }
  ];

  return (
    <div className="p-6">
      <div className="mb-8">
        <h2 className="text-3xl font-bold text-foreground mb-2">Library Dashboard</h2>
        <p className="text-muted-foreground">Manage your library operations efficiently</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        {stats.map((stat, index) => {
          const Icon = stat.icon;
          return (
            <div key={index} className="bg-card rounded-lg border border-border p-6 shadow-sm hover:shadow-md transition-shadow">
              <div className="flex items-center">
                <div className={`${stat.bgColor} p-3 rounded-lg`}>
                  <Icon className={`h-6 w-6 ${stat.color}`} />
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-muted-foreground">{stat.title}</p>
                  <p className="text-2xl font-bold text-foreground">{stat.value}</p>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {overdueBorrows.length > 0 && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <div className="flex items-center">
            <AlertCircle className="h-5 w-5 text-red-600 mr-2" />
            <h3 className="text-lg font-semibold text-red-800">Overdue Books Alert</h3>
          </div>
          <p className="text-red-700 mt-1">
            There are {overdueBorrows.length} overdue book(s) that need attention.
          </p>
        </div>
      )}

      <div className="bg-card rounded-lg border border-border p-6">
        <h3 className="text-xl font-semibold text-foreground mb-4">Quick Actions</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <button 
            onClick={() => onNavigate?.('members')}
            className="flex items-center justify-center p-4 border border-border rounded-lg hover:bg-accent transition-colors"
          >
            <Users className="h-5 w-5 mr-2 text-blue-600" />
            Add Member
          </button>
          <button 
            onClick={() => onNavigate?.('books')}
            className="flex items-center justify-center p-4 border border-border rounded-lg hover:bg-accent transition-colors"
          >
            <Book className="h-5 w-5 mr-2 text-green-600" />
            Add Book
          </button>
          <button 
            onClick={() => onNavigate?.('borrowers')}
            className="flex items-center justify-center p-4 border border-border rounded-lg hover:bg-accent transition-colors"
          >
            <BookOpen className="h-5 w-5 mr-2 text-purple-600" />
            New Borrow
          </button>
          <button 
            onClick={() => onNavigate?.('returns')}
            className="flex items-center justify-center p-4 border border-border rounded-lg hover:bg-accent transition-colors"
          >
            <RotateCcw className="h-5 w-5 mr-2 text-indigo-600" />
            Process Return
          </button>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
