import React, { useEffect, useMemo, useState } from 'react';
import { AlertCircle, BarChart3, BookOpen, CalendarDays, FileText, RefreshCw, Search, ChevronDown, ChevronUp } from 'lucide-react';
import { toast } from 'sonner';
import api from '../../services/api';

type AttendanceStatus = 'hadir' | 'sakit' | 'izin' | 'alpha';

interface Subject {
    id: number;
    name: string;
    code?: string | null;
}

interface Teacher {
    id: number;
    name: string;
}

interface SchoolClass {
    id: number;
    name: string;
}

interface StudentSummary {
    id: number;
    name: string;
    nik?: string | null;
    nisn?: string | null;
    school_class?: SchoolClass | null;
}

interface TeachingAssignment {
    id: number;
    day_of_week: number;
    start_time: string;
    end_time: string;
    room?: string | null;
    subject?: Subject;
    teacher?: Teacher;
    school_class?: SchoolClass;
}

interface AttendanceRecord {
    id: number;
    attendance_date: string;
    status: AttendanceStatus;
    notes?: string | null;
    teaching_assignment?: TeachingAssignment;
    school_class?: SchoolClass;
    recorded_by_teacher?: Teacher;
}

interface StudentNote {
    id: number;
    title: string;
    category: string;
    note: string;
    follow_up_at?: string | null;
    resolved_at?: string | null;
    created_at?: string | null;
    teacher?: Teacher | null;
}

const DAY_NAMES = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

const statusMeta: Record<AttendanceStatus, { label: string; className: string }> = {
    hadir: { label: 'Hadir', className: 'border-emerald-200 bg-emerald-50 text-emerald-700' },
    sakit: { label: 'Sakit', className: 'border-amber-200 bg-amber-50 text-amber-700' },
    izin: { label: 'Izin', className: 'border-blue-200 bg-blue-50 text-blue-700' },
    alpha: { label: 'Alpha', className: 'border-rose-200 bg-rose-50 text-rose-700' },
};

const timeLabel = (value?: string | null) => (value ? value.substring(0, 5) : '--:--');

const formatDate = (value?: string | null) => {
    if (!value) return '-';
    return new Date(`${value}T00:00:00`).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

const endpointPrefix = (portal: 'siswa' | 'orang-tua') => portal === 'orang-tua' ? '/orang-tua' : '/siswa';

const useParentChildren = (portal: 'siswa' | 'orang-tua') => {
    const [children, setChildren] = useState<StudentSummary[]>([]);
    const [selectedStudentId, setSelectedStudentId] = useState('');

    useEffect(() => {
        if (portal !== 'orang-tua') return;

        const fetchChildren = async () => {
            try {
                const response = await api.get('/orang-tua/anak');
                const data = response.data.data || [];
                setChildren(data);
                if (data.length > 0) {
                    setSelectedStudentId(String(data[0].id));
                }
            } catch (error) {
                toast.error('Gagal memuat data anak.');
            }
        };

        fetchChildren();
    }, [portal]);

    return { children, selectedStudentId, setSelectedStudentId };
};

const StudentPicker = ({
    portal,
    childrenData,
    selectedStudentId,
    setSelectedStudentId,
}: {
    portal: 'siswa' | 'orang-tua';
    childrenData: StudentSummary[];
    selectedStudentId: string;
    setSelectedStudentId: (value: string) => void;
}) => {
    if (portal !== 'orang-tua' || childrenData.length <= 1) {
        return null;
    }

    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <label className="flex max-w-md flex-col gap-1.5">
                <span className="text-xs font-semibold text-slate-600">Pilih Anak</span>
                <select
                    value={selectedStudentId}
                    onChange={(event) => setSelectedStudentId(event.target.value)}
                    className="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                >
                    {childrenData.map((student) => (
                        <option key={student.id} value={student.id}>
                            {student.name} {student.school_class?.name ? `- ${student.school_class.name}` : ''}
                        </option>
                    ))}
                </select>
            </label>
        </section>
    );
};

const SummaryCards = ({ records }: { records: AttendanceRecord[] }) => {
    const summary = (Object.keys(statusMeta) as AttendanceStatus[]).map((status) => ({
        status,
        ...statusMeta[status],
        total: records.filter((record) => record.status === status).length,
    }));

    return (
        <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
            {summary.map((item) => (
                <div key={item.status} className={`rounded-lg border px-4 py-3 ${item.className}`}>
                    <p className="text-xs font-semibold">{item.label}</p>
                    <p className="mt-1 text-2xl font-bold">{item.total}</p>
                </div>
            ))}
        </div>
    );
};

export const StudentSchedule = ({ portal = 'siswa' }: { portal?: 'siswa' | 'orang-tua' }) => {
    const [schedules, setSchedules] = useState<TeachingAssignment[]>([]);
    const [loading, setLoading] = useState(true);
    // Hari ini terbuka otomatis: JS getDay() (Minggu=0..Sabtu=6) -> index DAY_NAMES (Senin=0..Minggu=6)
    const [openDay, setOpenDay] = useState<string>(DAY_NAMES[(new Date().getDay() + 6) % 7]);
    const { children, selectedStudentId, setSelectedStudentId } = useParentChildren(portal);

    const fetchSchedules = async () => {
        setLoading(true);
        try {
            const response = await api.get(`${endpointPrefix(portal)}/jadwal-sekolah`, {
                params: selectedStudentId ? { student_id: selectedStudentId } : {},
            });
            setSchedules(response.data.data || []);
        } catch (error) {
            toast.error('Gagal memuat jadwal pelajaran.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchSchedules();
    }, [portal, selectedStudentId]);

    const groupedSchedules = useMemo(() => {
        return DAY_NAMES.map((day, index) => ({
            day,
            items: schedules
                .filter((schedule) => schedule.day_of_week === index)
                .sort((a, b) => timeLabel(a.start_time).localeCompare(timeLabel(b.start_time))),
        })).filter((group) => group.items.length > 0);
    }, [schedules]);

    return (
        <div className="min-h-full bg-slate-50 px-2 py-4 sm:p-6">
            <div className="mx-auto max-w-6xl space-y-5">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h1 className="text-xl font-bold text-slate-900 sm:text-2xl">Jadwal Pelajaran</h1>
                        <p className="mt-1 text-sm text-slate-500">Lihat jadwal mata pelajaran, guru, jam, dan ruang kelas.</p>
                    </div>
                    <button onClick={fetchSchedules} disabled={loading} className="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-60">
                        <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
                        Muat Ulang
                    </button>
                </div>

                <StudentPicker portal={portal} childrenData={children} selectedStudentId={selectedStudentId} setSelectedStudentId={setSelectedStudentId} />

                {loading ? (
                    <div className="flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white p-10 text-sm text-slate-500 shadow-sm">
                        <RefreshCw className="h-4 w-4 animate-spin" />
                        Memuat jadwal...
                    </div>
                ) : groupedSchedules.length === 0 ? (
                    <div className="rounded-lg border border-slate-200 bg-white p-10 text-center shadow-sm">
                        <CalendarDays className="mx-auto h-10 w-10 text-slate-300" />
                        <h2 className="mt-3 font-semibold text-slate-900">Belum ada jadwal pelajaran</h2>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {groupedSchedules.map((group) => {
                            const isOpen = openDay === group.day;
                            return (
                                <section key={group.day} className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                                    <button
                                        type="button"
                                        onClick={() => setOpenDay((prev) => (prev === group.day ? '' : group.day))}
                                        aria-expanded={isOpen}
                                        className={`flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition-colors hover:bg-slate-100 ${isOpen ? 'bg-slate-50' : 'bg-white'}`}
                                    >
                                        <div>
                                            <h2 className="font-semibold text-slate-900">{group.day}</h2>
                                            <p className="mt-0.5 text-xs text-slate-500">{group.items.length} mata pelajaran</p>
                                        </div>
                                        {isOpen ? (
                                            <ChevronUp className="h-5 w-5 shrink-0 text-slate-500" />
                                        ) : (
                                            <ChevronDown className="h-5 w-5 shrink-0 text-slate-500" />
                                        )}
                                    </button>
                                    {isOpen && (
                                        <div className="divide-y divide-slate-100 border-t border-slate-200">
                                            {group.items.map((schedule) => (
                                                <div key={schedule.id} className="grid gap-3 p-4 md:grid-cols-[140px_minmax(0,1fr)_180px_120px] md:items-center">
                                                    <div className="text-sm font-semibold text-blue-700">{timeLabel(schedule.start_time)} - {timeLabel(schedule.end_time)}</div>
                                                    <div>
                                                        <p className="font-semibold text-slate-900">{schedule.subject?.name || '-'}</p>
                                                        <p className="mt-0.5 text-xs text-slate-500">{schedule.subject?.code || 'Mata pelajaran'}</p>
                                                    </div>
                                                    <div className="text-sm text-slate-600">{schedule.teacher?.name || '-'}</div>
                                                    <div className="text-sm text-slate-500">{schedule.room || '-'}</div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </section>
                            );
                        })}
                    </div>
                )}
            </div>
        </div>
    );
};

export const StudentSubjectAttendance = ({ portal = 'siswa' }: { portal?: 'siswa' | 'orang-tua' }) => {
    const [records, setRecords] = useState<AttendanceRecord[]>([]);
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(true);
    const [expanded, setExpanded] = useState<Record<number, boolean>>({});
    const { children, selectedStudentId, setSelectedStudentId } = useParentChildren(portal);

    const fetchRecords = async () => {
        setLoading(true);
        try {
            const response = await api.get(`${endpointPrefix(portal)}/absensi-mapel`, {
                params: selectedStudentId ? { student_id: selectedStudentId } : {},
            });
            setRecords(response.data.data || []);
        } catch (error) {
            toast.error('Gagal memuat absensi mapel.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchRecords();
    }, [portal, selectedStudentId]);

    const filteredRecords = useMemo(() => {
        const keyword = query.trim().toLowerCase();
        if (!keyword) return records;

        return records.filter((record) => [
            record.teaching_assignment?.subject?.name || '',
            record.teaching_assignment?.teacher?.name || '',
            record.attendance_date,
            statusMeta[record.status]?.label || record.status,
        ].some((value) => value.toLowerCase().includes(keyword)));
    }, [records, query]);

    const subjectGroups = useMemo(() => {
        const groups = new Map<number, {
            key: number;
            subjectName: string;
            subjectCode: string;
            teacherName: string;
            hadir: number;
            sakit: number;
            izin: number;
            alpha: number;
            total: number;
            percentage: number;
            records: AttendanceRecord[];
        }>();

        filteredRecords.forEach((record) => {
            const key = record.teaching_assignment?.id || 0;
            const subjectName = record.teaching_assignment?.subject?.name || 'Mata Pelajaran';
            const subjectCode = record.teaching_assignment?.subject?.code || '-';
            const teacherName = record.teaching_assignment?.teacher?.name || '-';

            if (!groups.has(key)) {
                groups.set(key, {
                    key,
                    subjectName,
                    subjectCode,
                    teacherName,
                    hadir: 0,
                    sakit: 0,
                    izin: 0,
                    alpha: 0,
                    total: 0,
                    percentage: 0,
                    records: [],
                });
            }

            const g = groups.get(key)!;
            g.records.push(record);
            g.total += 1;

            if (record.status === 'hadir') g.hadir += 1;
            else if (record.status === 'sakit') g.sakit += 1;
            else if (record.status === 'izin') g.izin += 1;
            else if (record.status === 'alpha') g.alpha += 1;
        });

        return Array.from(groups.values()).map((g) => {
            g.percentage = g.total > 0 ? Math.round((g.hadir / g.total) * 100) : 0;
            g.records.sort((a, b) => b.attendance_date.localeCompare(a.attendance_date));
            return g;
        });
    }, [filteredRecords]);

    const toggleExpand = (key: number) => {
        setExpanded((prev) => ({
            ...prev,
            [key]: !prev[key],
        }));
    };

    return (
        <AttendanceListPage
            title="Absensi Mapel"
            description="Lihat absensi di setiap pertemuan untuk masing-masing mata pelajaran."
            icon={BookOpen}
            loading={loading}
            query={query}
            setQuery={setQuery}
            refresh={fetchRecords}
            records={filteredRecords}
            portal={portal}
            childrenData={children}
            selectedStudentId={selectedStudentId}
            setSelectedStudentId={setSelectedStudentId}
        >
            <div className="space-y-4">
                {subjectGroups.map((group) => {
                    const isExpanded = !!expanded[group.key];
                    const percentColor = group.percentage >= 85
                        ? 'bg-emerald-500'
                        : group.percentage >= 75
                        ? 'bg-amber-500'
                        : 'bg-rose-500';

                    return (
                        <section key={group.key} className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:shadow-md">
                            {/* Subject Header / Summary Card */}
                            <div
                                onClick={() => toggleExpand(group.key)}
                                className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between cursor-pointer select-none bg-white hover:bg-slate-50/50 transition-colors"
                            >
                                <div className="space-y-1 min-w-0 flex-1">
                                    <div className="flex items-center gap-2">
                                        <h2 className="font-bold text-slate-800 text-base">{group.subjectName}</h2>
                                        <span className="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">
                                            {group.subjectCode}
                                        </span>
                                    </div>
                                    <p className="text-xs text-slate-500 font-medium">Guru Pengampu: {group.teacherName}</p>
                                </div>

                                <div className="flex flex-wrap items-center gap-4">
                                    {/* Stats grid */}
                                    <div className="flex items-center gap-1.5 text-center">
                                        <div className="rounded-lg border border-emerald-100 bg-emerald-50/50 px-2.5 py-1 min-w-[45px]">
                                            <p className="text-[9px] font-bold text-emerald-600 uppercase">Hadir</p>
                                            <p className="text-xs font-bold text-emerald-700 mt-0.5">{group.hadir}</p>
                                        </div>
                                        <div className="rounded-lg border border-amber-100 bg-amber-50/50 px-2.5 py-1 min-w-[45px]">
                                            <p className="text-[9px] font-bold text-amber-600 uppercase">Sakit</p>
                                            <p className="text-xs font-bold text-amber-700 mt-0.5">{group.sakit}</p>
                                        </div>
                                        <div className="rounded-lg border border-blue-100 bg-blue-50/50 px-2.5 py-1 min-w-[45px]">
                                            <p className="text-[9px] font-bold text-blue-600 uppercase">Izin</p>
                                            <p className="text-xs font-bold text-blue-700 mt-0.5">{group.izin}</p>
                                        </div>
                                        <div className="rounded-lg border border-rose-100 bg-rose-50/50 px-2.5 py-1 min-w-[45px]">
                                            <p className="text-[9px] font-bold text-rose-600 uppercase">Alpha</p>
                                            <p className="text-xs font-bold text-rose-700 mt-0.5">{group.alpha}</p>
                                        </div>
                                    </div>

                                    {/* Percentage Gauge */}
                                    <div className="flex items-center gap-2.5 pl-2 sm:border-l sm:border-slate-100">
                                        <div className="text-right">
                                            <p className="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Kehadiran</p>
                                            <p className="text-base font-extrabold text-slate-800">{group.percentage}%</p>
                                        </div>
                                        <div className="w-1.5 h-8 bg-slate-100 rounded-full overflow-hidden">
                                            <div className={`w-full rounded-full transition-all duration-300 ${percentColor}`} style={{ height: `${group.percentage}%` }} />
                                        </div>
                                    </div>

                                    {/* Chevron icon */}
                                    <div className="text-slate-400 group-hover:text-slate-600 transition-colors ml-1">
                                        {isExpanded ? <ChevronUp className="h-5 w-5" /> : <ChevronDown className="h-5 w-5" />}
                                    </div>
                                </div>
                            </div>

                            {/* Meeting list detail dropdown */}
                            {isExpanded && (
                                <div className="border-t border-slate-100 bg-slate-50/30">
                                    <div className="px-5 py-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        <span>Tanggal Pertemuan</span>
                                        <span>Status Kehadiran</span>
                                    </div>
                                    <RecordRows records={group.records} />
                                </div>
                            )}
                        </section>
                    );
                })}
            </div>
        </AttendanceListPage>
    );
};

export const StudentClassAttendance = ({ portal = 'siswa' }: { portal?: 'siswa' | 'orang-tua' }) => {
    const [records, setRecords] = useState<AttendanceRecord[]>([]);
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(true);
    const [expanded, setExpanded] = useState<Record<number, boolean>>({});
    const { children, selectedStudentId, setSelectedStudentId } = useParentChildren(portal);

    const fetchRecords = async () => {
        setLoading(true);
        try {
            const response = await api.get(`${endpointPrefix(portal)}/absensi-kelas`, {
                params: selectedStudentId ? { student_id: selectedStudentId } : {},
            });
            setRecords(response.data.data || []);
        } catch (error) {
            toast.error('Gagal memuat absensi kelas.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchRecords();
    }, [portal, selectedStudentId]);

    const filteredRecords = useMemo(() => {
        const keyword = query.trim().toLowerCase();
        if (!keyword) return records;

        return records.filter((record) => [
            record.school_class?.name || '',
            record.attendance_date,
            statusMeta[record.status]?.label || record.status,
            record.notes || '',
        ].some((value) => value.toLowerCase().includes(keyword)));
    }, [records, query]);

    const classGroups = useMemo(() => {
        const groups = new Map<number, {
            key: number;
            className: string;
            homeroomTeacher: string;
            hadir: number;
            sakit: number;
            izin: number;
            alpha: number;
            total: number;
            percentage: number;
            records: AttendanceRecord[];
        }>();

        filteredRecords.forEach((record) => {
            const key = record.school_class?.id || 0;
            const className = record.school_class?.name || 'Kelas';
            const teacherName = record.recorded_by_teacher?.name || '-';

            if (!groups.has(key)) {
                groups.set(key, {
                    key,
                    className,
                    homeroomTeacher: teacherName,
                    hadir: 0,
                    sakit: 0,
                    izin: 0,
                    alpha: 0,
                    total: 0,
                    percentage: 0,
                    records: [],
                });
            }

            const g = groups.get(key)!;
            g.records.push(record);
            g.total += 1;

            if (record.status === 'hadir') g.hadir += 1;
            else if (record.status === 'sakit') g.sakit += 1;
            else if (record.status === 'izin') g.izin += 1;
            else if (record.status === 'alpha') g.alpha += 1;
        });

        return Array.from(groups.values()).map((g) => {
            g.percentage = g.total > 0 ? Math.round((g.hadir / g.total) * 100) : 0;
            g.records.sort((a, b) => b.attendance_date.localeCompare(a.attendance_date));
            return g;
        });
    }, [filteredRecords]);

    const toggleExpand = (key: number) => {
        setExpanded((prev) => ({
            ...prev,
            [key]: !prev[key],
        }));
    };

    return (
        <AttendanceListPage
            title="Absensi Kelas"
            description="Lihat riwayat absensi harian kelas per pertemuan."
            icon={BarChart3}
            loading={loading}
            query={query}
            setQuery={setQuery}
            refresh={fetchRecords}
            records={filteredRecords}
            portal={portal}
            childrenData={children}
            selectedStudentId={selectedStudentId}
            setSelectedStudentId={setSelectedStudentId}
        >
            <div className="space-y-4">
                {classGroups.map((group) => {
                    const isExpanded = !!expanded[group.key];
                    const percentColor = group.percentage >= 85
                        ? 'bg-emerald-500'
                        : group.percentage >= 75
                        ? 'bg-amber-500'
                        : 'bg-rose-500';

                    return (
                        <section key={group.key} className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:shadow-md">
                            {/* Class Header / Summary Card */}
                            <div
                                onClick={() => toggleExpand(group.key)}
                                className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between cursor-pointer select-none bg-white hover:bg-slate-50/50 transition-colors"
                            >
                                <div className="space-y-1 min-w-0 flex-1">
                                    <h2 className="font-bold text-slate-800 text-base">{group.className}</h2>
                                    {group.homeroomTeacher !== '-' && (
                                        <p className="text-xs text-slate-500 font-medium">Dicatat Oleh / Wali Kelas: {group.homeroomTeacher}</p>
                                    )}
                                </div>

                                <div className="flex flex-wrap items-center gap-4">
                                    {/* Stats grid */}
                                    <div className="flex items-center gap-1.5 text-center">
                                        <div className="rounded-lg border border-emerald-100 bg-emerald-50/50 px-2.5 py-1 min-w-[45px]">
                                            <p className="text-[9px] font-bold text-emerald-600 uppercase">Hadir</p>
                                            <p className="text-xs font-bold text-emerald-700 mt-0.5">{group.hadir}</p>
                                        </div>
                                        <div className="rounded-lg border border-amber-100 bg-amber-50/50 px-2.5 py-1 min-w-[45px]">
                                            <p className="text-[9px] font-bold text-amber-600 uppercase">Sakit</p>
                                            <p className="text-xs font-bold text-amber-700 mt-0.5">{group.sakit}</p>
                                        </div>
                                        <div className="rounded-lg border border-blue-100 bg-blue-50/50 px-2.5 py-1 min-w-[45px]">
                                            <p className="text-[9px] font-bold text-blue-600 uppercase">Izin</p>
                                            <p className="text-xs font-bold text-blue-700 mt-0.5">{group.izin}</p>
                                        </div>
                                        <div className="rounded-lg border border-rose-100 bg-rose-50/50 px-2.5 py-1 min-w-[45px]">
                                            <p className="text-[9px] font-bold text-rose-600 uppercase">Alpha</p>
                                            <p className="text-xs font-bold text-rose-700 mt-0.5">{group.alpha}</p>
                                        </div>
                                    </div>

                                    {/* Percentage Gauge */}
                                    <div className="flex items-center gap-2.5 pl-2 sm:border-l sm:border-slate-100">
                                        <div className="text-right">
                                            <p className="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Kehadiran</p>
                                            <p className="text-base font-extrabold text-slate-800">{group.percentage}%</p>
                                        </div>
                                        <div className="w-1.5 h-8 bg-slate-100 rounded-full overflow-hidden">
                                            <div className={`w-full rounded-full transition-all duration-300 ${percentColor}`} style={{ height: `${group.percentage}%` }} />
                                        </div>
                                    </div>

                                    {/* Chevron icon */}
                                    <div className="text-slate-400 group-hover:text-slate-600 transition-colors ml-1">
                                        {isExpanded ? <ChevronUp className="h-5 w-5" /> : <ChevronDown className="h-5 w-5" />}
                                    </div>
                                </div>
                            </div>

                            {/* Meeting list detail dropdown */}
                            {isExpanded && (
                                <div className="border-t border-slate-100 bg-slate-50/30">
                                    <div className="px-5 py-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        <span>Tanggal Pertemuan</span>
                                        <span>Status Kehadiran</span>
                                    </div>
                                    <RecordRows records={group.records} showClass={false} />
                                </div>
                            )}
                        </section>
                    );
                })}
            </div>
        </AttendanceListPage>
    );
};

const AttendanceListPage = ({
    title,
    description,
    icon: Icon,
    loading,
    query,
    setQuery,
    refresh,
    records,
    portal = 'siswa',
    childrenData = [],
    selectedStudentId = '',
    setSelectedStudentId = () => undefined,
    children,
}: {
    title: string;
    description: string;
    icon: any;
    loading: boolean;
    query: string;
    setQuery: (value: string) => void;
    refresh: () => void;
    records: AttendanceRecord[];
    portal?: 'siswa' | 'orang-tua';
    childrenData?: StudentSummary[];
    selectedStudentId?: string;
    setSelectedStudentId?: (value: string) => void;
    children: React.ReactNode;
}) => (
    <div className="min-h-full bg-slate-50 px-2 py-4 sm:p-6">
        <div className="mx-auto max-w-6xl space-y-5">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 className="text-xl font-bold text-slate-900 sm:text-2xl">{title}</h1>
                    <p className="mt-1 text-sm text-slate-500">{description}</p>
                </div>
                <button onClick={refresh} disabled={loading} className="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-60">
                    <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
                    Muat Ulang
                </button>
            </div>

            <StudentPicker portal={portal} childrenData={childrenData} selectedStudentId={selectedStudentId} setSelectedStudentId={setSelectedStudentId} />

            <SummaryCards records={records} />

            <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div className="relative">
                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        placeholder="Cari mapel, tanggal, status, atau catatan..."
                        className="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    />
                </div>
            </section>

            {loading ? (
                <div className="flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white p-10 text-sm text-slate-500 shadow-sm">
                    <RefreshCw className="h-4 w-4 animate-spin" />
                    Memuat data absensi...
                </div>
            ) : records.length === 0 ? (
                <div className="rounded-lg border border-slate-200 bg-white p-10 text-center shadow-sm">
                    <Icon className="mx-auto h-10 w-10 text-slate-300" />
                    <h2 className="mt-3 font-semibold text-slate-900">Belum ada data</h2>
                    <p className="mt-1 text-sm text-slate-500">Data akan muncul setelah absensi dicatat.</p>
                </div>
            ) : (
                <div className="space-y-4">{children}</div>
            )}
        </div>
    </div>
);

const RecordRows = ({ records, showClass = false }: { records: AttendanceRecord[]; showClass?: boolean }) => (
    <div className="divide-y divide-slate-100">
        {records.map((record) => {
            const meta = statusMeta[record.status] || { label: record.status, className: 'border-slate-200 bg-white text-slate-600' };

            return (
                <div key={record.id} className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="font-semibold text-slate-900">{formatDate(record.attendance_date)}</p>
                        {showClass && <p className="mt-0.5 text-xs text-slate-500">Kelas: {record.school_class?.name || '-'}</p>}
                        {record.notes && <p className="mt-1 text-xs text-slate-500">{record.notes}</p>}
                    </div>
                    <span className={`w-fit rounded border px-2.5 py-1 text-xs font-bold ${meta.className}`}>
                        {meta.label}
                    </span>
                </div>
            );
        })}
    </div>
);

export const StudentNotes = ({ portal = 'siswa' }: { portal?: 'siswa' | 'orang-tua' }) => {
    const [notes, setNotes] = useState<StudentNote[]>([]);
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(true);
    const { children, selectedStudentId, setSelectedStudentId } = useParentChildren(portal);

    const fetchNotes = async () => {
        setLoading(true);
        try {
            const response = await api.get(`${endpointPrefix(portal)}/catatan`, {
                params: selectedStudentId ? { student_id: selectedStudentId } : {},
            });
            setNotes(response.data.data || []);
        } catch (error) {
            toast.error('Gagal memuat catatan siswa.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchNotes();
    }, [portal, selectedStudentId]);

    const filteredNotes = useMemo(() => {
        const keyword = query.trim().toLowerCase();
        if (!keyword) return notes;

        return notes.filter((note) => [
            note.title,
            note.category,
            note.note,
            note.teacher?.name || '',
        ].some((value) => value.toLowerCase().includes(keyword)));
    }, [notes, query]);

    return (
        <div className="min-h-full bg-slate-50 px-2 py-4 sm:p-6">
            <div className="mx-auto max-w-5xl space-y-5">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h1 className="text-xl font-bold text-slate-900 sm:text-2xl">Catatan Siswa</h1>
                        <p className="mt-1 text-sm text-slate-500">Lihat catatan dan tindak lanjut yang diberikan guru atau wali kelas.</p>
                    </div>
                    <button onClick={fetchNotes} disabled={loading} className="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-60">
                        <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
                        Muat Ulang
                    </button>
                </div>

                <StudentPicker portal={portal} childrenData={children} selectedStudentId={selectedStudentId} setSelectedStudentId={setSelectedStudentId} />

                <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div className="relative">
                        <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder="Cari judul, kategori, guru, atau isi catatan..."
                            className="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>
                </section>

                {loading ? (
                    <div className="flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white p-10 text-sm text-slate-500 shadow-sm">
                        <RefreshCw className="h-4 w-4 animate-spin" />
                        Memuat catatan...
                    </div>
                ) : filteredNotes.length === 0 ? (
                    <div className="rounded-lg border border-slate-200 bg-white p-10 text-center shadow-sm">
                        <FileText className="mx-auto h-10 w-10 text-slate-300" />
                        <h2 className="mt-3 font-semibold text-slate-900">Belum ada catatan</h2>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {filteredNotes.map((note) => (
                            <section key={note.id} className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h2 className="font-semibold text-slate-900">{note.title}</h2>
                                        <p className="mt-1 text-xs text-slate-500">Kategori: {note.category} - Guru: {note.teacher?.name || '-'}</p>
                                    </div>
                                    <span className={`w-fit rounded border px-2.5 py-1 text-xs font-bold ${note.resolved_at ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'}`}>
                                        {note.resolved_at ? 'Selesai' : 'Terbuka'}
                                    </span>
                                </div>
                                <p className="mt-3 whitespace-pre-wrap text-sm text-slate-700">{note.note}</p>
                                {note.follow_up_at && <p className="mt-3 text-xs font-semibold text-slate-500">Tindak lanjut: {formatDate(note.follow_up_at)}</p>}
                            </section>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
};
