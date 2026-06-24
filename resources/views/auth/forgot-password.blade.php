<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Reset Password</h2>
        <p class="text-sm font-medium text-gray-600 leading-relaxed">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block text-sm font-semibold text-gray-700 ml-1 mb-1">{{ __('Email Address') }}</label>
            <div class="relative">
                <input id="email" class="block w-full px-4 py-2.5 bg-white/60 border border-white/50 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-transparent text-gray-900 placeholder-gray-500 backdrop-blur-sm shadow-sm transition-all" type="email" name="email" value="{{ old('email') }}" required autofocus />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600 text-sm" />
        </div>

        <div class="mt-8">
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-gray-900 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all uppercase tracking-wide">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout>
