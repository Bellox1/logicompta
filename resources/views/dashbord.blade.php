@extends('layouts.accounting')

@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col gap-8">
    <!-- Hero Banner with Requested Image -->
    <div class="relative h-64 md:h-80 rounded-[2.5rem] overflow-hidden shadow-2xl mb-8 group">
        <img src="https://imgs.search.brave.com/cuK2-rWc2jMdk9S_c5NQhLhwwf_zXgj4-wU8Ou2MrN4/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9zdGF0/aWMudmVjdGVlenku/Y29tL3RpL3Bob3Rv/cy1ncmF0dWl0ZS90/Mi83MDU3NDcyMS1h/ZXJpZW4tdnVlLWRl/LWNvbXB0YWJpbGl0/ZS1lc3BhY2UtZGUt/dHJhdmFpbC1hdmVj/LW9yZGluYXRldXIt/cG9ydGFibGUtY2Fs/Y3VsYXRyaWNlLWV0/LWNhcm5ldC1zdXIt/cGFzdGVsLWJsZXUt/Y29udGV4dGUtcGhv/dG8uanBlZw" 
             alt="Espace de travail comptable" 
             class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
        
        <div class="absolute inset-0 bg-gradient-to-r from-primary/80 to-transparent flex items-center px-10 md:px-16">
            <div class="max-w-xl">
                <h1 class="text-4xl md:text-5xl font-black text-white mb-4 tracking-tighter drop-shadow-lg">
                    Bonjour, {{ Auth::user()->name ?? 'Comptable' }} !
                </h1>
                <p class="text-white/90 text-lg font-medium leading-relaxed drop-shadow-md italic">
                    "La précision est l'âme de la comptabilité."<br>
                    Prêt à gérer vos écritures aujourd'hui ?
                </p>
            </div>
        </div>
        
        <div class="absolute bottom-6 right-8 hidden md:flex items-center gap-3 bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 rounded-2xl">
            <div class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center text-white">
                <i data-lucide="calendar" class="w-5 h-5"></i>
            </div>
            <div class="text-white font-bold text-sm">
                {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="{{ route('accounting.journal.index') }}" class="group relative bg-white border border-border rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i data-lucide="book-open" class="w-32 h-32 -mr-8 -mt-8 rotate-12"></i>
            </div>
            
            <div class="relative z-10">
                <div class="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <i data-lucide="book-open" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Comptabilité Générale</h3>
                <p class="text-sm text-gray-500 leading-relaxed mb-6">Gérez vos journaux, consultez le grand livre, la balance et éditez vos états financiers (Bilan, Résultat).</p>
                
                <div class="flex items-center text-primary font-bold text-sm gap-2 uppercase tracking-widest">
                    Accéder au module
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
