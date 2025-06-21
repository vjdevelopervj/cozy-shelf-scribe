
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Banknote, Settings, Eye, AlertCircle } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

interface FinesProps {
  borrowers: any[];
  returns: any[];
  books: any[];
  onUpdateSettings?: (settings: any) => void;
}

const Fines: React.FC<FinesProps> = ({ borrowers, returns, books, onUpdateSettings }) => {
  const [fineSettings, setFineSettings] = useState(() => {
    const saved = localStorage.getItem('fine_settings');
    return saved ? JSON.parse(saved) : { finePerDay: 1000, accountNumber: '', accountName: '' };
  });
  const { toast } = useToast();

  const saveFineSettings = () => {
    localStorage.setItem('fine_settings', JSON.stringify(fineSettings));
    onUpdateSettings?.(fineSettings);
    toast({
      title: "Pengaturan Tersimpan",
      description: "Pengaturan denda telah diperbarui.",
    });
  };

  const getOverdueBooks = () => {
    const today = new Date();
    return borrowers
      .filter(b => b.status === 'Borrowed' && new Date(b.dueDate) < today)
      .map(borrow => {
        const daysOverdue = Math.ceil((today.getTime() - new Date(borrow.dueDate).getTime()) / (1000 * 60 * 60 * 24));
        const fine = daysOverdue * fineSettings.finePerDay;
        return {
          ...borrow,
          daysOverdue,
          fine
        };
      });
  };

  const getFineHistory = () => {
    return returns.filter(r => r.fine > 0);
  };

  const overdueBooks = getOverdueBooks();
  const fineHistory = getFineHistory();
  const totalFines = fineHistory.reduce((sum, r) => sum + r.fine, 0);

  return (
    <div className="p-6 space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold text-foreground">Manajemen Denda</h2>
          <p className="text-muted-foreground">Kelola denda keterlambatan pengembalian buku</p>
        </div>
        
        <Dialog>
          <DialogTrigger asChild>
            <Button>
              <Settings className="h-4 w-4 mr-2" />
              Pengaturan Denda
            </Button>
          </DialogTrigger>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Pengaturan Denda</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div>
                <Label htmlFor="finePerDay">Denda per Hari (Rp)</Label>
                <Input
                  id="finePerDay"
                  type="number"
                  value={fineSettings.finePerDay}
                  onChange={(e) => setFineSettings(prev => ({ ...prev, finePerDay: parseInt(e.target.value) }))}
                />
              </div>
              <div>
                <Label htmlFor="accountNumber">Nomor Rekening</Label>
                <Input
                  id="accountNumber"
                  value={fineSettings.accountNumber}
                  onChange={(e) => setFineSettings(prev => ({ ...prev, accountNumber: e.target.value }))}
                  placeholder="Masukkan nomor rekening"
                />
              </div>
              <div>
                <Label htmlFor="accountName">Nama Pemilik Rekening</Label>
                <Input
                  id="accountName"
                  value={fineSettings.accountName}
                  onChange={(e) => setFineSettings(prev => ({ ...prev, accountName: e.target.value }))}
                  placeholder="Masukkan nama pemilik rekening"
                />
              </div>
              <Button onClick={saveFineSettings} className="w-full">
                Simpan Pengaturan
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <AlertCircle className="h-5 w-5 mr-2 text-red-600" />
              Buku Terlambat
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-red-600">{overdueBooks.length}</p>
            <p className="text-sm text-muted-foreground">buku terlambat</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <Banknote className="h-5 w-5 mr-2 text-yellow-600" />
              Total Pemasukan Denda
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-yellow-600">
              Rp {totalFines.toLocaleString('id-ID')}
            </p>
            <p className="text-sm text-muted-foreground">total denda terkumpul</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Pengaturan Denda</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-lg font-semibold">Rp {fineSettings.finePerDay.toLocaleString('id-ID')}</p>
            <p className="text-sm text-muted-foreground">per hari keterlambatan</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Buku yang Terlambat Dikembalikan</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Peminjam</TableHead>
                <TableHead>Judul Buku</TableHead>
                <TableHead>Tanggal Jatuh Tempo</TableHead>
                <TableHead>Hari Terlambat</TableHead>
                <TableHead>Denda</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {overdueBooks.map((borrow) => (
                <TableRow key={borrow.id}>
                  <TableCell>{borrow.memberName}</TableCell>
                  <TableCell>{borrow.bookTitle}</TableCell>
                  <TableCell>{new Date(borrow.dueDate).toLocaleDateString('id-ID')}</TableCell>
                  <TableCell className="text-red-600 font-medium">{borrow.daysOverdue} hari</TableCell>
                  <TableCell className="text-red-600 font-medium">
                    Rp {borrow.fine.toLocaleString('id-ID')}
                  </TableCell>
                </TableRow>
              ))}
              {overdueBooks.length === 0 && (
                <TableRow>
                  <TableCell colSpan={5} className="text-center text-muted-foreground">
                    Tidak ada buku yang terlambat
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Riwayat Denda</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Peminjam</TableHead>
                <TableHead>Judul Buku</TableHead>
                <TableHead>Tanggal Pengembalian</TableHead>
                <TableHead>Denda</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {fineHistory.map((returnRecord) => {
                const borrowRecord = borrowers.find(b => b.id === returnRecord.borrowId);
                return (
                  <TableRow key={returnRecord.id}>
                    <TableCell>{returnRecord.memberName}</TableCell>
                    <TableCell>{returnRecord.bookTitle}</TableCell>
                    <TableCell>{new Date(returnRecord.returnDate).toLocaleDateString('id-ID')}</TableCell>
                    <TableCell className="font-medium">
                      Rp {returnRecord.fine.toLocaleString('id-ID')}
                    </TableCell>
                  </TableRow>
                );
              })}
              {fineHistory.length === 0 && (
                <TableRow>
                  <TableCell colSpan={4} className="text-center text-muted-foreground">
                    Belum ada riwayat denda
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
};

export default Fines;
