<x-public-layout>
    @section('title', 'Welcome to Registration')
    @section('max-width', 'max-w-xl')

    <div class="w-full">
        <div class="bg-white/95 backdrop-blur-xl rounded-2xl ring-1 ring-slate-200/50 shadow-2xl overflow-hidden p-8 md:p-12 text-center">
            
            <div class="mx-auto w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-8 ring-1 ring-indigo-100 shadow-inner">
                <i data-lucide="graduation-cap" class="w-12 h-12 text-indigo-600"></i>
            </div>
            
            <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4 tracking-tight">Welcome, Future Student! 👋</h2>
            
            <p class="text-slate-600 mb-8 leading-relaxed text-base md:text-lg">
                We're excited to have you join <strong>I-Link College of Science and Technology</strong>. This portal will help you easily create your student profile and get verified in just a few steps.
            </p>

            <div class="bg-indigo-50/50 rounded-xl p-6 mb-10 border border-indigo-100/50 text-left">
                <h3 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-indigo-600"></i>
                    Before you begin, please prepare:
                </h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500 shrink-0"></i>
                        <span>Your <strong>institutional email</strong> (@ilinkcst.edu.ph) if provided, or a working personal email address.</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500 shrink-0"></i>
                        <span>Your basic personal information and contact details.</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500 shrink-0"></i>
                        <span>A working email inbox because we will send an OTP for verification.</span>
                    </li>
                </ul>
            </div>
            
            <a href="{{ route('student.register.form') }}" class="inline-flex items-center justify-center w-full md:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-lg font-bold shadow-lg shadow-indigo-600/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 gap-3 group">
                Proceed to Registration
                <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
            </a>
            
        </div>
    </div>
</x-public-layout>
