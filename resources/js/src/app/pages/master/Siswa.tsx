import React, { useState, useEffect } from 'react';
import { Plus, Search, Edit2, Trash2, Download, Upload, Save, X, RefreshCw, AlertCircle } from 'lucide-react';
import { toast } from 'sonner';
import api from '../../services/api';

interface Student {
    id: number;
    nik: string;
    nisn: string | null;
    name: string;
    gender: string | null;
    birth_date: string | null;
    phone: string | null;
    address: string | null;
    school_class_id: number | null;
    school_class?: { id: number; name: string };
    parent_user?: { id: number; name: string; email: string } | null;
    detail_siswa?: {
        religion?: string | null;
        birth_place?: string | null;
        address_street?: string | null;
        address_village?: string | null;
        address_district?: string | null;
        address_province?: string | null;
        address_city?: string | null;
        father_name?: string | null;
        father_education?: string | null;
        father_occupation?: string | null;
        mother_name?: string | null;
        mother_education?: string | null;
        mother_occupation?: string | null;
        parent_address?: string | null;
        parent_province?: string | null;
        parent_city?: string | null;
        postal_code?: string | null;
        parent_phone?: string | null;
        previous_school?: string | null;
    } | null;
}

interface SchoolClass {
    id: number;
    name: string;
}

export const Siswa = () => {
    const [students, setStudents] = useState<Student[]>([]);
    const [classes, setClasses] = useState<SchoolClass[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    
    // Modal states
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [isImportOpen, setIsImportOpen] = useState(false);
    const [editingStudent, setEditingStudent] = useState<Student | null>(null);
    const [saving, setSaving] = useState(false);
    const [importing, setImporting] = useState(false);

    // Form fields
    const [nik, setNik] = useState('');
    const [nisn, setNisn] = useState('');
    const [name, setName] = useState('');
    const [gender, setGender] = useState('L');
    const [birthDate, setBirthDate] = useState('');
    const [phone, setPhone] = useState('');
    const [address, setAddress] = useState('');
    const [selectedClass, setSelectedClass] = useState('');
    const [fatherName, setFatherName] = useState('');
    const [motherName, setMotherName] = useState('');
    const [parentPhone, setParentPhone] = useState('');
    const [importFile, setImportFile] = useState<File | null>(null);

    useEffect(() => {
        fetchStudents();
        fetchClasses();
    }, [currentPage, searchTerm]);

    const fetchStudents = async () => {
        try {
            const response = await api.get('/admin/siswa', {
                params: {
                    page: currentPage,
                    search: searchTerm,
                }
            });
            setStudents(response.data.data || response.data.data);
            // Laravel paginator fields
            setTotalPages(response.data.last_page || 1);
        } catch (error) {
            toast.error('Gagal memuat data siswa');
        } finally {
            setLoading(false);
        }
    };

    const fetchClasses = async () => {
        try {
            const response = await api.get('/admin/kelas');
            setClasses(response.data.data || []);
        } catch (error) {
            console.error('Gagal memuat data kelas', error);
        }
    };

    const handleOpenCreate = () => {
        setEditingStudent(null);
        setNik('');
        setNisn('');
        setName('');
        setGender('L');
        setBirthDate('');
        setPhone('');
        setAddress('');
        setSelectedClass('');
        setFatherName('');
        setMotherName('');
        setParentPhone('');
        setIsFormOpen(true);
    };

    const handleOpenEdit = (student: Student) => {
        setEditingStudent(student);
        setNik(student.nik);
        setNisn(student.nisn || '');
        setName(student.name);
        setGender(student.gender || 'L');
        setBirthDate(student.birth_date || '');
        setPhone(student.phone || '');
        setAddress(student.address || '');
        setSelectedClass(student.school_class_id?.toString() || '');
        setFatherName(student.detail_siswa?.father_name || '');
        setMotherName(student.detail_siswa?.mother_name || '');
        setParentPhone(student.detail_siswa?.parent_phone || '');
        setIsFormOpen(true);
    };

    const handleSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        const payload = {
            nik,
            nisn: nisn || null,
            name,
            gender,
            birth_date: birthDate || null,
            phone: phone || null,
            address: address || null,
            school_class_id: selectedClass ? parseInt(selectedClass) : null,
            detail_siswa: {
                father_name: fatherName || null,
                mother_name: motherName || null,
                parent_phone: parentPhone || null,
            },
        };

        try {
            if (editingStudent) {
                await api.put(`/admin/siswa/${editingStudent.id}`, payload);
                toast.success('Data siswa berhasil diperbarui!');
            } else {
                await api.post('/admin/siswa', payload);
                toast.success('Siswa baru berhasil ditambahkan!');
            }
            setIsFormOpen(false);
            fetchStudents();
        } catch (error: any) {
            const msg = error.response?.data?.message || 'Gagal menyimpan data';
            toast.error(msg);
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id: number) => {
        if (!confirm('Apakah Anda yakin ingin menghapus data siswa ini?')) return;
        try {
            await api.delete(`/admin/siswa/${id}`);
            toast.success('Siswa berhasil dihapus');
            fetchStudents();
        } catch (error) {
            toast.error('Gagal menghapus siswa');
        }
    };

    const handleImport = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!importFile) return;

        setImporting(true);
        const formData = new FormData();
        formData.append('file', importFile);

        try {
            const response = await api.post('/admin/import/siswa', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            toast.success(response.data.message || 'Import selesai!');
            setIsImportOpen(false);
            setImportFile(null);
            fetchStudents();
        } catch (error: any) {
            toast.error(error.response?.data?.message || 'Gagal mengimport data');
        } finally {
            setImporting(false);
        }
    };

    const downloadTemplate = () => {
        window.open('/api/v1/admin/import/template/siswa', '_blank');
    };

    return (
        <div className="p-6 max-w-7xl mx-auto">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 className="text-2xl font-bold text-slate-800">Data Siswa</h1>
                    <p className="text-sm text-slate-500 mt-1">Kelola seluruh informasi siswa aktif dan kelas mereka.</p>
                </div>
                <div className="flex flex-wrap gap-2.5">
                    <button onClick={downloadTemplate} className="flex items-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                        <Download className="w-4 h-4" />
                        Download Template
                    </button>
                    <button onClick={() => setIsImportOpen(true)} className="flex items-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                        <Upload className="w-4 h-4" />
                        Import Excel/CSV
                    </button>
                    <button onClick={handleOpenCreate} className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                        <Plus className="w-4 h-4" />
                        Tambah Siswa
                    </button>
                </div>
            </div>

            {/* Filter & Search */}
            <div className="bg-white border border-slate-200 rounded-xl shadow-sm mb-6 p-4 flex gap-4">
                <div className="relative flex-1 max-w-md">
                    <Search className="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                    <input
                        type="text"
                        placeholder="Cari siswa berdasarkan NIK, NISN, atau nama..."
                        value={searchTerm}
                        onChange={(e) => { setSearchTerm(e.target.value); setCurrentPage(1); }}
                        className="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    />
                </div>
            </div>

            {/* Student Table */}
            <div className="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                {loading ? (
                    <div className="p-8 text-center text-slate-500 flex items-center justify-center gap-2">
                        <RefreshCw className="w-5 h-5 animate-spin" />
                        Memuat data...
                    </div>
                ) : students.length === 0 ? (
                    <div className="p-8 text-center text-slate-500">
                        Tidak ada data siswa ditemukan.
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left border-collapse">
                            <thead>
                                <tr className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                    <th className="py-3 px-4 whitespace-nowrap sticky left-0 z-10 bg-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">NIK / NISN</th>
                                    <th className="py-3 px-4 whitespace-nowrap sticky left-[120px] z-10 bg-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">Nama Lengkap</th>
                                    <th className="py-3 px-4 whitespace-nowrap">Gender</th>
                                    <th className="py-3 px-4 whitespace-nowrap">Kelas</th>
                                    <th className="py-3 px-4 whitespace-nowrap">Tempat, Tgl Lahir</th>
                                    <th className="py-3 px-4 whitespace-nowrap">Agama</th>
                                    <th className="py-3 px-4 whitespace-nowrap">Kontak</th>
                                    <th className="py-3 px-4 min-w-[200px]">Alamat Lengkap</th>
                                    <th className="py-3 px-4 whitespace-nowrap">Asal Sekolah</th>
                                    <th className="py-3 px-4 min-w-[200px]">Data Ayah</th>
                                    <th className="py-3 px-4 min-w-[200px]">Data Ibu</th>
                                    <th className="py-3 px-4 min-w-[200px]">Data Wali / Kontak Ortu</th>
                                    <th className="py-3 px-4 whitespace-nowrap">Akun Orang Tua</th>
                                    <th className="py-3 px-4 text-right sticky right-0 z-10 bg-slate-50 shadow-[-2px_0_5px_-2px_rgba(0,0,0,0.1)]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 text-sm text-slate-700">
                                {students.map((student) => (
                                    <tr key={student.id} className="hover:bg-slate-50/50 transition-colors group">
                                        <td className="py-3.5 px-4 whitespace-nowrap sticky left-0 z-10 bg-white group-hover:bg-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] transition-colors">
                                            <span className="font-semibold text-slate-900 block">{student.nik}</span>
                                            <span className="text-xs text-slate-400">{student.nisn || '-'}</span>
                                        </td>
                                        <td className="py-3.5 px-4 font-medium text-slate-900 whitespace-nowrap sticky left-[120px] z-10 bg-white group-hover:bg-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] transition-colors">
                                            {student.name}
                                        </td>
                                        <td className="py-3.5 px-4 whitespace-nowrap">
                                            {student.gender === 'L' ? 'Laki-laki' : 'Perempuan'}
                                        </td>
                                        <td className="py-3.5 px-4 whitespace-nowrap">
                                            {student.school_class?.name ? (
                                                <span className="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full text-xs font-medium border border-blue-100">
                                                    {student.school_class.name}
                                                </span>
                                            ) : (
                                                <span className="text-xs text-slate-400 font-medium">Belum Diplot</span>
                                            )}
                                        </td>
                                        <td className="py-3.5 px-4 whitespace-nowrap">
                                            {student.detail_siswa?.birth_place || '-'}, {student.birth_date || '-'}
                                        </td>
                                        <td className="py-3.5 px-4 whitespace-nowrap">
                                            {student.detail_siswa?.religion || '-'}
                                        </td>
                                        <td className="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500">
                                            {student.phone || '-'}
                                        </td>
                                        <td className="py-3.5 px-4 text-xs text-slate-500">
                                            <div className="line-clamp-2" title={`${student.address || ''} ${student.detail_siswa?.address_street || ''} ${student.detail_siswa?.address_village || ''} ${student.detail_siswa?.address_district || ''} ${student.detail_siswa?.address_province || ''} ${student.detail_siswa?.address_city || ''}`}>
                                                {[student.address, student.detail_siswa?.address_street, student.detail_siswa?.address_village, student.detail_siswa?.address_district, student.detail_siswa?.address_province, student.detail_siswa?.address_city].filter(Boolean).join(', ') || '-'}
                                            </div>
                                        </td>
                                        <td className="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500">
                                            {student.detail_siswa?.previous_school || '-'}
                                        </td>
                                        <td className="py-3.5 px-4 text-xs text-slate-500">
                                            <span className="block font-semibold text-slate-700">{student.detail_siswa?.father_name || '-'}</span>
                                            <span className="block">{student.detail_siswa?.father_education || '-'} • {student.detail_siswa?.father_occupation || '-'}</span>
                                        </td>
                                        <td className="py-3.5 px-4 text-xs text-slate-500">
                                            <span className="block font-semibold text-slate-700">{student.detail_siswa?.mother_name || '-'}</span>
                                            <span className="block">{student.detail_siswa?.mother_education || '-'} • {student.detail_siswa?.mother_occupation || '-'}</span>
                                        </td>
                                        <td className="py-3.5 px-4 text-xs text-slate-500">
                                            <span className="block">HP: {student.detail_siswa?.parent_phone || '-'}</span>
                                            <span className="block line-clamp-1" title={`${student.detail_siswa?.parent_address || ''} ${student.detail_siswa?.parent_city || ''} ${student.detail_siswa?.parent_province || ''}`}>
                                                {[student.detail_siswa?.parent_address, student.detail_siswa?.parent_city, student.detail_siswa?.parent_province, student.detail_siswa?.postal_code].filter(Boolean).join(', ') || '-'}
                                            </span>
                                        </td>
                                        <td className="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500">
                                            {student.parent_user ? (
                                                <>
                                                    <span className="block font-semibold text-slate-700">{student.parent_user.name}</span>
                                                    <span>{student.parent_user.email}</span>
                                                </>
                                            ) : '-'}
                                        </td>
                                        <td className="py-3.5 px-4 text-right sticky right-0 z-10 bg-white group-hover:bg-slate-50 shadow-[-2px_0_5px_-2px_rgba(0,0,0,0.1)] transition-colors">
                                            <div className="flex justify-end gap-1.5">
                                                <button onClick={() => handleOpenEdit(student)} className="p-1.5 hover:bg-slate-100 text-slate-600 hover:text-slate-900 rounded-md transition-colors">
                                                    <Edit2 className="w-4 h-4" />
                                                </button>
                                                <button onClick={() => handleDelete(student.id)} className="p-1.5 hover:bg-red-50 text-slate-600 hover:text-red-600 rounded-md transition-colors">
                                                    <Trash2 className="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Pagination */}
                {totalPages > 1 && (
                    <div className="px-4 py-3 border-t border-slate-200 flex items-center justify-between bg-slate-50">
                        <button
                            disabled={currentPage === 1}
                            onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                            className="px-3 py-1 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-lg text-sm disabled:opacity-50"
                        >
                            Sebelumnya
                        </button>
                        <span className="text-xs text-slate-500 font-semibold">Halaman {currentPage} dari {totalPages}</span>
                        <button
                            disabled={currentPage === totalPages}
                            onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                            className="px-3 py-1 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-lg text-sm disabled:opacity-50"
                        >
                            Selanjutnya
                        </button>
                    </div>
                )}
            </div>

            {/* Modal CRUD Form */}
            {isFormOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
                        <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <h2 className="font-semibold text-slate-800">
                                {editingStudent ? 'Edit Data Siswa' : 'Tambah Siswa Baru'}
                            </h2>
                            <button onClick={() => setIsFormOpen(false)} className="text-slate-400 hover:text-slate-600 transition-colors p-1">
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleSave} className="overflow-y-auto p-5 space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">NIK (Wajib)</label>
                                    <input type="text" value={nik} onChange={(e) => setNik(e.target.value)} required className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                                </div>
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">NISN</label>
                                    <input type="text" value={nisn} onChange={(e) => setNisn(e.target.value)} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap (Wajib)</label>
                                <input type="text" value={name} onChange={(e) => setName(e.target.value)} required className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">Gender</label>
                                    <select value={gender} onChange={(e) => setGender(e.target.value)} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">Tanggal Lahir</label>
                                    <input type="date" value={birthDate} onChange={(e) => setBirthDate(e.target.value)} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">Nomor Telepon</label>
                                    <input type="text" value={phone} onChange={(e) => setPhone(e.target.value)} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                                </div>
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">Kelas</label>
                                    <select value={selectedClass} onChange={(e) => setSelectedClass(e.target.value)} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                        <option value="">Pilih Kelas...</option>
                                        {classes.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Alamat Tempat Tinggal</label>
                                <textarea value={address} onChange={(e) => setAddress(e.target.value)} rows={3} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                            </div>

                            <div className="pt-3 border-t border-slate-100">
                                <h3 className="text-sm font-semibold text-slate-800">Data Orang Tua</h3>
                                <p className="mt-1 text-xs text-slate-500">Akun orang tua otomatis dibuat. Email memakai nama siswa dan nama ibu, password default memakai tanggal lahir siswa format ddmmyyyy.</p>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">Nama Ayah</label>
                                    <input type="text" value={fatherName} onChange={(e) => setFatherName(e.target.value)} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                                </div>
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">Nama Ibu</label>
                                    <input type="text" value={motherName} onChange={(e) => setMotherName(e.target.value)} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Nomor Telepon Orang Tua</label>
                                <input type="text" value={parentPhone} onChange={(e) => setParentPhone(e.target.value)} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                            </div>

                            <div className="pt-2 border-t border-slate-100 flex justify-end gap-2.5">
                                <button type="button" onClick={() => setIsFormOpen(false)} className="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800">
                                    Batal
                                </button>
                                <button type="submit" disabled={saving} className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm disabled:opacity-75">
                                    <Save className="w-4 h-4" />
                                    {saving ? 'Menyimpan...' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal Import */}
            {isImportOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden flex flex-col">
                        <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <h2 className="font-semibold text-slate-800">Import Data Siswa</h2>
                            <button onClick={() => setIsImportOpen(false)} className="text-slate-400 hover:text-slate-600 p-1">
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <form onSubmit={handleImport} className="p-5 space-y-4">
                            <div className="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:border-blue-500 transition-colors">
                                <Upload className="w-10 h-10 text-slate-400 mx-auto mb-2" />
                                <span className="text-xs text-slate-500 block mb-3">Format file yang diperbolehkan: .csv</span>
                                <input
                                    type="file"
                                    accept=".csv"
                                    onChange={(e) => setImportFile(e.target.files?.[0] || null)}
                                    className="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 file:cursor-pointer hover:file:bg-blue-100"
                                    required
                                />
                            </div>

                            <div className="flex gap-2 text-xs text-slate-500 bg-amber-50 border border-amber-100 rounded-lg p-3">
                                <AlertCircle className="w-5 h-5 text-amber-600 flex-shrink-0" />
                                <p>Pastikan file CSV memiliki kolom-kolom yang sesuai dengan template. Download template terlebih dahulu jika belum memilikinya.</p>
                            </div>

                            <div className="flex justify-end gap-2">
                                <button type="button" onClick={() => setIsImportOpen(false)} className="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800">
                                    Batal
                                </button>
                                <button type="submit" disabled={importing} className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-75">
                                    {importing ? 'Mengimport...' : 'Mulai Import'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};
