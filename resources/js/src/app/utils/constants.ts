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

export const NAV_GROUPS = [
    { group: "Utama",        items: [{ id: "dashboard", label: "Dashboard",    icon: LayoutDashboard, path: "/" }] },
    { group: "Akademik",     items: [
      { id: "students",       label: "Siswa",         icon: GraduationCap, path: "/students"  },
      { id: "teachers",       label: "Guru & Staf",   icon: Users, path: "/teachers"          },
      { id: "classes",        label: "Kelas",         icon: School, path: "/classes"         },
      { id: "attendance",     label: "Kehadiran",     icon: ClipboardList, path: "/attendance"  },
      { id: "grades",         label: "Nilai",         icon: TrendingUp, path: "/grades"     },
    ]},
    { group: "Administrasi", items: [
      { id: "finance",        label: "Keuangan",      icon: DollarSign, path: "/finance"     },
      { id: "reports",        label: "Laporan",       icon: BarChart2, path: "/reports"      },
    ]},
    { group: "Developer",    items: [{ id: "design-system", label: "Design System", icon: Palette, path: "/design-system"    }] },
    { group: "Sistem",       items: [{ id: "settings",       label: "Pengaturan",    icon: Settings, path: "/settings"   }] },
];
