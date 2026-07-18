import React, { useState, useEffect, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import ConfirmDialog from '@/Components/ConfirmDialog';
import { 
    Users, Search, Filter, X, 
    Edit, Trash2, Shield, UserPlus, 
    ShieldCheck, Building
} from 'lucide-react';
import Pagination from '@/Components/Pagination';
import FilterBar from '@/Components/FilterBar';
import PageMotion, { MotionItem } from '@/Components/PageMotion';

export default function Index({ auth, users, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [role, setRole] = useState(filters?.role || '');
    const [deleteUserId, setDeleteUserId] = useState(null);
    const searchInputRef = useRef(null);

    // Debounced search
    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== (filters?.search || '') || role !== (filters?.role || '')) {
                const el = searchInputRef.current;
                const hadFocus = el && document.activeElement === el;
                const caret = el ? el.selectionStart : null;

                router.get(route('users.index'), { search, role }, {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    only: ['users', 'filters'],
                    onFinish: () => {
                        if (hadFocus && searchInputRef.current) {
                            const input = searchInputRef.current;
                            input.focus();
                            if (caret !== null) {
                                try { input.setSelectionRange(caret, caret); } catch (_) {}
                            }
                        }
                    },
                });
            }
        }, 600);
        return () => clearTimeout(timer);
    }, [search, role]);

    const handleClear = () => {
        setSearch('');
        setRole('');
        router.get(route('users.index'));
    };

    const handleDelete = (id) => {
        setDeleteUserId(id);
    };

    const confirmDelete = () => {
        if (deleteUserId) {
            router.delete(route('users.destroy', deleteUserId));
            setDeleteUserId(null);
        }
    };

    const getRoleBadge = (role) => {
        switch (role) {
            case 'super_admin':
                return <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-[11px] font-bold bg-violet-50 text-violet-700 border border-violet-200"><ShieldCheck className="w-3.5 h-3.5"/> Super Admin</span>;
            case 'admin':
                return <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200"><Shield className="w-3.5 h-3.5"/> Admin</span>;
            case 'dean':
                return <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-[11px] font-bold bg-amber-50 dark:bg-amber-900/20 text-amber-700 border border-amber-200"><Building className="w-3.5 h-3.5"/> Dean</span>;
            default:
                return <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-[11px] font-bold bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">{role}</span>;
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-slate-200 leading-tight">User Management</h2>}
        >
            <Head title="User Management" />

            <ConfirmDialog
                open={!!deleteUserId}
                onClose={() => setDeleteUserId(null)}
                onConfirm={confirmDelete}
                title="Delete User?"
                description="Are you sure you want to delete this user account? This cannot be undone."
                confirmLabel="Yes, delete user"
                destructive
            />

            <PageMotion>
                
                {/* Modern Header */}
                <MotionItem className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                <Users className="w-3.5 h-3.5" />
                                Administration
                            </div>
                            <h1 className="text-3xl font-bold text-white tracking-tight">System Users</h1>
                            <p className="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">Manage system administrators, deans, and their access privileges.</p>
                        </div>
                        
                        <div className="flex items-center gap-3">
                            <Link href={route('users.create')} className="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5">
                                <UserPlus className="w-4 h-4" />
                                Add User
                            </Link>
                        </div>
                    </div>
                </MotionItem>

                {/* Search & Filters */}
                <MotionItem>
                    <FilterBar
                        inputRef={searchInputRef}
                        search={search}
                        onSearchChange={setSearch}
                        onClear={handleClear}
                        placeholder="Search by name or email..."
                    >
                        <div className="sm:max-w-xs">
                            <label htmlFor="user-role" className="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Role</label>
                            <select id="user-role" value={role} onChange={(e) => setRole(e.target.value)} className="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm">
                                <option value="">All Roles</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="admin">Admin</option>
                                <option value="dean">Dean</option>
                            </select>
                        </div>
                    </FilterBar>
                </MotionItem>

                {/* Records List */}
                <MotionItem className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left">
                            <thead className="bg-slate-50/80 dark:bg-slate-800/80">
                                <tr>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">User Profile</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">System Role</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800">
                                {users.data.length > 0 ? (
                                    users.data.map((user) => (
                                        <tr key={user.id} className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors duration-150 group">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center gap-4">
                                                    <div className="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm border border-indigo-100">
                                                        {user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <p className="text-sm font-bold text-slate-900 dark:text-white">{user.name}</p>
                                                        <p className="text-xs font-medium text-slate-500 dark:text-slate-400">{user.email}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex flex-col items-start gap-1">
                                                    {getRoleBadge(user.role)}
                                                    {user.department && (
                                                        <span className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">{user.department}</span>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div className="flex items-center justify-end gap-1">
                                                    <Link 
                                                        href={route('users.edit', user.id)} 
                                                        className="p-2 text-slate-400 hover:text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:bg-amber-900/20 rounded-xl transition-all duration-150" 
                                                        title="Edit User"
                                                        aria-label={`Edit user ${user.name}`}
                                                    >
                                                        <Edit className="w-4 h-4" />
                                                    </Link>
                                                    {auth.user.id !== user.id && (
                                                        <button 
                                                            onClick={() => handleDelete(user.id)} 
                                                            className="p-2 text-slate-400 hover:text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:bg-rose-900/20 rounded-xl transition-all duration-150" 
                                                            title="Delete User"
                                                            aria-label={`Delete user ${user.name}`}
                                                        >
                                                            <Trash2 className="w-4 h-4" />
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="3" className="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                            <div className="flex flex-col items-center justify-center">
                                                <Users className="w-12 h-12 text-slate-300 mb-3" />
                                                <p className="text-sm font-medium">No users found.</p>
                                                <p className="text-xs mt-1">Try adjusting your filters or search query.</p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    
                    {/* Pagination */}
                    {users.links && users.links.length > 3 && (
                        <div className="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                            <Pagination links={users.links} />
                        </div>
                    )}
                </MotionItem>
            </PageMotion>
        </AuthenticatedLayout>
    );
}
