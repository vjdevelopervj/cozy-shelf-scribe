
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Badge } from './ui/badge';
import { BookOpen, Upload, AlertCircle, Clock } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

interface MyBorrowsProps {
  user: any;
  borrowers: any[];
  returns: any[];
  onAddReturn: (data: any) => void;
}

const MyBorrows: React.FC<MyBorrowsProps> = ({ 
  user, 
  borrowers, 
  returns, 
  onAddReturn 
}) => {
  const [isReturnModalOpen, setIsReturnModalOpen] = useState(false);
  const [selectedBorrow, setSelectedBorrow] = useState<any>(null);
  const [paymentProof, setPaymentProof] = useState<File | null>(null);
  const { toast } = useToast();

  const myBorrows = borrowers.filter(b => b.memberId === user?.id);
  const myActiveBorrows = myBorrows.filter(b => 
    b.status === 'Dipinjam' && !returns.find(r => r.borrowId === b.id)
  );

  const calculateFine = (dueDate: string) => {
    const due = new Date(dueDate);
    const today = new Date();
    const diffTime = today.getTime() - due.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays * 5000 : 0; // Rp 5000 per hari
  };

  const isOverdue = (dueDate: string) => {
    return new Date() > new Date(dueDate);
  };

  const handleReturnClick = (borrow: any) => {
    setSelectedBorrow(borrow);
    setIsReturnModalOpen(true);
  };

  const handleReturnSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!selectedBorrow) return;

    const fine = calculateFine(selectedBorrow.dueDate);
    
    if (fine > 0 && !paymentProof) {
      toast({
        title: "Bukti Transfer Diperlukan",
        description: "Silakan upload bukti transfer untuk pembayaran denda.",
        variant: "destructive"
      });
      return;
    }

    const returnData = {
      borrowId: selectedBorrow.id,
      memberName: selectedBorrow.memberName,
      bookTitle: selectedBorrow.bookTitle,
      returnDate: new Date().toISOString().split('T')[0],
      fine,
      paymentProof: paymentProof ? URL.createObjectURL(paymentProof) : null,
      paymentStatus: fine > 0 ? 'pending' : 'none'
    };

    onAddReturn(returnData);
    setIsReturnModalOpen(false);
    setSelectedBorrow(null);
    setPaymentProof(null);
    
    toast({
      title: "✓ Berhasil",
      description: fine > 0 
        ? "Pengembalian diajukan! Bukti transfer akan diverifikasi admin."
        : "Buku berhasil dikembalikan!",
      className: "bg-green-50 border-green-200 text-green-800"
    });
  };

  const getBankInfo = () => {
    // This would typically come from admin settings
    return {
      bankName: "Bank BCA",
      accountNumber: "1234567890",
      accountName: "Perpustakaan XYZ"
    };
  };

  const bankInfo = getBankInfo();

  return (
    <div className="p-6">
      <div className="mb-6">
        <h2 className="text-3xl font-bold text-foreground mb-2">Peminjaman Saya</h2>
        <p className="text-muted-foreground">Kelola dan kembalikan buku yang Anda pinjam</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center text-lg">
              <BookOpen className="h-5 w-5 mr-2 text-blue-600" />
              Total Dipinjam
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-blue-600">{myActiveBorrows.length}</p>
            <p className="text-sm text-muted-foreground">buku sedang dipinjam</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center text-lg">
              <AlertCircle className="h-5 w-5 mr-2 text-red-600" />
              Terlambat
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-red-600">
              {myActiveBorrows.filter(b => isOverdue(b.dueDate)).length}
            </p>
            <p className="text-sm text-muted-foreground">buku terlambat</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center text-lg">
              <Clock className="h-5 w-5 mr-2 text-green-600" />
              Tepat Waktu
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-green-600">
              {myActiveBorrows.filter(b => !isOverdue(b.dueDate)).length}
            </p>
            <p className="text-sm text-muted-foreground">buku tepat waktu</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Daftar Peminjaman Aktif</CardTitle>
        </CardHeader>
        <CardContent>
          {myActiveBorrows.length > 0 ? (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Judul Buku</TableHead>
                  <TableHead>Tanggal Pinjam</TableHead>
                  <TableHead>Tanggal Kembali</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Denda</TableHead>
                  <TableHead>Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {myActiveBorrows.map((borrow) => {
                  const fine = calculateFine(borrow.dueDate);
                  const overdue = isOverdue(borrow.dueDate);
                  
                  return (
                    <TableRow key={borrow.id}>
                      <TableCell className="font-medium">{borrow.bookTitle}</TableCell>
                      <TableCell>
                        {new Date(borrow.borrowDate).toLocaleDateString('id-ID')}
                      </TableCell>
                      <TableCell>
                        {new Date(borrow.dueDate).toLocaleDateString('id-ID')}
                      </TableCell>
                      <TableCell>
                        <Badge variant={overdue ? 'destructive' : 'default'}>
                          {overdue ? 'Terlambat' : 'Aktif'}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        {fine > 0 ? (
                          <span className="text-red-600 font-medium">
                            Rp {fine.toLocaleString('id-ID')}
                          </span>
                        ) : (
                          <span className="text-green-600">Tidak ada</span>
                        )}
                      </TableCell>
                      <TableCell>
                        <Button
                          size="sm"
                          onClick={() => handleReturnClick(borrow)}
                        >
                          Kembalikan
                        </Button>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          ) : (
            <div className="text-center py-8">
              <BookOpen className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
              <p className="text-muted-foreground">Anda belum meminjam buku apapun.</p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Return Modal */}
      <Dialog open={isReturnModalOpen} onOpenChange={setIsReturnModalOpen}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Kembalikan Buku</DialogTitle>
          </DialogHeader>
          {selectedBorrow && (
            <form onSubmit={handleReturnSubmit} className="space-y-4">
              <div className="space-y-2">
                <p><strong>Judul Buku:</strong> {selectedBorrow.bookTitle}</p>
                <p><strong>Tanggal Pinjam:</strong> {new Date(selectedBorrow.borrowDate).toLocaleDateString('id-ID')}</p>
                <p><strong>Jatuh Tempo:</strong> {new Date(selectedBorrow.dueDate).toLocaleDateString('id-ID')}</p>
              </div>

              {(() => {
                const fine = calculateFine(selectedBorrow.dueDate);
                if (fine > 0) {
                  return (
                    <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                      <div className="flex items-center mb-2">
                        <AlertCircle className="h-5 w-5 text-red-600 mr-2" />
                        <span className="font-medium text-red-800">Denda Keterlambatan</span>
                      </div>
                      <p className="text-red-700 mb-4">
                        Anda dikenakan denda sebesar <strong>Rp {fine.toLocaleString('id-ID')}</strong>
                      </p>
                      
                      <div className="bg-white p-3 rounded border">
                        <p className="font-medium mb-2">Informasi Rekening:</p>
                        <p>Bank: {bankInfo.bankName}</p>
                        <p>No. Rekening: {bankInfo.accountNumber}</p>
                        <p>Atas Nama: {bankInfo.accountName}</p>
                      </div>

                      <div className="mt-4">
                        <Label htmlFor="paymentProof">Upload Bukti Transfer *</Label>
                        <Input
                          id="paymentProof"
                          type="file"
                          accept="image/*"
                          onChange={(e) => setPaymentProof(e.target.files?.[0] || null)}
                          required
                          className="mt-1"
                        />
                        <p className="text-sm text-muted-foreground mt-1">
                          Silakan transfer ke rekening di atas dan upload bukti transfer
                        </p>
                      </div>
                    </div>
                  );
                }
                return (
                  <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div className="flex items-center">
                      <Clock className="h-5 w-5 text-green-600 mr-2" />
                      <span className="text-green-800">Tidak ada denda. Buku dikembalikan tepat waktu!</span>
                    </div>
                  </div>
                );
              })()}

              <div className="flex justify-end space-x-2">
                <Button 
                  type="button" 
                  variant="outline" 
                  onClick={() => {
                    setIsReturnModalOpen(false);
                    setSelectedBorrow(null);
                    setPaymentProof(null);
                  }}
                >
                  Batal
                </Button>
                <Button type="submit">
                  Kembalikan Buku
                </Button>
              </div>
            </form>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default MyBorrows;
