<x-app-layout>
    @section('header', 'Message Templates')

    <div class="space-y-6" x-data="{ templateId: null, templateTitle: '', templateContent: '', deleteId: null }">
        {{-- Modern Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-8 shadow-xl shadow-indigo-900/10 border border-indigo-900/20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-white/80 text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md">
                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                        Templates
                    </div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Message Templates</h1>
                    <p class="text-indigo-100/70 text-sm mt-2 max-w-xl leading-relaxed">Manage pre-made messages for quick SMS and email sending.</p>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="$dispatch('open-modal', 'create-template')" class="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5">
                        <i data-lucide="plus" class="w-4.5 h-4.5"></i>
                        New Template
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold tracking-wider uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4">Title</th>
                                <th class="px-6 py-4">Content Preview</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            @forelse($templates as $template)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-800">
                                        {{ $template->title }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-md truncate text-slate-500" title="{{ $template->content }}">
                                            {{ $template->content }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <button @click="$dispatch('open-modal', 'edit-template'); templateId = {{ $template->id }}; templateTitle = '{{ addslashes($template->title) }}'; templateContent = '{{ addslashes(str_replace(["\r", "\n"], ["", "\\n"], $template->content)) }}';" class="text-indigo-600 hover:text-indigo-900 transition-colors p-2 bg-indigo-50 hover:bg-indigo-100 rounded-lg inline-flex" title="Edit">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <button @click="$dispatch('open-modal', 'delete-template'); deleteId = {{ $template->id }};" class="text-red-600 hover:text-red-900 transition-colors p-2 bg-red-50 hover:bg-red-100 rounded-lg inline-flex" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                            <i data-lucide="message-square-dashed" class="w-8 h-8 text-slate-400"></i>
                                        </div>
                                        <p class="text-slate-500 font-medium">No templates found.</p>
                                        <p class="text-sm text-slate-400 mt-1">Click 'New Template' to create one.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($templates->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $templates->links() }}
                </div>
                @endif
            </div>

        </div>

        <!-- Create Modal -->
        <x-modal name="create-template" :show="false" maxWidth="lg">
            <form action="{{ route('message-templates.store') }}" method="POST" class="p-6">
                @csrf
                <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-indigo-500"></i>
                    Create Message Template
                </h2>

                <div class="space-y-4">
                    <div>
                        <x-input-label for="title" value="Template Title" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required placeholder="e.g. Notice of Minor Violation" />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="content" value="Message Content" />
                        <textarea id="content" name="content" rows="5" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required placeholder="You can use @{{ $student->full_name }} for the student's name."></textarea>
                        <p class="text-xs text-slate-500 mt-1">Tip: Use <code class="bg-slate-100 px-1 py-0.5 rounded">@{{ $student->full_name }}</code> inside the message to automatically insert the student's name.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('content')" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                    <x-primary-button>Save Template</x-primary-button>
                </div>
            </form>
        </x-modal>

        <!-- Edit Modal -->
        <x-modal name="edit-template" :show="false" maxWidth="lg">
            <form :action="'{{ url('message-templates') }}/' + templateId" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="edit-2" class="w-5 h-5 text-indigo-500"></i>
                    Edit Message Template
                </h2>

                <div class="space-y-4">
                    <div>
                        <x-input-label for="edit_title" value="Template Title" />
                        <x-text-input id="edit_title" name="title" type="text" class="mt-1 block w-full" x-model="templateTitle" required />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="edit_content" value="Message Content" />
                        <textarea id="edit_content" name="content" rows="5" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="templateContent" required></textarea>
                        <p class="text-xs text-slate-500 mt-1">Tip: Use <code class="bg-slate-100 px-1 py-0.5 rounded">@{{ $student->full_name }}</code> inside the message to automatically insert the student's name.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('content')" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                    <x-primary-button>Update Template</x-primary-button>
                </div>
            </form>
        </x-modal>

        <!-- Delete Modal -->
        <x-modal name="delete-template" :show="false" maxWidth="sm">
            <form :action="'{{ url('message-templates') }}/' + deleteId" method="POST" class="p-6">
                @csrf
                @method('DELETE')
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-8 h-8 text-red-500"></i>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800 mb-2">Delete Template?</h2>
                    <p class="text-sm text-slate-500 mb-6">Are you sure you want to delete this message template? This action cannot be undone.</p>
                    
                    <div class="flex justify-center gap-3">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                        <x-danger-button>Yes, Delete</x-danger-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @endpush
</x-app-layout>
