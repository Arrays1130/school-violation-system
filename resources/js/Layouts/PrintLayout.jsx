import { Head } from '@inertiajs/react';

export default function PrintLayout({ title, children }) {
    return (
        <div className="bg-slate-900 text-black min-h-screen p-6 print:p-0">
            <Head title={title} />
            <style>{`
                @media print {
                    .no-print { display: none !important; }
                    @page { size: portrait; margin: 1cm; }
                }
            `}</style>
            {children}
        </div>
    );
}
