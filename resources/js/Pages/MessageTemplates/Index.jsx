import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Modal from '@/Components/Modal';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import DangerButton from '@/Components/DangerButton';
import { 
    MessageSquare, Plus, Edit2, Trash2, MessageSquareDashed,
    PlusCircle, AlertTriangle
} from 'lucide-react';

export default function Index({ templates }) {
    const [createModalOpen, setCreateModalOpen] = useState(false);
    const [editModalOpen, setEditModalOpen] = useState(false);
    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const [activeTemplate, setActiveTemplate] = useState(null);

    const { data, setData, post, put, delete: destroy, errors, clearErrors, reset, processing } = useForm({
        title: '',
        content: '',
    });

    const openCreateModal = () => {
        clearErrors();
        reset();
        setCreateModalOpen(true);
    };

    const closeCreateModal = () => {
        setCreateModalOpen(false);
        reset();
        clearErrors();
    };

    const handleCreate = (e) => {
        e.preventDefault();
        post(route('message-templates.store'), {
            onSuccess: () => closeCreateModal(),
        });
    };

    const openEditModal = (template) => {
        setActiveTemplate(template);
        setData({
            title: template.title,
            content: template.content,
        });
        clearErrors();
        setEditModalOpen(true);
    };

    const closeEditModal = () => {
        setEditModalOpen(false);
        setActiveTemplate(null);
        reset();
        clearErrors();
    };

    const handleEdit = (e) => {
        e.preventDefault();
        put(route('message-templates.update', activeTemplate.id), {
            onSuccess: () => closeEditModal(),
        });
    };

    const openDeleteModal = (template) => {
        setActiveTemplate(template);
        setDeleteModalOpen(true);
    };

    const closeDeleteModal = () => {
        setDeleteModalOpen(false);
        setActiveTemplate(null);
    };

    const handleDelete = (e) => {
        e.preventDefault();
        destroy(route('message-templates.destroy', activeTemplate.id), {
            onSuccess: () => closeDeleteModal(),
        });
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Message Templates</h2>}
        >
            <Head title="Message Templates" />

            <div className="py-8">
                <div className="space-y-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-2xl shadow-indigo-900/20 border border-indigo-900/50">
                        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
                        <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                        
                        <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-6 z-10">
                            <div>
                                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 dark:bg-slate-900/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                                    <MessageSquare className="w-3.5 h-3.5" />
                                    Templates
                                </div>
                                <h1 className="text-3xl font-black text-white tracking-tight">Message Templates</h1>
                                <p className="text-indigo-200/70 text-sm mt-2 max-w-xl leading-relaxed font-medium">Manage pre-made messages for quick SMS and email sending.</p>
                            </div>

                            <div className="flex items-center gap-3">
                                <button onClick={openCreateModal} className="px-5 py-3 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 active:scale-95">
                                    <Plus className="w-4.5 h-4.5" />
                                    New Template
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Table */}
                    <div className="bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl rounded-3xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left whitespace-nowrap">
                                <thead className="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 text-slate-400 font-black tracking-widest uppercase text-[10px]">
                                    <tr>
                                        <th className="px-8 py-5">Title</th>
                                        <th className="px-8 py-5">Content Preview</th>
                                        <th className="px-8 py-5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-50 bg-white/50 dark:bg-slate-900/50">
                                    {templates.data.map((template) => (
                                        <tr key={template.id} className="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors group">
                                            <td className="px-8 py-5">
                                                <span className="font-bold text-sm text-slate-800 dark:text-slate-200">{template.title}</span>
                                            </td>
                                            <td className="px-8 py-5">
                                                <div className="max-w-md truncate text-sm font-medium text-slate-500 dark:text-slate-400" title={template.content}>
                                                    {template.content}
                                                </div>
                                            </td>
                                            <td className="px-8 py-5 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <button onClick={() => openEditModal(template)} className="text-slate-400 hover:text-indigo-600 dark:text-indigo-400 transition-colors p-2.5 bg-white dark:bg-slate-900 border border-transparent hover:border-indigo-200 hover:bg-indigo-50 dark:bg-indigo-900/20 rounded-xl shadow-sm active:scale-95" title="Edit">
                                                        <Edit2 className="w-4 h-4" />
                                                    </button>
                                                    <button onClick={() => openDeleteModal(template)} className="text-slate-400 hover:text-rose-600 dark:text-rose-400 transition-colors p-2.5 bg-white dark:bg-slate-900 border border-transparent hover:border-rose-200 hover:bg-rose-50 dark:bg-rose-900/20 rounded-xl shadow-sm active:scale-95" title="Delete">
                                                        <Trash2 className="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}

                                    {templates.data.length === 0 && (
                                        <tr>
                                            <td colSpan="3" className="px-8 py-24 text-center">
                                                <div className="flex flex-col items-center justify-center">
                                                    <div className="w-16 h-16 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-inner">
                                                        <MessageSquareDashed className="w-8 h-8 text-slate-300" />
                                                    </div>
                                                    <p className="text-slate-900 dark:text-white font-black text-base mb-1">No templates found.</p>
                                                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Click 'New Template' to create one.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                        
                        {templates.links && templates.links.length > 3 && (
                            <div className="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex justify-center">
                                <div className="flex flex-wrap gap-1">
                                    {templates.links.map((link, k) => (
                                        <Link
                                            key={k}
                                            href={link.url || '#'}
                                            className={`px-3 py-1.5 text-[13px] font-semibold rounded-lg transition-colors ${
                                                link.active 
                                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' 
                                                    : !link.url 
                                                        ? 'text-slate-300 cursor-not-allowed' 
                                                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 bg-slate-100'
                                            }`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Create Modal */}
                <Modal show={createModalOpen} onClose={closeCreateModal} maxWidth="lg">
                    <form onSubmit={handleCreate} className="p-8">
                        <h2 className="text-xl font-black text-slate-900 dark:text-white mb-6 flex items-center gap-3 tracking-tight">
                            <div className="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-100">
                                <PlusCircle className="w-5 h-5" />
                            </div>
                            Create Message Template
                        </h2>

                        <div className="space-y-5">
                            <div>
                                <InputLabel htmlFor="title" value="Template Title" className="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest text-[10px]" />
                                <TextInput 
                                    id="title" 
                                    name="title" 
                                    type="text" 
                                    className="mt-2 block w-full rounded-xl" 
                                    value={data.title}
                                    onChange={e => setData('title', e.target.value)}
                                    required 
                                    placeholder="e.g. Notice of Minor Violation" 
                                />
                                <InputError className="mt-2" message={errors.title} />
                            </div>

                            <div>
                                <InputLabel htmlFor="content" value="Message Content" className="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest text-[10px]" />
                                <textarea 
                                    id="content" 
                                    name="content" 
                                    rows="5" 
                                    className="mt-2 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-sm" 
                                    value={data.content}
                                    onChange={e => setData('content', e.target.value)}
                                    required 
                                    placeholder="You can use {{ $student->full_name }} for the student's name."
                                ></textarea>
                                <p className="text-[11px] font-bold text-slate-400 mt-2">
                                    Tip: Use <code className="bg-slate-100 px-1.5 py-0.5 rounded text-indigo-500">{"{{ $student->full_name }}"}</code> inside the message to automatically insert the student's name.
                                </p>
                                <InputError className="mt-2" message={errors.content} />
                            </div>
                        </div>

                        <div className="mt-8 flex justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <SecondaryButton onClick={closeCreateModal}>Cancel</SecondaryButton>
                            <PrimaryButton disabled={processing} className="bg-indigo-600 hover:bg-indigo-700">Save Template</PrimaryButton>
                        </div>
                    </form>
                </Modal>

                {/* Edit Modal */}
                <Modal show={editModalOpen} onClose={closeEditModal} maxWidth="lg">
                    <form onSubmit={handleEdit} className="p-8">
                        <h2 className="text-xl font-black text-slate-900 dark:text-white mb-6 flex items-center gap-3 tracking-tight">
                            <div className="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-100">
                                <Edit2 className="w-5 h-5" />
                            </div>
                            Edit Message Template
                        </h2>

                        <div className="space-y-5">
                            <div>
                                <InputLabel htmlFor="edit_title" value="Template Title" className="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest text-[10px]" />
                                <TextInput 
                                    id="edit_title" 
                                    name="title" 
                                    type="text" 
                                    className="mt-2 block w-full rounded-xl" 
                                    value={data.title}
                                    onChange={e => setData('title', e.target.value)}
                                    required 
                                />
                                <InputError className="mt-2" message={errors.title} />
                            </div>

                            <div>
                                <InputLabel htmlFor="edit_content" value="Message Content" className="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest text-[10px]" />
                                <textarea 
                                    id="edit_content" 
                                    name="content" 
                                    rows="5" 
                                    className="mt-2 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-sm" 
                                    value={data.content}
                                    onChange={e => setData('content', e.target.value)}
                                    required
                                ></textarea>
                                <p className="text-[11px] font-bold text-slate-400 mt-2">
                                    Tip: Use <code className="bg-slate-100 px-1.5 py-0.5 rounded text-indigo-500">{"{{ $student->full_name }}"}</code> inside the message to automatically insert the student's name.
                                </p>
                                <InputError className="mt-2" message={errors.content} />
                            </div>
                        </div>

                        <div className="mt-8 flex justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <SecondaryButton onClick={closeEditModal}>Cancel</SecondaryButton>
                            <PrimaryButton disabled={processing} className="bg-indigo-600 hover:bg-indigo-700">Update Template</PrimaryButton>
                        </div>
                    </form>
                </Modal>

                {/* Delete Modal */}
                <Modal show={deleteModalOpen} onClose={closeDeleteModal} maxWidth="sm">
                    <form onSubmit={handleDelete} className="p-8 text-center">
                        <div className="w-20 h-20 bg-rose-50 dark:bg-rose-900/20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-rose-100">
                            <AlertTriangle className="w-10 h-10 text-rose-500" />
                        </div>
                        <h2 className="text-2xl font-black text-slate-900 dark:text-white mb-2 tracking-tight">Delete Template?</h2>
                        <p className="text-sm font-medium text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">Are you sure you want to delete this message template? This action cannot be undone.</p>
                        
                        <div className="flex justify-center gap-3">
                            <SecondaryButton onClick={closeDeleteModal}>Cancel</SecondaryButton>
                            <DangerButton disabled={processing}>Yes, Delete</DangerButton>
                        </div>
                    </form>
                </Modal>

            </div>
        </AuthenticatedLayout>
    );
}
