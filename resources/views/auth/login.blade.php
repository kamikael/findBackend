<x-guest-layout>

    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">
            Connexion à votre compte
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Accédez à votre espace donateur ou organisateur
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Custom Error Message (status blocked etc.) -->
    @if(session('error'))
        <div class="mb-4 text-sm text-red-600 bg-red-100 p-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Adresse Email" />

            <x-text-input id="email"
                          class="block mt-1 w-full"
                          type="email"
                          name="email"
                          :value="old('email')"
                          required
                          autofocus
                          autocomplete="username" />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Mot de passe" />

            <x-text-input id="password"
                          class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required
                          autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me"
                       type="checkbox"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                       name="remember">
                <span class="ms-2 text-sm text-gray-600">
                    Se souvenir de moi
                </span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-600 hover:underline"
                   href="{{ route('password.request') }}">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <!-- Submit -->
        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                Se connecter
            </x-primary-button>
        </div>

        <!-- Register Link -->
        <div class="mt-4 text-center text-sm text-gray-600">
            Pas encore de compte ?
            <a href="{{ route('register') }}"
               class="text-indigo-600 hover:underline font-medium">
                Créer un compte
            </a>
        </div>

    </form>
</x-guest-layout>