<x-app-layout>
    @section('header', 'AI Assistant')

    <div class="max-w-5xl mx-auto h-[calc(100vh-10rem)] flex flex-col space-y-6" x-data="handbookChat()">
        
        <style>
            .ai-glow {
                box-shadow: 0 0 40px -5px rgba(124, 58, 237, 0.12);
            }
            .chat-bg {
                background-image: 
                    radial-gradient(at 0% 0%, rgba(243, 244, 246, 0.4) 0, transparent 50%),
                    radial-gradient(at 100% 100%, rgba(224, 231, 255, 0.3) 0, transparent 50%);
            }
            .user-bubble {
                background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
                box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.2);
            }
            @keyframes pulse-ring {
                0% { transform: scale(0.95); opacity: 1; }
                50% { transform: scale(1.15); opacity: 0.5; }
                100% { transform: scale(1.3); opacity: 0; }
            }
            .pulse-ring {
                animation: pulse-ring 2s cubic-bezier(0.24, 0, 0.38, 1) infinite;
            }
        </style>

        {{-- Top Premium Header Panel --}}
        <div class="bg-white/85 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-gray-150 shrink-0 ai-glow flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 text-white flex items-center justify-center shadow-lg shadow-purple-500/20 relative">
                    <i data-lucide="sparkles" class="w-5.5 h-5.5 animate-pulse"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">CST Guidance AI</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="relative flex h-2 w-2">
                            <span class="pulse-ring absolute inline-flex h-full w-full rounded-full bg-indigo-400"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                        </span>
                        <p class="text-xs text-gray-500 font-semibold tracking-wide uppercase">Core Intelligence Online</p>
                    </div>
                </div>
            </div>
            
            {{-- Quick Stats / Status pill --}}
            <span class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-150 rounded-xl text-xs font-semibold text-gray-600">
                <i data-lucide="brain-circuit" class="w-4 h-4 text-indigo-500"></i>
                Model: Handbook-GPT v4.0
            </span>
        </div>

        {{-- Main Chat Hub --}}
        <div class="flex-1 bg-white/95 backdrop-blur-md rounded-2xl border border-gray-150 shadow-md flex flex-col overflow-hidden ai-glow">
            
            {{-- Chat Conversation Stream --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6 scroll-smooth chat-bg" id="chat-container">
                
                {{-- Welcome Screen --}}
                <div x-show="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center space-y-6 max-w-lg mx-auto py-12">
                    <div class="w-20 h-20 bg-gradient-to-tr from-indigo-50 to-purple-50 rounded-2xl flex items-center justify-center border border-indigo-100/50 shadow-inner">
                        <i data-lucide="bot-message-square" class="w-10 h-10 text-indigo-600"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Institutional AI Companion</h2>
                        <p class="text-sm text-gray-500 mt-2.5 leading-relaxed">
                            Ask me anything about student regulations, handbook compliance standards, code of conduct, and case procedures.
                        </p>
                        
                        {{-- Interactive Suggestion Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mt-8 text-left">
                            <button @click="input = 'What are the major offenses?'; sendMessage()" class="p-4 rounded-xl bg-white border border-gray-200 hover:border-indigo-400 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-start gap-4 text-left group">
                                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:bg-indigo-100 transition-colors">
                                    <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Major Offenses</h3>
                                    <p class="text-xs text-gray-500 mt-1">Review severe violations and sanction limits.</p>
                                </div>
                            </button>
                            
                            <button @click="input = 'What is the disciplinary hearing process?'; sendMessage()" class="p-4 rounded-xl bg-white border border-gray-200 hover:border-indigo-400 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-start gap-4 text-left group">
                                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 group-hover:bg-purple-100 transition-colors">
                                    <i data-lucide="git-merge" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Disciplinary Steps</h3>
                                    <p class="text-xs text-gray-500 mt-1">Understand the timeline of a logged case.</p>
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
                             :class="msg.role === 'user' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-700'">
                            <i :data-lucide="msg.role === 'user' ? 'user' : 'bot'" class="w-4.5 h-4.5"></i>
                        </div>

                        {{-- Message Bubble --}}
                        <div class="max-w-[75%] flex flex-col" :class="msg.role === 'user' ? 'items-end' : 'items-start'">
                            <div class="px-5 py-3.5 text-sm leading-relaxed"
                                 :class="msg.role === 'user'
                                    ? 'user-bubble text-white rounded-2xl rounded-tr-none font-medium'
                                    : 'bg-white text-gray-800 rounded-2xl rounded-tl-none border border-gray-150 shadow-sm'">
                                <span x-html="msg.content"></span>
                            </div>
                            
                            {{-- Document Citations / Sources --}}
                            <template x-if="msg.role === 'bot' && msg.sources && msg.sources.length > 0">
                                <div class="mt-2.5 flex flex-wrap gap-2">
                                    <template x-for="source in msg.sources">
                                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-[10px] font-bold text-gray-500 shadow-sm uppercase tracking-wide">
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
                    <div class="w-9 h-9 rounded-xl bg-white border border-gray-200 flex-shrink-0 flex items-center justify-center shadow-sm">
                        <i data-lucide="bot" class="w-4.5 h-4.5 text-gray-400"></i>
                    </div>
                    <div class="bg-white border border-gray-150 rounded-2xl rounded-tl-none px-5 py-4 shadow-sm flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce"></span>
                        <span class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.15s"></span>
                        <span class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
                    </div>
                </div>
            </div>

            {{-- Input Control Board --}}
            <div class="p-4 bg-white border-t border-gray-150 shrink-0">
                <form @submit.prevent="sendMessage" class="relative flex items-end gap-3 max-w-4xl mx-auto">
                    <div class="relative flex-1 bg-gray-50 rounded-xl border border-gray-200 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/10 transition-all duration-200">
                        <textarea 
                            x-model="input"
                            @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                            rows="1"
                            class="w-full bg-transparent border-0 focus:ring-0 py-3.5 px-4 text-sm text-gray-900 placeholder-gray-400 resize-none"
                            placeholder="Ask me about rules, regulations, infraction consequences..."
                            oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                            style="min-height: 48px; max-height: 120px;"
                        ></textarea>
                    </div>
                    <button type="submit" 
                            :disabled="!input.trim() || isTyping"
                            class="h-[48px] px-5 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-semibold text-sm shadow-md shadow-indigo-600/10 hover:bg-indigo-700 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:pointer-events-none transition-all duration-200">
                        <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                        Send Query
                    </button>
                </form>
                <p class="text-center mt-3 text-[10px] text-gray-400 font-semibold uppercase tracking-wider">
                    Powered by semantic handbook vector indexing • always review the <a href="/handbooks" class="text-indigo-600 hover:underline">Official Handbook</a>
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
                    if (textarea) textarea.style.height = '48px';
                    
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
