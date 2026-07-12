@if(session()->has('success') || session()->has('error') || session()->has('warning'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isDark = document.documentElement.classList.contains('dark');
            const baseClass = {
                popup: '!rounded-2xl !shadow-2xl !border !border-slate-100 dark:!border-slate-700',
                title: '!text-slate-900 dark:!text-white !font-bold',
                htmlContainer: '!text-slate-600 dark:!text-slate-300',
            };

            @if(session('success'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: isDark ? '#0f172a' : '#ffffff',
                color: isDark ? '#e2e8f0' : '#1e293b',
                customClass: baseClass,
            });
            @endif

            @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Cannot Proceed',
                text: @json(session('error')),
                confirmButtonColor: '#e11d48',
                background: isDark ? '#0f172a' : '#ffffff',
                color: isDark ? '#e2e8f0' : '#1e293b',
                customClass: baseClass,
            });
            @endif

            @if(session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: @json(session('warning')),
                confirmButtonColor: '#d97706',
                background: isDark ? '#0f172a' : '#ffffff',
                color: isDark ? '#e2e8f0' : '#1e293b',
                customClass: baseClass,
            });
            @endif
        });
    </script>
@endif
