
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Badge } from './ui/badge';
import { RotateCcw, Plus, Trash2, AlertCircle } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

interface ReturnsAdminProps {
  returns: any[];
  borrowers: any[];
  onAddReturn: (data: any) => void;
  onDeleteReturn: (id: string) => void;
}

const ReturnsAdmin: React.FC<ReturnsAdminProps> = ({ 
  returns, 
  borrowers, 
  onAddReturn, 
  onDeleteReturn 
}) => {
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [formData, setFormData] = useState({
    borrowId: '',
    returnDate: new Date().toISOString().split('T')[0],
  });
  const { toast } = useToast();

  const resetForm = () => {
    setFormData({
      borrowId: '',
      returnDate: new Date().toISOString().split('T')[0],
    });
  };

  const calculateFine = (dueDate: string, returnDate: string) => {
    const due = new Date(dueDate);
    const returned = new Date(returnDate);
    const diffTime = returned.getTime() - due.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays * 5000 : 0; // Rp 5000 per hari
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const borrowRecord = borrowers.find(b => b.id === formData.borrowId);
    if (borrowRecord) {
      const fine = calculateFine(borrowRecord.dueDate, formData.returnDate);
      
      const returnData = {
        borrowId: formData.borrowId,
        memberName: borrowRecord.memberName,
        bookTitle: borrowRecord.bookTitle,
        returnDate: formData.returnDate,
        fine,
      };
      
      onAddReturn(returnData);
      setIsAddModalOpen(false);
      toast({
        title: "✓ Berhasil",
        description: "Pengembalian berhasil diproses!",
        className: "bg-green-50 border-green-200 text-green-800"
      });
      resetForm();
    }
  };

  const handleDelete = (id: string, memberName: string, bookTitle: string) => {
    if (window.confirm(`Apakah Anda yakin ingin menghapus data pengembalian "${bookTitle}" oleh ${memberName}?`)) {
      onDeleteReturn(id);
      toast({
        title: "✓ Berhasil",
        description: "Data pengembalian berhasil dihapus!",
        className: "bg-green-50 border-green-200 text-green-800"
      });
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  // Get active borrows (not returned yet)
  const activeBorrows = borrowers.filter(b => 
    b.status === 'Dipinjam' && !returns.find(r => r.borrowId === b.id)
  );

  return (
    <div className="p-6">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h2 className="text-3xl font-bold text-foreground">Manajemen Pengembalian</h2>
          <p className="text-muted-foreground">Kelola data pengembalian buku</p>
        </div>
        
        <Dialog open={isAddModalOpen} onOpenChange={setIsAddModalOpen}>
          <DialogTrigger asChild>
            <Button 
              onClick={() => setIsAddModalOpen(true)}
              disabled={activeBorrows.length === 0}
            >
              <Plus className="h-4 w-4 mr-2" />
              Proses Pengembalian
            </Button>
          </DialogTrigger>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Proses Pengembalian Buku</DialogTitle>
            </DialogHeader>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <Label htmlFor="borrowId">Pilih Peminjaman</Label>
                <select
                  id="borrowId"
                  name="borrowId"
                  value={formData.borrowId}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground"
                  required
                >
                  <option value="">Pilih data peminjaman</option>
                  {activeBorrows.map((borrow) => (
                    <option key={borrow.id} value={borrow.id}>
                      {borrow.memberName} - {borrow.bookTitle} 
                      (Jatuh tempo: {new Date(borrow.dueDate).toLocaleDateString('id-ID')})
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <Label htmlFor="returnDate">Tanggal Pengembalian</Label>
                <Input
                  id="returnDate"
                  name="returnDate"
                  type="date"
                  value={formData.returnDate}
                  onChange={handleInputChange}
                  required
                />
              </div>
              {formData.borrowId && (
                <div className="p-3 bg-muted rounded-md">
                  <p className="text-sm text-muted-foreground">
                    {(() => {
                      const borrowRecord = borrowers.find(b => b.id === formData.borrowId);
                      if (borrowRecord) {
                        const fine = calculateFine(borrowRecord.dueDate, formData.returnDate);
                        return fine > 0 
                          ? `Denda keterlambatan: Rp ${fine.toLocaleString('id-ID')} (${Math.ceil((new Date(formData.returnDate).getTime() - new Date(borrowRecord.dueDate).getTime()) / (1000 * 60 * 60 * 24))} hari)`
                          : 'Tidak ada denda';
                      }
                      return '';
                    })()}
                  </p>
                </div>
              )}
              <div className="flex justify-end space-x-2">
                <Button 
                  type="button" 
                  variant="outline" 
                  onClick={() => setIsAddModalOpen(false)}
                >
                  Batal
                </Button>
                <Button type="submit">Proses Pengembalian</Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      {activeBorrows.length === 0 && (
        <Card className="mb-6">
          <CardContent className="pt-6">
            <div className="flex items-center text-muted-foreground">
              <AlertCircle className="h-5 w-5 mr-2" />
              Tidak ada peminjaman aktif yang dapat dikembalikan.
            </div>
          </CardContent>
        </Card>
      )}

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center">
            <RotateCcw className="h-5 w-5 mr-2" />
            Daftar Pengembalian
          </CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Peminjam</TableHead>
                <TableHead>Judul Buku</TableHead>
                <TableHead>Tanggal Kembali</TableHead>
                <TableHead>Denda</TableHead>
                <TableHead>Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {returns.map((returnRecord) => (
                <TableRow key={returnRecord.id}>
                  <TableCell className="font-medium">{returnRecord.memberName}</TableCell>
                  <TableCell>{returnRecord.bookTitle}</TableCell>
                  <TableCell>
                    {new Date(returnRecord.returnDate).toLocaleDateString('id-ID')}
                  </TableCell>
                  <TableCell>
                    <Badge 
                      variant={returnRecord.fine > 0 ? 'destructive' : 'secondary'}
                    >
                      {returnRecord.fine > 0 
                        ? `Rp ${returnRecord.fine.toLocaleString('id-ID')}` 
                        : 'Tidak ada denda'
                      }
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Button
                      variant="destructive"
                      size="sm"
                      onClick={() => handleDelete(returnRecord.id, returnRecord.memberName, returnRecord.bookTitle)}
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

export default ReturnsAdmin;
