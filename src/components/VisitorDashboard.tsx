
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Book, BookOpen, Clock, CheckCircle } from 'lucide-react';
import DashboardCharts from './DashboardCharts';
import TopReadersRanking from './TopReadersRanking';
import TopBooksRanking from './TopBooksRanking';

interface VisitorDashboardProps {
  user: any;
  borrowers: any[];
  books: any[];
  returns: any[];
}

const VisitorDashboard: React.FC<VisitorDashboardProps> = ({ 
  user, 
  borrowers, 
  books, 
  returns 
}) => {
  const userBorrows = borrowers.filter(b => b.memberId === user.id);
  const activeBorrows = userBorrows.filter(b => b.status === 'Borrowed');
  const userReturns = returns.filter(r => {
    const borrowRecord = borrowers.find(b => b.id === r.borrowId);
    return borrowRecord?.memberId === user.id;
  });

  const stats = [
    {
      title: 'Total Peminjaman',
      value: userBorrows.length,
      icon: BookOpen,
      color: 'text-blue-600',
      bgColor: 'bg-blue-50'
    },
    {
      title: 'Sedang Dipinjam',
      value: activeBorrows.length,
      icon: Clock,
      color: 'text-yellow-600',
      bgColor: 'bg-yellow-50'
    },
    {
      title: 'Sudah Dikembalikan',
      value: userReturns.length,
      icon: CheckCircle,
      color: 'text-green-600',
      bgColor: 'bg-green-50'
    },
    {
      title: 'Total Buku Tersedia',
      value: books.reduce((sum, book) => sum + book.stock, 0),
      icon: Book,
      color: 'text-purple-600',
      bgColor: 'bg-purple-50'
    }
  ];

  return (
    <div className="p-4 sm:p-6">
      <div className="mb-8">
        <h2 className="text-2xl sm:text-3xl font-bold text-foreground mb-2">
          Selamat Datang, {user.fullname}!
        </h2>
        <p className="text-muted-foreground">Dashboard pengunjung perpustakaan</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        {stats.map((stat, index) => {
          const Icon = stat.icon;
          return (
            <Card key={index} className="shadow-sm hover:shadow-md transition-shadow">
              <CardContent className="p-4 sm:p-6">
                <div className="flex items-center">
                  <div className={`${stat.bgColor} p-3 rounded-lg`}>
                    <Icon className={`h-6 w-6 ${stat.color}`} />
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-muted-foreground">{stat.title}</p>
                    <p className="text-xl sm:text-2xl font-bold text-foreground">{stat.value}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>

      {/* Charts Section */}
      <div className="mb-8">
        <h3 className="text-xl font-semibold text-foreground mb-4">Statistik Perpustakaan</h3>
        <DashboardCharts borrowers={borrowers} books={books} />
      </div>

      {/* Rankings Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <TopReadersRanking borrowers={borrowers} />
        <TopBooksRanking borrowers={borrowers} books={books} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Buku yang Sedang Dipinjam</CardTitle>
          </CardHeader>
          <CardContent>
            {activeBorrows.length === 0 ? (
              <p className="text-muted-foreground">Tidak ada buku yang sedang dipinjam</p>
            ) : (
              <div className="space-y-4">
                {activeBorrows.map((borrow) => (
                  <div key={borrow.id} className="flex items-center space-x-4 p-4 border rounded-lg">
                    <div className="flex-shrink-0">
                      {borrow.bookCover ? (
                        <img
                          src={borrow.bookCover}
                          alt="Cover"
                          className="w-12 h-16 rounded object-cover"
                        />
                      ) : (
                        <div className="w-12 h-16 bg-gray-300 rounded flex items-center justify-center">
                          <Book className="h-6 w-6 text-gray-600" />
                        </div>
                      )}
                    </div>
                    <div className="flex-1">
                      <h4 className="font-medium">{borrow.bookTitle}</h4>
                      <p className="text-sm text-muted-foreground">
                        Tenggat: {new Date(borrow.dueDate).toLocaleDateString('id-ID')}
                      </p>
                      {new Date(borrow.dueDate) < new Date() && (
                        <p className="text-sm text-red-600 font-medium">Terlambat!</p>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Riwayat Pengembalian</CardTitle>
          </CardHeader>
          <CardContent>
            {userReturns.length === 0 ? (
              <p className="text-muted-foreground">Belum ada riwayat pengembalian</p>
            ) : (
              <div className="space-y-4">
                {userReturns.slice(0, 5).map((returnRecord) => {
                  const borrowRecord = borrowers.find(b => b.id === returnRecord.borrowId);
                  return (
                    <div key={returnRecord.id} className="flex items-center space-x-4 p-4 border rounded-lg">
                      <div className="flex-1">
                        <h4 className="font-medium">{borrowRecord?.bookTitle}</h4>
                        <p className="text-sm text-muted-foreground">
                          Dikembalikan: {new Date(returnRecord.returnDate).toLocaleDateString('id-ID')}
                        </p>
                        {returnRecord.fine > 0 && (
                          <p className="text-sm text-red-600">
                            Denda: Rp {returnRecord.fine.toLocaleString('id-ID')}
                          </p>
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default VisitorDashboard;
