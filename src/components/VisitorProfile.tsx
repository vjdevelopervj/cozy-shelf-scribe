
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Textarea } from './ui/textarea';
import { Avatar, AvatarFallback, AvatarImage } from './ui/avatar';
import { User, Edit, Save, X } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

interface VisitorProfileProps {
  user: any;
}

const VisitorProfile: React.FC<VisitorProfileProps> = ({ user }) => {
  const [isEditing, setIsEditing] = useState(false);
  const [formData, setFormData] = useState({
    fullname: user?.fullname || '',
    email: user?.email || '',
    phone: user?.phone || '',
    address: user?.address || '',
    profilePicture: null as File | null
  });
  const { toast } = useToast();

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value, files } = e.target as HTMLInputElement;
    if (name === 'profilePicture' && files) {
      setFormData(prev => ({ ...prev, profilePicture: files[0] }));
    } else {
      setFormData(prev => ({ ...prev, [name]: value }));
    }
  };

  const handleSave = () => {
    // Update user data in localStorage
    const users = JSON.parse(localStorage.getItem('library_users') || '[]');
    const updatedUsers = users.map((u: any) => {
      if (u.id === user.id) {
        return {
          ...u,
          ...formData,
          profilePicture: formData.profilePicture 
            ? URL.createObjectURL(formData.profilePicture) 
            : u.profilePicture
        };
      }
      return u;
    });
    
    localStorage.setItem('library_users', JSON.stringify(updatedUsers));
    
    // Update current user in localStorage
    const updatedCurrentUser = {
      ...user,
      ...formData,
      profilePicture: formData.profilePicture 
        ? URL.createObjectURL(formData.profilePicture) 
        : user.profilePicture
    };
    localStorage.setItem('current_user', JSON.stringify(updatedCurrentUser));
    
    setIsEditing(false);
    toast({
      title: "✓ Berhasil",
      description: "Profil berhasil diperbarui!",
      className: "bg-green-50 border-green-200 text-green-800"
    });
  };

  const handleCancel = () => {
    setFormData({
      fullname: user?.fullname || '',
      email: user?.email || '',
      phone: user?.phone || '',
      address: user?.address || '',
      profilePicture: null
    });
    setIsEditing(false);
  };

  return (
    <div className="p-6">
      <div className="mb-6">
        <h2 className="text-3xl font-bold text-foreground mb-2">Profil Saya</h2>
        <p className="text-muted-foreground">Kelola informasi profil Anda</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <Card className="lg:col-span-1">
          <CardHeader>
            <CardTitle className="flex items-center">
              <User className="h-5 w-5 mr-2" />
              Foto Profil
            </CardTitle>
          </CardHeader>
          <CardContent className="text-center">
            <Avatar className="w-32 h-32 mx-auto mb-4">
              <AvatarImage src={user?.profilePicture} alt="Profile" />
              <AvatarFallback className="text-2xl">
                {user?.fullname?.charAt(0)?.toUpperCase() || 'U'}
              </AvatarFallback>
            </Avatar>
            
            {isEditing && (
              <div>
                <Label htmlFor="profilePicture" className="block mb-2">
                  Ganti Foto Profil
                </Label>
                <Input
                  id="profilePicture"
                  name="profilePicture"
                  type="file"
                  accept="image/*"
                  onChange={handleInputChange}
                />
              </div>
            )}
          </CardContent>
        </Card>

        <Card className="lg:col-span-2">
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle>Informasi Pribadi</CardTitle>
              {!isEditing ? (
                <Button
                  onClick={() => setIsEditing(true)}
                  variant="outline"
                  size="sm"
                >
                  <Edit className="h-4 w-4 mr-2" />
                  Edit Profil
                </Button>
              ) : (
                <div className="flex space-x-2">
                  <Button
                    onClick={handleSave}
                    size="sm"
                  >
                    <Save className="h-4 w-4 mr-2" />
                    Simpan
                  </Button>
                  <Button
                    onClick={handleCancel}
                    variant="outline"
                    size="sm"
                  >
                    <X className="h-4 w-4 mr-2" />
                    Batal
                  </Button>
                </div>
              )}
            </div>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label htmlFor="fullname">Nama Lengkap</Label>
                {isEditing ? (
                  <Input
                    id="fullname"
                    name="fullname"
                    value={formData.fullname}
                    onChange={handleInputChange}
                  />
                ) : (
                  <p className="p-2 bg-muted rounded">{user?.fullname || '-'}</p>
                )}
              </div>
              
              <div>
                <Label htmlFor="email">Email</Label>
                {isEditing ? (
                  <Input
                    id="email"
                    name="email"
                    type="email"
                    value={formData.email}
                    onChange={handleInputChange}
                  />
                ) : (
                  <p className="p-2 bg-muted rounded">{user?.email || '-'}</p>
                )}
              </div>
            </div>

            <div>
              <Label htmlFor="phone">Nomor Telepon</Label>
              {isEditing ? (
                <Input
                  id="phone"
                  name="phone"
                  value={formData.phone}
                  onChange={handleInputChange}
                  placeholder="Masukkan nomor telepon"
                />
              ) : (
                <p className="p-2 bg-muted rounded">{user?.phone || '-'}</p>
              )}
            </div>

            <div>
              <Label htmlFor="address">Alamat</Label>
              {isEditing ? (
                <Textarea
                  id="address"
                  name="address"
                  value={formData.address}
                  onChange={handleInputChange}
                  placeholder="Masukkan alamat lengkap"
                />
              ) : (
                <p className="p-2 bg-muted rounded min-h-[80px]">
                  {user?.address || '-'}
                </p>
              )}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label>Username</Label>
                <p className="p-2 bg-muted rounded">{user?.username || '-'}</p>
              </div>
              
              <div>
                <Label>Tanggal Bergabung</Label>
                <p className="p-2 bg-muted rounded">
                  {user?.createdAt 
                    ? new Date(user.createdAt).toLocaleDateString('id-ID')
                    : '-'
                  }
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default VisitorProfile;
