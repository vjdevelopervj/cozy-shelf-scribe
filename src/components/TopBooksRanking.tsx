
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Book } from 'lucide-react';

interface TopBooksRankingProps {
  borrowers: any[];
  books: any[];
}

const TopBooksRanking: React.FC<TopBooksRankingProps> = ({ borrowers, books }) => {
  const getTopBooks = () => {
    const bookStats: { [key: string]: number } = {};
    
    borrowers.forEach(borrow => {
      if (!bookStats[borrow.bookId]) {
        bookStats[borrow.bookId] = 0;
      }
      bookStats[borrow.bookId]++;
    });

    return Object.entries(bookStats)
      .sort(([,a], [,b]) => b - a)
      .slice(0, 5)
      .map(([bookId, count], index) => {
        const book = books.find(b => b.id === bookId);
        return {
          rank: index + 1,
          book,
          borrowCount: count
        };
      });
  };

  const topBooks = getTopBooks();

  return (
    <Card>
      <CardHeader>
        <CardTitle>Ranking Buku Terpopuler</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {topBooks.map((bookData) => (
            <div key={bookData.book?.id} className="flex items-center space-x-4">
              <div className="flex-shrink-0">
                <span className="bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                  #{bookData.rank}
                </span>
              </div>
              <div className="flex-shrink-0">
                {bookData.book?.cover ? (
                  <img
                    src={bookData.book.cover}
                    alt="Cover"
                    className="w-10 h-14 rounded object-cover"
                  />
                ) : (
                  <div className="w-10 h-14 bg-gray-300 rounded flex items-center justify-center">
                    <Book className="h-5 w-5 text-gray-600" />
                  </div>
                )}
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 truncate">
                  {bookData.book?.title || 'Unknown Book'}
                </p>
                <p className="text-sm text-gray-500 truncate">
                  {bookData.book?.author || 'Unknown Author'}
                </p>
                <p className="text-sm text-gray-500">
                  {bookData.borrowCount} kali dipinjam
                </p>
              </div>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
};

export default TopBooksRanking;
