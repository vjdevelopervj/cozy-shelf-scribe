
import React from 'react';
import { Book, Users, BookOpen, RotateCcw, AlertCircle, Banknote } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';
import { Button } from './ui/button';
import DashboardCharts from './DashboardCharts';
import TopReadersRanking from './TopReadersRanking';
import TopBooksRanking from './TopBooksRanking';

interface AdminDashboardProps {
  members: any[];
  books: any[];
  borrowers: any[];
  returns: any[];
  onNavigate?: (view: string) => void;
}

const AdminDashboard: React.FC<AdminDashboardProps> = ({ 
  members, 
  books, 
  borrowers, 
  returns, 
  onNavigate 
}) => {
  const activeBorrows = borrowers.filter(b => b.status === 'Borrowed');
  const overdueBorrows = activeBorrows.filter(b => new Date(b.dueDate) < new Date());
  const availableBooks = books.reduce((sum, book) => sum + book.stock, 0);
  const totalFines = returns.reduce((sum, ret) => sum + (ret.fine || 0), 0);

  const stats = [
    {
      title: 'Total Anggota',
      value: members.length,
      icon: Users,
      color: 'text-blue-600',
      bgColor: 'bg-blue-50'
    },
    {
      title: 'Buku Tersedia',
      value: availableBooks,
      icon: Book,
      color: 'text-green-600',
      bgColor: 'bg-green-50'
    },
    {
      title: 'Peminjaman Aktif',
      value: activeBorrows.length,
      icon: BookOpen,
      color: 'text-purple-600',
      bgColor: 'bg-purple-50'
    },
    {
      title: 'Total Pengembalian',
      value: returns.length,
      icon: RotateCcw,
      color: 'text-indigo-600',
      bgColor: 'bg-indigo-50'
    },
    {
      title: 'Buku Terlambat',
      value: overdueBorrows.length,
      icon: AlertCircle,
      color: 'text-red-600',
      bgColor: 'bg-red-50'
    },
    {
      title: 'Total Denda',
      value: `Rp ${totalFines.toLocaleString('id-ID')}`,
      icon: Banknote,
      color: 'text-yellow-600',
      bgColor: 'bg-yellow-50'
    }
  ];

  return (
    <div className="p-6">
      <div className="mb-8">
        <h2 className="text-3xl font-bold text-foreground mb-2">Dashboard Perpustakaan</h2>
        <p className="text-muted-foreground">Kelola operasi perpustakaan dengan efisien</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {stats.map((stat, index) => {
              const Icon = stat.icon;
              return (
                <Card key={index} className="shadow-sm hover:shadow-md transition-shadow">
                  <CardContent className="p-6">
                    <div className="flex items-center">
                      <div className={`${stat.bgColor} p-3 rounded-lg`}>
                        <Icon className={`h-6 w-6 ${stat.color}`} />
                      </div>
                      <div className="ml-4">
                        <p className="text-sm font-medium text-muted-foreground">{stat.title}</p>
                        <p className="text-2xl font-bold text-foreground">{stat.value}</p>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              );
            })}
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Tindakan Cepat</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Button
                  variant="outline"
                  onClick={() => onNavigate?.('members')}
                  className="flex items-center justify-center p-4 h-auto"
                >
                  <Users className="h-5 w-5 mr-2 text-blue-600" />
                  Tambah Anggota
                </Button>
                <Button
                  variant="outline"
                  onClick={() => onNavigate?.('books')}
                  className="flex items-center justify-center p-4 h-auto"
                >
                  <Book className="h-5 w-5 mr-2 text-green-600" />
                  Tambah Buku
                </Button>
                <Button
                  variant="outline"
                  onClick={() => onNavigate?.('borrowers')}
                  className="flex items-center justify-center p-4 h-auto"
                >
                  <BookOpen className="h-5 w-5 mr-2 text-purple-600" />
                  Peminjaman Baru
                </Button>
                <Button
                  variant="outline"
                  onClick={() => onNavigate?.('returns')}
                  className="flex items-center justify-center p-4 h-auto"
                >
                  <RotateCcw className="h-5 w-5 mr-2 text-indigo-600" />
                  Proses Pengembalian
                </Button>
              </div>
            </CardContent>
          </Card>

          <DashboardCharts borrowers={borrowers} books={books} />
        </div>

        <div className="space-y-6">
          <TopReadersRanking borrowers={borrowers} />
          <TopBooksRanking borrowers={borrowers} books={books} />
        </div>
      </div>

      {overdueBorrows.length > 0 && (
        <Card className="mt-6 bg-red-50 border-red-200">
          <CardContent className="p-4">
            <div className="flex items-center">
              <AlertCircle className="h-5 w-5 text-red-600 mr-2" />
              <div>
                <h3 className="text-lg font-semibold text-red-800">Peringatan Buku Terlambat</h3>
                <p className="text-red-700 mt-1">
                  Ada {overdueBorrows.length} buku yang terlambat dikembalikan dan memerlukan perhatian.
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
};

export default AdminDashboard;
