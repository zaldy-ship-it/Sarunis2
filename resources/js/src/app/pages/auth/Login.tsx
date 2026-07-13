import React, { useState } from 'react';
import { School, Mail, Lock, Eye, EyeOff, AlertCircle } from 'lucide-react';
import { cn } from '../../components/ui/utils';
import { useAuth } from '../../context/AuthContext';
import api from '../../services/api';

export const Login = () => {
    const { login, checkAuth } = useAuth();
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [showPwd, setShowPwd] = useState(false);
    const [remember, setRemember] = useState(false);
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState<{email?: string; pwd?: string; global?: string}>({});

    const doLogin = async () => {
        const e: typeof errors = {};
        if (!email) e.email = "Email atau Username wajib diisi";
        if (!password) e.pwd = "Password wajib diisi";
        setErrors(e);
        
        if (Object.keys(e).length) return;
        
        setLoading(true);
        try {
            const response = await api.post('/auth/login', {
                login: email,
                password: password,
                remember: remember
            });

            if (response.data?.data) {
                // Instead of relying on the local manually mapped data, we'll force a checkAuth
                // which calls /auth/me and guarantees we get exactly what the app needs globally.
                await checkAuth();
            }
        } catch (error: any) {
            setErrors({
                global: error.response?.data?.message || "Terjadi kesalahan saat login."
            });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex">
        <div className="hidden lg:flex flex-col w-[400px] bg-blue-600 text-white p-10 flex-shrink-0 relative overflow-hidden">
          <div className="absolute inset-0 opacity-10">
            {[...Array(5)].map((_, i) => (
              <div key={i} className="absolute rounded-full border border-white/30" style={{ width: `${(i+1)*140}px`, height: `${(i+1)*140}px`, top: "50%", left: "50%", transform: "translate(-50%, -50%)" }} />
            ))}
          </div>
          <div className="relative flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center"><School className="w-4.5 h-4.5" /></div>
            <div><p className="font-bold text-sm">SMP IP YAKIN</p><p className="text-xs text-blue-200">Sistem Manajemen Sekolah</p></div>
          </div>
          <div className="relative my-auto">
            <h2 className="text-3xl font-bold mb-4 leading-tight">Platform<br />Pendidikan<br />Terintegrasi</h2>
            <p className="text-blue-200 text-sm leading-relaxed mb-8">Kelola seluruh aktivitas akademik, kehadiran, nilai, dan administrasi sekolah dalam satu platform.</p>
            <div className="grid grid-cols-2 gap-3">
              {[["1,247","Siswa Aktif"],["89","Guru & Staf"],["36","Kelas"],["94.7%","Kehadiran"]].map(([v,l]) => (
                <div key={l} className="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
                  <p className="text-2xl font-bold">{v}</p>
                  <p className="text-xs text-blue-200 mt-0.5">{l}</p>
                </div>
              ))}
            </div>
          </div>
          <p className="relative text-xs text-blue-300">© 2026 SMP IP YAKIN. All rights reserved.</p>
        </div>

        <div className="flex-1 flex items-center justify-center bg-slate-50 p-6">
          <div className="w-full max-w-md">
            <div className="flex items-center gap-3 mb-8 lg:hidden">
              <div className="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center"><School className="w-4 h-4 text-white" /></div>
              <p className="font-bold text-slate-900 text-sm">SMP IP YAKIN</p>
            </div>
            
            <h1 className="text-xl font-bold text-slate-900 mb-1">Masuk ke Akun</h1>
            <p className="text-sm text-slate-500 mb-6">Gunakan email atau username sekolah Anda untuk semua peran</p>
            <form onSubmit={(e) => { e.preventDefault(); doLogin(); }} className="space-y-4">
                {errors.global && (
                    <div className="bg-red-50 text-red-700 p-3 rounded-lg text-sm flex items-center gap-2">
                        <AlertCircle className="w-4 h-4" />
                        {errors.global}
                    </div>
                )}
              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-slate-700">Email / Username <span className="text-red-500">*</span></label>
                <div className="relative">
                  <Mail className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4" />
                  <input type="text" placeholder="nama@smpipyakin.sch.id" value={email} onChange={e => setEmail(e.target.value)} 
                    className={cn(
                        "w-full h-9 rounded-lg border text-sm bg-white text-slate-900 placeholder:text-slate-400 transition-all focus:outline-none focus:ring-2 pl-9 pr-3",
                        errors.email ? "border-red-400 focus:border-red-500 focus:ring-red-100" : "border-slate-300 hover:border-slate-400 focus:border-blue-500 focus:ring-blue-100"
                    )} />
                </div>
                {errors.email && <p className="text-xs text-red-600 flex items-center gap-1"><AlertCircle className="w-3 h-3" />{errors.email}</p>}
              </div>
              <div className="flex flex-col gap-1">
                <label className="text-sm font-medium text-slate-700">Password <span className="text-red-500">*</span></label>
                <div className="relative">
                  <Lock className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                  <input type={showPwd ? "text" : "password"} placeholder="Masukkan password" value={password} onChange={e => setPassword(e.target.value)}
                    className={cn("w-full h-9 rounded-lg border text-sm pl-9 pr-10 bg-white placeholder:text-slate-400 focus:outline-none focus:ring-2 transition-all",
                      errors.pwd ? "border-red-400 focus:border-red-500 focus:ring-red-100" : "border-slate-300 hover:border-slate-400 focus:border-blue-500 focus:ring-blue-100")} />
                  <button type="button" onClick={() => setShowPwd(!showPwd)} className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700">
                    {showPwd ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
                {errors.pwd && <p className="text-xs text-red-600 flex items-center gap-1"><AlertCircle className="w-3 h-3" />{errors.pwd}</p>}
              </div>
              <div className="flex items-center justify-between">
                <label className="flex items-center gap-2 cursor-pointer select-none">
                  <input type="checkbox" checked={remember} onChange={e => setRemember(e.target.checked)} className="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                  <span className="text-sm text-slate-600">Ingat saya</span>
                </label>
                <button type="button" className="text-sm text-blue-600 hover:text-blue-700 font-medium">Lupa password?</button>
              </div>
              
              <button 
                type="submit" 
                disabled={loading} 
                className="w-full inline-flex items-center justify-center gap-2 font-medium rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 bg-blue-600 text-white hover:bg-blue-700 text-sm px-5 py-2.5 h-11 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {loading ? "Memverifikasi..." : "Masuk"}
              </button>
              <p className="text-xs text-center text-slate-400 mt-4">Butuh bantuan? Hubungi <span className="text-blue-600 cursor-pointer hover:underline">IT Support</span></p>
            </form>
          </div>
        </div>
      </div>
    );
};
