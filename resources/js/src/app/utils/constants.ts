import {
    LayoutDashboard, Users, BookOpen, ClipboardList, TrendingUp,
    DollarSign, BarChart2, Settings, Palette, GraduationCap, School,
    UserCheck, Home, Award, Shield
} from "lucide-react";
import { Role } from "../context/AuthContext";

export const ROLE_LABELS: Record<Role, string> = {
    administrator: "Administrator", "guru-mapel": "Guru Mapel", "wali-kelas": "Wali Kelas",
    siswa: "Siswa", "orang-tua": "Orang Tua", "guru-bk": "Guru BK", "wakil-kepala": "Wakil Kepala",
};

export const ROLES = [
    { id: "administrator" as Role,  label: "Administrator",   desc: "Pengelola sistem",   icon: Shield,       color: "bg-blue-100 text-blue-700"    },
    { id: "guru-mapel" as Role,     label: "Guru Mapel",      desc: "Pengajar",           icon: BookOpen,     color: "bg-emerald-100 text-emerald-700" },
    { id: "wali-kelas" as Role,     label: "Wali Kelas",      desc: "Wali kelas",         icon: Users,        color: "bg-violet-100 text-violet-700" },
    { id: "siswa" as Role,          label: "Siswa",           desc: "Peserta didik",      icon: GraduationCap,color: "bg-amber-100 text-amber-700"   },
    { id: "orang-tua" as Role,      label: "Orang Tua",       desc: "Wali murid",         icon: Home,         color: "bg-rose-100 text-rose-700"     },
    { id: "guru-bk" as Role,        label: "Guru BK",         desc: "Bimbingan",          icon: UserCheck,    color: "bg-cyan-100 text-cyan-700"     },
    { id: "wakil-kepala" as Role,   label: "Wakil Kepala",    desc: "Wakil kepala",       icon: Award,        color: "bg-orange-100 text-orange-700" },
];

import {
    LayoutDashboard, Users, BookOpen, ClipboardList, TrendingUp,
    Settings, GraduationCap, School, UserCheck, Home, Shield,
    Calendar, Clock, Database, FileText, AlertTriangle, Speaker,
    BarChart2, Key, List, Sliders, Archive, Download, CheckSquare,
    Activity, CalendarDays
} from "lucide-react";

export type NavItem = {
    id: string;
    label: string;
    icon?: any;
    path?: string;
    subItems?: Omit<NavItem, 'icon' | 'subItems'>[];
};

export type NavGroup = {
    group: string;
    items: NavItem[];
};

export type NavCapabilities = {
    hasTeachingSchedule?: boolean | null;
    hasHomeroomClass?: boolean | null;
};

export const ADMIN_NAV_GROUPS: NavGroup[] = [
    {
        group: "Utama",
        items: [
            { id: "admin-dashboard", label: "Dashboard", icon: LayoutDashboard, path: "/" }
        ]
    },
    {
        group: "Manajemen Utama",
        items: [
            {
                id: "akademik", label: "Akademik", icon: BookOpen,
                subItems: [
                    { id: "akademik-tahun", label: "Tahun Ajaran", path: "/akademik/tahun-ajaran" },
                    { id: "akademik-mapel", label: "Mata Pelajaran", path: "/akademik/mapel" },
                    { id: "akademik-kelas", label: "Kelas", path: "/akademik/kelas" },
                    { id: "akademik-kalender", label: "Kalender Akademik", path: "/akademik/kalender" },
                ]
            },
            {
                id: "data-master", label: "Data Master", icon: Database,
                subItems: [
                    { id: "master-siswa", label: "Siswa", path: "/master/siswa" },
                    { id: "master-guru", label: "Guru", path: "/master/guru" },
                    { id: "master-ortu", label: "Orang Tua", path: "/master/orang-tua" },
                ]
            },
        ]
    },
    {
        group: "Operasional",
        items: [
            {
                id: "absensi", label: "Absensi", icon: CheckSquare,
                subItems: [
                    { id: "absensi-rekap", label: "Rekap Absen Siswa", path: "/absensi/rekap" },
                    { id: "absensi-guru", label: "Rekap Guru", path: "/absensi/guru" },
                    { id: "absensi-siswa", label: "Rekap Siswa", path: "/absensi/siswa" },
                    { id: "absensi-statistik", label: "Statistik", path: "/absensi/statistik" },
                ]
            },
            {
                id: "pengumuman", label: "Pengumuman", icon: Speaker, path: "/pengumuman"
            },
        ]
    },
    {
        group: "Sistem & Laporan",
        items: [
            {
                id: "laporan", label: "Laporan", icon: FileText,
                subItems: [
                    { id: "laporan-kehadiran", label: "Kehadiran", path: "/laporan/kehadiran" },
                    { id: "laporan-siswa", label: "Siswa", path: "/laporan/siswa" },
                    { id: "laporan-guru", label: "Guru", path: "/laporan/guru" },
                    { id: "laporan-jadwal", label: "Jadwal", path: "/laporan/jadwal" },
                    { id: "laporan-pelanggaran", label: "Pelanggaran", path: "/laporan/pelanggaran" },
                ]
            },
            {
                id: "manajemen-pengguna", label: "Pengguna", icon: Users,
                subItems: [
                    { id: "users-akun", label: "Semua Akun", path: "/pengguna/akun" },
                    { id: "users-role", label: "Role & Permission", path: "/pengguna/role" },
                    { id: "users-riwayat", label: "Riwayat Login", path: "/pengguna/riwayat" },
                ]
            },
            {
                id: "pengaturan", label: "Pengaturan", icon: Settings,
                subItems: [
                    { id: "settings-profil", label: "Profil Sekolah", path: "/pengaturan/profil" },
                    { id: "settings-konfigurasi", label: "Konfigurasi Sistem", path: "/pengaturan/konfigurasi" },
                    { id: "settings-backup", label: "Backup & Restore", path: "/pengaturan/backup" },
                    { id: "settings-audit", label: "Audit Log", path: "/pengaturan/audit" },
                ]
            },
        ]
    }
];

export const TEACHER_NAV_GROUPS: NavGroup[] = [
    {
        group: "Utama",
        items: [
            { id: "teacher-dashboard", label: "Dashboard", icon: LayoutDashboard, path: "/guru-mapel/dashboard" }
        ]
    },
    {
        group: "Akademik",
        items: [
            { id: "teacher-jadwal", label: "Jadwal Mengajar", icon: CalendarDays, path: "/guru-mapel/jadwal" },
            { id: "teacher-kalender", label: "Kalender Akademik", icon: Calendar, path: "/guru-mapel/kalender" },
        ]
    },
    {
        group: "Kegiatan Belajar",
        items: [
            {
                id: "teacher-absensi", label: "Absensi Kelas", icon: CheckSquare,
                subItems: [
                    { id: "teacher-absensi-data-kelas", label: "Lihat Data Kelas", path: "/guru-mapel/absensi/data-kelas" },
                    { id: "teacher-absensi-input", label: "Input Absen Kelas", path: "/guru-mapel/absensi/input" },
                    { id: "teacher-absensi-riwayat", label: "Riwayat Absensi", path: "/guru-mapel/absensi/riwayat" },
                    { id: "teacher-absensi-rekap", label: "Rekap Kehadiran", path: "/guru-mapel/absensi/rekap" },
                ]
            },
        ]
    },
    {
        group: "Pengaturan",
        items: [
            { id: "teacher-profil", label: "Profil Saya", icon: UserCheck, path: "/guru-mapel/profil" },
        ]
    }
];

const INHERITED_WALIKELAS_TEACHER_GROUPS: NavGroup[] = TEACHER_NAV_GROUPS
    .filter(g => g.group !== "Utama")
    .map((group) => ({
        ...group,
        items: group.items.filter((item) => item.id !== "teacher-absensi"),
    }))
    .filter((group) => group.items.length > 0);

export const WALIKELAS_NAV_GROUPS: NavGroup[] = [
    {
        group: "Utama",
        items: [
            { id: "walikelas-dashboard", label: "Dashboard Wali Kelas", icon: LayoutDashboard, path: "/walikelas/dashboard" }
        ]
    },
    {
        group: "Tugas Wali Kelas",
        items: [
            {
                id: "walikelas-absensi", label: "Absensi Harian", icon: CheckSquare,
                subItems: [
                    { id: "walikelas-absensi-data-kelas", label: "Lihat Data Kelas", path: "/walikelas/absensi/data-kelas" },
                    { id: "walikelas-absensi-input", label: "Input Absen Kelas", path: "/walikelas/absensi/input" },
                    { id: "walikelas-absensi-riwayat", label: "Riwayat Absensi", path: "/walikelas/absensi/riwayat" },
                    { id: "walikelas-absensi-rekap", label: "Rekap Absen Kelas", path: "/walikelas/absensi/rekap" },
                ]
            },
        ]
    },
    ...INHERITED_WALIKELAS_TEACHER_GROUPS // Inherit teacher menus since they also teach
];

const filterByCapabilities = (groups: NavGroup[], capabilities?: NavCapabilities): NavGroup[] => {
    const hasTeachingSchedule = capabilities?.hasTeachingSchedule;
    const hasHomeroomClass = capabilities?.hasHomeroomClass;

    return groups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => {
                if (item.id === 'teacher-jadwal') {
                    return hasTeachingSchedule !== false;
                }

                if (item.id === 'teacher-absensi' || item.id === 'walikelas-absensi' || item.id === 'walikelas-siswa') {
                    return hasHomeroomClass !== false;
                }

                return true;
            }),
        }))
        .filter((group) => group.items.length > 0);
};

export const getNavGroups = (portal?: string, capabilities?: NavCapabilities): NavGroup[] => {
    const applyCapabilityFilter = (groups: NavGroup[]) => filterByCapabilities(groups, capabilities);

    switch (portal) {
        case 'walikelas':
            return applyCapabilityFilter(WALIKELAS_NAV_GROUPS);
        case 'guru-mapel':
            return applyCapabilityFilter(TEACHER_NAV_GROUPS);
        case 'admin':
        default:
            return ADMIN_NAV_GROUPS;
    }
};
