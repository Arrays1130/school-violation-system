import React, { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    Users, Search, Filter, X, UploadCloud, UserPlus, 
    Eye, Edit, Trash2, FilePlus, GraduationCap, TrendingUp, Link as LinkIcon
} from 'lucide-react';
import useInertiaLoading from '@/hooks/useInertiaLoading';
import { ListPageSkeleton } from '@/Components/ui/Skeleton';
import EmptyState from '@/Components/EmptyState';
import ConfirmDialog from '@/Components/ConfirmDialog';
import FilterBar, { filterFieldClass, filterLabelClass } from '@/Components/FilterBar';
import { YEAR_LEVELS } from '@/constants/studentForm';
import showUndoToast from '@/lib/undoToast';

export default function Index({ auth, students, departments, summary, filterAcademicYears, filters }) {
    const isLoading = useInertiaLoading();
    const [search, setSearch] = useState(filters?.search || '');
    const [department, setDepartment] = useState(filters?.department || '');
    const [yearLevel, setYearLevel] = useState(filters?.yearLevel || '');
    const [academicYear, setAcademicYear] = useState(filters?.academicYear || 'All');

    // Debounced search
    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== filters?.search || department !== filters?.department || yearLevel !== filters?.yearLevel || academicYear !== filters?.academicYear) {
                router.get(route('students.index'), { search, department, yearLevel, academicYear }, {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                });
            }
        }, 300);
        return () => clearTimeout(timer);
    }, [search, department, yearLevel, academicYear]);

    const handleClear = () => {
        setSearch('');
        setDepartment('');
        setYearLevel('');
        setAcademicYear('All');
        router.get(route('students.index'));
    };


    const [deleteStudentId, setDeleteStudentId] = useState(null);
    const [showGraduateDialog, setShowGraduateDialog] = useState(false);
    const [graduateYear, setGraduateYear] = useState('');
    const [graduateError, setGraduateError] = useState('');
    const [showPromoteConfirm, setShowPromoteConfirm] = useState(false);

    const handleDelete = (id) => setDeleteStudentId(id);

    const confirmDelete = () => {
        const id = deleteStudentId;
        setDeleteStudentId(null);
        router.delete(route('students.destroy', id), {
            onSuccess: () => {
                showUndoToast('Student moved to trash', () => {
                    router.post(route('students.restore', id));
                });
            },
        });
    };

    const handleGraduate = () => {
        setGraduateYear('');
        setGraduateError('');
        setShowGraduateDialog(true);
    };

    const confirmGraduate = () => {
        if (!graduateYear.trim()) {
            setGraduateError('Please enter an academic year.');
            return;
        }
        router.post(route('students.graduate_fourth_years'), {
            academic_year: graduateYear,
        });
        setShowGraduateDialog(false);
    };

    const handlePromote = () => setShowPromoteConfirm(true);

    const confirmPromote = () => {
        router.post(route('students.promote'));
        setShowPromoteConfirm(false);
    };

    const containerVariants = {
        hidden: { opacity: 0 },
        show: { opacity: 1, transition: { staggerChildren: 0.1 } }
    };

    const itemVariants = {
        hidden: { opacity: 0, y: 20 },
        show: { opacity: 1, y: 0, transition: { type: 'spring', stiffness: 300, damping: 24 } }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-slate-800 dark:text-white leading-tight">Student Records</h2>}
        >
            <Head title="Student Records" />

            <ConfirmDialog
                open={!!deleteStudentId}
                onClose={() => setDeleteStudentId(null)}
                onConfirm={confirmDelete}
                title="Delete this student profile?"
                description="The student will be moved to the trash. You can undo this right after, or restore them later from the Trash Bin."
                confirmLabel="Yes, Delete"
                destructive
            />

            <ConfirmDialog
                open={showGraduateDialog}
                onClose={() => setShowGraduateDialog(false)}
                onConfirm={confirmGraduate}
                title="Graduate 4th Years"
                description="This will graduate and archive ALL 4th-year students. Enter the academic year for this batch."
                confirmLabel="Yes, Graduate"
            >
                <div>
                    <label htmlFor="graduate-year" className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Academic Year <span className="text-rose-500">*</span>
                    </label>
                    <input
                        id="graduate-year"
                        type="text"
                        value={graduateYear}
                        onChange={(e) => { setGraduateYear(e.target.value); setGraduateError(''); }}
                        placeholder="SY 2023-2024"
                        className="form-input"
                        autoFocus
                    />
                    {graduateError && <p className="text-rose-500 text-xs mt-1.5 font-semibold">{graduateError}</p>}
                </div>
            </ConfirmDialog>

            <ConfirmDialog
                open={showPromoteConfirm}
                onClose={() => setShowPromoteConfirm(false)}
                onConfirm={confirmPromote}
                title="Confirm Promotion"
                description="Promote all 1st, 2nd, and 3rd year students to the next year level?"
                confirmLabel="Yes, Promote"
            />

            {isLoading ? (
                <div className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <ListPageSkeleton />
                </div>
            ) : (
            <motion.div 
                className="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
                variants={containerVariants}
                initial="hidden"
                animate="show"
            >
                
                {/* Modern Header */}
                <motion.div variants={itemVariants} className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                    
                    <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                <Users className="w-3.5 h-3.5" />
                                Student Database
                            </div>
                            <h1 className="text-3xl font-bold text-white tracking-tight">Student Records</h1>
                            <p className="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">Centralized management of student profiles, academic details, and violation histories.</p>
                        </div>
                        
                        <div className="flex flex-col lg:flex-row items-end lg:items-center justify-end gap-3 mt-4 md:mt-0 w-full lg:w-auto">
                            {/* Academic Actions Group */}
                            <div className="flex items-center gap-2 bg-white/5 p-1.5 rounded-2xl border border-white/10 w-full sm:w-auto justify-end">
                                <motion.button onClick={handlePromote} whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }} className="px-4 py-2 bg-blue-500/20 hover:bg-blue-500/30 border border-blue-400/30 text-blue-200 rounded-xl text-xs sm:text-sm font-semibold transition-all flex items-center justify-center gap-2 shadow-sm">
                                    <TrendingUp className="w-4 h-4" />
                                    Promote Students
                                </motion.button>
                                <motion.button onClick={handleGraduate} whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }} className="px-4 py-2 bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-400/30 text-emerald-200 rounded-xl text-xs sm:text-sm font-semibold transition-all flex items-center justify-center gap-2 shadow-sm">
                                    <GraduationCap className="w-4 h-4" />
                                    Graduate 4th Years
                                </motion.button>
                            </div>
                            
                            {/* Standard Actions */}
                            <div className="flex items-center gap-2 w-full sm:w-auto justify-end">
                                <motion.a whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }} href={route('students.import_form')} className="px-4 sm:px-5 py-2.5 bg-white/10 dark:bg-slate-900/10 border border-white/20 text-white rounded-xl text-xs sm:text-sm font-semibold hover:bg-white/20 transition-all backdrop-blur-md inline-flex items-center justify-center gap-2 shadow-sm whitespace-nowrap">
                                    <UploadCloud className="w-4 h-4 shrink-0" />
                                    Import Data
                                </motion.a>
                                <motion.a whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }} href={route('students.create')} className="px-4 sm:px-5 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 whitespace-nowrap">
                                    <UserPlus className="w-4 h-4 shrink-0" />
                                    Add Student
                                </motion.a>
                            </div>
                        </div>
                    </div>
                </motion.div>

                {/* Search & Filters */}
                <motion.div variants={itemVariants}>
                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onClear={handleClear}
                        placeholder="Search by name, ID, or email..."
                        filtersClassName="grid grid-cols-1 sm:grid-cols-3 gap-4 [&>*]:min-w-0"
                    >
                        <div className="min-w-0">
                            <label htmlFor="student-department" className={filterLabelClass}>Department</label>
                            <select id="student-department" value={department} onChange={(e) => setDepartment(e.target.value)} className={filterFieldClass}>
                                <option value="">All Departments</option>
                                {departments.map((dept, index) => <option key={index} value={dept}>{dept}</option>)}
                            </select>
                        </div>
                        <div className="min-w-0">
                            <label htmlFor="student-year-level" className={filterLabelClass}>Year Level</label>
                            <select id="student-year-level" value={yearLevel} onChange={(e) => setYearLevel(e.target.value)} className={filterFieldClass}>
                                <option value="">All Levels</option>
                                {YEAR_LEVELS.map((year) => <option key={year} value={year}>{year}</option>)}
                            </select>
                        </div>
                        <div className="min-w-0">
                            <label htmlFor="student-academic-year" className={filterLabelClass}>Academic Year</label>
                            <select id="student-academic-year" value={academicYear} onChange={(e) => setAcademicYear(e.target.value)} className={filterFieldClass}>
                                <option value="All">All Years</option>
                                {filterAcademicYears?.map((year, index) => <option key={index} value={year}>{year}</option>)}
                            </select>
                        </div>
                    </FilterBar>
                </motion.div>

                {/* Records List */}
                <motion.div variants={itemVariants} className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto no-scrollbar">
                        <table className="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left block md:table">
                            <thead className="bg-slate-50/80 dark:bg-slate-800/80 dark:bg-slate-800/80 hidden md:table-header-group">
                                <tr>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Student</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Academic Details</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-center">Incidents</th>
                                    <th className="px-6 py-4 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800 block md:table-row-group">
                                {students.data.length > 0 ? (
                                    students.data.map((student) => (
                                        <motion.tr variants={itemVariants} key={student.id} className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 dark:hover:bg-indigo-900/20 transition-colors duration-150 group block md:table-row border-b border-slate-100 dark:border-slate-800 md:border-none p-4 md:p-0">
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm border border-indigo-100 dark:border-indigo-500/20">
                                                        {student.full_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <p className="text-sm font-bold text-slate-900 dark:text-white">{student.full_name}</p>
                                                        <p className="text-xs text-slate-500 dark:text-slate-400">{student.email}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-medium text-slate-700 dark:text-slate-300">{student.department}</span>
                                                    <span className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{student.year_level} — {student.section}</span>
                                                </div>
                                            </td>
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell md:text-center mt-2 md:mt-0">
                                                {student.cases_count > 0 ? (
                                                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20">
                                                        <span className="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        {student.cases_count} Case{student.cases_count > 1 ? 's' : ''}
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                                                        <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Clear
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-2 md:px-6 py-2 md:py-4 whitespace-nowrap block md:table-cell text-left md:text-right text-sm font-medium mt-4 md:mt-0 border-t border-slate-100 dark:border-slate-800 md:border-none pt-4 md:pt-4 pr-16 lg:pr-6">
                                                <div className="flex items-center justify-start md:justify-end gap-2">
                                                    <Link 
                                                        href={route('cases.create', { student_id: student.id })} 
                                                        className="p-2 text-slate-400 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-400 hover:bg-rose-50 dark:bg-rose-900/20 dark:hover:bg-rose-500/10 rounded-xl transition-all duration-150" 
                                                        title="Log Violation"
                                                    >
                                                        <FilePlus className="w-4 h-4" />
                                                    </Link>
                                                    <a 
                                                        href={route('students.show', student.id)} 
                                                        className="p-2 text-slate-400 hover:text-indigo-600 dark:text-indigo-400 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/20 dark:hover:bg-indigo-500/10 rounded-xl transition-all duration-150"
                                                        title="View Profile"
                                                        aria-label={`View profile of ${student.full_name}`}
                                                    >
                                                        <Eye className="w-4 h-4" />
                                                    </a>
                                                    <a 
                                                        href={route('students.edit', student.id)} 
                                                        className="p-2 text-slate-400 hover:text-amber-600 dark:text-amber-400 dark:hover:text-amber-400 hover:bg-amber-50 dark:bg-amber-900/20 dark:hover:bg-amber-500/10 rounded-xl transition-all duration-150"
                                                        title="Edit Profile"
                                                        aria-label={`Edit profile of ${student.full_name}`}
                                                    >
                                                        <Edit className="w-4 h-4" />
                                                    </a>
                                                    <button 
                                                        onClick={() => handleDelete(student.id)} 
                                                        className="p-2 text-slate-400 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-400 hover:bg-rose-50 dark:bg-rose-900/20 dark:hover:bg-rose-500/10 rounded-xl transition-all duration-150"
                                                        title="Delete Profile"
                                                        aria-label={`Delete profile of ${student.full_name}`}
                                                    >
                                                        <Trash2 className="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </motion.tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="4">
                                            <EmptyState
                                                icon={Users}
                                                title="No Students Found"
                                                message="Refine your search parameters or add a new student."
                                                action={
                                                    <a href={route('students.create')} className="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors">
                                                        Add First Student
                                                    </a>
                                                }
                                            />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    
                    {/* Pagination */}
                    {students.links && students.links.length > 3 && (
                        <div className="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
                            <div className="text-sm text-slate-500 dark:text-slate-400">
                                Showing <span className="font-medium text-slate-900 dark:text-white">{students.from || 0}</span> to <span className="font-medium text-slate-900 dark:text-white">{students.to || 0}</span> of <span className="font-medium text-slate-900 dark:text-white">{students.total}</span> results
                            </div>
                            <div className="flex gap-1">
                                {students.links.map((link, i) => (
                                    <Link
                                        key={i}
                                        href={link.url || '#'}
                                        className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${
                                            link.active 
                                                ? 'bg-indigo-600 text-white' 
                                                : link.url 
                                                    ? 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700' 
                                                    : 'bg-transparent text-slate-400 dark:text-slate-500 dark:text-slate-400 cursor-not-allowed'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </motion.div>
            </motion.div>
            )}
        </AuthenticatedLayout>
    );
}
