import React, { useEffect, useMemo, useState } from 'react';
import { BarChart3, CalendarDays, Download, FileSpreadsheet, FileText, Filter, RefreshCw, Search } from 'lucide-react';
import api from '../../services/api';

type PageMode = 'kehadiran' | 'guru' | 'siswa' | 'statistik';

type Option = {
    id: number;
    name: string;
    level?: string;
    academic_year?: string;
};

type Filters = {
    dataset: string;
    format: 'csv' | 'xls' | 'pdf';
    type: string;
    school_class_id: string;
    subject_id: string;
    teacher_id: string;
    student_id: string;
    attendance_date: string;
    date_from: string;
    date_to: string;
    status: string;
    gender: string;
    category: string;
    employment_status: string;
    level: string;
    academic_year: string;
    usage: string;
    search: string;
};

const initialFilters: Filters = {
    dataset: 'absensi',
    format: 'csv',
    type: 'gabungan',
    school_class_id: '',
    subject_id: '',
    teacher_id: '',
    student_id: '',
    attendance_date: '',
    date_from: '',
    date_to: '',
    status: '',
    gender: '',
    category: '',
    employment_status: '',
    level: '',
    academic_year: '',
    usage: '',
    search: '',
};

const datasetLabels: Record<string, string> = {
    absensi: 'Absensi Gabungan',
    siswa: 'Data Siswa',
    guru: 'Data Guru',
    kelas: 'Data Kelas',
    mapel: 'Mata Pelajaran',
    'catatan-siswa': 'Catatan Siswa',
};

const modeDefaults: Record<PageMode, Partial<Filters>> = {
    kehadiran: { dataset: 'absensi', type: 'gabungan' },
    guru: { dataset: 'guru' },
    siswa: { dataset: 'siswa' },
    statistik: { dataset: 'absensi', type: 'gabungan' },
};

const titles: Record<PageMode, { title: string; subtitle: string }> = {
    kehadiran: {
        title: 'Rekap Absen Siswa',
        subtitle: 'Unduh data absensi siswa berdasarkan kelas, mapel, tanggal, dan status kehadiran.',
    },
    guru: {
        title: 'Rekap Guru',
        subtitle: 'Unduh data guru saja dengan filter kategori, gender, status kepegawaian, dan pencarian.',
    },
    siswa: {
        title: 'Rekap Siswa',
        subtitle: 'Unduh data siswa saja dengan filter kelas, gender, tahun ajaran, dan pencarian.',
    },
    statistik: {
        title: 'Statistik Kehadiran',
        subtitle: 'Siapkan file olahan statistik absensi untuk periode, kelas, mapel, atau status tertentu.',
    },
};

const attendanceStatuses = [
    { value: 'hadir', label: 'Hadir' },
    { value: 'izin', label: 'Izin' },
    { value: 'sakit', label: 'Sakit' },
    { value: 'alpha', label: 'Alpha' },
];

const teacherCategories = [
    { value: 'guru', label: 'Guru' },
    { value: 'guru-mapel', label: 'Guru Mapel' },
    { value: 'walikelas', label: 'Wali Kelas' },
    { value: 'guru-mapel-walikelas', label: 'Guru Mapel + Wali Kelas' },
];

const getCollection = (payload: any): Option[] => {
    if (Array.isArray(payload?.data?.data)) return payload.data.data;
    if (Array.isArray(payload?.data)) return payload.data;
    return [];
};

const fieldClass = 'h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100';

export const AdminRecapExport = ({ mode }: { mode: PageMode }) => {
    const [filters, setFilters] = useState<Filters>({ ...initialFilters, ...modeDefaults[mode] });
    const [classes, setClasses] = useState<Option[]>([]);
    const [subjects, setSubjects] = useState<Option[]>([]);
    const [teachers, setTeachers] = useState<Option[]>([]);
    const [students, setStudents] = useState<Option[]>([]);
    const [loadingOptions, setLoadingOptions] = useState(true);

    useEffect(() => {
        setFilters({ ...initialFilters, ...modeDefaults[mode] });
    }, [mode]);

    useEffect(() => {
        let active = true;

        const loadOptions = async () => {
            setLoadingOptions(true);
            try {
                const [classRes, subjectRes, teacherRes, studentRes] = await Promise.all([
                    api.get('/admin/kelas', { params: { per_page: 500 } }),
                    api.get('/admin/mapel', { params: { per_page: 500 } }),
                    api.get('/admin/guru', { params: { per_page: 500 } }),
                    api.get('/admin/siswa', { params: { per_page: 500 } }),
                ]);

                if (!active) return;

                setClasses(getCollection(classRes));
                setSubjects(getCollection(subjectRes));
                setTeachers(getCollection(teacherRes));
                setStudents(getCollection(studentRes));
            } finally {
                if (active) setLoadingOptions(false);
            }
        };

        loadOptions();

        return () => {
            active = false;
        };
    }, []);

    const levels = useMemo(() => Array.from(new Set(classes.map((item) => item.level).filter(Boolean))) as string[], [classes]);
    const academicYears = useMemo(() => Array.from(new Set(classes.map((item) => item.academic_year).filter(Boolean))) as string[], [classes]);
    const selectedDataset = datasetLabels[filters.dataset] || 'Data';
    const page = titles[mode];
    const isAttendanceRecap = mode === 'kehadiran' || mode === 'statistik';
    const isTeacherRecap = mode === 'guru';
    const isStudentRecap = mode === 'siswa';

    const updateFilter = (key: keyof Filters, value: string) => {
        setFilters((current) => ({ ...current, [key]: value }));
    };

    const resetFilters = () => {
        setFilters({ ...initialFilters, ...modeDefaults[mode], format: filters.format });
    };

    const exportUrl = (format: Filters['format']) => {
        const params = new URLSearchParams();

        Object.entries({ ...filters, format }).forEach(([key, value]) => {
            if (!value || key === 'dataset' || key === 'format') return;
            params.set(key, value);
        });

        const query = params.toString();
        return `/api/v1/admin/export/${filters.dataset}/${format}${query ? `?${query}` : ''}`;
    };

    const download = (format: Filters['format']) => {
        window.open(exportUrl(format), '_blank', 'noopener,noreferrer');
    };

    return (
        <div className="min-h-full bg-slate-50 p-4 sm:p-6">
            <div className="mx-auto max-w-7xl space-y-5">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">{page.title}</h1>
                        <p className="mt-1 text-sm text-slate-500">{page.subtitle}</p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <button onClick={() => download('csv')} className="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            <Download className="h-4 w-4" />
                            CSV
                        </button>
                        <button onClick={() => download('xls')} className="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <FileSpreadsheet className="h-4 w-4" />
                            Excel
                        </button>
                        <button onClick={() => download('pdf')} className="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <FileText className="h-4 w-4" />
                            Cetak
                        </button>
                    </div>
                </div>

                <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <div className="mb-4 flex items-center justify-between gap-3">
                            <div className="flex items-center gap-2">
                                <Filter className="h-4 w-4 text-blue-600" />
                                <h2 className="text-sm font-semibold text-slate-900">Filter Download</h2>
                            </div>
                            <button onClick={resetFilters} className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                <RefreshCw className="h-3.5 w-3.5" />
                                Reset
                            </button>
                        </div>

                        <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            {(isAttendanceRecap || isStudentRecap) && (
                                <label className="flex flex-col gap-1.5">
                                    <span className="text-xs font-semibold text-slate-600">Kelas</span>
                                    <select className={fieldClass} value={filters.school_class_id} onChange={(event) => updateFilter('school_class_id', event.target.value)}>
                                        <option value="">Semua kelas</option>
                                        {classes.map((item) => (
                                            <option key={item.id} value={item.id}>{item.name}</option>
                                        ))}
                                    </select>
                                </label>
                            )}

                            {isAttendanceRecap && (
                                <>
                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Mapel</span>
                                        <select className={fieldClass} value={filters.subject_id} onChange={(event) => updateFilter('subject_id', event.target.value)}>
                                            <option value="">Semua mapel</option>
                                            {subjects.map((item) => (
                                                <option key={item.id} value={item.id}>{item.name}</option>
                                            ))}
                                        </select>
                                    </label>

                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Siswa</span>
                                        <select className={fieldClass} value={filters.student_id} onChange={(event) => updateFilter('student_id', event.target.value)}>
                                            <option value="">Semua siswa</option>
                                            {students.map((item) => (
                                                <option key={item.id} value={item.id}>{item.name}</option>
                                            ))}
                                        </select>
                                    </label>

                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Jenis Absensi</span>
                                        <select className={fieldClass} value={filters.type} onChange={(event) => updateFilter('type', event.target.value)}>
                                            <option value="gabungan">Gabungan</option>
                                            <option value="kelas">Absensi Kelas</option>
                                            <option value="mapel">Absensi Mapel</option>
                                        </select>
                                    </label>

                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Tanggal Tepat</span>
                                        <input className={fieldClass} type="date" value={filters.attendance_date} onChange={(event) => updateFilter('attendance_date', event.target.value)} />
                                    </label>

                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Dari Tanggal</span>
                                        <input className={fieldClass} type="date" value={filters.date_from} onChange={(event) => updateFilter('date_from', event.target.value)} />
                                    </label>

                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Sampai Tanggal</span>
                                        <input className={fieldClass} type="date" value={filters.date_to} onChange={(event) => updateFilter('date_to', event.target.value)} />
                                    </label>

                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Status Kehadiran</span>
                                        <select className={fieldClass} value={filters.status} onChange={(event) => updateFilter('status', event.target.value)}>
                                            <option value="">Semua status</option>
                                            {attendanceStatuses.map((item) => (
                                                <option key={item.value} value={item.value}>{item.label}</option>
                                            ))}
                                        </select>
                                    </label>
                                </>
                            )}

                            {(isTeacherRecap || isStudentRecap) && (
                                <label className="flex flex-col gap-1.5">
                                    <span className="text-xs font-semibold text-slate-600">Jenis Kelamin</span>
                                    <select className={fieldClass} value={filters.gender} onChange={(event) => updateFilter('gender', event.target.value)}>
                                        <option value="">Semua gender</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </label>
                            )}

                            {isTeacherRecap && (
                                <>
                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Kategori Guru</span>
                                        <select className={fieldClass} value={filters.category} onChange={(event) => updateFilter('category', event.target.value)}>
                                            <option value="">Semua kategori</option>
                                            {teacherCategories.map((item) => (
                                                <option key={item.value} value={item.value}>{item.label}</option>
                                            ))}
                                        </select>
                                    </label>

                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Status Kepegawaian</span>
                                        <input className={fieldClass} value={filters.employment_status} onChange={(event) => updateFilter('employment_status', event.target.value)} placeholder="PNS, honorer, tetap..." />
                                    </label>
                                </>
                            )}

                            {isStudentRecap && (
                                <>
                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Tingkat</span>
                                        <select className={fieldClass} value={filters.level} onChange={(event) => updateFilter('level', event.target.value)}>
                                            <option value="">Semua tingkat</option>
                                            {levels.map((level) => (
                                                <option key={level} value={level}>{level}</option>
                                            ))}
                                        </select>
                                    </label>

                                    <label className="flex flex-col gap-1.5">
                                        <span className="text-xs font-semibold text-slate-600">Tahun Ajaran</span>
                                        <select className={fieldClass} value={filters.academic_year} onChange={(event) => updateFilter('academic_year', event.target.value)}>
                                            <option value="">Semua tahun</option>
                                            {academicYears.map((year) => (
                                                <option key={year} value={year}>{year}</option>
                                            ))}
                                        </select>
                                    </label>
                                </>
                            )}

                            <label className="flex flex-col gap-1.5 md:col-span-2 xl:col-span-3">
                                <span className="text-xs font-semibold text-slate-600">Cari {isTeacherRecap ? 'Nama / NIK / NIP' : isStudentRecap ? 'Nama / NIK / NISN' : 'Nama Siswa'}</span>
                                <div className="relative">
                                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                    <input className={`${fieldClass} w-full pl-9`} value={filters.search} onChange={(event) => updateFilter('search', event.target.value)} placeholder={isTeacherRecap ? 'Cari guru berdasarkan nama, NIK, atau NIP' : isStudentRecap ? 'Cari siswa berdasarkan nama, NIK, atau NISN' : 'Cari nama siswa pada rekap absen'} />
                                </div>
                            </label>
                        </div>
                    </section>

                    <aside className="space-y-4">
                        <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div className="flex items-center gap-2">
                                <CalendarDays className="h-4 w-4 text-blue-600" />
                                <h2 className="text-sm font-semibold text-slate-900">Ringkasan Pilihan</h2>
                            </div>
                            <dl className="mt-4 space-y-3 text-sm">
                                <div>
                                    <dt className="text-xs font-semibold uppercase text-slate-400">Dataset</dt>
                                    <dd className="mt-0.5 font-semibold text-slate-800">{selectedDataset}</dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-semibold uppercase text-slate-400">Periode</dt>
                                    <dd className="mt-0.5 text-slate-700">{filters.attendance_date || `${filters.date_from || 'awal'} sampai ${filters.date_to || 'akhir'}`}</dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-semibold uppercase text-slate-400">Status</dt>
                                    <dd className="mt-0.5 text-slate-700">{filters.status || 'Semua status'}</dd>
                                </div>
                            </dl>
                        </div>

                        <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div className="flex items-center gap-2">
                                <BarChart3 className="h-4 w-4 text-blue-600" />
                                <h2 className="text-sm font-semibold text-slate-900">Data Tersedia</h2>
                            </div>
                            <div className="mt-4 grid grid-cols-2 gap-2 text-sm">
                                <div className="rounded-lg bg-slate-50 p-3">
                                    <p className="text-xs text-slate-500">Kelas</p>
                                    <p className="text-lg font-bold text-slate-900">{loadingOptions ? '-' : classes.length}</p>
                                </div>
                                <div className="rounded-lg bg-slate-50 p-3">
                                    <p className="text-xs text-slate-500">Mapel</p>
                                    <p className="text-lg font-bold text-slate-900">{loadingOptions ? '-' : subjects.length}</p>
                                </div>
                                <div className="rounded-lg bg-slate-50 p-3">
                                    <p className="text-xs text-slate-500">Guru</p>
                                    <p className="text-lg font-bold text-slate-900">{loadingOptions ? '-' : teachers.length}</p>
                                </div>
                                <div className="rounded-lg bg-slate-50 p-3">
                                    <p className="text-xs text-slate-500">Siswa</p>
                                    <p className="text-lg font-bold text-slate-900">{loadingOptions ? '-' : students.length}</p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    );
};
