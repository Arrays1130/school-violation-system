import Swal from 'sweetalert2';
import { swalTheme } from '@/lib/sweetAlert';

// Shows a toast with an Undo action after a soft delete.
// onUndo runs only if the user clicks Undo before the toast times out.
export default function showUndoToast(message, onUndo) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: message,
        showConfirmButton: true,
        confirmButtonText: 'Undo',
        showCloseButton: true,
        timer: 6000,
        timerProgressBar: true,
        customClass: {
            popup: swalTheme.customClass.popup,
            confirmButton: '!px-4 !py-1.5 !bg-indigo-600 hover:!bg-indigo-700 !text-white !rounded-lg !font-bold !text-xs !shadow-sm',
        },
        buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
            onUndo();
        }
    });
}
