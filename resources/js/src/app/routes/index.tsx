import React from 'react';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import { AuthProvider } from '../context/AuthContext';
import { AuthLayout } from '../layouts/AuthLayout';
import { MainLayout } from '../layouts/MainLayout';
import { Login } from '../pages/auth/Login';
import { AdminDashboard } from '../pages/dashboard/AdminDashboard';
import { JadwalKelas } from '../pages/akademik/JadwalKelas';
import { PengaturanAkademik } from '../pages/akademik/PengaturanAkademik';
import { Siswa } from '../pages/master/Siswa';
import { Guru } from '../pages/master/Guru';
import { Mapel } from '../pages/akademik/Mapel';
import { Kelas } from '../pages/akademik/Kelas';
import { Kalender } from '../pages/akademik/Kalender';
import { GuruMapelJadwal } from '../pages/guru-mapel/GuruMapelJadwal';
import { WaliKelasAbsensi } from '../pages/walikelas/WaliKelasAbsensi';
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
            { path: '/guru-mapel/dashboard', element: <Placeholder title="Dashboard Guru" /> },
            { path: '/guru-mapel/jadwal', element: <GuruMapelJadwal /> },
            { path: '/guru-mapel/kalender', element: <Placeholder title="Kalender Akademik" /> },
            { path: '/guru-mapel/absensi/input', element: <WaliKelasAbsensi pageTitle="Absensi Kelas" pageDescription="Pilih pertemuan kelas perwalian, lalu isi kehadiran harian siswa." /> },
            { path: '/guru-mapel/absensi/riwayat', element: <Placeholder title="Riwayat Absensi" /> },
            { path: '/guru-mapel/absensi/rekap', element: <GuruMapelJadwal /> },
            { path: '/guru-mapel/lms/nilai', element: <Placeholder title="Input Nilai" /> },
            { path: '/guru-mapel/lms/materi', element: <Placeholder title="Upload Materi" /> },
            { path: '/guru-mapel/pengumuman', element: <Placeholder title="Pengumuman Kelas" /> },
            { path: '/guru-mapel/profil', element: <Placeholder title="Profil Saya" /> },
 
            // --- WALI KELAS ROUTES ---
            { path: '/walikelas/dashboard', element: <Placeholder title="Dashboard Wali Kelas" /> },
            { path: '/walikelas/absensi/input', element: <WaliKelasAbsensi /> },
            { path: '/walikelas/absensi/rekap', element: <Placeholder title="Rekap Absensi Harian Kelas" /> },
            { path: '/walikelas/siswa', element: <Placeholder title="Data Siswa Kelas" /> },
            { path: '/walikelas/rapor', element: <Placeholder title="Rapor & Nilai Kelas" /> },
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
