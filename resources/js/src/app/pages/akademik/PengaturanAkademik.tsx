import React, { useState, useEffect } from 'react';
import { Save, RefreshCw, CheckCircle, AlertTriangle } from 'lucide-react';
import { toast } from 'sonner';
import api from '../../services/api';

interface Setting {
    id: number;
    key: string;
    label: string;
    value: string;
    type: string;
    description: string;
}

export const PengaturanAkademik = () => {
    const [settings, setSettings] = useState<Setting[]>([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    // Local form state
    const [academicYear, setAcademicYear] = useState('');
    const [activeSemester, setActiveSemester] = useState('');
    const [schoolName, setSchoolName] = useState('');
    const [startDate, setStartDate] = useState('');

    useEffect(() => {
        fetchSettings();
    }, []);

    const fetchSettings = async () => {
        try {
            const response = await api.get('/admin/setting');
            const data = response.data.data as Setting[];
            setSettings(data);

            // Populate states
            const yearSetting = data.find(s => s.key === 'academic_year');
            const semSetting = data.find(s => s.key === 'active_semester');
            const nameSetting = data.find(s => s.key === 'school_name');
            const dateSetting = data.find(s => s.key === 'school_start_date');

            if (yearSetting) setAcademicYear(yearSetting.value);
            if (semSetting) setActiveSemester(semSetting.value);
            if (nameSetting) setSchoolName(nameSetting.value);
            if (dateSetting) setStartDate(dateSetting.value);
        } catch (error) {
            toast.error('Gagal mengambil data pengaturan');
        } finally {
            setLoading(false);
        }
    };

    const handleSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        try {
            const updates = [
                { key: 'academic_year',    value: academicYear    },
                { key: 'active_semester',  value: activeSemester  },
                { key: 'school_name',      value: schoolName      },
                { key: 'school_start_date', value: startDate      },
            ];

            for (const item of updates) {
                const settingObj = settings.find(s => s.key === item.key);
                if (settingObj) {
                    // Gunakan endpoint PATCH /value agar tidak perlu kirim semua field
                    await api.patch(`/admin/setting/${settingObj.id}/value`, {
                        value: item.value,
                    });
                } else if (item.value) {
                    // Buat setting baru jika belum ada
                    const labelMap: Record<string, string> = {
                        academic_year:    'Tahun Ajaran Aktif',
                        active_semester:  'Semester Aktif',
                        school_name:      'Nama Sekolah',
                        school_start_date:'Tanggal Masuk Sekolah',
                    };
                    const typeMap: Record<string, string> = {
                        academic_year:    'text',
                        active_semester:  'select',
                        school_name:      'text',
                        school_start_date:'date',
                    };
                    await api.post('/admin/setting', {
                        key:         item.key,
                        label:       labelMap[item.key] ?? item.key,
                        value:       item.value,
                        type:        typeMap[item.key] ?? 'text',
                        description: '',
                    });
                }
            }

            toast.success('Pengaturan berhasil diperbarui!');
            fetchSettings();
        } catch (error: any) {
            const msg = error?.response?.data?.message ?? 'Gagal memperbarui pengaturan';
            toast.error(msg);
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <div className="p-6 flex items-center gap-2 text-slate-500">
                <RefreshCw className="w-5 h-5 animate-spin" />
                <span>Memuat pengaturan...</span>
            </div>
        );
    }

    return (
        <div className="p-6 max-w-2xl mx-auto">
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-slate-800">Pengaturan Akademik</h1>
                <p className="text-sm text-slate-500 mt-1">Konfigurasi tahun ajaran, semester aktif, dan informasi sekolah.</p>
            </div>

            <form onSubmit={handleSave} className="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div className="p-6 space-y-6">
                    <div>
                        <label className="block text-sm font-semibold text-slate-700 mb-1.5">Nama Sekolah</label>
                        <input
                            type="text"
                            value={schoolName}
                            onChange={(e) => setSchoolName(e.target.value)}
                            className="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            placeholder="SMP IP YAKIN"
                            required
                        />
                        <p className="text-xs text-slate-400 mt-1">Nama sekolah yang tampil di portal dan laporan.</p>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-semibold text-slate-700 mb-1.5">Tahun Ajaran Aktif</label>
                            <input
                                type="text"
                                value={academicYear}
                                onChange={(e) => setAcademicYear(e.target.value)}
                                className="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                placeholder="2025/2026"
                                required
                            />
                            <p className="text-xs text-slate-400 mt-1">Format: YYYY/YYYY (Misal: 2025/2026)</p>
                        </div>

                        <div>
                            <label className="block text-sm font-semibold text-slate-700 mb-1.5">Semester Aktif</label>
                            <select
                                value={activeSemester}
                                onChange={(e) => setActiveSemester(e.target.value)}
                                className="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                required
                            >
                                <option value="ganjil">Ganjil</option>
                                <option value="genap">Genap</option>
                            </select>
                            <p className="text-xs text-slate-400 mt-1">Ganjil/Genap untuk semester berjalan.</p>
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-slate-700 mb-1.5">Mulai Masuk Sekolah (Awal KBM)</label>
                        <input
                            type="date"
                            value={startDate}
                            onChange={(e) => setStartDate(e.target.value)}
                            className="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            required
                        />
                        <p className="text-xs text-slate-400 mt-1">Digunakan sebagai patokan awal absensi dan agenda akademik.</p>
                    </div>

                    <div className="bg-blue-50 border border-blue-100 rounded-lg p-4 flex gap-3 text-blue-800 text-sm">
                        <AlertTriangle className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                        <div>
                            <span className="font-semibold">Perhatian:</span> Mengubah tahun ajaran atau semester aktif akan mempengaruhi data absensi baru dan laporan jadwal pelajaran yang diakses oleh Guru dan Siswa. Pastikan data master pada tahun ajaran baru sudah diinputkan.
                        </div>
                    </div>
                </div>

                <div className="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end">
                    <button
                        type="submit"
                        disabled={saving}
                        className="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-semibold text-sm disabled:opacity-75"
                    >
                        {saving ? 'Menyimpan...' : <><Save className="w-4 h-4" /> Simpan Pengaturan</>}
                    </button>
                </div>
            </form>
        </div>
    );
};
