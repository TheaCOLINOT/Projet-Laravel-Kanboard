<div class="overflow-x-auto w-full">
    <div class="flex gap-6 p-6 w-max">
        @foreach($project->columns as $column)
            <div class="w-80 flex-none">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full" style="background-color: {{$column->color}}"></span>
                        <h2 class="text-lg font-semibold text-gray-800">{{ $column->name }}</h2>
                        <span class="text-xs text-gray-500 bg-gray-200 px-2 rounded-full">{{ count($column->tasks) }}</span>
                    </div>
                    <button data-modal-target="modalCreateTask{{$column->id}}" data-modal-toggle="modalCreateTask{{$column->id}}" class="text-xl font-semibold text-gray-500 hover:text-gray-700">+</button>
                </div>

                <div class="task-list space-y-4" data-column-id="{{ $column->id }}">
                    @forelse($column->tasks as $task)
                        @php
                            $class = 'text-white';

                            if ($task->completed_at) {
                                $class = 'text-green-500';
                            } elseif ($task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast()) {
                                $class = 'text-red-500';
                            }
                        @endphp
                        <div data-modal-target="modalEditTask{{ $task->id }}" data-modal-toggle="modalEditTask{{ $task->id }}" class="task bg-white p-4 rounded-xl border {{ $class }} shadow-sm cursor-move" draggable="true" data-task-id="{{ $task->id }}">
                        <div class="flex justify-between items-center mb-2">
                                <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-600 font-medium">Développement</span>
                                @php
                                    $priorityColors = [
                                        'Urgente' => 'bg-red-100 text-red-600',
                                        'Importante' => 'bg-orange-100 text-orange-600',
                                        'Moyenne' => 'bg-yellow-100 text-yellow-600',
                                        'Faible' => 'bg-green-100 text-green-600'
                                    ];
                                    $priorityLabel = $task->priority?->name ?? 'Aucune';
                                    $priorityClass = $priorityColors[$priorityLabel] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="text-xs px-2 py-1 rounded-full {{ $priorityClass }} font-medium">
                                    {{ $priorityLabel }}
                                </span>
                            </div>

                            <p class="font-semibold text-gray-800">{{ $task->title }}</p>
                            <p class="text-sm text-gray-600">{{ $task->description }}</p>

                            @if($task->due_date)
                                <div class="mt-2 flex items-center text-sm {{\Carbon\Carbon::parse($task->due_date)->isPast() && !$task->completed_at ? "text-red-500" : "text-gray-500"}}">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 7V3M16 7V3M4 11h16M4 19h16M4 15h16"></path>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                                    @if( \Carbon\Carbon::parse($task->due_date)->isPast())
                                        <span class="ml-1">(En retard)</span>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center justify-between mt-4 text-gray-500 text-sm">
                                <div class="flex gap-3">

                                </div>
                                <div class="flex -space-x-2">
                                    @foreach($task->assignedUsers as $user)
                                        <div class="w-8 h-8 rounded-full border-2 border-white text-white text-xs flex items-center justify-center font-bold {{ $user->avatar()['color'] }}">
                                            {{ $user->avatar()['initials'] }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div id="modalEditTask{{$task->id}}" tabindex="-1" class="hidden fixed inset-0 bg-black/30 z-50 flex justify-center items-center">
                            <div class="relative w-full max-w-lg bg-white rounded-lg shadow p-6">
                                <button data-modal-hide="modalEditTask{{$task->id}}" class="absolute top-3 right-3 text-gray-400 hover:text-gray-900">
                                    ✕
                                </button>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Créer une tâche</h3>
                                <form method="POST" action="{{ route('tasks.update', [$task]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-4">
                                        <label for="title{{$column->id}}" class="block mb-1 font-medium text-gray-900">Titre</label>
                                        <input value="{{$task->title}}" type="text" name="title" id="title{{$column->id}}" class="w-full border rounded px-3 py-2" required>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description{{$column->id}}" class="block mb-1 font-medium text-gray-900">Description</label>
                                        <textarea name="description" id="description{{$column->id}}" class="w-full border rounded px-3 py-2">{{$task->description}}</textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label for="due_date{{$column->id}}" class="block mb-1 font-medium text-gray-900">Date d'échéance</label>
                                        <input id="due_date{{$column->id}}" class="w-full border rounded px-3 py-2" value="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}" type="date" name="due_date">
                                    </div>

                                    <div class="mb-4">
                                        <label for="priority{{$column->id}}" class="block mb-1 font-medium text-gray-900">Priorité</label>
                                        <select name="priority_id" id="priority{{$column->id}}" class="w-full border rounded px-3 py-2">
                                            <option value="">-- Choisir --</option>
                                            @foreach(\App\Models\Priority::all() as $priority)
                                                <option selected="{{$task->priority_id == $priority->id}}" value="{{ $priority->id }}">{{ $priority->name }}</option>
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
                                    <input type="hidden" name="column_id" value="{{ $column->id }}">
                                    <div class="text-right">
                                        <button type="button" class="px-4 py-2 text-red-500 border border-red-500 rounded hover:bg-red-500 hover:text-white" onclick="deleteTask({{$task->id}})">
                                            Supprimer
                                        </button>
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Modifier
                                        </button>
                                    </div>
                                </form>
                                <form method="post" action="{{route('tasks.delete', [$task])}}" id="deleteForm{{$task->id}}" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>

                    @empty
                        <div class="empty-placeholder h-10"></div>
                    @endforelse
                </div>
            </div>
            <div id="modalCreateTask{{$column->id}}" tabindex="-1" class="hidden fixed inset-0 bg-black/30 z-50 flex justify-center items-center">
                <div class="relative w-full max-w-lg bg-white rounded-lg shadow p-6">
                    <button data-modal-hide="modalCreateTask{{$column->id}}" class="absolute top-3 right-3 text-gray-400 hover:text-gray-900">
                        ✕
                    </button>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Créer une tâche</h3>
                    <form method="POST" action="{{ route('tasks.store', ['project' => $project->slug, 'column' => $column->id]) }}">
                        @csrf

                        <div class="mb-4">
                            <label for="title{{$column->id}}" class="block mb-1 font-medium text-gray-900">Titre</label>
                            <input type="text" name="title" id="title{{$column->id}}" class="w-full border rounded px-3 py-2" required>
                        </div>

                        <div class="mb-4">
                            <label for="description{{$column->id}}" class="block mb-1 font-medium text-gray-900">Description</label>
                            <textarea name="description" id="description{{$column->id}}" class="w-full border rounded px-3 py-2"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="due_date{{$column->id}}" class="block mb-1 font-medium text-gray-900">Date d'échéance</label>
                            <input type="date" name="due_date" id="due_date{{$column->id}}" class="w-full border rounded px-3 py-2">
                        </div>

                        <div class="mb-4">
                            <label for="priority{{$column->id}}" class="block mb-1 font-medium text-gray-900">Priorité</label>
                            <select name="priority_id" id="priority{{$column->id}}" class="w-full border rounded px-3 py-2">
                                <option value="">-- Choisir --</option>
                                @foreach(\App\Models\Priority::all() as $priority)
                                    <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

</div>

<div id="modalCreateColumn" tabindex="-1" class="hidden fixed inset-0 bg-black/30 z-50 flex justify-center items-center">
    <div class="relative w-full max-w-lg bg-white rounded-lg shadow p-6">
        <button data-modal-hide="modalCreateColumn" class="absolute top-3 right-3 text-gray-400 hover:text-gray-900">
            ✕
        </button>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Créer une nouvelle colonne</h3>
        <form method="POST" action="{{ route('columns.store', $project->slug) }}">
            @csrf

            <div class="mb-4">
                <label for="name" class="block mb-1 font-medium text-gray-900">Nom de la colonne</label>
                <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="color" class="block mb-1 font-medium text-gray-900">Couleur</label>
                <input type="color" name="color" id="color" class="w-full border rounded">
            </div>

            <div class="mb-4">
                <label for="finished_column" class="block mb-1 font-medium text-gray-900">Terminale ?</label>
                <input type="checkbox" name="finished_column" id="finished_column" class="w-5 h-5">
            </div>

            <div class="text-right">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Créer</button>
            </div>
        </form>
    </div>
</div>

<style>
    .drop-indicator {
        height: 30px;
        background-color: #3b82f6;
        margin: 8px 0;
        border-radius: 4px;
    }

    .task.dragging {
        opacity: 0.5;
    }
</style>

<script>
    function deleteTask(taskId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
            document.getElementById('deleteForm' + taskId).submit();
        }
    }
</script>

<script>
    let draggedTask = null;
    const dropIndicator = document.createElement('div');
    dropIndicator.className = 'drop-indicator';

    function setupDragAndDrop() {
        document.querySelectorAll('.task').forEach(task => {
            task.setAttribute('draggable', 'true');

            task.addEventListener('dragstart', () => {
                draggedTask = task;
                task.classList.add('dragging');
                setTimeout(() => {
                    task.style.display = 'none';
                }, 0);
            });

            task.addEventListener('dragend', () => {
                task.style.display = '';
                task.classList.remove('dragging');
                draggedTask = null;
                dropIndicator.remove();
            });
        });

        document.querySelectorAll('.task-list').forEach(list => {
            list.addEventListener('dragover', e => {
                e.preventDefault();
                const afterElement = getDragAfterElement(list, e.clientY);
                if (!draggedTask) return;

                if (afterElement == null) {
                    list.appendChild(dropIndicator);
                } else {
                    list.insertBefore(dropIndicator, afterElement);
                }
            });

            list.addEventListener('drop', e => {
                e.preventDefault();
                if (!draggedTask) return;

                if (dropIndicator.parentNode) {
                    dropIndicator.parentNode.insertBefore(draggedTask, dropIndicator);
                } else {
                    list.appendChild(draggedTask);
                }

                dropIndicator.remove();

                const placeholder = list.querySelector('.empty-placeholder');
                if (placeholder) placeholder.remove();

                const columnId = list.closest('[data-column-id]').dataset.columnId;
                updateTaskOrder(list, columnId);
            });
        });
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.task:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function updateTaskOrder(list, columnId) {
        const tasks = [...list.querySelectorAll('.task')];
        const orderValues = tasks.map((el, index) => ({
            id: el.dataset.taskId,
            order: (index + 1) * 1000
        }));

        fetch('{{ route('tasks.reorder') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                column_id: columnId,
                tasks: orderValues
            })
        });
    }

    document.addEventListener('DOMContentLoaded', setupDragAndDrop);
</script>
