import React, { useEffect, useMemo, useState } from 'react';
import { AlertCircle, RefreshCw, Search, Users } from 'lucide-react';
import { toast } from 'sonner';
import api from '../../services/api';

interface SchoolClass {
    id: number;
    name: string;
}

interface Student {
    id: number;
    nik: string;
    nisn?: string | null;
    name: string;
    gender?: 'L' | 'P' | string | null;
    birth_date?: string | null;
    phone?: string | null;
    address?: string | null;
    school_class_id?: number | null;
    school_class?: SchoolClass | null;
}

export const ClassStudents = ({
    pageTitle = 'Data Kelas',
    pageDescription = 'Lihat seluruh siswa pada kelas perwalian yang diampu.',
}: {
    pageTitle?: string;
    pageDescription?: string;
}) => {
    const [classes, setClasses] = useState<SchoolClass[]>([]);
    const [students, setStudents] = useState<Student[]>([]);
    const [selectedClassId, setSelectedClassId] = useState('');
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(true);
    const [loadError, setLoadError] = useState('');

    const fetchData = async () => {
        setLoading(true);
        setLoadError('');
        try {
            const [classResponse, studentResponse] = await Promise.all([
                api.get('/walikelas/kelas'),
                api.get('/walikelas/siswa'),
            ]);

            const classList: SchoolClass[] = classResponse.data.data || [];
            setClasses(classList);
            setStudents(studentResponse.data.data || []);

            if (!selectedClassId && classList.length === 1) {
                setSelectedClassId(String(classList[0].id));
            }
        } catch (error: any) {
            const message = error.response?.data?.message || 'Gagal memuat data kelas.';
            setLoadError(message);
            toast.error(message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    const filteredStudents = useMemo(() => {
        const keyword = query.trim().toLowerCase();

        return students.filter((student) => {
            const matchesClass = selectedClassId ? String(student.school_class_id || student.school_class?.id || '') === selectedClassId : true;
            const matchesQuery = keyword
                ? [
                    student.name,
                    student.nik,
                    student.nisn || '',
                    student.phone || '',
                    student.address || '',
                    student.school_class?.name || '',
                ].some((value) => value.toLowerCase().includes(keyword))
                : true;

            return matchesClass && matchesQuery;
        });
    }, [students, selectedClassId, query]);

    const selectedClass = classes.find((item) => String(item.id) === selectedClassId) || null;
    const maleCount = filteredStudents.filter((student) => student.gender === 'L').length;
    const femaleCount = filteredStudents.filter((student) => student.gender === 'P').length;

    if (loadError && !loading) {
        return (
            <div className="min-h-full bg-slate-50 p-6">
                <div className="mx-auto max-w-3xl rounded-lg border border-amber-200 bg-amber-50 p-5 text-amber-800 shadow-sm">
                    <div className="flex items-start gap-3">
                        <AlertCircle className="mt-0.5 h-5 w-5 shrink-0" />
                        <div>
                            <h1 className="font-semibold">Data kelas belum tersedia</h1>
                            <p className="mt-1 text-sm">{loadError}</p>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-full bg-slate-50 px-2 py-4 sm:p-6">
            <div className="mx-auto w-full max-w-7xl space-y-5">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h1 className="text-xl font-bold text-slate-900 sm:text-2xl">{pageTitle}</h1>
                        <p className="mt-1 text-sm text-slate-500">{pageDescription}</p>
                    </div>
                    <button
                        onClick={fetchData}
                        disabled={loading}
                        className="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:opacity-60 sm:w-auto"
                    >
                        <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
                        Muat Ulang
                    </button>
                </div>

                <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase text-slate-400">Kelas</p>
                        <p className="mt-1 text-xl font-bold text-slate-900">{selectedClass?.name || (classes.length ? 'Semua' : '-')}</p>
                    </div>
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase text-slate-400">Total Siswa</p>
                        <p className="mt-1 text-xl font-bold text-slate-900">{filteredStudents.length}</p>
                    </div>
                    <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-700 shadow-sm">
                        <p className="text-xs font-semibold uppercase">Laki-laki</p>
                        <p className="mt-1 text-xl font-bold">{maleCount}</p>
                    </div>
                    <div className="rounded-lg border border-rose-200 bg-rose-50 p-4 text-rose-700 shadow-sm">
                        <p className="text-xs font-semibold uppercase">Perempuan</p>
                        <p className="mt-1 text-xl font-bold">{femaleCount}</p>
                    </div>
                </div>

                <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div className="grid gap-3 md:grid-cols-[220px_minmax(0,1fr)]">
                        <label className="flex flex-col gap-1.5">
                            <span className="text-xs font-semibold text-slate-600">Kelas</span>
                            <select
                                value={selectedClassId}
                                onChange={(event) => setSelectedClassId(event.target.value)}
                                className="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >
                                <option value="">Semua kelas perwalian</option>
                                {classes.map((item) => (
                                    <option key={item.id} value={item.id}>Kelas {item.name}</option>
                                ))}
                            </select>
                        </label>

                        <label className="flex flex-col gap-1.5">
                            <span className="text-xs font-semibold text-slate-600">Cari Siswa</span>
                            <div className="relative">
                                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input
                                    value={query}
                                    onChange={(event) => setQuery(event.target.value)}
                                    placeholder="Cari nama, NIK, NISN, nomor HP, atau alamat..."
                                    className="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                />
                            </div>
                        </label>
                    </div>
                </section>

                <section className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 p-4">
                        <div className="flex items-center gap-2">
                            <Users className="h-4 w-4 text-blue-600" />
                            <h2 className="text-sm font-semibold text-slate-900">Daftar Siswa</h2>
                        </div>
                    </div>

                    {loading ? (
                        <div className="flex items-center justify-center gap-2 p-10 text-sm text-slate-500">
                            <RefreshCw className="h-4 w-4 animate-spin" />
                            Memuat data siswa...
                        </div>
                    ) : filteredStudents.length === 0 ? (
                        <div className="p-10 text-center text-sm text-slate-500">Tidak ada siswa yang sesuai filter.</div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                                    <tr>
                                        <th className="px-4 py-3">Identitas</th>
                                        <th className="px-4 py-3">Nama</th>
                                        <th className="px-4 py-3">Kelas</th>
                                        <th className="px-4 py-3">Gender</th>
                                        <th className="px-4 py-3">Kontak</th>
                                        <th className="px-4 py-3">Alamat</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {filteredStudents.map((student) => (
                                        <tr key={student.id} className="hover:bg-slate-50/60">
                                            <td className="px-4 py-3">
                                                <p className="font-semibold text-slate-900">NIK: {student.nik || '-'}</p>
                                                <p className="mt-0.5 text-xs text-slate-400">NISN: {student.nisn || '-'}</p>
                                            </td>
                                            <td className="px-4 py-3 font-semibold text-slate-900">{student.name}</td>
                                            <td className="px-4 py-3 text-slate-600">{student.school_class?.name || '-'}</td>
                                            <td className="px-4 py-3 text-slate-600">
                                                {student.gender === 'L' ? 'Laki-laki' : student.gender === 'P' ? 'Perempuan' : '-'}
                                            </td>
                                            <td className="px-4 py-3 text-slate-600">{student.phone || '-'}</td>
                                            <td className="max-w-xs truncate px-4 py-3 text-slate-600">{student.address || '-'}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </section>
            </div>
        </div>
    );
};
