<x-public-layout>
    @section('title', 'Registration Successful')
@section('max-width', 'max-w-md')
    <div class="w-full">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-500/10 text-emerald-400 mb-6 ring-1 ring-emerald-500/20 shadow-[0_0_30px_rgba(16,185,129,0.2)]">
                <i data-lucide="check-circle" class="w-10 h-10"></i>
            </div>
            <h2 class="text-3xl font-bold text-white mb-2 tracking-tight">Success!</h2>
            <p class="text-emerald-200/80 text-sm">Your registration is complete.</p>
        </div>

        <div class="bg-white/95 backdrop-blur-xl rounded-2xl ring-1 ring-slate-200/50 shadow-2xl p-6 sm:p-10 text-center">
            <p class="text-slate-600 font-medium leading-relaxed">
                Your student profile has been created successfully. Your email is verified and your record is now active in our system. You may now close this page.
            </p>
        </div>
    </div>
</x-public-layout>
