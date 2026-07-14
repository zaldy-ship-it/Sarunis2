import React, { useState, useEffect } from 'react';
import { Plus, Search, Edit2, Trash2, Download, Upload, Save, X, RefreshCw, AlertCircle, Calendar, BookOpen } from 'lucide-react';
import { toast } from 'sonner';
import api from '../../services/api';

interface Subject {
    id: number;
    code: string;
    name: string;
    description: string | null;
}

interface Teacher {
    id: number;
    name: string;
    nip: string;
}

interface SchoolClass {
    id: number;
    name: string;
}

interface TeachingAssignment {
    id: number;
    teacher_id: number;
    subject_id: number;
    school_class_id: number;
    day_of_week: number;
    start_time: string;
    end_time: string;
    room: string | null;
    teacher?: Teacher;
    subject?: Subject;
    school_class?: SchoolClass;
}

const DAY_NAMES = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

export const Mapel = () => {
    const [activeTab, setActiveTab] = useState<'jadwal' | 'master'>('jadwal');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [importing, setImporting] = useState(false);

    // Data lists
    const [assignments, setAssignments] = useState<TeachingAssignment[]>([]);
    const [subjects, setSubjects] = useState<Subject[]>([]);
    const [teachers, setTeachers] = useState<Teacher[]>([]);
    const [classes, setClasses] = useState<SchoolClass[]>([]);

    // Modals
    const [isJadwalFormOpen, setIsJadwalFormOpen] = useState(false);
    const [isMasterFormOpen, setIsMasterFormOpen] = useState(false);
    const [isImportOpen, setIsImportOpen] = useState(false);

    // Editing states
    const [editingAssignment, setEditingAssignment] = useState<TeachingAssignment | null>(null);
    const [editingSubject, setEditingSubject] = useState<Subject | null>(null);

    // Form fields - Jadwal
    const [selectedTeacher, setSelectedTeacher] = useState('');
    const [selectedSubject, setSelectedSubject] = useState('');
    const [selectedClass, setSelectedClass] = useState('');
    const [selectedDay, setSelectedDay] = useState('0');
    const [startTime, setStartTime] = useState('');
    const [endTime, setEndTime] = useState('');
    const [room, setRoom] = useState('');
    const [academicYear, setAcademicYear] = useState('2025/2026');

    // Form fields - Master Mapel
    const [mapelCode, setMapelCode] = useState('');
    const [mapelName, setMapelName] = useState('');
    const [mapelDesc, setMapelDesc] = useState('');

    // Import file
    const [importFile, setImportFile] = useState<File | null>(null);

    useEffect(() => {
        fetchInitialData();
    }, []);

    const fetchInitialData = async () => {
        setLoading(true);
        try {
            const [assignRes, subjRes, teachRes, classRes] = await Promise.all([
                api.get('/admin/jadwal-ajar'),
                api.get('/admin/mapel'),
                api.get('/admin/guru'),
                api.get('/admin/kelas')
            ]);

            setAssignments(assignRes.data.data || []);
            setSubjects(subjRes.data.data || []);
            setTeachers(teachRes.data.data || []);
            setClasses(classRes.data.data || []);
        } catch (error) {
            toast.error('Gagal memuat data');
        } finally {
            setLoading(false);
        }
    };

    const handleOpenJadwalCreate = () => {
        setEditingAssignment(null);
        setSelectedTeacher('');
        setSelectedSubject('');
        setSelectedClass('');
        setSelectedDay('0');
        setStartTime('');
        setEndTime('');
        setRoom('');
        setIsJadwalFormOpen(true);
    };

    const handleOpenJadwalEdit = (assignment: TeachingAssignment) => {
        setEditingAssignment(assignment);
        setSelectedTeacher(assignment.teacher_id.toString());
        setSelectedSubject(assignment.subject_id.toString());
        setSelectedClass(assignment.school_class_id.toString());
        setSelectedDay(assignment.day_of_week.toString());
        setStartTime(assignment.start_time.substring(0, 5));
        setEndTime(assignment.end_time.substring(0, 5));
        setRoom(assignment.room || '');
        setIsJadwalFormOpen(true);
    };

    const handleSaveJadwal = async (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        const payload = {
            academic_year: academicYear,
            teacher_id: parseInt(selectedTeacher),
            subject_id: parseInt(selectedSubject),
            school_class_id: parseInt(selectedClass),
            day_of_week: parseInt(selectedDay),
            start_time: startTime,
            end_time: endTime,
            room: room || null,
        };

        try {
            if (editingAssignment) {
                await api.put(`/admin/jadwal-ajar/${editingAssignment.id}`, payload);
                toast.success('Jadwal berhasil diperbarui!');
            } else {
                await api.post('/admin/jadwal-ajar', payload);
                toast.success('Jadwal berhasil disimpan!');
            }
            setIsJadwalFormOpen(false);
            fetchInitialData();
        } catch (error: any) {
            const msg = error.response?.data?.message || 'Gagal menyimpan jadwal';
            toast.error(msg);
        } finally {
            setSaving(false);
        }
    };

    const handleDeleteJadwal = async (id: number) => {
        if (!confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) return;
        try {
            await api.delete(`/admin/jadwal-ajar/${id}`);
            toast.success('Jadwal berhasil dihapus');
            fetchInitialData();
        } catch (error) {
            toast.error('Gagal menghapus jadwal');
        }
    };

    // Master Subject CRUD
    const handleOpenMasterCreate = () => {
        setEditingSubject(null);
        setMapelCode('');
        setMapelName('');
        setMapelDesc('');
        setIsMasterFormOpen(true);
    };

    const handleOpenMasterEdit = (subj: Subject) => {
        setEditingSubject(subj);
        setMapelCode(subj.code);
        setMapelName(subj.name);
        setMapelDesc(subj.description || '');
        setIsMasterFormOpen(true);
    };

    const handleSaveMaster = async (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        const payload = {
            code: mapelCode,
            name: mapelName,
            description: mapelDesc || null,
        };

        try {
            if (editingSubject) {
                await api.put(`/admin/mapel/${editingSubject.id}`, payload);
                toast.success('Mata pelajaran berhasil diperbarui!');
            } else {
                await api.post('/admin/mapel', payload);
                toast.success('Mata pelajaran baru berhasil ditambahkan!');
            }
            setIsMasterFormOpen(false);
            fetchInitialData();
        } catch (error: any) {
            const msg = error.response?.data?.message || 'Gagal menyimpan data';
            toast.error(msg);
        } finally {
            setSaving(false);
        }
    };

    const handleDeleteMaster = async (id: number) => {
        if (!confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini? (Ini dapat menghapus jadwal terkait)')) return;
        try {
            await api.delete(`/admin/mapel/${id}`);
            toast.success('Mata pelajaran berhasil dihapus');
            fetchInitialData();
        } catch (error) {
            toast.error('Gagal menghapus mata pelajaran');
        }
    };

    // Import
    const handleImport = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!importFile) return;

        setImporting(true);
        const formData = new FormData();
        formData.append('file', importFile);

        try {
            const response = await api.post('/admin/import/jadwal', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            toast.success(response.data.message || 'Import selesai!');
            setIsImportOpen(false);
            setImportFile(null);
            fetchInitialData();
        } catch (error: any) {
            toast.error(error.response?.data?.message || 'Gagal mengimport data');
        } finally {
            setImporting(false);
        }
    };

    const downloadTemplate = () => {
        window.open('/api/v1/admin/import/template/jadwal', '_blank');
    };

    return (
        <div className="p-6 max-w-7xl mx-auto">
            {/* Tab Switching */}
            <div className="flex border-b border-slate-200 mb-6">
                <button
                    onClick={() => setActiveTab('jadwal')}
                    className={`py-3 px-6 text-sm font-semibold border-b-2 transition-colors ${activeTab === 'jadwal' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'}`}
                >
                    Jadwal Pelajaran
                </button>
                <button
                    onClick={() => setActiveTab('master')}
                    className={`py-3 px-6 text-sm font-semibold border-b-2 transition-colors ${activeTab === 'master' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'}`}
                >
                    Master Mata Pelajaran
                </button>
            </div>

            {activeTab === 'jadwal' ? (
                // TAB 1: JADWAL PELAJARAN
                <div>
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <div>
                            <h1 className="text-2xl font-bold text-slate-800">Jadwal Pelajaran</h1>
                            <p className="text-sm text-slate-500 mt-1">Konfigurasi hari, jam, kelas, mata pelajaran, dan guru pengampu.</p>
                        </div>
                        <div className="flex gap-2.5">
                            <button onClick={downloadTemplate} className="flex items-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                                <Download className="w-4 h-4" />
                                Template Excel/CSV
                            </button>
                            <button onClick={() => setIsImportOpen(true)} className="flex items-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                                <Upload className="w-4 h-4" />
                                Import Jadwal
                            </button>
                            <button onClick={handleOpenJadwalCreate} className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                                <Plus className="w-4 h-4" />
                                Tambah Jadwal
                            </button>
                        </div>
                    </div>

                    {/* Table Schedule */}
                    <div className="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        {loading ? (
                            <div className="p-8 text-center text-slate-500 flex items-center justify-center gap-2">
                                <RefreshCw className="w-5 h-5 animate-spin" /> Memuat data...
                            </div>
                        ) : assignments.length === 0 ? (
                            <div className="p-12 text-center text-slate-400">
                                <Calendar className="w-12 h-12 mx-auto text-slate-300 mb-3" />
                                <p className="font-semibold text-slate-600">Belum ada jadwal KBM</p>
                                <p className="text-sm text-slate-400 mt-1">Silakan tambahkan jadwal secara manual atau lakukan import.</p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left border-collapse">
                                    <thead>
                                        <tr className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                            <th className="py-3 px-4">Hari / Jam</th>
                                            <th className="py-3 px-4">Kelas</th>
                                            <th className="py-3 px-4">Mata Pelajaran</th>
                                            <th className="py-3 px-4">Guru Pengampu</th>
                                            <th className="py-3 px-4">Ruangan</th>
                                            <th className="py-3 px-4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 text-sm text-slate-700">
                                        {assignments.map((assign) => (
                                            <tr key={assign.id} className="hover:bg-slate-50/50 transition-colors">
                                                <td className="py-3.5 px-4 font-semibold text-slate-900">
                                                    <span>{DAY_NAMES[assign.day_of_week] || '-'}</span>
                                                    <span className="block text-xs font-normal text-slate-500 mt-0.5">
                                                        {assign.start_time.substring(0, 5)} - {assign.end_time.substring(0, 5)}
                                                    </span>
                                                </td>
                                                <td className="py-3.5 px-4">
                                                    <span className="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full text-xs font-medium border border-blue-100">
                                                        {assign.school_class?.name || '-'}
                                                    </span>
                                                </td>
                                                <td className="py-3.5 px-4 font-medium">{assign.subject?.name || '-'}</td>
                                                <td className="py-3.5 px-4 text-slate-900">{assign.teacher?.name || '-'}</td>
                                                <td className="py-3.5 px-4 text-xs text-slate-500">{assign.room || 'Utama'}</td>
                                                <td className="py-3.5 px-4 text-right">
                                                    <div className="flex justify-end gap-1.5">
                                                        <button onClick={() => handleOpenJadwalEdit(assign)} className="p-1.5 hover:bg-slate-100 text-slate-600 hover:text-slate-900 rounded-md transition-colors">
                                                            <Edit2 className="w-4 h-4" />
                                                        </button>
                                                        <button onClick={() => handleDeleteJadwal(assign.id)} className="p-1.5 hover:bg-red-50 text-slate-600 hover:text-red-600 rounded-md transition-colors">
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
                    </div>
                </div>
            ) : (
                // TAB 2: MASTER SUBJECTS
                <div>
                    <div className="flex items-center justify-between mb-6">
                        <div>
                            <h1 className="text-2xl font-bold text-slate-800">Master Mata Pelajaran</h1>
                            <p className="text-sm text-slate-500 mt-1">Daftar semua mata pelajaran yang tersedia di kurikulum sekolah.</p>
                        </div>
                        <button onClick={handleOpenMasterCreate} className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                            <Plus className="w-4 h-4" />
                            Tambah Mata Pelajaran
                        </button>
                    </div>

                    <div className="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        {loading ? (
                            <div className="p-8 text-center text-slate-500 flex items-center justify-center gap-2">
                                <RefreshCw className="w-5 h-5 animate-spin" /> Memuat data...
                            </div>
                        ) : subjects.length === 0 ? (
                            <div className="p-12 text-center text-slate-400">
                                <BookOpen className="w-12 h-12 mx-auto text-slate-300 mb-3" />
                                <p className="font-semibold text-slate-600">Belum ada mata pelajaran</p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left border-collapse">
                                    <thead>
                                        <tr className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                            <th className="py-3 px-4">Kode Pelajaran</th>
                                            <th className="py-3 px-4">Nama Pelajaran</th>
                                            <th className="py-3 px-4">Deskripsi</th>
                                            <th className="py-3 px-4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 text-sm text-slate-700">
                                        {subjects.map((subj) => (
                                            <tr key={subj.id} className="hover:bg-slate-50/50 transition-colors">
                                                <td className="py-3.5 px-4 font-mono font-semibold text-blue-600">{subj.code}</td>
                                                <td className="py-3.5 px-4 font-medium">{subj.name}</td>
                                                <td className="py-3.5 px-4 text-xs text-slate-500 max-w-sm truncate">{subj.description || '-'}</td>
                                                <td className="py-3.5 px-4 text-right">
                                                    <div className="flex justify-end gap-1.5">
                                                        <button onClick={() => handleOpenMasterEdit(subj)} className="p-1.5 hover:bg-slate-100 text-slate-600 hover:text-slate-900 rounded-md transition-colors">
                                                            <Edit2 className="w-4 h-4" />
                                                        </button>
                                                        <button onClick={() => handleDeleteMaster(subj.id)} className="p-1.5 hover:bg-red-50 text-slate-600 hover:text-red-600 rounded-md transition-colors">
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
                    </div>
                </div>
            )}

            {/* Modal Form Jadwal */}
            {isJadwalFormOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden flex flex-col max-h-[90vh]">
                        <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <h2 className="font-semibold text-slate-800">
                                {editingAssignment ? 'Edit Jadwal Pelajaran' : 'Tambah Jadwal Baru'}
                            </h2>
                            <button onClick={() => setIsJadwalFormOpen(false)} className="text-slate-400 hover:text-slate-600 p-1">
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <form onSubmit={handleSaveJadwal} className="p-5 space-y-4 overflow-y-auto">
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Tahun Ajaran</label>
                                <input type="text" value={academicYear} onChange={(e) => setAcademicYear(e.target.value)} required className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none" />
                            </div>

                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Kelas (Wajib)</label>
                                <select value={selectedClass} onChange={(e) => setSelectedClass(e.target.value)} required className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                    <option value="">Pilih Kelas...</option>
                                    {classes.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Guru Pengampu (Wajib)</label>
                                <select value={selectedTeacher} onChange={(e) => setSelectedTeacher(e.target.value)} required className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                    <option value="">Pilih Guru...</option>
                                    {teachers.map(t => <option key={t.id} value={t.id}>{t.name} (NIP: {t.nip})</option>)}
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Mata Pelajaran (Wajib)</label>
                                <select value={selectedSubject} onChange={(e) => setSelectedSubject(e.target.value)} required className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                    <option value="">Pilih Mata Pelajaran...</option>
                                    {subjects.map(s => <option key={s.id} value={s.id}>{s.name} ({s.code})</option>)}
                                </select>
                            </div>

                            <div className="grid grid-cols-3 gap-3">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">Hari</label>
                                    <select value={selectedDay} onChange={(e) => setSelectedDay(e.target.value)} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none">
                                        {DAY_NAMES.map((name, idx) => <option key={idx} value={idx}>{name}</option>)}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">Mulai</label>
                                    <input type="time" value={startTime} onChange={(e) => setStartTime(e.target.value)} required className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" />
                                </div>
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1">Selesai</label>
                                    <input type="time" value={endTime} onChange={(e) => setEndTime(e.target.value)} required className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" />
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Ruangan</label>
                                <input type="text" value={room} onChange={(e) => setRoom(e.target.value)} placeholder="Misal: Lab Komputer" className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" />
                            </div>

                            <div className="pt-2 border-t border-slate-100 flex justify-end gap-2.5">
                                <button type="button" onClick={() => setIsJadwalFormOpen(false)} className="px-4 py-2 text-sm font-medium text-slate-600">Batal</button>
                                <button type="submit" disabled={saving} className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-75">
                                    {saving ? 'Menyimpan...' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal Form Master Mapel */}
            {isMasterFormOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden flex flex-col">
                        <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <h2 className="font-semibold text-slate-800">
                                {editingSubject ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran'}
                            </h2>
                            <button onClick={() => setIsMasterFormOpen(false)} className="text-slate-400 hover:text-slate-600 p-1">
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <form onSubmit={handleSaveMaster} className="p-5 space-y-4">
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Kode Pelajaran (Wajib)</label>
                                <input type="text" value={mapelCode} onChange={(e) => setMapelCode(e.target.value)} required className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" />
                            </div>
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Nama Mata Pelajaran (Wajib)</label>
                                <input type="text" value={mapelName} onChange={(e) => setMapelName(e.target.value)} required className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" />
                            </div>
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1">Deskripsi</label>
                                <textarea value={mapelDesc} onChange={(e) => setMapelDesc(e.target.value)} rows={3} className="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" />
                            </div>

                            <div className="pt-2 border-t border-slate-100 flex justify-end gap-2.5">
                                <button type="button" onClick={() => setIsMasterFormOpen(false)} className="px-4 py-2 text-sm font-medium text-slate-600">Batal</button>
                                <button type="submit" disabled={saving} className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Simpan</button>
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
                            <h2 className="font-semibold text-slate-800">Import Jadwal Pelajaran</h2>
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
                                <p>Format CSV harus memuat kolom: <strong>nip_guru, nama_mapel, nama_kelas, hari, jam_mulai, jam_selesai, ruangan</strong>. NIP Guru, Nama Mapel, dan Nama Kelas harus sudah terdaftar terlebih dahulu.</p>
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
