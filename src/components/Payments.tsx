
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Button } from './ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Badge } from './ui/badge';
import { CreditCard, Eye, CheckCircle, XCircle, Clock } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

interface PaymentsProps {
  returns: any[];
  borrowers: any[];
  onUpdatePayment?: (id: string, status: string) => void;
}

const Payments: React.FC<PaymentsProps> = ({ returns, borrowers, onUpdatePayment }) => {
  const [selectedPayment, setSelectedPayment] = useState<any>(null);
  const { toast } = useToast();

  const getPayments = () => {
    return returns
      .filter(r => r.fine > 0 && r.paymentProof)
      .map(returnRecord => {
        const borrowRecord = borrowers.find(b => b.id === returnRecord.borrowId);
        return {
          ...returnRecord,
          borrowRecord,
          status: returnRecord.paymentStatus || 'pending'
        };
      });
  };

  const updatePaymentStatus = (paymentId: string, status: string) => {
    // Update payment status in localStorage
    const returns_data = JSON.parse(localStorage.getItem('library_returns') || '[]');
    const updatedReturns = returns_data.map((r: any) => 
      r.id === paymentId ? { ...r, paymentStatus: status } : r
    );
    localStorage.setItem('library_returns', JSON.stringify(updatedReturns));
    
    onUpdatePayment?.(paymentId, status);
    
    toast({
      title: status === 'approved' ? "Pembayaran Disetujui" : "Pembayaran Ditolak",
      description: `Status pembayaran telah diperbarui.`,
    });
  };

  const payments = getPayments();
  const pendingPayments = payments.filter(p => p.status === 'pending');
  const approvedPayments = payments.filter(p => p.status === 'approved');
  const totalIncome = approvedPayments.reduce((sum, p) => sum + p.fine, 0);

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'approved':
        return <Badge className="bg-green-100 text-green-800">Disetujui</Badge>;
      case 'rejected':
        return <Badge className="bg-red-100 text-red-800">Ditolak</Badge>;
      default:
        return <Badge className="bg-yellow-100 text-yellow-800">Menunggu</Badge>;
    }
  };

  return (
    <div className="p-6 space-y-6">
      <div>
        <h2 className="text-3xl font-bold text-foreground">Manajemen Pembayaran</h2>
        <p className="text-muted-foreground">Kelola bukti pembayaran denda dari pengunjung</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <Clock className="h-5 w-5 mr-2 text-yellow-600" />
              Menunggu Verifikasi
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-yellow-600">{pendingPayments.length}</p>
            <p className="text-sm text-muted-foreground">pembayaran pending</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <CheckCircle className="h-5 w-5 mr-2 text-green-600" />
              Disetujui
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-green-600">{approvedPayments.length}</p>
            <p className="text-sm text-muted-foreground">pembayaran disetujui</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <CreditCard className="h-5 w-5 mr-2 text-blue-600" />
              Total Pemasukan
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold text-blue-600">
              Rp {totalIncome.toLocaleString('id-ID')}
            </p>
            <p className="text-sm text-muted-foreground">dari denda yang disetujui</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Daftar Pembayaran</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Peminjam</TableHead>
                <TableHead>Judul Buku</TableHead>
                <TableHead>Tanggal Pengembalian</TableHead>
                <TableHead>Denda</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {payments.map((payment) => (
                <TableRow key={payment.id}>
                  <TableCell>{payment.memberName}</TableCell>
                  <TableCell>{payment.bookTitle}</TableCell>
                  <TableCell>{new Date(payment.returnDate).toLocaleDateString('id-ID')}</TableCell>
                  <TableCell className="font-medium">
                    Rp {payment.fine.toLocaleString('id-ID')}
                  </TableCell>
                  <TableCell>{getStatusBadge(payment.status)}</TableCell>
                  <TableCell>
                    <div className="flex space-x-2">
                      <Dialog>
                        <DialogTrigger asChild>
                          <Button 
                            variant="outline" 
                            size="sm"
                            onClick={() => setSelectedPayment(payment)}
                          >
                            <Eye className="h-4 w-4 mr-1" />
                            Lihat
                          </Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-2xl">
                          <DialogHeader>
                            <DialogTitle>Bukti Pembayaran</DialogTitle>
                          </DialogHeader>
                          {selectedPayment && (
                            <div className="space-y-4">
                              <div className="grid grid-cols-2 gap-4">
                                <div>
                                  <p className="font-medium">Peminjam:</p>
                                  <p>{selectedPayment.memberName}</p>
                                </div>
                                <div>
                                  <p className="font-medium">Judul Buku:</p>
                                  <p>{selectedPayment.bookTitle}</p>
                                </div>
                                <div>
                                  <p className="font-medium">Denda:</p>
                                  <p>Rp {selectedPayment.fine.toLocaleString('id-ID')}</p>
                                </div>
                                <div>
                                  <p className="font-medium">Status:</p>
                                  {getStatusBadge(selectedPayment.status)}
                                </div>
                              </div>
                              
                              <div>
                                <p className="font-medium mb-2">Bukti Transfer:</p>
                                <img
                                  src={selectedPayment.paymentProof}
                                  alt="Bukti Transfer"
                                  className="w-full max-w-md h-auto rounded-lg border"
                                />
                              </div>
                              
                              {selectedPayment.status === 'pending' && (
                                <div className="flex space-x-2">
                                  <Button
                                    onClick={() => updatePaymentStatus(selectedPayment.id, 'approved')}
                                    className="bg-green-600 hover:bg-green-700"
                                  >
                                    <CheckCircle className="h-4 w-4 mr-2" />
                                    Setujui
                                  </Button>
                                  <Button
                                    onClick={() => updatePaymentStatus(selectedPayment.id, 'rejected')}
                                    variant="destructive"
                                  >
                                    <XCircle className="h-4 w-4 mr-2" />
                                    Tolak
                                  </Button>
                                </div>
                              )}
                            </div>
                          )}
                        </DialogContent>
                      </Dialog>
                    </div>
                  </TableCell>
                </TableRow>
              ))}
              {payments.length === 0 && (
                <TableRow>
                  <TableCell colSpan={6} className="text-center text-muted-foreground">
                    Belum ada pembayaran
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

export default Payments;
