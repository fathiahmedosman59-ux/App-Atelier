@extends('layouts.app')
@section('title', 'Journal d\'activité')
@section('page-title', 'Journal d\'activité')
@section('page-subtitle', 'Historique complet des actions effectuées dans le système')

@section('content')
<div class="space-y-4">

{{-- Filtres --}}
<div class="bg-white rounded-2xl border border-gray-200 p-4">
    <form method="GET" action="{{ route('activites.index') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Utilisateur</label>
            <select name="user_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option value="">Tous les utilisateurs</option>
                @foreach($utilisateurs as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Type d'action</label>
            <select name="action" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option value="">Toutes les actions</option>
                <option value="connexion"    {{ request('action') === 'connexion'    ? 'selected' : '' }}>Connexions</option>
                <option value="creer"        {{ request('action') === 'creer'        ? 'selected' : '' }}>Créations</option>
                <option value="modifier"     {{ request('action') === 'modifier'     ? 'selected' : '' }}>Modifications</option>
                <option value="supprimer"    {{ request('action') === 'supprimer'    ? 'selected' : '' }}>Suppressions</option>
                <option value="accepter"     {{ request('action') === 'accepter'     ? 'selected' : '' }}>Acceptations</option>
                <option value="facture"      {{ request('action') === 'facture'      ? 'selected' : '' }}>Factures</option>
                <option value="payer"        {{ request('action') === 'payer'        ? 'selected' : '' }}>Paiements</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Date</label>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
            Filtrer
        </button>
        @if(request()->hasAny(['user_id','action','date']))
        <a href="{{ route('activites.index') }}" class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2 border border-gray-200 rounded-lg transition-colors">
            Réinitialiser
        </a>
        @endif
    </form>
</div>

{{-- Tableau --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-3 border-b border-gray-100 flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $activites->total() }} événement(s)</p>
        <p class="text-xs text-slate-400">Page {{ $activites->currentPage() }} / {{ $activites->lastPage() }}</p>
    </div>

    @if($activites->isEmpty())
    <div class="py-16 text-center text-slate-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="font-medium">Aucune activité enregistrée</p>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 w-40">Date & Heure</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 w-44">Utilisateur</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500">Description</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 w-36">Objet</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 w-28">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($activites as $a)
            @php $color = $a->getActionColor(); @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-700">{{ $a->created_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-slate-400">{{ $a->created_at->format('H:i:s') }}</p>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0
                            {{ $a->user_role === 'admin' ? 'bg-orange-500' : ($a->user_role === 'chef_garage' ? 'bg-purple-500' : ($a->user_role === 'mecanicien' ? 'bg-blue-500' : 'bg-green-500')) }}">
                            {{ strtoupper(substr($a->user_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-800">{{ $a->user_name }}</p>
                            <p class="text-xs text-slate-400">{{ $a->user_role ? ucfirst(str_replace('_', ' ', $a->user_role)) : '' }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                            {{ $color === 'green'  ? 'bg-green-100 text-green-700'   :
                               ($color === 'blue'  ? 'bg-blue-100 text-blue-700'     :
                               ($color === 'red'   ? 'bg-red-100 text-red-700'       :
                               ($color === 'orange'? 'bg-orange-100 text-orange-700' :
                               ($color === 'slate' ? 'bg-slate-100 text-slate-600'   :
                                                     'bg-gray-100 text-gray-600')))) }}">
                            {{ str_replace('_', ' ', $a->action) }}
                        </span>
                        <span class="text-slate-700 text-xs">{{ $a->description }}</span>
                    </div>
                </td>
                <td class="px-5 py-3">
                    @if($a->subject_type)
                    <p class="text-xs text-slate-500">{{ $a->subject_type }}</p>
                    <p class="text-xs font-mono text-slate-400">#{{ $a->subject_id }} {{ $a->subject_label ? '— '.$a->subject_label : '' }}</p>
                    @endif
                </td>
                <td class="px-5 py-3 text-xs font-mono text-slate-400">{{ $a->ip_address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($activites->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $activites->links() }}</div>
    @endif
    @endif
</div>

</div>
@endsection
