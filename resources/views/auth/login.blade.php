<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />


    @if ($errors->has('login_error'))
        <div class="flex mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-800" role="alert">
            <div class="flex-shrink-0">
                {{-- Icono Alerta --}}
                <svg class="h-5 w-5 text-orange-100 dark:text-orange-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.707-4.707l-3-3a1 1 0 011.414-1.414L10 10.586l3.293-3.293a1 1 0 011.414 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-gray-50 dark:text-red-100">
                    {{  __('Error de acceso:') }}
                </h3>
                <div class="mt-2 text-sm  text-gray-50 dark:text-red-100">
                    <p role="list" class="list-disc space-y-1">
                        @foreach ($errors->get('login_error') as $message)
                            <span>{{ $message }}</span>
                        @endforeach
                    </p>
                </div>

            </div>
        </div>

    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#1B396A] shadow-sm focus:ring-[#1B396A]" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            {{-- @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif --}}

            {{-- <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button> --}}

            <button type="submit"
                class="w-full bg-[#1B396A] text-white hover:bg-[#152d54] font-semibold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1B396A] transition duration-300 ease-in-out">
                {{ __('Log in') }}
            </button>
        </div>
    </form>
</x-guest-layout>
