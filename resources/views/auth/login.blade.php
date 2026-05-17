@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
<div class="min-h-screen flex">

    {{-- Panneau gauche — Branding --}}
    <div class="hidden lg:flex lg:w-5/12 bg-slate-900 flex-col justify-between p-12 relative overflow-hidden">

        {{-- Cercles décoratifs --}}
        <div class="absolute -top-16 -left-16 w-64 h-64 bg-orange-500 opacity-10 rounded-full"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-orange-500 opacity-5 rounded-full"></div>

        {{-- Logo --}}
        <div class="relative z-10 flex items-center gap-3">
            <img src="{{ asset('logo-stcd.jpg') }}" alt="STCD Motors" class="h-14 w-auto rounded-xl shadow-lg flex-shrink-0">
            <div>
                <p class="text-white font-bold text-lg leading-tight">STCD Motors</p>
                <p class="text-slate-400 text-xs italic">Your Dream Car, One Visit Away</p>
            </div>
        </div>

        {{-- Texte central --}}
        <div class="relative z-10">
            <h2 class="text-4xl font-bold text-white leading-tight mb-4">
                Gérez votre atelier<br>
                <span class="text-orange-400">en toute simplicité</span>
            </h2>
            <p class="text-slate-400 text-base leading-relaxed mb-8">
                Clients, véhicules, réparations, devis et factures — tout dans un seul outil conçu pour les ateliers mécaniques.
            </p>

            {{-- Features --}}
            <div class="space-y-3">
                @foreach([
                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'text' => 'Gestion des clients et véhicules'],
                    ['icon' => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63', 'text' => 'Suivi des réparations en temps réel'],
                    ['icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'text' => 'Devis et facturation intégrés'],
                ] as $feature)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-slate-800 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-slate-300 text-sm">{{ $feature['text'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Footer --}}
        <div class="relative z-10">
            <p class="text-slate-600 text-xs">© {{ date('Y') }} STCD Motors — Tous droits réservés</p>
        </div>
    </div>

    {{-- Panneau droit — Formulaire --}}
    <div class="w-full lg:w-7/12 flex items-center justify-center bg-gray-50 px-6 py-12">
        <div class="w-full max-w-md">

            {{-- Mobile logo --}}
            <div class="flex lg:hidden items-center gap-3 mb-8 justify-center">
                <img src="{{ asset('logo-stcd.jpg') }}" alt="STCD Motors" class="h-10 w-auto rounded-xl">
                <div>
                    <p class="font-bold text-slate-900 text-base leading-tight">STCD Motors</p>
                    <p class="text-slate-400 text-xs italic">Your Dream Car, One Visit Away</p>
                </div>
            </div>

            {{-- Titre --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900 mb-1">Connexion</h1>
                <p class="text-slate-500">Bienvenue, veuillez entrer vos identifiants.</p>
            </div>

            {{-- Erreurs générales --}}
            @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm text-red-700">{{ $errors->first('email') }}</p>
            </div>
            @endif

            {{-- Formulaire --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Adresse email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        autofocus
                        placeholder="exemple@atelier.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-slate-900 placeholder-slate-400 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('email') border-red-400 bg-red-50 @enderror"
                    >
                </div>

                {{-- Mot de passe --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Mot de passe
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-slate-900 placeholder-slate-400 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition @error('password') border-red-400 bg-red-50 @enderror"
                        >
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Se souvenir + mot de passe oublié --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                        <label for="remember" class="ml-2 text-sm text-slate-600">Se souvenir de moi</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-sm text-orange-500 hover:text-orange-600 font-medium">
                        Mot de passe oublié ?
                    </a>
                </div>

                {{-- Bouton --}}
                <button
                    type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                >
                    Se connecter
                </button>

            </form>

            <p class="mt-8 text-center text-xs text-slate-400">
                Vous n'avez pas de compte ? Contactez votre administrateur.
            </p>

        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endsection
