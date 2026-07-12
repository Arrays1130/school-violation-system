import Swal from 'sweetalert2';

const isDark = () => document.documentElement.classList.contains('dark');

const basePopupClass = '!rounded-2xl !shadow-2xl !border !border-slate-100 dark:!border-slate-700';

export const swalTheme = {
    customClass: {
        popup: basePopupClass,
        title: '!text-slate-900 dark:!text-white !font-bold',
        htmlContainer: '!text-slate-600 dark:!text-slate-300',
        confirmButton: '!rounded-xl !font-bold !px-5 !py-2.5',
        cancelButton: '!rounded-xl !font-bold !px-5 !py-2.5',
    },
};

export function showSuccessToast(message, title = 'Success') {
    return Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title,
        text: message,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        background: isDark() ? '#0f172a' : '#ffffff',
        color: isDark() ? '#e2e8f0' : '#1e293b',
        customClass: {
            popup: `${basePopupClass} !py-3`,
            title: '!text-sm !font-bold !text-slate-800 dark:!text-slate-200',
            htmlContainer: '!text-sm !text-slate-500 dark:!text-slate-400',
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
    });
}

export function showErrorAlert(message, title = 'Cannot Proceed') {
    return Swal.fire({
        icon: 'error',
        title,
        text: message,
        confirmButtonColor: '#e11d48',
        background: isDark() ? '#0f172a' : '#ffffff',
        color: isDark() ? '#e2e8f0' : '#1e293b',
        ...swalTheme,
    });
}

export function showWarningAlert(message, title = 'Warning') {
    return Swal.fire({
        icon: 'warning',
        title,
        text: message,
        confirmButtonColor: '#d97706',
        background: isDark() ? '#0f172a' : '#ffffff',
        color: isDark() ? '#e2e8f0' : '#1e293b',
        ...swalTheme,
    });
}

export function showInfoToast(message, title = 'Notification') {
    return Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title,
        text: message,
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        showCloseButton: true,
        background: isDark() ? '#0f172a' : '#ffffff',
        color: isDark() ? '#e2e8f0' : '#1e293b',
        customClass: {
            popup: `${basePopupClass} !py-3`,
            title: '!text-sm !font-bold !text-slate-800 dark:!text-slate-200',
            htmlContainer: '!text-sm !text-slate-500 dark:!text-slate-400',
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
    });
}

export function showConfirmDialog({
    title,
    text,
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    destructive = false,
    icon = destructive ? 'warning' : 'question',
}) {
    return Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        confirmButtonColor: destructive ? '#e11d48' : '#4f46e5',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        focusCancel: destructive,
        background: isDark() ? '#0f172a' : '#ffffff',
        color: isDark() ? '#e2e8f0' : '#1e293b',
        ...swalTheme,
    });
}

let lastFlashSignature = '';
let lastFlashShownAt = 0;

export function handleFlashMessages(flash) {
    if (!flash) {
        return;
    }

    const success = flash.success ?? null;
    const error = flash.error ?? null;
    const warning = flash.warning ?? null;

    if (!success && !error && !warning) {
        return;
    }

    const signature = `${success ?? ''}|${error ?? ''}|${warning ?? ''}`;
    const now = Date.now();
    if (signature === lastFlashSignature && now - lastFlashShownAt < 800) {
        return;
    }

    lastFlashSignature = signature;
    lastFlashShownAt = now;

    if (success) {
        showSuccessToast(success);
    }
    if (error) {
        showErrorAlert(error);
    }
    if (warning) {
        showWarningAlert(warning);
    }
}
