import React from 'react';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import { AuthProvider } from '../context/AuthContext';
import { AuthLayout } from '../layouts/AuthLayout';
import { MainLayout } from '../layouts/MainLayout';
import { Login } from '../pages/auth/Login';
import { AdminDashboard } from '../pages/dashboard/AdminDashboard';
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
            {
                path: '/',
                element: <AdminDashboard />,
            },
            { path: '/students', element: <Placeholder title="Siswa" /> },
            { path: '/teachers', element: <Placeholder title="Guru & Staf" /> },
            { path: '/classes', element: <Placeholder title="Kelas" /> },
            { path: '/attendance', element: <Placeholder title="Kehadiran" /> },
            { path: '/grades', element: <Placeholder title="Nilai" /> },
            { path: '/finance', element: <Placeholder title="Keuangan" /> },
            { path: '/reports', element: <Placeholder title="Laporan" /> },
            { path: '/design-system', element: <Placeholder title="Design System" /> },
            { path: '/settings', element: <Placeholder title="Pengaturan" /> },
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
