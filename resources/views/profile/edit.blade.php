<x-app-layout>
    @section('header', 'Account Security')

    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-5 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex items-center gap-5">
                <a href="{{ route('dashboard') }}" class="w-10 h-10 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all shadow-sm backdrop-blur-md hover:-translate-x-0.5">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-widest mb-2 backdrop-blur-md">
                        <i data-lucide="user-cog" class="w-3.5 h-3.5"></i>
                        Account Settings
                    </div>
                    <h2 class="text-2xl font-bold text-white tracking-tight">Profile Settings</h2>
                    <p class="text-indigo-100/70 text-xs mt-1.5">Manage your institutional credentials and security preferences.</p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Profile Info --}}
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            {{-- Password --}}
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="bg-white rounded-lg border border-red-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 bg-red-50/30">
                    <div class="max-w-2xl text-red-900">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
