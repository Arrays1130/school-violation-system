import React, { useState, useRef, useEffect, useCallback } from 'react';
import Swal from 'sweetalert2';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import {
    Bot, User, Sparkles, Send,
    Moon, Sun, Mic, MicOff, Trash2, Copy,
    CheckCheck, AlertCircle, RefreshCw
} from 'lucide-react';

/* ─────────────────────────────────────────────
   Tiny markdown-to-html helper (no dependency)
───────────────────────────────────────────── */
function renderMarkdown(text) {
    if (!text) return '';
    return text
        // code blocks
        .replace(/```([\s\S]*?)```/g, '<pre class="ai-pre"><code>$1</code></pre>')
        // inline code
        .replace(/`([^`]+)`/g, '<code class="ai-code">$1</code>')
        // bold
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        // italic
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        // headings
        .replace(/^### (.*$)/gm, '<h3 class="ai-h3">$1</h3>')
        .replace(/^## (.*$)/gm, '<h2 class="ai-h2">$1</h2>')
        .replace(/^# (.*$)/gm, '<h1 class="ai-h1">$1</h1>')
        // unordered list items
        .replace(/^\s*[-•]\s+(.+)/gm, '<li>$1</li>')
        .replace(/(<li>.*<\/li>)/s, '<ul class="ai-ul">$1</ul>')
        // numbered list
        .replace(/^\d+\.\s+(.+)/gm, '<li>$1</li>')
        // horizontal rule
        .replace(/^---$/gm, '<hr class="ai-hr"/>')
        // line breaks (after everything else)
        .replace(/\n/g, '<br/>');
}

/* ─────────────────────────────────────────────
   Typing indicator (3 bouncing dots)
───────────────────────────────────────────── */
function TypingDots() {
    return (
        <div className="flex gap-1.5 items-center py-2 h-6">
            {[0, 0.15, 0.3].map((delay, i) => (
                <span
                    key={i}
                    className="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500 animate-bounce"
                    style={{ animationDelay: `${delay}s`, animationDuration: '0.6s' }}
                />
            ))}
        </div>
    );
}

/* ─────────────────────────────────────────────
   Single message block
───────────────────────────────────────────── */
function MessageBlock({ msg, onCopy }) {
    const [copied, setCopied] = useState(false);
    const isUser = msg.role === 'user';
    const isError = msg.isError;

    const handleCopy = () => {
        navigator.clipboard.writeText(msg.content);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
        if (onCopy) onCopy();
    };

    if (isUser) {
        return (
            <div className="w-full flex justify-end px-4 py-6">
                <div className="max-w-[70%] bg-slate-100 dark:bg-[#2f2f2f] text-slate-800 dark:text-slate-100 rounded-3xl px-5 py-3 text-[15px] leading-relaxed">
                    {msg.content}
                </div>
            </div>
        );
    }

    // Bot or Error Message
    return (
        <div className="w-full flex justify-center px-4 py-6 hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-[#2a2a2a] transition-colors group">
            <div className="max-w-3xl w-full flex gap-4">
                <div className={`w-8 h-8 rounded-full shrink-0 flex items-center justify-center mt-1 ${isError ? 'bg-rose-100 text-rose-600 dark:text-rose-400' : 'bg-indigo-600 text-white'}`}>
                    {isError ? <AlertCircle className="w-5 h-5" /> : <Sparkles className="w-4 h-4" />}
                </div>
                <div className="flex-1 overflow-hidden">
                    <div className="text-[15px] leading-relaxed text-slate-800 dark:text-slate-100 min-w-0">
                        {isError ? (
                            <span className="text-rose-600 dark:text-rose-400">{msg.content}</span>
                        ) : (
                            <div
                                className="ai-message-body font-sans break-words"
                                dangerouslySetInnerHTML={{ __html: renderMarkdown(msg.content) }}
                            />
                        )}
                    </div>

                    {/* Sources array */}
                    {msg.sources && msg.sources.length > 0 && (
                        <div className="mt-3 flex flex-wrap gap-2">
                            {msg.sources.map((source, idx) => (
                                <span key={idx} className="px-2 py-1 bg-slate-100 dark:bg-[#3f3f3f] text-slate-600 dark:text-slate-300 text-[11px] font-medium rounded-md cursor-default">
                                    {source}
                                </span>
                            ))}
                        </div>
                    )}

                    {/* Action buttons (copy) */}
                    {!isError && msg.content && (
                        <div className="mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button
                                onClick={handleCopy}
                                className="p-1.5 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:text-slate-400 dark:hover:text-slate-300 rounded-md hover:bg-slate-200 dark:hover:bg-[#3f3f3f] transition-colors"
                                title="Copy"
                            >
                                {copied ? <CheckCheck className="w-4 h-4 text-emerald-500" /> : <Copy className="w-4 h-4" />}
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

/* ─────────────────────────────────────────────
   Main Component
───────────────────────────────────────────── */
export default function AiAssistant({ auth }) {
    const [input, setInput]         = useState('');
    const [messages, setMessages]   = useState([]);
    const [isTyping, setIsTyping]   = useState(false);
    const [dark, setDark]           = useState(() => localStorage.getItem('ai_dark') === 'true');
    const [listening, setListening] = useState(false);
    const [streamError, setStreamError] = useState(null);

    const chatRef     = useRef(null);
    const textareaRef = useRef(null);
    const recognitionRef = useRef(null);

    /* Dark mode persistence */
    useEffect(() => {
        localStorage.setItem('ai_dark', dark);
    }, [dark]);

    /* Auto-scroll to bottom only when typing or sending */
    useEffect(() => {
        if (chatRef.current) {
            chatRef.current.scrollTop = chatRef.current.scrollHeight;
        }
    }, [messages, isTyping]);

    /* Textarea auto-resize */
    const handleInputChange = (e) => {
        setInput(e.target.value);
        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
            textareaRef.current.style.height = `${Math.min(textareaRef.current.scrollHeight, 200)}px`;
        }
    };

    /* Speech to text */
    const toggleMic = () => {
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) return;
        if (listening) {
            recognitionRef.current?.stop();
            setListening(false);
            return;
        }
        const rec = new SR();
        rec.lang = 'en-PH';
        rec.interimResults = true;
        rec.onresult = (e) => {
            const transcript = Array.from(e.results).map(r => r[0].transcript).join('');
            setInput(transcript);
        };
        rec.onend = () => setListening(false);
        rec.start();
        recognitionRef.current = rec;
        setListening(true);
    };

    /* Send message */
    const sendMessage = useCallback(async (overrideText = null) => {
        const text = (overrideText ?? input).trim();
        if (!text || isTyping) return;

        setMessages(prev => [...prev, { role: 'user', content: text }]);
        setInput('');
        setIsTyping(true);
        setStreamError(null);
        if (textareaRef.current) textareaRef.current.style.height = '52px';

        try {
            const streamUrl = window.location.pathname.replace(/\/$/, '') + '/stream';
            const res = await fetch(streamUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'text/event-stream',
                },
                body: JSON.stringify({ message: text }),
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            setMessages(prev => [...prev, { role: 'bot', content: '', sources: [] }]);

            const reader  = res.body.getReader();
            const decoder = new TextDecoder();
            let botReply  = '';

            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                const lines = decoder.decode(value, { stream: true }).split('\n');
                for (const line of lines) {
                    if (!line.startsWith('data: ')) continue;
                    const raw = line.slice(6).trim();
                    if (raw === '[DONE]') break;
                    try {
                        const parsed = JSON.parse(raw);
                        if (parsed.text) {
                            botReply += parsed.text;
                            setMessages(prev => {
                                const copy = [...prev];
                                copy[copy.length - 1] = { ...copy[copy.length - 1], content: botReply };
                                return copy;
                            });
                        }
                        if (parsed.sources) {
                            setMessages(prev => {
                                const copy = [...prev];
                                copy[copy.length - 1] = { ...copy[copy.length - 1], sources: parsed.sources };
                                return copy;
                            });
                        }
                    } catch (_) {}
                }
            }
        } catch (err) {
            console.error(err);
            setStreamError(err.message);
            setMessages(prev => [...prev, {
                role: 'bot',
                content: '⚠️ I encountered a connection issue with the intelligence core. Please try again.',
                isError: true,
                sources: [],
            }]);
        } finally {
            setIsTyping(false);
        }
    }, [input, isTyping]);

    const clearChat = () => {
        Swal.fire({
            title: 'Clear history?',
            text: "Are you sure you want to clear the conversation history?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, clear it'
        }).then((result) => {
            if (result.isConfirmed) {
                setMessages([]);
            }
        });
    };

    const isEmpty = messages.length === 0;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Nexus AI" />

            {/* Outer wrapper — supports dark mode via class toggle */}
            <div className={dark ? 'dark' : ''}>
                <div className="flex flex-col h-[calc(100vh-4.1rem)] bg-white dark:bg-[#212121] text-slate-800 dark:text-slate-100 font-sans transition-colors duration-300">
                    
                    {/* Header Topbar */}
                    <div className="flex items-center justify-between px-4 py-3 shrink-0">
                        <div className="flex items-center gap-2">
                            <h1 className="text-lg font-semibold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                Nexus AI
                                <span className="px-2 py-0.5 bg-indigo-100 dark:bg-[#2f2f2f] text-indigo-700 dark:text-slate-300 text-[10px] font-bold rounded-md uppercase tracking-wide">
                                    Ollama
                                </span>
                            </h1>
                        </div>
                        <div className="flex items-center gap-1">
                            {messages.length > 0 && (
                                <button onClick={clearChat} className="p-2 text-slate-500 dark:text-slate-400 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-[#2f2f2f] rounded-lg transition-colors" title="Clear chat">
                                    <Trash2 className="w-5 h-5" />
                                </button>
                            )}
                            <button onClick={() => setDark(!dark)} className="p-2 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:text-slate-200 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-[#2f2f2f] rounded-lg transition-colors" title="Toggle dark mode">
                                {dark ? <Sun className="w-5 h-5" /> : <Moon className="w-5 h-5" />}
                            </button>
                        </div>
                    </div>

                    {/* Chat Area */}
                    <div ref={chatRef} className="flex-1 overflow-y-auto scroll-smooth ai-scrollbar min-h-0 pb-32">
                        {isEmpty ? (
                            <div className="h-full flex flex-col items-center justify-center px-4 fade-in">
                                <div className="w-16 h-16 rounded-full bg-slate-100 dark:bg-[#2f2f2f] flex items-center justify-center mb-6">
                                    <Sparkles className="w-8 h-8 text-indigo-600 dark:text-slate-300" />
                                </div>
                                <h2 className="text-2xl font-semibold mb-8 text-slate-800 dark:text-slate-100 text-center">
                                    How can I help you today?
                                </h2>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-w-2xl">
                                    {[
                                        { label: "Search major offenses", q: "What are the major offenses and their sanctions?" },
                                        { label: "Explain hearing process", q: "What is the violation hearing process step by step?" },
                                        { label: "Check student records", q: "Show me the top violators in the system." },
                                        { label: "Review uniform policy", q: "What is the policy on school uniforms?" }
                                    ].map((item, idx) => (
                                        <button
                                            key={idx}
                                            onClick={() => sendMessage(item.q)}
                                            className="text-left px-4 py-3 rounded-xl border border-slate-200 dark:border-[#3f3f3f] hover:bg-slate-50 dark:hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-[#2f2f2f] text-[14px] text-slate-600 dark:text-slate-300 transition-colors"
                                        >
                                            <span className="block font-medium mb-1">{item.label}</span>
                                            <span className="text-slate-400 dark:text-slate-500 dark:text-slate-400 text-[13px] truncate block">{item.q}</span>
                                        </button>
                                    ))}
                                </div>
                            </div>
                        ) : (
                            <div className="flex flex-col pb-6">
                                {messages.map((msg, i) => (
                                    <MessageBlock key={i} msg={msg} onCopy={() => {}} />
                                ))}
                                {isTyping && (
                                    <div className="w-full flex justify-center px-4 py-6">
                                        <div className="max-w-3xl w-full flex gap-4">
                                            <div className="w-8 h-8 rounded-full shrink-0 flex items-center justify-center mt-1 bg-indigo-600 text-white">
                                                <Sparkles className="w-4 h-4" />
                                            </div>
                                            <div className="flex-1">
                                                <TypingDots />
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Input Area */}
                    <div className="absolute bottom-0 left-0 right-0 pt-6 pb-6 px-4 bg-gradient-to-t from-white via-white to-transparent dark:from-[#212121] dark:via-[#212121] dark:to-transparent">
                        <div className="max-w-3xl mx-auto w-full relative">
                            <form
                                onSubmit={e => { e.preventDefault(); sendMessage(); }}
                                className="relative flex items-end bg-slate-100 dark:bg-[#2f2f2f] rounded-[24px] overflow-hidden focus-within:ring-2 focus-within:ring-slate-300 dark:focus-within:ring-slate-500 transition-shadow"
                            >
                                {'SpeechRecognition' in window || 'webkitSpeechRecognition' in window ? (
                                    <button
                                        type="button"
                                        onClick={toggleMic}
                                        className={`p-3.5 shrink-0 ${listening ? 'text-rose-500 animate-pulse' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:text-slate-200 dark:hover:text-slate-100'}`}
                                    >
                                        {listening ? <MicOff className="w-5 h-5" /> : <Mic className="w-5 h-5" />}
                                    </button>
                                ) : <div className="w-4 shrink-0" />}

                                <textarea
                                    ref={textareaRef}
                                    value={input}
                                    onChange={handleInputChange}
                                    onKeyDown={e => {
                                        if (e.key === 'Enter' && !e.shiftKey) {
                                            e.preventDefault();
                                            sendMessage();
                                        }
                                    }}
                                    disabled={isTyping}
                                    rows={1}
                                    className="flex-1 py-3.5 px-2 bg-transparent border-none focus:ring-0 resize-none text-[15px] max-h-[200px] outline-none ai-scrollbar"
                                    placeholder="Message Nexus AI..."
                                    style={{ minHeight: '52px' }}
                                />

                                <div className="p-2 shrink-0">
                                    <button
                                        type="submit"
                                        disabled={!input.trim() || isTyping}
                                        className="p-2 bg-black dark:bg-white dark:bg-slate-900 text-white dark:text-black rounded-full disabled:opacity-30 disabled:bg-slate-300 dark:disabled:bg-slate-600 dark:disabled:text-slate-400 transition-colors"
                                    >
                                        {isTyping ? <RefreshCw className="w-4 h-4 animate-spin" /> : <Send className="w-4 h-4" />}
                                    </button>
                                </div>
                            </form>
                            <p className="text-center text-[11px] text-slate-500 dark:text-slate-400 mt-3 font-medium">
                                AI responses are generated by Ollama and RAG. Always verify critical decisions with the Official Handbook.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {/* ── GLOBAL STYLES ───────────────────────────────── */}
            <style>{`
                /* Modern Custom Scrollbars */
                .ai-scrollbar::-webkit-scrollbar { width: 6px; }
                .ai-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .ai-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); border-radius: 99px; }
                .dark .ai-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.15); }
                
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .fade-in { animation: fadeIn 0.4s ease-out forwards; }

                /* Markdown Body Styles - Clean Typography */
                .ai-message-body p { margin-bottom: 0.75em; }
                .ai-message-body p:last-child { margin-bottom: 0; }
                
                .ai-message-body h1.ai-h1,
                .ai-message-body h2.ai-h2,
                .ai-message-body h3.ai-h3 { font-weight: 700; margin: 1.2em 0 0.5em; color: inherit; }
                
                .ai-message-body h1.ai-h1 { font-size: 1.4em; border-bottom: 1px solid rgba(148, 163, 184, 0.2); padding-bottom: 0.3em; }
                .ai-message-body h2.ai-h2 { font-size: 1.2em; }
                .ai-message-body h3.ai-h3 { font-size: 1.05em; color: #4f46e5; }
                .dark .ai-message-body h3.ai-h3 { color: #818cf8; }
                
                .ai-message-body ul.ai-ul, .ai-message-body ol.ai-ol { padding-left: 1.5em; margin: 0.5em 0 1em; }
                .ai-message-body ul.ai-ul { list-style-type: disc; }
                .ai-message-body li { margin: 0.3em 0; padding-left: 0.25em; }
                
                .ai-message-body pre.ai-pre {
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    color: #0f172a;
                    border-radius: 8px;
                    padding: 12px;
                    font-size: 0.85em;
                    overflow-x: auto;
                    margin: 0.8em 0;
                }
                .dark .ai-message-body pre.ai-pre {
                    background: #000000;
                    border: 1px solid #333333;
                    color: #f8fafc;
                }
                .ai-message-body code.ai-code {
                    background: #f1f5f9;
                    color: #ec4899;
                    border-radius: 4px;
                    padding: 2px 4px;
                    font-size: 0.85em;
                    font-family: monospace;
                }
                .dark .ai-message-body code.ai-code {
                    background: #3f3f3f;
                    color: #f472b6;
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
