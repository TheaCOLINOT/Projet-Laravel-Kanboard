<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Calendrier – {{ \Carbon\Carbon::now()->format('F Y') }}</h2>
        <div class="flex gap-2 items-center">
            <button id="calendarPrevBtn" class="hidden sm:flex items-center justify-center w-9 h-9 bg-white border border-gray-300 shadow-sm text-gray-600 rounded-full hover:bg-blue-100 hover:text-blue-600 transition" onclick="calendarPrev()" type="button" aria-label="Jour précédent">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="relative">
                <select id="calendarViewMode" class="appearance-none border border-gray-300 rounded-full px-4 py-2 pr-8 text-sm bg-white shadow-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition" onchange="updateCalendarView()">
                    <option value="month">Mois</option>
                    <option value="3days">3 jours</option>
                    <option value="day">Jour</option>
                </select>
                <svg class="pointer-events-none absolute right-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </div>
            <button id="calendarNextBtn" class="hidden sm:flex items-center justify-center w-9 h-9 bg-white border border-gray-300 shadow-sm text-gray-600 rounded-full hover:bg-blue-100 hover:text-blue-600 transition" onclick="calendarNext()" type="button" aria-label="Jour suivant">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button data-modal-target="modalCreateTask" data-modal-toggle="modalCreateTask" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nouvelle tâche
            </button>
            <button data-modal-target="modalCreateColumn" data-modal-toggle="modalCreateColumn" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nouvelle colonne
            </button>
        </div>
    </div>

    @php
        use Carbon\Carbon;

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek();
        $endOfCalendar = $endOfMonth->copy()->endOfWeek();

        $days = [];
        $current = $startOfCalendar->copy();

        while ($current <= $endOfCalendar) {
            $days[] = $current->copy();
            $current->addDay();
        }

        $tasksByDate = $project->tasks->groupBy(function($task) {
            return optional($task->due_date)->format('Y-m-d');
        });
    @endphp

    <div id="calendarGrid">
        <div class="grid grid-cols-7 gap-2 text-center text-gray-600 font-medium mb-2" id="calendarHeader">
            @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $dayName)
                <div>{{ $dayName }}</div>
            @endforeach
        </div>
        <div class="grid grid-cols-7 gap-4" id="calendarDays">
            @foreach($days as $day)
                <div class="border rounded-lg p-2 h-40 flex flex-col {{ $day->isToday() ? 'bg-blue-50 border-blue-400' : 'bg-white' }} group relative calendar-day" data-date="{{ $day->format('Y-m-d') }}" onclick="selectCalendarDay(this)">
                    <div class="text-sm font-semibold mb-1 text-gray-800">
                        {{ $day->day }}
                    </div>
                    <button class="absolute top-2 right-2 bg-blue-100 text-blue-600 rounded-full p-1 text-xs hover:bg-blue-200 transition" onclick="event.stopPropagation();openCreateTaskModalForDate('{{ $day->format('Y-m-d') }}')" title="Créer une tâche pour ce jour">+</button>
                    <button class="absolute bottom-2 right-2 bg-blue-200 text-blue-800 rounded-full p-1 text-xs hover:bg-blue-300 transition" onclick="event.stopPropagation();openCreateColumnModal()" title="Créer une colonne">≡</button>
                    <div class="space-y-1 overflow-auto text-left mt-5">
                        @foreach($tasksByDate[$day->format('Y-m-d')] ?? [] as $task)
                            <div data-modal-target="modalEditTask{{ $task->id }}" data-modal-toggle="modalEditTask{{ $task->id }}" class="text-xs bg-gray-100 p-1 rounded border-l-4 {{ $task->priority?->name === 'Urgente' ? 'border-red-500' : 'border-gray-300' }} cursor-pointer hover:bg-gray-200 transition-colors">
                                <strong>{{ $task->title }}</strong>
                                <div class="text-gray-500 truncate">{{ $task->description }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modals d'édition de tâches -->
    @foreach($project->tasks as $task)
        <div id="modalEditTask{{$task->id}}" tabindex="-1" class="hidden fixed inset-0 bg-black/30 z-50 flex justify-center items-center">
            <div class="relative w-full max-w-lg bg-white rounded-lg shadow p-6">
                <button data-modal-hide="modalEditTask{{$task->id}}" class="absolute top-3 right-3 text-gray-400 hover:text-gray-900">
                    ✕
                </button>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Modifier une tâche</h3>
                <form method="POST" action="{{ route('tasks.update', [$task]) }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label for="title{{$task->id}}" class="block mb-1 font-medium text-gray-900">Titre</label>
                        <input value="{{$task->title}}" type="text" name="title" id="title{{$task->id}}" class="w-full border rounded px-3 py-2" required>
                    </div>

                    <div class="mb-4">
                        <label for="description{{$task->id}}" class="block mb-1 font-medium text-gray-900">Description</label>
                        <textarea name="description" id="description{{$task->id}}" class="w-full border rounded px-3 py-2">{{$task->description}}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="due_date{{$task->id}}" class="block mb-1 font-medium text-gray-900">Date d'échéance</label>
                        <input id="due_date{{$task->id}}" class="w-full border rounded px-3 py-2" value="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}" type="date" name="due_date">
                    </div>

                    <div class="mb-4">
                        <label for="priority{{$task->id}}" class="block mb-1 font-medium text-gray-900">Priorité</label>
                        <select name="priority_id" id="priority{{$task->id}}" class="w-full border rounded px-3 py-2">
                            <option value="">-- Choisir --</option>
                            @foreach(\App\Models\Priority::all() as $priority)
                                <option value="{{ $priority->id }}" {{ $task->priority_id == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label>Assigner à</label>
                        <div class="flex flex-col gap-2 h-32 overflow-y-scroll mt-2">
                            @foreach($project->users as $user)
                                <div class="flex items-center gap-2 bg-gray-100 px-4 py-3 rounded-md">
                                    <input type="checkbox" name="assigned_users[]" value="{{ $user->id }}" id="user_{{ $user->id }}" {{ $task->assignedUsers->contains($user) ? 'checked' : '' }}>
                                    <div class="w-8 h-8 rounded-full border-2 border-white text-white text-xs flex items-center justify-center font-bold {{ $user->avatar()['color'] }}">
                                        {{ $user->avatar()['initials'] }}
                                    </div>
                                    <div>
                                        <label for="user_{{ $user->id }}" class="text-sm text-gray-700">{{ $user->fullName() }}</label>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="text-right">
                        <form method="post" action="{{route("tasks.delete", [$task])}}">
                            @csrf
                            @method('DELETE')
                            <input type="submit" value="Supprimer" class="px-4 py-2 text-red-500 border-1 border-red-500 rounded hover:bg-red-500 hover:text-white">
                        </form>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <!-- Modal de création de tâche -->
    <div id="modalCreateTask" tabindex="-1" class="hidden fixed inset-0 bg-black/30 z-50 flex justify-center items-center">
        <div class="relative w-full max-w-lg bg-white rounded-lg shadow p-6">
            <button data-modal-hide="modalCreateTask" class="absolute top-3 right-3 text-gray-400 hover:text-gray-900">
                ✕
            </button>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Créer une nouvelle tâche</h3>
            @if($project->columns->count() > 0)
                <form method="POST" action="{{ route('tasks.store', ['project' => $project->slug, 'column' => $project->columns->first()->id]) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="title" class="block mb-1 font-medium text-gray-900">Titre</label>
                        <input type="text" name="title" id="title" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block mb-1 font-medium text-gray-900">Description</label>
                        <textarea name="description" id="description" class="w-full border rounded px-3 py-2"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="due_date" class="block mb-1 font-medium text-gray-900">Date d'échéance</label>
                        <input id="due_date" class="w-full border rounded px-3 py-2" type="date" name="due_date">
                    </div>
                    <div class="mb-4">
                        <label for="column_id" class="block mb-1 font-medium text-gray-900">Colonne</label>
                        <div class="flex gap-2">
                            <select name="column_id" id="column_id" class="w-full border rounded px-3 py-2">
                                @foreach($project->columns as $column)
                                    <option value="{{ $column->id }}">{{ $column->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="toggleInlineColumnForm()" class="bg-blue-200 text-blue-800 px-2 py-1 rounded text-xs">Nouvelle colonne</button>
                        </div>
                        <div id="inlineColumnForm" class="mt-2 hidden bg-gray-50 p-2 rounded relative" style="max-width:320px; min-width:220px; width:100%;">
                            <button type="button" onclick="toggleInlineColumnForm()" class="absolute top-1 right-1 text-gray-400 hover:text-gray-700 text-lg leading-none">&times;</button>
                            <input type="text" id="inlineColumnName" class="border rounded px-2 py-1 mr-2 mb-2 w-2/3" placeholder="Nom de la colonne" />
                            <input type="color" id="inlineColumnColor" value="#3B82F6" class="border rounded px-2 py-1 mr-2 mb-2 align-middle" style="width:36px; height:36px;" />
                            <div class="flex gap-2 mt-2">
                                <button type="button" onclick="addInlineColumn()" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Ajouter</button>
                                <button type="button" onclick="toggleInlineColumnForm()" class="text-xs text-gray-500">Annuler</button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="priority" class="block mb-1 font-medium text-gray-900">Priorité</label>
                        <select name="priority_id" id="priority" class="w-full border rounded px-3 py-2">
                            <option value="">-- Choisir --</option>
                            @foreach(\App\Models\Priority::all() as $priority)
                                <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label>Assigner à</label>
                        <div class="flex flex-col gap-2 h-32 overflow-y-scroll mt-2">
                            @foreach($project->users as $user)
                                <div class="flex items-center gap-2 bg-gray-100 px-4 py-3 rounded-md">
                                    <input type="checkbox" name="assigned_users[]" value="{{ $user->id }}" id="user_{{ $user->id }}">
                                    <div class="w-8 h-8 rounded-full border-2 border-white text-white text-xs flex items-center justify-center font-bold {{ $user->avatar()['color'] }}">
                                        {{ $user->avatar()['initials'] }}
                                    </div>
                                    <div>
                                        <label for="user_{{ $user->id }}" class="text-sm text-gray-700">{{ $user->fullName() }}</label>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Créer</button>
                    </div>
                </form>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500 mb-4">Aucune colonne trouvée dans ce projet.</p>
                    <p class="text-sm text-gray-400">Veuillez d'abord créer au moins une colonne pour pouvoir ajouter des tâches.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function openCreateTaskModalForDate(date) {
    const modal = document.getElementById('modalCreateTask');
    if (modal) {
        modal.classList.remove('hidden');
        // Préremplir la date dans le champ du modal
        setTimeout(() => {
            const dateInput = document.getElementById('due_date');
            if (dateInput) dateInput.value = date;
        }, 100);
    }
}
function openCreateColumnModal() {
    const modal = document.getElementById('modalCreateColumn');
    if (modal) {
        modal.classList.remove('hidden');
    }
}
function toggleInlineColumnForm() {
    const form = document.getElementById('inlineColumnForm');
    if (form) form.classList.toggle('hidden');
}
function addInlineColumn() {
    const name = document.getElementById('inlineColumnName').value.trim();
    const color = document.getElementById('inlineColumnColor').value;
    if (!name) return;
    // Ajoute l'option à la liste déroulante
    const select = document.getElementById('column_id');
    const option = document.createElement('option');
    option.value = 'new:' + name + ':' + color;
    option.textContent = name + ' (nouvelle)';
    option.selected = true;
    select.appendChild(option);
    // Cache le mini-formulaire et reset
    document.getElementById('inlineColumnName').value = '';
    document.getElementById('inlineColumnColor').value = '#3B82F6';
    toggleInlineColumnForm();
}
function updateCalendarView() {
    const mode = document.getElementById('calendarViewMode').value;
    const allDays = Array.from(document.querySelectorAll('#calendarDays > div'));
    const header = document.getElementById('calendarHeader');
    document.getElementById('calendarPrevBtn').style.display = (mode === 'month') ? 'none' : '';
    document.getElementById('calendarNextBtn').style.display = (mode === 'month') ? 'none' : '';
    // Sélection par défaut si rien
    if (calendarSelectedIndex < 0) calendarSelectedIndex = 0;
    if (mode === 'month') {
        allDays.forEach(d => d.style.display = '');
        header.style.display = '';
        document.getElementById('calendarDays').className = 'grid grid-cols-7 gap-4';
    } else if (mode === '3days') {
        allDays.forEach((d, i) => d.style.display = (i >= calendarSelectedIndex && i < calendarSelectedIndex+3 ? '' : 'none'));
        header.style.display = 'none';
        document.getElementById('calendarDays').className = 'grid grid-cols-3 gap-4';
    } else if (mode === 'day') {
        allDays.forEach((d, i) => d.style.display = (i === calendarSelectedIndex ? '' : 'none'));
        header.style.display = 'none';
        document.getElementById('calendarDays').className = 'grid grid-cols-1 gap-4';
    }
    // Mise en surbrillance
    allDays.forEach((d, i) => d.classList.toggle('ring-4', i === calendarSelectedIndex));
    allDays.forEach((d, i) => d.classList.toggle('ring-blue-400', i === calendarSelectedIndex));
}
function calendarPrev() {
    const mode = document.getElementById('calendarViewMode').value;
    const allDays = Array.from(document.querySelectorAll('#calendarDays > div'));
    if (mode === 'day' && calendarSelectedIndex > 0) calendarSelectedIndex--;
    if (mode === '3days' && calendarSelectedIndex > 0) calendarSelectedIndex--;
    updateCalendarView();
}
function calendarNext() {
    const mode = document.getElementById('calendarViewMode').value;
    const allDays = Array.from(document.querySelectorAll('#calendarDays > div'));
    if (mode === 'day' && calendarSelectedIndex < allDays.length-1) calendarSelectedIndex++;
    if (mode === '3days' && calendarSelectedIndex < allDays.length-3) calendarSelectedIndex++;
    updateCalendarView();
}
function selectCalendarDay(el) {
    const allDays = Array.from(document.querySelectorAll('#calendarDays > div'));
    calendarSelectedIndex = allDays.indexOf(el);
    updateCalendarView();
}
window.addEventListener('DOMContentLoaded', function() {
    calendarSelectedIndex = Array.from(document.querySelectorAll('#calendarDays > div')).findIndex(d => d.classList.contains('bg-blue-50'));
    if (calendarSelectedIndex < 0) calendarSelectedIndex = 0;
    updateCalendarView();
});
</script>
