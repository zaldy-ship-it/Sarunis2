import React, { useState, useEffect, useCallback, useMemo } from 'react';
import {
    GraduationCap, Users, BookOpen, Calendar,
    RefreshCw, ClipboardList, CheckCircle2, AlertCircle, Speaker, CalendarDays, BarChart2
} from 'lucide-react';
import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer
} from 'recharts';
import { cn } from '../../components/ui/utils';
import api from '../../services/api';
import { toast } from 'sonner';

function Crd({ children, className, np }: { children: any; className?: string; np?: boolean }) {
    return <div className={cn("bg-white rounded-xl border border-slate-200 shadow-sm", !np && "p-5", className)}>{children}</div>;
}

function StatCard({
    title, value, sub, icon: Icon, bg, iconColor
}: {
    title: string; value: string | number; sub?: string;
    icon: any; bg: string; iconColor: string;
}) {
    return (
        <Crd>
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0 flex-1">
                    <p className="text-xs text-slate-500 mb-1 truncate font-medium">{title}</p>
                    <p className="text-2xl font-bold text-slate-900 leading-none tracking-tight">{value}</p>
                    {sub && <p className="text-[11px] text-slate-400 mt-1">{sub}</p>}
                </div>
                <div className={cn("w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0", bg)}>
                    <Icon className={cn("w-5 h-5", iconColor)} />
                </div>
            </div>
        </Crd>
    );
}

interface StudentDashboardData {
    hero: { title: string; subtitle: string; badge: string; asset: string };
    summary: { label: string; value: number | string; meta?: string }[];
    todayLabel: string;
    studentClassName?: string;
    scheduleRows: {
        lesson_period: number;
        time: string;
        subject: string;
        teacher: string;
        status: string;
    }[];
    attendanceRows: {
        date: string;
        subject: string;
        status: string;
        notes: string;
    }[];
    subjectStats?: {
        subject_name: string;
        hadir: number;
        sakit: number;
        izin: number;
        alpha: number;
    }[];
    checklist: string[];
    announcements: { id: number; title: string; content: string; created_at: string; author_name?: string }[];
    activeAcademicYear: string;
    activeSemester: string;
}

export const StudentDashboard = () => {
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [data, setData] = useState<StudentDashboardData | null>(null);

    const fetchData = useCallback(async (silent = false) => {
        if (!silent) setLoading(true);
        else setRefreshing(true);
        try {
            const res = await api.get('/siswa/dashboard');
            setData(res.data);
        } catch (err) {
            console.error('Student dashboard fetch error:', err);
            if (!silent) toast.error('Gagal memuat data dashboard.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const attendanceRate = useMemo(() => {
        if (!data?.summary) return 0;
        const hadirCard = data.summary.find(item => item.label === 'Hadir');
        const sakitAlphaCard = data.summary.find(item => item.label === 'Sakit/Alpha');
        const izinCard = data.summary.find(item => item.label === 'Izin');

        const hadir = Number(hadirCard?.value || 0);
        const sakitAlpha = Number(sakitAlphaCard?.value || 0);
        const izin = Number(izinCard?.value || 0);
        
        const total = hadir + sakitAlpha + izin;
        return total > 0 ? Math.round((hadir / total) * 100) : 100;
    }, [data]);

    if (loading || !data) {
        return (
            <div className="flex flex-col items-center justify-center h-64 gap-3">
                <RefreshCw className="w-8 h-8 text-blue-500 animate-spin" />
                <p className="text-sm text-slate-500">Memuat dashboard siswa...</p>
            </div>
        );
    }

    const { hero, summary, todayLabel, studentClassName, scheduleRows, attendanceRows, subjectStats = [], checklist, announcements } = data;

    return (
        <div className="p-4 md:p-6 space-y-5 max-w-7xl mx-auto">
            {/* Hero Card */}
            <div className="bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-2xl p-6 shadow-md relative overflow-hidden">
                <div className="absolute right-0 top-0 bottom-0 w-1/3 opacity-10 flex items-center justify-center pointer-events-none">
                    <GraduationCap className="w-64 h-64" />
                </div>
                <div className="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div className="space-y-1.5">
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur-sm">
                            {hero.badge || todayLabel}
                        </span>
                        <h1 className="text-2xl font-bold">{hero.title}</h1>
                        <p className="text-emerald-100 text-sm max-w-xl">{hero.subtitle}</p>
                    </div>
                    <button
                        onClick={() => fetchData(true)}
                        disabled={refreshing}
                        className="inline-flex items-center gap-1.5 text-sm font-semibold px-4 py-2 rounded-xl bg-white text-emerald-700 hover:bg-emerald-50 transition-all disabled:opacity-50 flex-shrink-0 self-start md:self-center"
                    >
                        <RefreshCw className={cn("w-4 h-4", refreshing && "animate-spin")} />
                        Refresh Data
                    </button>
                </div>
            </div>

            {/* Metrics */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                <StatCard
                    title="Kelas Kamu"
                    value={studentClassName || '-'}
                    sub="Kelas aktif semester ini"
                    icon={Users}
                    bg="bg-blue-50"
                    iconColor="text-blue-600"
                />
                <StatCard
                    title="Persentase Hadir"
                    value={`${attendanceRate}%`}
                    sub="Berdasarkan absensi kelas"
                    icon={CheckCircle2}
                    bg="bg-emerald-50"
                    iconColor="text-emerald-600"
                />
                {summary.filter(item => item.label !== 'Kelas').map((item) => {
                    const iconMap: Record<string, any> = {
                        'Hadir': CheckCircle2,
                        'Izin': ClipboardList,
                        'Sakit/Alpha': AlertCircle,
                    };
                    const bgMap: Record<string, string> = {
                        'Hadir': 'bg-emerald-50',
                        'Izin': 'bg-violet-50',
                        'Sakit/Alpha': 'bg-red-50',
                    };
                    const colorMap: Record<string, string> = {
                        'Hadir': 'text-emerald-600',
                        'Izin': 'text-violet-600',
                        'Sakit/Alpha': 'text-red-600',
                    };
                    const Icon = iconMap[item.label] || BookOpen;
                    return (
                        <StatCard
                            key={item.label}
                            title={item.label === 'Sakit/Alpha' ? 'Sakit & Alpha' : item.label}
                            value={item.value}
                            sub={item.meta}
                            icon={Icon}
                            bg={bgMap[item.label] || 'bg-slate-50'}
                            iconColor={colorMap[item.label] || 'text-slate-600'}
                        />
                    );
                })}
            </div>

            {/* Split Layout */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
                
                {/* Left Area (2 cols) */}
                <div className="lg:col-span-2 space-y-5">
                    
                    {/* Recharts Bar Chart */}
                    <Crd className="flex flex-col">
                        <div className="mb-4">
                            <h3 className="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <BarChart2 className="w-4 h-4 text-emerald-600" />
                                Grafik Kehadiran Mata Pelajaran
                            </h3>
                            <p className="text-xs text-slate-400 mt-0.5">Analisis riwayat pertemuan absensi tiap mata pelajaran</p>
                        </div>
                        <div className="w-full h-[280px]">
                            {subjectStats.length === 0 ? (
                                <div className="flex flex-col items-center justify-center h-full text-slate-400 text-sm">
                                    <ClipboardList className="w-8 h-8 opacity-30 mb-2" />
                                    Belum ada data kehadiran mata pelajaran terekam.
                                </div>
                            ) : (
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart
                                        data={subjectStats}
                                        margin={{ top: 10, right: 10, left: -20, bottom: 0 }}
                                    >
                                        <CartesianGrid strokeDasharray="3 3" vertical={false} />
                                        <XAxis dataKey="subject_name" tick={{ fontSize: 11 }} />
                                        <YAxis tick={{ fontSize: 11 }} allowDecimals={false} />
                                        <Tooltip />
                                        <Legend wrapperStyle={{ fontSize: 11 }} />
                                        <Bar dataKey="hadir" stackId="a" fill="#10b981" name="Hadir" />
                                        <Bar dataKey="sakit" stackId="a" fill="#f59e0b" name="Sakit" />
                                        <Bar dataKey="izin" stackId="a" fill="#3b82f6" name="Izin" />
                                        <Bar dataKey="alpha" stackId="a" fill="#ef4444" name="Alpha" />
                                    </BarChart>
                                </ResponsiveContainer>
                            )}
                        </div>
                    </Crd>

                    {/* Today's Schedule Timeline */}
                    <Crd np className="flex flex-col">
                        <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 className="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <Calendar className="w-4 h-4 text-emerald-600" />
                                Jadwal Pelajaran Hari Ini
                            </h3>
                            <span className="text-xs text-slate-400 font-medium">{todayLabel}</span>
                        </div>
                        <div className="p-4">
                            {scheduleRows.length === 0 ? (
                                <div className="py-8 text-center text-slate-400 text-sm">
                                    <CalendarDays className="w-8 h-8 mx-auto opacity-30 mb-2" />
                                    Tidak ada jadwal pelajaran hari ini.
                                </div>
                            ) : (
                                <div className="space-y-4 relative before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-100">
                                    {scheduleRows.map((row, idx) => (
                                        <div key={idx} className="flex items-start gap-4 relative pl-5">
                                            <span className="absolute left-[3px] top-[7px] w-2.5 h-2.5 rounded-full border-2 border-emerald-500 bg-white" />
                                            <div className="flex-1 bg-slate-50/50 hover:bg-slate-50 border border-slate-100 rounded-xl p-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 transition-all">
                                                <div>
                                                    <span className="text-[10px] uppercase font-bold tracking-wider text-slate-400">Jam ke-{row.lesson_period} · {row.time}</span>
                                                    <h4 className="font-bold text-slate-800 text-sm mt-0.5">{row.subject}</h4>
                                                    <p className="text-xs text-slate-500 font-medium">Guru: {row.teacher}</p>
                                                </div>
                                                <span className={cn(
                                                    "w-fit text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider",
                                                    row.status === 'selesai' ? "bg-emerald-50 text-emerald-700" : "bg-blue-50 text-blue-700"
                                                )}>
                                                    {row.status}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </Crd>
                </div>

                {/* Right Area (1 col) */}
                <div className="space-y-5">
                    
                    {/* Announcements */}
                    <Crd np>
                        <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 className="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <Speaker className="w-4 h-4 text-emerald-500" />
                                Pengumuman Sekolah
                            </h3>
                        </div>
                        <div className="divide-y divide-slate-100 max-h-72 overflow-y-auto">
                            {announcements.length === 0 ? (
                                <div className="p-5 text-center text-slate-400 text-xs">
                                    Tidak ada pengumuman terbaru.
                                </div>
                            ) : announcements.map((ann) => (
                                <div key={ann.id} className="p-4 hover:bg-slate-50 transition-colors">
                                    <h4 className="text-xs font-bold text-slate-800 line-clamp-1">{ann.title}</h4>
                                    <p className="text-xs text-slate-500 mt-1 line-clamp-2">{ann.content}</p>
                                    <p className="text-[10px] text-slate-400 mt-2 font-medium">
                                        {ann.author_name ? `${ann.author_name} · ` : ''}{ann.created_at}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </Crd>

                    {/* Student Guidelines / Tips */}
                    <Crd className="bg-emerald-50/30 border-emerald-100">
                        <h4 className="text-xs font-bold text-emerald-800 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                            <CheckCircle2 className="w-4 h-4 text-emerald-600" /> Checklist Harian Kamu
                        </h4>
                        <ul className="space-y-2.5">
                            {checklist.map((item, idx) => (
                                <li key={idx} className="flex items-start gap-2.5 text-xs text-slate-700 font-medium">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-600 mt-0.5 flex-shrink-0" />
                                    <span>{item}</span>
                                </li>
                            ))}
                        </ul>
                    </Crd>
                </div>
            </div>
        </div>
    );
};
