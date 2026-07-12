import { Dialog, Transition } from '@headlessui/react';
import { Fragment, useEffect, useRef } from 'react';
import { Button } from '@/Components/ui/button';
import { showConfirmDialog } from '@/lib/sweetAlert';

export default function ConfirmDialog({
    open,
    onClose,
    onConfirm,
    title,
    description,
    confirmLabel = 'Confirm',
    cancelLabel = 'Cancel',
    destructive = false,
    processing = false,
    children,
}) {
    const hasFormContent = !!children;
    const swalActiveRef = useRef(false);
    const prevOpenRef = useRef(false);
    const onCloseRef = useRef(onClose);
    const onConfirmRef = useRef(onConfirm);

    onCloseRef.current = onClose;
    onConfirmRef.current = onConfirm;

    useEffect(() => {
        if (hasFormContent || processing) {
            prevOpenRef.current = open;
            return;
        }

        const justOpened = open && !prevOpenRef.current;
        prevOpenRef.current = open;

        if (!justOpened || swalActiveRef.current) {
            return;
        }

        swalActiveRef.current = true;

        showConfirmDialog({
            title,
            text: description,
            confirmText: confirmLabel,
            cancelText: cancelLabel,
            destructive,
        }).then((result) => {
            swalActiveRef.current = false;
            onCloseRef.current();
            if (result.isConfirmed) {
                onConfirmRef.current();
            }
        });
    }, [open, hasFormContent, processing, title, description, confirmLabel, cancelLabel, destructive]);

    if (!hasFormContent) {
        return null;
    }

    return (
        <Transition show={open} as={Fragment}>
            <Dialog as="div" className="relative z-50" onClose={onClose}>
                <Transition.Child
                    as={Fragment}
                    enter="ease-out duration-200"
                    enterFrom="opacity-0"
                    enterTo="opacity-100"
                    leave="ease-in duration-150"
                    leaveFrom="opacity-100"
                    leaveTo="opacity-0"
                >
                    <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" />
                </Transition.Child>

                <div className="fixed inset-0 overflow-y-auto p-4">
                    <div className="flex min-h-full items-center justify-center">
                        <Transition.Child
                            as={Fragment}
                            enter="ease-out duration-200"
                            enterFrom="opacity-0 scale-95"
                            enterTo="opacity-100 scale-100"
                            leave="ease-in duration-150"
                            leaveFrom="opacity-100 scale-100"
                            leaveTo="opacity-0 scale-95"
                        >
                            <Dialog.Panel className="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xl p-6">
                                <Dialog.Title className="text-lg font-bold text-slate-900 dark:text-white">
                                    {title}
                                </Dialog.Title>
                                {description && (
                                    <Dialog.Description className="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                        {description}
                                    </Dialog.Description>
                                )}
                                {children && <div className="mt-4">{children}</div>}
                                <div className="mt-6 flex justify-end gap-3">
                                    <Button type="button" variant="outline" onClick={onClose} disabled={processing}>
                                        {cancelLabel}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant={destructive ? 'destructive' : 'default'}
                                        onClick={onConfirm}
                                        disabled={processing}
                                    >
                                        {processing ? 'Please wait...' : confirmLabel}
                                    </Button>
                                </div>
                            </Dialog.Panel>
                        </Transition.Child>
                    </div>
                </div>
            </Dialog>
        </Transition>
    );
}
