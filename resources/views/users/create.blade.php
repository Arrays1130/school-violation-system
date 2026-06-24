<x-app-layout>
    @section('header', 'Create User')

    <div class="space-y-6">
        {{-- Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 mb-8 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>

            <div class="relative flex items-center gap-4">
                <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white/10 border border-white/15 text-white hover:bg-white/20 transition-all backdrop-blur-md">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-1.5 backdrop-blur-md">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                        New Account
                    </div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight">Create User</h1>
                    <p class="text-indigo-100/70 text-sm mt-0.5">Add a new system user with specific role and access level.</p>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl ring-1 ring-slate-100/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <h2 class="text-base font-bold text-slate-800">Account Information</h2>
                <p class="text-sm text-slate-500 mt-0.5">Fill in the details to create a new user account.</p>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="px-8 py-6 space-y-6">
                @csrf

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="flex items-start gap-3 px-5 py-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-bold mb-1">Please fix the following errors:</p>
                            <ul class="list-disc list-inside space-y-0.5 text-red-700">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Full Name --}}
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g. Juan Dela Cruz"
                                   class="w-full pl-10 pr-4 py-2.5 bg-white border {{ $errors->has('name') ? 'border-red-400 focus:ring-red-500/20 focus:border-red-400' : 'border-gray-200 focus:ring-blue-500/20 focus:border-blue-500' }} text-slate-800 rounded-xl text-sm focus:ring-2 transition-all duration-200">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                        </div>
                        @error('name')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email Address --}}
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   placeholder="user@school.edu.ph"
                                   class="w-full pl-10 pr-4 py-2.5 bg-white border {{ $errors->has('email') ? 'border-red-400 focus:ring-red-500/20 focus:border-red-400' : 'border-gray-200 focus:ring-blue-500/20 focus:border-blue-500' }} text-slate-800 rounded-xl text-sm focus:ring-2 transition-all duration-200">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password"
                                   placeholder="Minimum 8 characters"
                                   class="w-full pl-10 pr-4 py-2.5 bg-white border {{ $errors->has('password') ? 'border-red-400 focus:ring-red-500/20 focus:border-red-400' : 'border-gray-200 focus:ring-blue-500/20 focus:border-blue-500' }} text-slate-800 rounded-xl text-sm focus:ring-2 transition-all duration-200">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   placeholder="Re-enter password"
                                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-800 rounded-xl text-sm transition-all duration-200">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <i data-lucide="lock-keyhole" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Role --}}
                    <div>
                        <label for="role" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            System Role <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="role" name="role"
                                    class="w-full pl-10 pr-10 py-2.5 bg-white border {{ $errors->has('role') ? 'border-red-400 focus:ring-red-500/20 focus:border-red-400' : 'border-gray-200 focus:ring-blue-500/20 focus:border-blue-500' }} text-slate-800 rounded-xl text-sm focus:ring-2 transition-all duration-200 appearance-none">
                                <option value="">Select a role...</option>
                                <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="dean" {{ old('role') === 'dean' ? 'selected' : '' }}>Dean</option>
                            </select>
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <i data-lucide="shield" class="w-4 h-4"></i>
                            </div>
                            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                        @error('role')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div>
                        <label for="department" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Department <span class="text-xs text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <div class="relative">
                            <select id="department" name="department"
                                    class="w-full pl-10 pr-10 py-2.5 bg-white border border-gray-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-800 rounded-xl text-sm transition-all duration-200 appearance-none">
                                <option value="">No specific department</option>
                                <option value="Bachelor Of Science In Information System" {{ old('department') === 'Bachelor Of Science In Information System' ? 'selected' : '' }}>Bachelor Of Science In Information System</option>
                                <option value="Bachelor Of Science In Criminology" {{ old('department') === 'Bachelor Of Science In Criminology' ? 'selected' : '' }}>Bachelor Of Science In Criminology</option>
                                <option value="Bachelor Of Technical Vocational Teachers Education" {{ old('department') === 'Bachelor Of Technical Vocational Teachers Education' ? 'selected' : '' }}>Bachelor Of Technical Vocational Teachers Education</option>
                                <option value="College Of Business And Accounting Education" {{ old('department') === 'College Of Business And Accounting Education' ? 'selected' : '' }}>College Of Business And Accounting Education</option>
                            </select>
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <i data-lucide="building-2" class="w-4 h-4"></i>
                            </div>
                            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                        @error('department')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('users.index') }}"
                       class="px-5 py-2.5 bg-white text-gray-600 border border-gray-200 rounded-xl text-sm font-semibold hover:bg-gray-50 hover:text-gray-800 transition-all duration-200">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-500/20 transition-all duration-200">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        Create User Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
