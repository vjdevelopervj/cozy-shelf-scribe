
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { User } from 'lucide-react';

interface TopReadersRankingProps {
  borrowers: any[];
}

const TopReadersRanking: React.FC<TopReadersRankingProps> = ({ borrowers }) => {
  const getTopReaders = () => {
    const users = JSON.parse(localStorage.getItem('library_users') || '[]');
    const readerStats: { [key: string]: number } = {};
    
    borrowers.forEach(borrow => {
      if (!readerStats[borrow.memberId]) {
        readerStats[borrow.memberId] = 0;
      }
      readerStats[borrow.memberId]++;
    });

    return Object.entries(readerStats)
      .sort(([,a], [,b]) => b - a)
      .slice(0, 5)
      .map(([userId, count], index) => {
        const user = users.find((u: any) => u.id === userId);
        return {
          rank: index + 1,
          user,
          borrowCount: count
        };
      });
  };

  const topReaders = getTopReaders();

  return (
    <Card>
      <CardHeader>
        <CardTitle>Ranking Pembaca Teraktif</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {topReaders.map((reader) => (
            <div key={reader.user?.id} className="flex items-center space-x-4">
              <div className="flex-shrink-0">
                <span className="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                  #{reader.rank}
                </span>
              </div>
              <div className="flex-shrink-0">
                {reader.user?.profilePicture ? (
                  <img
                    src={reader.user.profilePicture}
                    alt="Profile"
                    className="w-10 h-10 rounded-full object-cover"
                  />
                ) : (
                  <div className="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                    <User className="h-5 w-5 text-gray-600" />
                  </div>
                )}
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 truncate">
                  {reader.user?.fullname || 'Unknown User'}
                </p>
                <p className="text-sm text-gray-500">
                  {reader.borrowCount} peminjaman
                </p>
              </div>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
};

export default TopReadersRanking;
