import React, { createContext, useContext, useState, useEffect } from 'react';
import api from '../services/api';

export type Role = "administrator" | "guru-mapel" | "wali-kelas" | "siswa" | "orang-tua" | "guru-bk" | "wakil-kepala";

export interface User {
    id: number;
    name: string;
    email: string;
    roles: Role[];
    avatar?: string;
    // other fields as necessary
}

interface AuthContextType {
    user: User | null;
    isLoading: boolean;
    login: (userData: User) => void;
    logout: () => Promise<void>;
    checkAuth: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const [user, setUser] = useState<User | null>(null);
    const [isLoading, setIsLoading] = useState(true);

    const checkAuth = async () => {
        try {
            const response = await api.get('/auth/me');
            if (response.data?.data) {
                // Map the backend role to frontend role (assuming the backend gives us an array of roles or a primary role)
                const backendUser = response.data.data;
                const mappedUser: User = {
                    id: backendUser.id,
                    name: backendUser.name,
                    email: backendUser.email,
                    roles: backendUser.roles || ["administrator"], // fallback mapping logic
                    avatar: backendUser.avatar
                };
                setUser(mappedUser);
            }
        } catch (error) {
            setUser(null);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        checkAuth();
    }, []);

    const login = (userData: User) => {
        setUser(userData);
    };

    const logout = async () => {
        try {
            await api.post('/auth/logout');
        } finally {
            setUser(null);
            window.location.href = '/login';
        }
    };

    return (
        <AuthContext.Provider value={{ user, isLoading, login, logout, checkAuth }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (context === undefined) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};
