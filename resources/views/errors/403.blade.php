<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé — App Atelier</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">

        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-2">Accès refusé</h1>
        <p class="text-slate-500 mb-2">
            {{ $exception->getMessage() ?: 'Vous n\'avez pas la permission d\'accéder à cette page.' }}
        </p>
        <p class="text-slate-400 text-sm mb-8">Contactez votre administrateur si vous pensez que c'est une erreur.</p>

        <a href="{{ url('/') }}"
           class="inline-block bg-slate-700 hover:bg-slate-800 text-white font-semibold py-3 px-8 rounded-xl transition-colors shadow-sm">
            Retour à l'accueil
        </a>

    </div>
</body>
</html>
