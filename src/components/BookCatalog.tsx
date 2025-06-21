
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Badge } from './ui/badge';
import { BookOpen, Search } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

interface BookCatalogProps {
  books: any[];
  borrowers: any[];
  user: any;
  onAddBorrow: (data: any) => void;
}

const BookCatalog: React.FC<BookCatalogProps> = ({ 
  books, 
  borrowers, 
  user, 
  onAddBorrow 
}) => {
  const [searchTerm, setSearchTerm] = useState('');
  const { toast } = useToast();

  const handleBorrow = (book: any) => {
    // Check if book is already borrowed
    const isBorrowed = borrowers.some(b => b.bookId === book.id && b.status === 'Dipinjam');
    
    if (isBorrowed) {
      toast({
        title: "Tidak Dapat Meminjam",
        description: "Buku ini sedang dipinjam oleh pengguna lain.",
        variant: "destructive"
      });
      return;
    }

    // Check if user already has 3 active borrows
    const userBorrows = borrowers.filter(b => b.memberId === user.id && b.status === 'Dipinjam');
    if (userBorrows.length >= 3) {
      toast({
        title: "Batas Peminjaman Tercapai",
        description: "Anda tidak dapat meminjam lebih dari 3 buku sekaligus.",
        variant: "destructive"
      });
      return;
    }

    const borrowDate = new Date();
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + 14); // 2 weeks

    const borrowData = {
      memberId: user.id,
      memberName: user.fullname,
      bookId: book.id,
      bookTitle: book.title,
      borrowDate: borrowDate.toISOString().split('T')[0],
      dueDate: dueDate.toISOString().split('T')[0],
      status: 'Dipinjam'
    };

    onAddBorrow(borrowData);
    toast({
      title: "✓ Berhasil",
      description: `"${book.title}" berhasil dipinjam!`,
      className: "bg-green-50 border-green-200 text-green-800"
    });
  };

  const filteredBooks = books.filter(book =>
    book.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
    book.author.toLowerCase().includes(searchTerm.toLowerCase()) ||
    book.category.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const isBookBorrowed = (bookId: string) => {
    return borrowers.some(b => b.bookId === bookId && b.status === 'Dipinjam');
  };

  return (
    <div className="p-6">
      <div className="mb-6">
        <h2 className="text-3xl font-bold text-foreground mb-2">Katalog Buku</h2>
        <p className="text-muted-foreground">Jelajahi dan pinjam buku dari koleksi perpustakaan</p>
      </div>

      <Card className="mb-6">
        <CardContent className="pt-6">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
            <Input
              placeholder="Cari buku berdasarkan judul, penulis, atau kategori..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10"
            />
          </div>
        </CardContent>
      </Card>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {filteredBooks.map((book) => (
          <Card key={book.id} className="flex flex-col">
            <CardHeader className="pb-3">
              <div className="aspect-[3/4] mb-3 bg-muted rounded-lg overflow-hidden">
                {book.cover ? (
                  <img
                    src={book.cover}
                    alt={book.title}
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center">
                    <BookOpen className="h-12 w-12 text-muted-foreground" />
                  </div>
                )}
              </div>
              <CardTitle className="text-lg line-clamp-2">{book.title}</CardTitle>
            </CardHeader>
            <CardContent className="flex-1 flex flex-col">
              <p className="text-muted-foreground mb-2">oleh {book.author}</p>
              <Badge variant="secondary" className="w-fit mb-3">
                {book.category}
              </Badge>
              <p className="text-sm text-muted-foreground mb-4 line-clamp-3">
                {book.description}
              </p>
              <div className="mt-auto">
                <Button
                  onClick={() => handleBorrow(book)}
                  disabled={isBookBorrowed(book.id)}
                  className="w-full"
                  variant={isBookBorrowed(book.id) ? "secondary" : "default"}
                >
                  {isBookBorrowed(book.id) ? "Sedang Dipinjam" : "Pinjam Buku"}
                </Button>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {filteredBooks.length === 0 && (
        <Card>
          <CardContent className="pt-6 text-center">
            <BookOpen className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
            <p className="text-muted-foreground">
              {searchTerm ? "Tidak ada buku yang sesuai dengan pencarian." : "Belum ada buku dalam katalog."}
            </p>
          </CardContent>
        </Card>
      )}
    </div>
  );
};

export default BookCatalog;
