@props(['active' => false, 'href' => '#'])

<a href="{{ $href }}"
   class="{{ $active
       ? 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium bg-orange-500 text-white'
       : 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white transition-colors' }}">
    {{ $slot }}
</a>
