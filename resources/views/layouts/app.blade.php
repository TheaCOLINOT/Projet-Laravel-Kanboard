@php use Illuminate\Support\Facades\Route; @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Kanboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 text-gray-900 h-screen overflow-hidden">

<nav class="fixed top-0 left-0 right-0 z-10 bg-white shadow-sm px-6 py-3 flex justify-between items-center border-b border-gray-200 h-20">
    <div class="flex items-center gap-4">
        <!-- Hamburger menu for mobile -->
        <button id="sidebarToggle" class="block md:hidden mr-2 text-gray-700 focus:outline-none" aria-label="Ouvrir le menu">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <a href="/" class="text-xl font-bold text-blue-600">📈 Kanboard</a>
        @if(isset($project))
        <div class="text-sm text-gray-500 hidden sm:block">
            Projet : <span class="font-medium text-gray-700">{{ optional($project)?->name }}</span>
        </div>
        @endif
    </div>
    <div class="flex items-center gap-4">
        @if(isset($project))
            <a href="{{ route('calendar.export', ['project' => optional($project)?->slug]) }}" target="_blank" class="border border-gray-500 text-gray-500 px-4 py-2 rounded hover:bg-gray-600 hover:text-white transition duration-200 flex items-center">
                Exporter vers calendrier
            </a>
            <a href="#" data-modal-target="modalInviteUser" data-modal-toggle="modalInviteUser" class="border border-gray-500 text-gray-500 px-4 py-2 rounded hover:bg-gray-600 hover:text-white transition duration-200 flex items-center">
                Inviter des utilisateurs
            </a>
            <a data-modal-target="modalCreateColumn" data-modal-toggle="modalCreateColumn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200 flex items-center hidden sm:flex">
                Nouvelle colonne
                <svg class="inline-block w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </a>
        @endif
        @if(Route::currentRouteName() === 'home')
            <input type="text" placeholder="Rechercher..." class="border border-gray-300 rounded px-3 py-1 text-sm focus:ring-blue-500 focus:border-blue-500 hidden sm:block">
        @endif
        @auth
        <div class="relative flex items-center gap-2">
            <!-- Bulle utilisateur -->
            <div class="relative group">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs text-white font-bold {{ \Illuminate\Support\Facades\Auth::user()->avatar()['color'] }} cursor-pointer" alt="Avatar utilisateur">
                    {{ \Illuminate\Support\Facades\Auth::user()->avatar()['initials'] }}
                </div>
                <div class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg py-2 z-50 hidden group-hover:block group-focus-within:block">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Mon profil</a>
                    <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Se déconnecter</button>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
        @endauth
    </div>
</nav>

<!-- Sidebar responsive : hidden on mobile, overlay on mobile when open -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-30 hidden md:hidden"></div>
@auth
@if(isset($project))
<aside id="sidebar" class="w-64 bg-white shadow-lg h-[calc(100vh-5rem)] fixed top-20 left-0 z-40 p-6 border-r border-gray-200 hidden md:block transition-transform duration-200">
        <nav class="flex flex-col gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Projets</h2>
                <ul class="space-y-2">
                    @foreach (\Illuminate\Support\Facades\Auth::user()->projects as $proj)
                        <li>
                            <a href="{{route('projects.show', [$proj])}}" class="inline-flex items-center gap-2 text-blue-600 bg-blue-50 border border-blue-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h4l2 3h10v9H3V7z" />
                                </svg>
                                {{ $proj->name }}
                            </a>
                        </li>
                    @endforeach
            </div>
            <hr class="border-gray-200 my-4">
            <a href="{{route('projects.users', ['project' => optional($project)?->slug])}}" class="inline-flex items-center gap-2 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h4l2 3h10v9H3V7z" />
                </svg>
                Equipe
            </a>
            <a href="#" data-modal-target="modalProjectSettings" data-modal-toggle="modalProjectSettings" class="inline-flex items-center gap-2 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h4l2 3h10v9H3V7z" />
                </svg>
                Paramètres
            </a>
        </nav>
    </aside>
@endif
@endauth

<div class="flex h-[calc(100vh-5rem)]">
    <main class="w-full overflow-y-auto p-2 sm:p-6 mt-20 transition-all duration-200 md:ml-64">
        @yield('content')
    </main>
</div>

<script>
// Sidebar responsive JS
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const sidebarToggle = document.getElementById('sidebarToggle');
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.remove('hidden');
        sidebarOverlay.classList.remove('hidden');
    });
}
if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.add('hidden');
        sidebarOverlay.classList.add('hidden');
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
@if(isset($project))
    <div id="modalInviteUser" tabindex="-1" class="hidden fixed top-0 left-0 right-0 z-50 w-full p-4 overflow-x-hidden overflow-y-auto inset-0 h-modal h-full bg-black bg-opacity-50">
        <div class="relative w-full max-w-md mx-auto mt-20">
            <div class="relative bg-white rounded-lg shadow p-6">
                <button type="button" class="absolute top-2 right-2 text-gray-400 hover:text-gray-900" data-modal-hide="modalInviteUser">
                    ✕
                </button>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Inviter un utilisateur</h3>

                <form method="POST" action="{{ route('projects.invite', optional($project)?->slug) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="invite_email" class="block text-sm font-medium text-gray-700">Email de l'utilisateur</label>
                        <input type="email" name="email" id="invite_email" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
                        Envoyer l'invitation
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODALE DE PARAMÈTRES -->
    <div id="modalProjectSettings" tabindex="-1" class="hidden fixed top-0 left-0 right-0 z-50 w-full p-4 overflow-x-hidden overflow-y-auto inset-0 h-modal h-full bg-black bg-opacity-50">
        <div class="relative w-full max-w-lg mx-auto mt-20">
            <div class="relative bg-white rounded-lg shadow p-6">
                <button type="button" class="absolute top-2 right-2 text-gray-400 hover:text-gray-900" data-modal-hide="modalProjectSettings">
                    ✕
                </button>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Modifier le projet</h3>

                <form method="POST" action="{{ route('projects.update', optional($project)?->slug) }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom du projet</label>
                        <input type="text" name="name" id="name" value="{{ optional($project)?->name }}" required
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="4"
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ optional($project)?->description }}</textarea>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif

</body>


</html>
