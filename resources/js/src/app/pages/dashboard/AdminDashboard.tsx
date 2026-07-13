import React from 'react';
import { 
    GraduationCap, Users, BookOpen, ClipboardList, TrendingUp, Download, Plus 
} from 'lucide-react';
import { 
    AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, 
    PieChart, Pie, Cell 
} from 'recharts';
import { cn } from '../../components/ui/utils';

// --- Local Components based on App.tsx UI Primitives ---
function Crd({ children, className, np }: { children: any; className?: string; np?: boolean }) {
    return <div className={cn("bg-white rounded-xl border border-slate-200 shadow-sm", !np && "p-5", className)}>{children}</div>;
}

function StatCard({ title, value, change, icon: Icon, bg }: { title: string; value: string; change?: string; icon: any; bg: string }) {
    const pos = change?.startsWith("+");
    return (
        <Crd>
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0">
                    <p className="text-xs text-slate-500 mb-1 truncate">{title}</p>
                    <p className="text-2xl font-bold text-slate-900 leading-none">{value}</p>
                    {change && (
                        <p className={cn("text-xs mt-2 flex items-center gap-1 font-medium", pos ? "text-green-600" : "text-red-500")}>
                            <TrendingUp className={cn("w-3 h-3", !pos && "rotate-180")} />
                            {change} dari bulan lalu
                        </p>
                    )}
                </div>
                <div className={cn("w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0", bg)}>
                    <Icon className="w-5 h-5" />
                </div>
            </div>
        </Crd>
    );
}

function Bdg({ v = "default", children, className }: { v?: "default"|"success"|"warning"|"danger"|"info"|"outline"; children: any; className?: string }) {
    const vs = {
        default: "bg-slate-100 text-slate-700", success: "bg-green-100 text-green-700",
        warning: "bg-amber-100 text-amber-700",  danger:  "bg-red-100 text-red-700",
        info:    "bg-blue-100 text-blue-700",    outline: "border border-slate-300 text-slate-600 bg-transparent",
    };
    return <span className={cn("inline-flex items-center px-2 py-0.5 rounded text-xs font-medium", vs[v], className)}>{children}</span>;
}

function StBdg({ st }: { st: string }) {
    const map: Record<string, {v: "default"|"success"|"warning"|"danger"|"info"|"outline"; l: string}> = {
        hadir:       { v: "success", l: "Hadir"     },
        sakit:       { v: "warning", l: "Sakit"     },
        terlambat:   { v: "info",    l: "Terlambat" },
        alpa:        { v: "danger",  l: "Alpa"      },
        izin:        { v: "default", l: "Izin"      },
        "Aktif":     { v: "success", l: "Aktif"     },
        "Non-Aktif": { v: "danger",  l: "Non-Aktif" },
    };
    const item = map[st] || { v: "default", l: st };
    return <Bdg v={item.v}>{item.l}</Bdg>;
}

// --- Mock Data ---
const ATTEND_CHART = [
    { month: "Jan", hadir: 94 }, { month: "Feb", hadir: 91 }, { month: "Mar", hadir: 96 },
    { month: "Apr", hadir: 93 }, { month: "Mei", hadir: 97 }, { month: "Jun", hadir: 89 },
];
const GRADE_PIE = [
    { name: "A (90-100)", value: 234, color: "#22C55E" },
    { name: "B (75-89)",  value: 456, color: "#3B82F6" },
    { name: "C (60-74)",  value: 312, color: "#F59E0B" },
    { name: "D (< 60)",   value: 89,  color: "#EF4444" },
];
const RECENT_ACT = [
    { id: 1, student: "Andi Pratama",    cls: "9A", action: "Masuk kelas",  time: "08:05", st: "hadir"     },
    { id: 2, student: "Budi Santoso",    cls: "8B", action: "Sakit",        time: "07:30", st: "sakit"     },
    { id: 3, student: "Citra Dewi",      cls: "7C", action: "Terlambat",    time: "08:20", st: "terlambat" },
    { id: 4, student: "Dina Marlina",    cls: "9A", action: "Masuk kelas",  time: "07:45", st: "hadir"     },
    { id: 5, student: "Eko Prasetyo",    cls: "8A", action: "Tidak hadir",  time: "—",     st: "alpa"      },
];
const STUDENTS_DATA = [
    { id: 1, name: "Andi Pratama",    nisn: "0051234567", cls: "9A", gender: "L", status: "Aktif",     pct: 95, gpa: 88.5 },
    { id: 2, name: "Budi Santoso",    nisn: "0051234568", cls: "8B", gender: "L", status: "Aktif",     pct: 87, gpa: 76.2 },
    { id: 3, name: "Citra Dewi",      nisn: "0051234569", cls: "7C", gender: "P", status: "Aktif",     pct: 92, gpa: 91.3 },
    { id: 4, name: "Dina Marlina",    nisn: "0051234570", cls: "9A", gender: "P", status: "Aktif",     pct: 98, gpa: 94.7 },
    { id: 5, name: "Eko Prasetyo",    nisn: "0051234571", cls: "8A", gender: "L", status: "Non-Aktif", pct: 72, gpa: 65.8 },
];

export const AdminDashboard = () => {
    return (
        <div className="p-4 md:p-6 space-y-5 max-w-7xl mx-auto">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                <h1 className="text-lg font-bold text-slate-900">Dashboard</h1>
                <p className="text-sm text-slate-500 mt-0.5">Ringkasan aktivitas sekolah — Senin, 13 Juli 2026</p>
                </div>
                <div className="flex gap-2">
                <button className="inline-flex items-center justify-center gap-2 font-medium rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 border border-slate-300 text-slate-700 hover:bg-slate-50 focus:ring-slate-300 bg-white text-sm px-4 py-2 h-9">
                    <Download className="w-3.5 h-3.5" /> Export
                </button>
                <button className="inline-flex items-center justify-center gap-2 font-medium rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-400 shadow-sm text-sm px-4 py-2 h-9">
                    <Plus className="w-3.5 h-3.5" /> Tambah Data
                </button>
                </div>
            </div>

            <div className="grid grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4">
                <StatCard title="Total Siswa"    value="1,247" change="+3.2%" icon={GraduationCap} bg="bg-blue-100 text-blue-600"    />
                <StatCard title="Guru & Staf"    value="89"    change="+1.1%" icon={Users}         bg="bg-emerald-100 text-emerald-600" />
                <StatCard title="Kelas Aktif"    value="36"                  icon={BookOpen}       bg="bg-violet-100 text-violet-600"  />
                <StatCard title="Kehadiran"      value="94.7%" change="+0.5%" icon={ClipboardList}  bg="bg-amber-100 text-amber-600"   />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <Crd className="lg:col-span-2" np>
                <div className="px-5 pt-5 pb-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                    <h3 className="text-sm font-semibold text-slate-900">Tren Kehadiran</h3>
                    <p className="text-xs text-slate-500 mt-0.5">6 bulan terakhir (%)</p>
                    </div>
                    <Bdg v="success">Rata-rata 93.3%</Bdg>
                </div>
                <div className="p-5">
                    <ResponsiveContainer width="100%" height={180}>
                    <AreaChart data={ATTEND_CHART} margin={{ top: 5, right: 5, left: -20, bottom: 0 }}>
                        <defs>
                        <linearGradient id="gb" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="5%" stopColor="#3B82F6" stopOpacity={0.2} />
                            <stop offset="95%" stopColor="#3B82F6" stopOpacity={0} />
                        </linearGradient>
                        </defs>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#E2E8F0" />
                        <XAxis dataKey="month" axisLine={false} tickLine={false} tick={{ fontSize: 12, fill: '#64748B' }} dy={10} />
                        <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 12, fill: '#64748B' }} domain={[80, 100]} />
                        <Tooltip contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }} />
                        <Area type="monotone" dataKey="hadir" stroke="#2563EB" strokeWidth={3} fillOpacity={1} fill="url(#gb)" />
                    </AreaChart>
                    </ResponsiveContainer>
                </div>
                </Crd>
                
                <Crd np>
                <div className="px-5 pt-5 pb-4 border-b border-slate-100">
                    <h3 className="text-sm font-semibold text-slate-900">Distribusi Nilai</h3>
                    <p className="text-xs text-slate-500 mt-0.5">Semester Ganjil 2025/2026</p>
                </div>
                <div className="p-5 flex flex-col items-center">
                    <div className="w-40 h-40 relative">
                    <ResponsiveContainer width="100%" height="100%">
                        <PieChart>
                        <Pie data={GRADE_PIE} innerRadius={50} outerRadius={70} paddingAngle={2} dataKey="value" stroke="none">
                            {GRADE_PIE.map((e, i) => <Cell key={i} fill={e.color} />)}
                        </Pie>
                        <Tooltip contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }} />
                        </PieChart>
                    </ResponsiveContainer>
                    <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span className="text-xl font-bold text-slate-900">1,091</span>
                        <span className="text-[10px] text-slate-500 uppercase">Total Siswa</span>
                    </div>
                    </div>
                    <div className="w-full mt-4 grid grid-cols-2 gap-2">
                    {GRADE_PIE.map(g => (
                        <div key={g.name} className="flex items-center justify-between text-xs p-1.5 rounded-lg bg-slate-50">
                        <div className="flex items-center gap-1.5"><div className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: g.color }} /><span>{g.name}</span></div>
                        <span className="font-semibold text-slate-700">{g.value}</span>
                        </div>
                    ))}
                    </div>
                </div>
                </Crd>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <Crd np className="flex flex-col">
                <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-slate-900">Aktivitas Terbaru</h3>
                    <button className="text-xs text-blue-600 font-medium hover:text-blue-700">Lihat Semua</button>
                </div>
                <div className="flex-1 overflow-auto">
                    <table className="w-full text-left text-sm whitespace-nowrap">
                    <thead className="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr><th className="px-4 py-2.5 font-medium">Siswa</th><th className="px-4 py-2.5 font-medium">Kelas</th><th className="px-4 py-2.5 font-medium">Status</th><th className="px-4 py-2.5 font-medium text-right">Waktu</th></tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {RECENT_ACT.map(a => (
                        <tr key={a.id} className="hover:bg-slate-50/50">
                            <td className="px-4 py-3"><p className="font-medium text-slate-800">{a.student}</p></td>
                            <td className="px-4 py-3 text-slate-500">{a.cls}</td>
                            <td className="px-4 py-3"><StBdg st={a.st} /></td>
                            <td className="px-4 py-3 text-right text-slate-500">{a.time}</td>
                        </tr>
                        ))}
                    </tbody>
                    </table>
                </div>
                </Crd>

                <Crd np className="flex flex-col">
                <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-slate-900">Performa Siswa (Top 5)</h3>
                    <button className="text-xs text-blue-600 font-medium hover:text-blue-700">Lihat Semua</button>
                </div>
                <div className="flex-1 overflow-auto">
                    <table className="w-full text-left text-sm whitespace-nowrap">
                    <thead className="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr><th className="px-4 py-2.5 font-medium">Siswa</th><th className="px-4 py-2.5 font-medium">Kehadiran</th><th className="px-4 py-2.5 font-medium text-right">Nilai Rata-rata</th></tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {STUDENTS_DATA.map(s => (
                        <tr key={s.id} className="hover:bg-slate-50/50">
                            <td className="px-4 py-3"><p className="font-medium text-slate-800">{s.name}</p><p className="text-xs text-slate-500">Kelas {s.cls}</p></td>
                            <td className="px-4 py-3">
                            <div className="flex items-center gap-2">
                                <div className="flex-1 h-1.5 bg-slate-100 rounded-full w-20 overflow-hidden">
                                <div className={cn("h-full rounded-full", s.pct >= 90 ? "bg-green-500" : s.pct >= 75 ? "bg-amber-500" : "bg-red-500")} style={{ width: `${s.pct}%` }} />
                                </div>
                                <span className="text-xs font-medium text-slate-600 w-7">{s.pct}%</span>
                            </div>
                            </td>
                            <td className="px-4 py-3 text-right font-semibold text-slate-700">{s.gpa}</td>
                        </tr>
                        ))}
                    </tbody>
                    </table>
                </div>
                </Crd>
            </div>
        </div>
    );
};
