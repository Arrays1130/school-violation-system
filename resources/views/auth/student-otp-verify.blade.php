<x-public-layout>
    @section('title', 'Verify OTP')
    @section('max-width', 'max-w-md')
    @section('header', 'OTP Verification')

    <div class="w-full">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-500/10 text-indigo-400 mb-4 ring-1 ring-indigo-500/20 shadow-[0_0_30px_rgba(99,102,241,0.2)]">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2 tracking-tight">Verify Your Email</h2>
            <p class="text-indigo-200/80 text-sm">We've sent a code to <span class="text-white font-medium">{{ session('registration_data.email') }}</span></p>
        </div>

        <div class="bg-white/95 backdrop-blur-xl rounded-2xl ring-1 ring-slate-200/50 shadow-2xl p-6 sm:p-8">
            @if (session('error'))
                <div class="mb-4 bg-red-50 text-red-600 p-4 rounded-xl text-sm font-semibold border border-red-100 text-center">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div class="mb-4 bg-green-50 text-green-600 p-4 rounded-xl text-sm font-semibold border border-green-100 text-center">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('student.register.verify_otp') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 text-center">Enter 6-Digit OTP</label>
                    <input type="text" name="otp" required maxlength="6" pattern="\d{6}" placeholder="------"
                        class="w-full text-center text-3xl tracking-[1em] py-4 bg-gray-50 border border-gray-200 rounded-xl font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-300">
                </div>
                
                <button type="submit" class="w-full px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                    Verify & Complete Registration
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-xs text-slate-500 mb-2">Didn't receive the code?</p>
                <form action="{{ route('student.register.resend_otp') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors">Resend OTP</button>
                </form>
            </div>
            </div>
        </div>
    </div>
</x-public-layout>
