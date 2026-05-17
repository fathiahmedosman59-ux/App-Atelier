@extends('layouts.auth')

@section('title', 'Mot de passe oublié')

@section('content')
<div class="min-h-screen flex">

    {{-- Panneau gauche — Branding --}}
    <div class="hidden lg:flex lg:w-5/12 bg-slate-900 flex-col justify-between p-12 relative overflow-hidden">

        <div class="absolute -top-16 -left-16 w-64 h-64 bg-orange-500 opacity-10 rounded-full"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-orange-500 opacity-5 rounded-full"></div>

        <div class="relative z-10 flex items-center gap-3">
            <div class="w-11 h-11 bg-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-lg leading-tight">App Atelier</p>
                <p class="text-slate-400 text-xs">Système de gestion</p>
            </div>
        </div>

        <div class="relative z-10">
            <h2 class="text-4xl font-bold text-white leading-tight mb-4">
                Récupérez votre<br>
                <span class="text-orange-400">accès facilement</span>
            </h2>
            <p class="text-slate-400 text-base leading-relaxed">
                Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
            </p>
        </div>

        <div class="relative z-10">
            <p class="text-slate-600 text-xs">© {{ date('Y') }} App Atelier — Tous droits réservés</p>
        </div>
    </div>

    {{-- Panneau droit — Formulaire --}}
    <div class="w-full lg:w-7/12 flex items-center justify-center bg-gray-50 px-6 py-12">
        <div class="w-full max-w-md">

            {{-- Mobile logo --}}
            <div class="flex lg:hidden items-center gap-3 mb-8 justify-center">
                <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-slate-900">App Atelier</span>
            </div>

            {{-- Titre --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900 mb-1">Mot de passe oublié</h1>
                <p class="text-slate-500">Entrez votre email pour recevoir un lien de réinitialisation.</p>
            </div>

            {{-- Message de succès --}}
            @if (session('status'))
            <div class="mb-5 bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-start gap-3">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-green-700">{{ session('status') }}</p>
            </div>
            @endif

            {{-- Erreurs --}}
            @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm text-red-700">{{ $errors->first('email') }}</p>
            </div>
            @endif

            {{-- Formulaire --}}
            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

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

                <button
                    type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                >
                    Envoyer le lien de réinitialisation
                </button>

            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-orange-500 hover:text-orange-600 font-medium">
                    ← Retour à la connexion
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
