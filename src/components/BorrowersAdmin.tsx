
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Badge } from './ui/badge';
import { Users, Plus, Trash2, BookOpen } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

interface BorrowersAdminProps {
  borrowers: any[];
  members: any[];
  books: any[];
  onAddBorrow: (data: any) => void;
  onDeleteBorrow: (id: string) => void;
}

const BorrowersAdmin: React.FC<BorrowersAdminProps> = ({ 
  borrowers, 
  members, 
  books, 
  onAddBorrow, 
  onDeleteBorrow 
}) => {
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [formData, setFormData] = useState({
    memberId: '',
    bookId: '',
    borrowDate: new Date().toISOString().split('T')[0],
    dueDate: '',
  });
  const { toast } = useToast();

  const resetForm = () => {
    setFormData({
      memberId: '',
      bookId: '',
      borrowDate: new Date().toISOString().split('T')[0],
      dueDate: '',
    });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const member = members.find(m => m.id === formData.memberId);
    const book = books.find(b => b.id === formData.bookId);
    
    if (member && book) {
      const borrowData = {
        ...formData,
        memberName: member.name || member.fullname,
        bookTitle: book.title,
        status: 'Dipinjam'
      };
      
      onAddBorrow(borrowData);
      setIsAddModalOpen(false);
      toast({
        title: "✓ Berhasil",
        description: "Peminjaman berhasil dicatat!",
        className: "bg-green-50 border-green-200 text-green-800"
      });
      resetForm();
    }
  };

  const handleDelete = (id: string, memberName: string, bookTitle: string) => {
    if (window.confirm(`Apakah Anda yakin ingin menghapus peminjaman "${bookTitle}" oleh ${memberName}?`)) {
      onDeleteBorrow(id);
      toast({
        title: "✓ Berhasil",
        description: "Data peminjaman berhasil dihapus!",
        className: "bg-green-50 border-green-200 text-green-800"
      });
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  // Get available books (not currently borrowed)
  const availableBooks = books.filter(book => 
    !borrowers.find(borrow => borrow.bookId === book.id && borrow.status === 'Dipinjam')
  );

  // Get users from localStorage
  const users = JSON.parse(localStorage.getItem('library_users') || '[]');
  const visitorMembers = users.filter((user: any) => user.role === 'visitor');

  return (
    <div className="p-6">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h2 className="text-3xl font-bold text-foreground">Manajemen Peminjaman</h2>
          <p className="text-muted-foreground">Kelola data peminjaman buku</p>
        </div>
        
        <Dialog open={isAddModalOpen} onOpenChange={setIsAddModalOpen}>
          <DialogTrigger asChild>
            <Button onClick={() => setIsAddModalOpen(true)}>
              <Plus className="h-4 w-4 mr-2" />
              Tambah Peminjaman
            </Button>
          </DialogTrigger>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Tambah Peminjaman Baru</DialogTitle>
            </DialogHeader>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <Label htmlFor="memberId">Anggota</Label>
                <select
                  id="memberId"
                  name="memberId"
                  value={formData.memberId}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                  required
                >
                  <option value="">Pilih Anggota</option>
                  {visitorMembers.map((member: any) => (
                    <option key={member.id} value={member.id}>
                      {member.fullname} - {member.email}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <Label htmlFor="bookId">Buku</Label>
                <select
                  id="bookId"
                  name="bookId"
                  value={formData.bookId}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                  required
                >
                  <option value="">Pilih Buku</option>
                  {availableBooks.map((book) => (
                    <option key={book.id} value={book.id}>
                      {book.title} - {book.author}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <Label htmlFor="borrowDate">Tanggal Pinjam</Label>
                <Input
                  id="borrowDate"
                  name="borrowDate"
                  type="date"
                  value={formData.borrowDate}
                  onChange={handleInputChange}
                  required
                />
              </div>
              <div>
                <Label htmlFor="dueDate">Tanggal Kembali</Label>
                <Input
                  id="dueDate"
                  name="dueDate"
                  type="date"
                  value={formData.dueDate}
                  onChange={handleInputChange}
                  required
                />
              </div>
              <div className="flex justify-end space-x-2">
                <Button 
                  type="button" 
                  variant="outline" 
                  onClick={() => setIsAddModalOpen(false)}
                >
                  Batal
                </Button>
                <Button type="submit">Tambah Peminjaman</Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center">
            <Users className="h-5 w-5 mr-2" />
            Daftar Peminjaman
          </CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Peminjam</TableHead>
                <TableHead>Judul Buku</TableHead>
                <TableHead>Tanggal Pinjam</TableHead>
                <TableHead>Tanggal Kembali</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {borrowers.map((borrow) => (
                <TableRow key={borrow.id}>
                  <TableCell className="font-medium">{borrow.memberName}</TableCell>
                  <TableCell>{borrow.bookTitle}</TableCell>
                  <TableCell>
                    {new Date(borrow.borrowDate).toLocaleDateString('id-ID')}
                  </TableCell>
                  <TableCell>
                    {new Date(borrow.dueDate).toLocaleDateString('id-ID')}
                  </TableCell>
                  <TableCell>
                    <Badge 
                      variant={borrow.status === 'Dipinjam' ? 'default' : 'secondary'}
                    >
                      {borrow.status}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Button
                      variant="destructive"
                      size="sm"
                      onClick={() => handleDelete(borrow.id, borrow.memberName, borrow.bookTitle)}
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
};

export default BorrowersAdmin;
