
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { PolarAreaChart, PolarArea, RadarChart, PolarGrid, PolarAngleAxis, PolarRadiusAxis, Radar, ResponsiveContainer, Cell } from 'recharts';

interface DashboardChartsProps {
  borrowers: any[];
  books: any[];
}

const DashboardCharts: React.FC<DashboardChartsProps> = ({ borrowers, books }) => {
  const getTopActiveReaders = () => {
    const users = JSON.parse(localStorage.getItem('library_users') || '[]');
    const readerStats: { [key: string]: number } = {};
    
    borrowers.forEach(borrow => {
      if (!readerStats[borrow.memberId]) {
        readerStats[borrow.memberId] = 0;
      }
      readerStats[borrow.memberId]++;
    });

    const topReaders = Object.entries(readerStats)
      .sort(([,a], [,b]) => b - a)
      .slice(0, 5)
      .map(([userId, count]) => {
        const user = users.find((u: any) => u.id === userId);
        return {
          name: user?.fullname || 'Unknown',
          value: count
        };
      });

    return topReaders;
  };

  const getTopPopularBooks = () => {
    const bookStats: { [key: string]: number } = {};
    
    borrowers.forEach(borrow => {
      if (!bookStats[borrow.bookId]) {
        bookStats[borrow.bookId] = 0;
      }
      bookStats[borrow.bookId]++;
    });

    const topBooks = Object.entries(bookStats)
      .sort(([,a], [,b]) => b - a)
      .slice(0, 5)
      .map(([bookId, count]) => {
        const book = books.find(b => b.id === bookId);
        return {
          subject: book?.title || 'Unknown',
          A: count
        };
      });

    return topBooks;
  };

  const topReaders = getTopActiveReaders();
  const topBooks = getTopPopularBooks();

  const COLORS = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444'];

  return (
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <Card>
        <CardHeader>
          <CardTitle>Top 5 Pembaca Teraktif</CardTitle>
        </CardHeader>
        <CardContent>
          <ResponsiveContainer width="100%" height={300}>
            <PolarAreaChart data={topReaders}>
              <PolarGrid />
              <PolarAngleAxis dataKey="name" />
              <PolarRadiusAxis />
              <PolarArea dataKey="value" fill="#8884d8">
                {topReaders.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                ))}
              </PolarArea>
            </PolarAreaChart>
          </ResponsiveContainer>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Top 5 Buku Terpopuler</CardTitle>
        </CardHeader>
        <CardContent>
          <ResponsiveContainer width="100%" height={300}>
            <RadarChart data={topBooks}>
              <PolarGrid />
              <PolarAngleAxis dataKey="subject" />
              <PolarRadiusAxis />
              <Radar
                name="Peminjaman"
                dataKey="A"
                stroke="#10B981"
                fill="#10B981"
                fillOpacity={0.2}
              />
            </RadarChart>
          </ResponsiveContainer>
        </CardContent>
      </Card>
    </div>
  );
};

export default DashboardCharts;
