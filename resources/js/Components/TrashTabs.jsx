import { Link } from '@inertiajs/react';
import { Users, FolderX } from 'lucide-react';

export default function TrashTabs({ active }) {
    const tabClass = (name) =>
        `px-6 py-2.5 text-sm font-bold rounded-xl transition-all flex items-center gap-2 ${
            active === name
                ? 'bg-rose-600 text-white shadow-md shadow-rose-500/20'
                : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'
        }`;

    return (
        <div className="flex items-center gap-2 mb-8 bg-white p-1.5 rounded-2xl border border-slate-200/80 shadow-sm w-fit">
            <Link href={route('students.trash')} className={tabClass('students')}>
                <Users className="w-4 h-4" />
                Deleted Students
            </Link>
            <Link href={route('cases.trash')} className={tabClass('cases')}>
                <FolderX className="w-4 h-4" />
                Deleted Cases
            </Link>
        </div>
    );
}
