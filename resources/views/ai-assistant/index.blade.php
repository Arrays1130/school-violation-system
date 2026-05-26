<x-app-layout>
    @section('header', 'AI Assistant')

    <div class="max-w-5xl mx-auto h-[calc(100vh-10rem)] flex flex-col gap-6" x-data="handbookChat()">
        
        <style>
            .ai-glow {
                box-shadow: 0 0 50px -10px rgba(99, 102, 241, 0.15);
            }
            .chat-bg {
                background-image: 
                    radial-gradient(at 0% 0%, rgba(243, 244, 246, 0.5) 0, transparent 50%),
                    radial-gradient(at 100% 100%, rgba(238, 242, 255, 0.4) 0, transparent 50%);
            }
            .user-bubble {
                background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
                box-shadow: 0 4px 15px -3px rgba(99, 102, 241, 0.3);
            }
            @keyframes pulse-ring {
                0% { transform: scale(0.95); opacity: 1; }
                50% { transform: scale(1.15); opacity: 0.5; }
                100% { transform: scale(1.3); opacity: 0; }
            }
            .pulse-ring {
                animation: pulse-ring 2.5s cubic-bezier(0.24, 0, 0.38, 1) infinite;
            }
            #chat-container::-webkit-scrollbar {
                width: 6px;
            }
            #chat-container::-webkit-scrollbar-track {
                background: transparent;
            }
            #chat-container::-webkit-scrollbar-thumb {
                background: #e2e8f0;
                border-radius: 4px;
            }
            #chat-container::-webkit-scrollbar-thumb:hover {
                background: #cbd5e1;
            }
        </style>

        {{-- Top Premium Header Panel --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-6 shadow-xl shadow-indigo-950/10 border border-indigo-950/20 flex items-center justify-between group shrink-0">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_50%)]"></div>
            <div class="absolute -right-20 -top-20 h-52 w-52 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 text-white flex items-center justify-center shadow-lg shadow-purple-500/30 relative transition-transform group-hover:scale-110 group-hover:rotate-3 duration-300">
                    <i data-lucide="sparkles" class="w-5.5 h-5.5 animate-pulse"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white tracking-tight">CST Guidance AI</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="relative flex h-2 w-2">
                            <span class="pulse-ring absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <p class="text-[10px] text-indigo-200/80 font-bold tracking-wider uppercase">Core Intelligence Online</p>
                    </div>
                </div>
            </div>
            
            <div class="relative flex items-center gap-3">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/10 backdrop-blur-md border border-white/10 rounded-xl text-[11px] font-bold text-indigo-100/90 shadow-sm">
                    <i data-lucide="brain-circuit" class="w-4 h-4 text-indigo-400"></i>
                    Model: Handbook-GPT v4.0
                </span>
            </div>
        </div>

        {{-- Main Chat Hub --}}
        <div class="flex-1 bg-white rounded-2xl border border-slate-100 shadow-lg shadow-indigo-950/5 flex flex-col overflow-hidden ai-glow">
            
            {{-- Chat Conversation Stream --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6 scroll-smooth chat-bg no-scrollbar" id="chat-container">
                
                {{-- Welcome Screen --}}
                <div x-show="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center space-y-6 max-w-lg mx-auto py-12">
                    <div class="relative w-20 h-20 bg-white rounded-2xl flex items-center justify-center border border-indigo-50 shadow-md shadow-indigo-500/5 group transition-transform hover:scale-105 duration-300">
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 blur-md opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative w-12 h-12 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                            <i data-lucide="bot" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Institutional AI Companion</h2>
                        <p class="text-sm text-slate-400 mt-2.5 leading-relaxed font-medium">
                            Ask me anything about student regulations, handbook compliance standards, code of conduct, and case procedures.
                        </p>
                        
                        {{-- Interactive Suggestion Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-8 text-left">
                            <button @click="input = 'What are the major offenses?'; sendMessage()" class="p-4.5 rounded-2xl bg-white border border-slate-100 hover:border-indigo-400 hover:shadow-lg hover:shadow-indigo-500/5 hover:-translate-y-1 transition-all duration-300 flex items-start gap-4 text-left group">
                                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 border border-rose-100/50 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Major Offenses</h3>
                                    <p class="text-xs text-slate-400 mt-1 font-medium leading-relaxed">Review severe violations and sanction limits.</p>
                                </div>
                            </button>
                            
                            <button @click="input = 'What is the disciplinary hearing process?'; sendMessage()" class="p-4.5 rounded-2xl bg-white border border-slate-100 hover:border-purple-400 hover:shadow-lg hover:shadow-purple-500/5 hover:-translate-y-1 transition-all duration-300 flex items-start gap-4 text-left group">
                                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 border border-purple-100/50 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i data-lucide="git-merge" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Disciplinary Steps</h3>
                                    <p class="text-xs text-slate-400 mt-1 font-medium leading-relaxed">Understand the timeline of a logged case.</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Render Messages --}}
                <template x-for="(msg, index) in messages" :key="index">
                    <div class="flex gap-4 w-full" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                        
                        {{-- Avatar Icon --}}
                        <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center shadow-sm text-sm"
                             :class="msg.role === 'user' ? 'bg-indigo-600 text-white shadow-indigo-600/10' : 'bg-white border border-slate-200 text-slate-700 shadow-sm'">
                            <i :data-lucide="msg.role === 'user' ? 'user' : 'bot'" class="w-4.5 h-4.5"></i>
                        </div>

                        {{-- Message Bubble --}}
                        <div class="max-w-[75%] flex flex-col" :class="msg.role === 'user' ? 'items-end' : 'items-start'">
                            <div class="px-5 py-3.5 text-sm leading-relaxed"
                                 :class="msg.role === 'user'
                                    ? 'user-bubble text-white rounded-2xl rounded-tr-none font-medium'
                                    : 'bg-white text-slate-800 rounded-2xl rounded-tl-none border border-slate-100 shadow-sm shadow-slate-100/50'">
                                <span x-html="msg.content"></span>
                            </div>
                            
                            {{-- Document Citations / Sources --}}
                            <template x-if="msg.role === 'bot' && msg.sources && msg.sources.length > 0">
                                <div class="mt-2.5 flex flex-wrap gap-2">
                                    <template x-for="source in msg.sources">
                                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50/50 border border-indigo-100 text-[10px] font-bold text-indigo-600 shadow-sm uppercase tracking-wide transition-transform hover:scale-105 cursor-default">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-indigo-500"></i>
                                            <span x-text="source"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Loading Anim --}}
                <div x-show="isTyping" class="flex gap-4 w-full">
                    <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex-shrink-0 flex items-center justify-center shadow-sm">
                        <i data-lucide="bot" class="w-4.5 h-4.5 text-slate-400"></i>
                    </div>
                    <div class="bg-white border border-slate-100 rounded-2xl rounded-tl-none px-5 py-4 shadow-sm flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce"></span>
                        <span class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.15s"></span>
                        <span class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
                    </div>
                </div>
            </div>

            {{-- Input Control Board --}}
            <div class="p-5 bg-slate-50/70 border-t border-slate-100 shrink-0">
                <form @submit.prevent="sendMessage" class="relative flex items-center bg-white rounded-2xl border border-slate-200 focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-500/5 transition-all duration-300 shadow-sm pl-4 pr-2.5 py-2 w-full max-w-4xl mx-auto group">
                    <i data-lucide="message-square" class="w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors mr-2"></i>
                    <textarea 
                        x-model="input"
                        @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                        rows="1"
                        class="flex-1 bg-transparent border-0 focus:ring-0 py-2.5 text-sm text-slate-800 placeholder-slate-400 resize-none outline-none"
                        placeholder="Ask me about rules, regulations, infraction consequences..."
                        oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                        style="min-height: 40px; max-height: 120px;"
                    ></textarea>
                    <button type="submit" 
                            :disabled="!input.trim() || isTyping"
                            class="h-[40px] px-5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white flex items-center justify-center font-bold text-xs shadow-md shadow-indigo-600/10 hover:from-indigo-500 hover:to-indigo-600 hover:shadow-lg hover:shadow-indigo-500/20 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:pointer-events-none transition-all duration-200 ml-2 whitespace-nowrap">
                        <i data-lucide="send" class="w-3.5 h-3.5 mr-2"></i>
                        Send Query
                    </button>
                </form>
                <p class="text-center mt-3.5 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                    Powered by semantic handbook vector indexing • always review the <a href="{{ route('handbooks.index') }}" class="text-indigo-600 hover:underline">Official Handbook</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('handbookChat', () => ({
                input: '',
                isTyping: false,
                messages: [],
                
                async sendMessage() {
                    const userInput = this.input.trim();
                    if (!userInput || this.isTyping) return;

                    this.messages.push({ role: 'user', content: userInput });
                    this.input = '';
                    
                    const textarea = document.querySelector('textarea');
                    if (textarea) textarea.style.height = '40px';
                    
                    this.scrollToBottom();
                    this.isTyping = true;
                    
                    setTimeout(() => lucide.createIcons(), 50);

                    try {
                        const response = await fetch("{{ route('ai-assistant.chat') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ message: userInput })
                        });

                        if (!response.ok) throw new Error('API Unavailable');
                        const data = await response.json();
                        
                        this.messages.push({ 
                            role: 'bot', 
                            content: data.reply,
                            sources: data.sources || []
                        });
                    } catch (error) {
                        this.messages.push({ 
                            role: 'bot', 
                            content: "Error: Connection lost. Please check server availability and try again." 
                        });
                    } finally {
                        this.isTyping = false;
                        this.scrollToBottom();
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                },
                
                scrollToBottom() {
                    setTimeout(() => {
                        const container = document.getElementById('chat-container');
                        if (container) container.scrollTop = container.scrollHeight;
                    }, 50);
                }
            }));
        });
    </script>
</x-app-layout>
