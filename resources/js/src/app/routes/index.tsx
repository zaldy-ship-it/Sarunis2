import React from 'react';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import { AuthProvider } from '../context/AuthContext';
import { AuthLayout } from '../layouts/AuthLayout';
import { MainLayout } from '../layouts/MainLayout';
import { Login } from '../pages/auth/Login';
import { AdminDashboard } from '../pages/dashboard/AdminDashboard';
import { TeacherDashboard } from '../pages/dashboard/TeacherDashboard';
import { HomeroomDashboard } from '../pages/dashboard/HomeroomDashboard';
import { JadwalKelas } from '../pages/akademik/JadwalKelas';
import { PengaturanAkademik } from '../pages/akademik/PengaturanAkademik';
import { Siswa } from '../pages/master/Siswa';
import { Guru } from '../pages/master/Guru';
import { Mapel } from '../pages/akademik/Mapel';
import { Kelas } from '../pages/akademik/Kelas';
import { Kalender } from '../pages/akademik/Kalender';
import { GuruMapelJadwal } from '../pages/guru-mapel/GuruMapelJadwal';
import { SubjectAttendanceRecap } from '../pages/guru-mapel/SubjectAttendanceRecap';
import { WaliKelasAbsensi } from '../pages/walikelas/WaliKelasAbsensi';
import { ClassAttendanceRecap } from '../pages/walikelas/ClassAttendanceRecap';
import { ClassStudents } from '../pages/walikelas/ClassStudents';
import { StudentClassAttendance, StudentNotes, StudentSchedule, StudentSubjectAttendance } from '../pages/siswa/StudentPages';
import { AdminRecapExport } from '../pages/absensi/AdminRecapExport';
import { Pengumuman } from '../pages/pengumuman/Pengumuman';
import { Toaster } from 'sonner';

// Placeholder component for unimplemented pages
const Placeholder = ({ title }: { title: string }) => (
    <div className="p-6">
        <h1 className="text-2xl font-bold">{title}</h1>
        <p className="text-slate-500 mt-2">Halaman ini sedang dalam pengembangan.</p>
    </div>
);

const router = createBrowserRouter([
    {
        element: <AuthLayout />,
        children: [
            {
                path: '/login',
                element: <Login />,
            },
        ],
    },
    {
        element: <MainLayout />,
        children: [
            { path: '/', element: <AdminDashboard /> },
            { path: '/admin/dashboard', element: <AdminDashboard /> },
            
            // Akademik
            { path: '/akademik/tahun-ajaran', element: <PengaturanAkademik /> },
            { path: '/akademik/mapel', element: <Mapel /> },
            { path: '/akademik/kelas', element: <Kelas /> },
            { path: '/akademik/kalender', element: <Kalender /> },

            // Data Master
            { path: '/master/siswa', element: <Siswa /> },
            { path: '/master/guru', element: <Guru /> },
            { path: '/master/orang-tua', element: <Placeholder title="Data Orang Tua" /> },
            { path: '/master/kelas', element: <Placeholder title="Plotting Kelas" /> },

            // Jadwal
            { path: '/jadwal/generate', element: <Placeholder title="Generate Jadwal" /> },
            { path: '/jadwal/guru', element: <Placeholder title="Jadwal Guru" /> },
            { path: '/jadwal/kelas', element: <JadwalKelas /> },
            { path: '/jadwal/ruangan', element: <Placeholder title="Jadwal Ruangan" /> },
            { path: '/jadwal/konflik', element: <Placeholder title="Deteksi Konflik" /> },

            // Operasional
            { path: '/absensi/rekap', element: <AdminRecapExport mode="kehadiran" /> },
            { path: '/absensi/guru', element: <AdminRecapExport mode="guru" /> },
            { path: '/absensi/siswa', element: <AdminRecapExport mode="siswa" /> },
            { path: '/absensi/statistik', element: <AdminRecapExport mode="statistik" /> },
            { path: '/pelanggaran/daftar', element: <Placeholder title="Daftar Pelanggaran" /> },
            { path: '/pelanggaran/jenis', element: <Placeholder title="Jenis Pelanggaran" /> },
            { path: '/pelanggaran/poin', element: <Placeholder title="Poin Pelanggaran" /> },
            { path: '/pelanggaran/riwayat', element: <Placeholder title="Riwayat Pelanggaran" /> },
            { path: '/pelanggaran/statistik', element: <Placeholder title="Statistik Pelanggaran" /> },
            { path: '/pengumuman', element: <Pengumuman /> },

            // Laporan
            { path: '/laporan/kehadiran', element: <Placeholder title="Laporan Kehadiran" /> },
            { path: '/laporan/siswa', element: <Placeholder title="Laporan Siswa" /> },
            { path: '/laporan/guru', element: <Placeholder title="Laporan Guru" /> },
            { path: '/laporan/jadwal', element: <Placeholder title="Laporan Jadwal" /> },
            { path: '/laporan/pelanggaran', element: <Placeholder title="Laporan Pelanggaran" /> },

            // Manajemen Pengguna
            { path: '/pengguna/akun', element: <Placeholder title="Semua Akun" /> },
            { path: '/pengguna/role', element: <Placeholder title="Role & Permission" /> },
            { path: '/pengguna/riwayat', element: <Placeholder title="Riwayat Login" /> },

            // Pengaturan Admin
            { path: '/pengaturan/profil', element: <Placeholder title="Profil Sekolah" /> },
            { path: '/pengaturan/konfigurasi', element: <Placeholder title="Konfigurasi Sistem" /> },
            { path: '/pengaturan/backup', element: <Placeholder title="Backup & Restore" /> },
            { path: '/pengaturan/audit', element: <Placeholder title="Audit Log" /> },

            // --- GURU MAPEL ROUTES ---
            { path: '/guru-mapel/dashboard', element: <TeacherDashboard /> },
            { path: '/guru-mapel/jadwal', element: <GuruMapelJadwal /> },
            { path: '/guru-mapel/kalender', element: <Placeholder title="Kalender Akademik" /> },
            { path: '/guru-mapel/absensi/data-kelas', element: <ClassStudents pageTitle="Data Kelas" pageDescription="Lihat seluruh siswa pada kelas perwalian yang diampu." /> },
            { path: '/guru-mapel/absensi/input', element: <WaliKelasAbsensi pageTitle="Absensi Kelas" pageDescription="Pilih pertemuan kelas perwalian, lalu isi kehadiran harian siswa." /> },
            { path: '/guru-mapel/absensi/riwayat', element: <ClassAttendanceRecap mode="history" pageTitle="Riwayat Absensi Kelas" pageDescription="Lihat catatan absensi kelas perwalian berdasarkan tanggal." /> },
            { path: '/guru-mapel/absensi/rekap', element: <ClassAttendanceRecap mode="recap" pageTitle="Rekap Absensi Kelas" pageDescription="Ringkas kehadiran siswa kelas perwalian berdasarkan periode." /> },
            { path: '/guru-mapel/absensi/riwayat-mapel', element: <SubjectAttendanceRecap mode="history" pageTitle="Riwayat Absensi Mapel" pageDescription="Lihat catatan absensi per mata pelajaran yang diajar." /> },
            { path: '/guru-mapel/absensi/rekap-mapel', element: <SubjectAttendanceRecap mode="recap" pageTitle="Rekap Absensi Mapel" pageDescription="Ringkas kehadiran siswa untuk mata pelajaran yang diajar." /> },
            { path: '/guru-mapel/profil', element: <Placeholder title="Profil Saya" /> },
 
            // --- WALI KELAS ROUTES ---
            { path: '/walikelas/dashboard', element: <HomeroomDashboard /> },
            { path: '/walikelas/absensi/data-kelas', element: <ClassStudents pageTitle="Data Kelas Perwalian" pageDescription="Lihat seluruh siswa pada kelas yang menjadi tanggung jawab wali kelas." /> },
            { path: '/walikelas/absensi/input', element: <WaliKelasAbsensi /> },
            { path: '/walikelas/absensi/riwayat', element: <ClassAttendanceRecap mode="history" /> },
            { path: '/walikelas/absensi/rekap', element: <ClassAttendanceRecap mode="recap" /> },
            { path: '/walikelas/siswa', element: <ClassStudents pageTitle="Data Siswa Kelas" pageDescription="Lihat seluruh siswa pada kelas yang menjadi tanggung jawab wali kelas." /> },
            { path: '/walikelas/rapor', element: <Placeholder title="Rapor & Nilai Kelas" /> },

            // --- SISWA ROUTES ---
            { path: '/siswa/dashboard', element: <StudentSchedule /> },
            { path: '/siswa/jadwal-pelajaran', element: <StudentSchedule /> },
            { path: '/siswa/absensi-mapel', element: <StudentSubjectAttendance /> },
            { path: '/siswa/absensi-kelas', element: <StudentClassAttendance /> },
            { path: '/siswa/catatan', element: <StudentNotes /> },

            // --- ORANG TUA ROUTES ---
            { path: '/orang-tua/dashboard', element: <StudentSchedule portal="orang-tua" /> },
            { path: '/orang-tua/absensi-mapel', element: <StudentSubjectAttendance portal="orang-tua" /> },
            { path: '/orang-tua/absensi-kelas', element: <StudentClassAttendance portal="orang-tua" /> },
            { path: '/orang-tua/catatan', element: <StudentNotes portal="orang-tua" /> },
        ],
    },
]);

export const AppRouter = () => {
    return (
        <AuthProvider>
            <RouterProvider router={router} />
            <Toaster position="top-right" richColors />
        </AuthProvider>
    );
};
