import React, { useState, useRef, useEffect, useCallback, useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ConfirmDialog from '@/Components/ConfirmDialog';
import { Head, Link } from '@inertiajs/react';
import {
    Sparkles, Send, Mic, MicOff, Trash2, Copy,
    CheckCheck, AlertCircle, RefreshCw, BookOpen, RotateCcw, ExternalLink,
    ThumbsUp, ThumbsDown, Scale
} from 'lucide-react';
import { escapeHtml } from '@/lib/safeHtml';
import { pageContextPayload, csrfHeaders, withCsrf } from '@/lib/aiAssistant';

const SESSION_PREFIX = 'nexus_ai_chat_';

function renderMarkdown(text) {
    if (!text) return '';
    let html = escapeHtml(text);

    html = html.replace(/```([\s\S]*?)```/g, '<pre class="ai-pre"><code>$1</code></pre>');
    html = html.replace(/`([^`]+)`/g, '<code class="ai-code">$1</code>');
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/^### (.*$)/gm, '<h3 class="ai-h3">$1</h3>');
    html = html.replace(/^## (.*$)/gm, '<h2 class="ai-h2">$1</h2>');
    html = html.replace(/^# (.*$)/gm, '<h1 class="ai-h1">$1</h1>');
    html = html.replace(/^---$/gm, '<hr class="ai-hr"/>');

    html = html.replace(/(?:^|\n)((?:\s*[-•]\s+.+(?:\n|$))+)/g, (_, block) => {
        const items = block.trim().split('\n').map((line) => {
            const item = line.replace(/^\s*[-•]\s+/, '').trim();
            return `<li>${item}</li>`;
        }).join('');
        return `\n<ul class="ai-ul">${items}</ul>\n`;
    });

    html = html.replace(/(?:^|\n)((?:\s*\d+\.\s+.+(?:\n|$))+)/g, (_, block) => {
        const items = block.trim().split('\n').map((line) => {
            const item = line.replace(/^\s*\d+\.\s+/, '').trim();
            return `<li>${item}</li>`;
        }).join('');
        return `\n<ol class="ai-ol">${items}</ol>\n`;
    });

    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
    html = html.replace(/\n/g, '<br/>');
    html = html.replace(/<br\/>\s*(<(?:ul|ol|h[123]|pre|hr))/g, '$1');
    html = html.replace(/(<\/(?:ul|ol|h[123]|pre)>)\s*<br\/>/g, '$1');

    return html;
}

function normalizeSource(source) {
    if (typeof source === 'string') {
        return { title: source, url: null, type: 'handbook' };
    }
    return { type: 'handbook', ...source };
}

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

function MessageBlock({ msg, onRegenerate, onFeedback }) {
    const [copied, setCopied] = useState(false);
    const [feedback, setFeedback] = useState(msg.feedback ?? null);
    const isUser = msg.role === 'user';
    const isError = msg.isError;

    const handleCopy = () => {
        navigator.clipboard.writeText(msg.content);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    if (isUser) {
        return (
            <div className="w-full flex justify-end px-4 py-4">
                <div className="max-w-[70%] bg-indigo-600 text-white rounded-3xl px-5 py-3 text-[15px] leading-relaxed shadow-sm">
                    {msg.content}
                </div>
            </div>
        );
    }

    const sources = (msg.sources || []).map(normalizeSource);
    const handbookSources = sources.filter((source) => source.type !== 'violation');
    const violationSources = sources.filter((source) => source.type === 'violation');

    const submitFeedback = async (rating) => {
        if (!msg.usageLogId || feedback !== null) return;
        setFeedback(rating);
        onFeedback?.(msg.usageLogId, rating);
        try {
            await fetch(route('ai-assistant.feedback'), {
                method: 'POST',
                credentials: 'same-origin',
                headers: csrfHeaders({
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }),
                body: JSON.stringify(withCsrf({ usage_log_id: msg.usageLogId, rating })),
            });
        } catch (_) {
            setFeedback(null);
        }
    };

    return (
        <div className="w-full flex justify-center px-4 py-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group">
            <div className="max-w-3xl w-full flex gap-4">
                <div className={`w-8 h-8 rounded-2xl shrink-0 flex items-center justify-center mt-1 ${isError ? 'bg-rose-100 text-rose-600' : 'bg-gradient-to-br from-violet-500 via-indigo-500 to-cyan-400 text-white shadow-md shadow-indigo-500/30'}`}>
                    {isError ? <AlertCircle className="w-5 h-5" /> : <Sparkles className="w-4 h-4" />}
                </div>
                <div className="flex-1 overflow-hidden min-w-0">
                    {msg.mode && (
                        <span className={`inline-flex mb-2 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide ${
                            msg.mode === 'gemini'
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                        }`}>
                            {msg.mode === 'gemini' ? 'Live intelligence' : 'Handbook search'}
                        </span>
                    )}
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

                    {sources.length > 0 && (
                        <div className="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 space-y-3">
                            {handbookSources.length > 0 && (
                                <div>
                                    <p className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2 flex items-center gap-1.5">
                                        <BookOpen className="w-3.5 h-3.5" />
                                        Handbook sources
                                    </p>
                                    <div className="flex flex-wrap gap-2">
                                        {handbookSources.map((source, idx) => (
                                            source.url ? (
                                                <Link
                                                    key={`handbook-${idx}`}
                                                    href={source.url}
                                                    className="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-[11px] font-semibold rounded-lg border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
                                                >
                                                    <BookOpen className="w-3 h-3 shrink-0" />
                                                    {source.title}
                                                    <ExternalLink className="w-3 h-3 shrink-0 opacity-60" />
                                                </Link>
                                            ) : (
                                                <span
                                                    key={`handbook-${idx}`}
                                                    className="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-[11px] font-semibold rounded-lg border border-indigo-100 dark:border-indigo-800"
                                                >
                                                    <BookOpen className="w-3 h-3 shrink-0" />
                                                    {source.title}
                                                </span>
                                            )
                                        ))}
                                    </div>
                                </div>
                            )}
                            {violationSources.length > 0 && (
                                <div>
                                    <p className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2 flex items-center gap-1.5">
                                        <Scale className="w-3.5 h-3.5" />
                                        Violation rules
                                    </p>
                                    <div className="flex flex-wrap gap-2">
                                        {violationSources.map((source, idx) => (
                                            <Link
                                                key={`violation-${idx}`}
                                                href={source.url}
                                                className="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 text-[11px] font-semibold rounded-lg border border-amber-100 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors"
                                            >
                                                <Scale className="w-3 h-3 shrink-0" />
                                                {source.title}
                                                <ExternalLink className="w-3 h-3 shrink-0 opacity-60" />
                                            </Link>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {!isError && msg.content && (
                        <div className="mt-2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button
                                type="button"
                                onClick={handleCopy}
                                className="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 rounded-md hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                                title="Copy"
                            >
                                {copied ? <CheckCheck className="w-4 h-4 text-emerald-500" /> : <Copy className="w-4 h-4" />}
                            </button>
                            {msg.usageLogId && (
                                <>
                                    <button
                                        type="button"
                                        onClick={() => submitFeedback(1)}
                                        disabled={feedback !== null}
                                        className={`p-1.5 rounded-md transition-colors ${
                                            feedback === 1
                                                ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30'
                                                : 'text-slate-400 hover:text-emerald-600 hover:bg-slate-200 dark:hover:bg-slate-700'
                                        }`}
                                        title="Helpful"
                                    >
                                        <ThumbsUp className="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => submitFeedback(-1)}
                                        disabled={feedback !== null}
                                        className={`p-1.5 rounded-md transition-colors ${
                                            feedback === -1
                                                ? 'text-rose-600 bg-rose-50 dark:bg-rose-900/30'
                                                : 'text-slate-400 hover:text-rose-600 hover:bg-slate-200 dark:hover:bg-slate-700'
                                        }`}
                                        title="Not helpful"
                                    >
                                        <ThumbsDown className="w-4 h-4" />
                                    </button>
                                </>
                            )}
                            {onRegenerate && (
                                <button
                                    type="button"
                                    onClick={onRegenerate}
                                    className="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-md hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                                    title="Regenerate"
                                >
                                    <RotateCcw className="w-4 h-4" />
                                </button>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

function getSuggestedPrompts(role, department) {
    if (role === 'dean') {
        return [
            { label: 'College pulse', q: `Give a sharp briefing of open cases and repeat offenders in ${department || 'my'} department.` },
            { label: 'Who is high risk', q: 'Who are the top violators in my department and what should we do next with each?' },
            { label: 'Hearing, simplified', q: 'Walk me through the hearing process as if I have a case this week — exact steps, in order.' },
            { label: 'Uniform call', q: 'If a student is out of uniform, what is the policy and the first three actions OSA should take?' },
        ];
    }

    return [
        { label: 'Major offenses', q: 'Explain major offenses like a briefing for OSA: codes, first action, and when to escalate.' },
        { label: 'Hearing playbook', q: 'Give the hearing process as a numbered playbook staff can follow today.' },
        { label: 'Live caseload', q: 'Brief me on open cases and top violators — what needs attention first?' },
        { label: 'Uniform & grooming', q: 'What is the uniform and grooming policy, and the catalog sanction path for a first offense?' },
    ];
}

export default function AiAssistant({
    auth,
    provider = 'Gemini AI',
    providerMode = 'gemini',
    schoolName,
    conversationId: initialConversationId = null,
    initialMessages = [],
    vectorSearchReady = false,
    pageContext = null,
    initialPrompt = null,
}) {
    const sessionKey = `${SESSION_PREFIX}${auth.user.id}`;
    const [conversationId, setConversationId] = useState(initialConversationId);
    const [input, setInput] = useState('');
    const [messages, setMessages] = useState(() => {
        if (initialMessages?.length) {
            return initialMessages;
        }
        try {
            const saved = sessionStorage.getItem(sessionKey);
            return saved ? JSON.parse(saved) : [];
        } catch {
            return [];
        }
    });
    const [isTyping, setIsTyping] = useState(false);
    const [listening, setListening] = useState(false);
    const [activeMode, setActiveMode] = useState(providerMode);

    const chatRef = useRef(null);
    const textareaRef = useRef(null);
    const recognitionRef = useRef(null);
    const autoSentRef = useRef(false);
    const activePageContext = useMemo(() => pageContextPayload(pageContext), [pageContext]);

    const suggestedPrompts = useMemo(
        () => getSuggestedPrompts(auth.user.role, auth.user.department),
        [auth.user.role, auth.user.department]
    );

    useEffect(() => {
        try {
            sessionStorage.setItem(sessionKey, JSON.stringify(messages));
        } catch (_) {}
    }, [messages, sessionKey]);

    useEffect(() => {
        if (chatRef.current) {
            chatRef.current.scrollTop = chatRef.current.scrollHeight;
        }
    }, [messages, isTyping]);

    const sendMessage = useCallback(async (overrideText = null, options = {}) => {
        const text = (overrideText ?? input).trim();
        if (!text || isTyping) return;

        if (!options.replaceLast) {
            setMessages((prev) => [...prev, { role: 'user', content: text }]);
        }
        setInput('');
        setIsTyping(true);
        if (textareaRef.current) textareaRef.current.style.height = '52px';

        if (options.replaceLast) {
            setMessages((prev) => {
                const copy = [...prev];
                if (copy.length) copy.pop();
                return [...copy, { role: 'bot', content: '', sources: [], mode: null }];
            });
        } else {
            setMessages((prev) => [...prev, { role: 'bot', content: '', sources: [], mode: null }]);
        }

        try {
            const streamUrl = window.location.pathname.replace(/\/$/, '') + '/stream';
            const res = await fetch(streamUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: csrfHeaders({
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                }),
                body: JSON.stringify(withCsrf({
                    message: text,
                    conversation_id: conversationId,
                    page_context: activePageContext,
                })),
            });

            if (res.status === 419) {
                throw new Error('Session expired (419). Mag-refresh (Ctrl+Shift+R), tapos try again.');
            }
            if (res.status === 429) {
                throw new Error('Too many requests. Please wait a moment.');
            }
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const reader = res.body.getReader();
            const decoder = new TextDecoder();
            let botReply = '';

            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                const lines = decoder.decode(value, { stream: true }).split('\n');
                for (const line of lines) {
                    if (!line.startsWith('data: ')) continue;
                    const raw = line.slice(6).trim();
                    if (!raw) continue;
                    try {
                        const parsed = JSON.parse(raw);
                        if (parsed.done) break;
                        if (parsed.text) {
                            botReply += parsed.text;
                            setMessages((prev) => {
                                const copy = [...prev];
                                copy[copy.length - 1] = { ...copy[copy.length - 1], content: botReply };
                                return copy;
                            });
                        }
                        if (parsed.sources) {
                            setMessages((prev) => {
                                const copy = [...prev];
                                copy[copy.length - 1] = { ...copy[copy.length - 1], sources: parsed.sources };
                                return copy;
                            });
                        }
                        if (parsed.meta?.mode) {
                            setActiveMode(parsed.meta.mode);
                            setMessages((prev) => {
                                const copy = [...prev];
                                copy[copy.length - 1] = {
                                    ...copy[copy.length - 1],
                                    mode: parsed.meta.mode,
                                    usageLogId: parsed.meta.usageLogId ?? copy[copy.length - 1].usageLogId,
                                };
                                return copy;
                            });
                        }
                        if (parsed.meta?.conversationId) {
                            setConversationId(parsed.meta.conversationId);
                        }
                    } catch (_) {}
                }
            }
        } catch (err) {
            setMessages((prev) => {
                const copy = [...prev];
                if (copy.length && copy[copy.length - 1].role === 'bot' && !copy[copy.length - 1].content) {
                    copy[copy.length - 1] = {
                        role: 'bot',
                        content: `⚠️ ${err.message || 'Connection issue. Please try again.'}`,
                        isError: true,
                        sources: [],
                    };
                    return copy;
                }
                return [...copy, {
                    role: 'bot',
                    content: `⚠️ ${err.message || 'Connection issue. Please try again.'}`,
                    isError: true,
                    sources: [],
                }];
            });
        } finally {
            setIsTyping(false);
        }
    }, [input, isTyping, messages, conversationId, activePageContext]);

    useEffect(() => {
        if (!initialPrompt || autoSentRef.current || initialMessages?.length) {
            return;
        }

        autoSentRef.current = true;
        sendMessage(initialPrompt);
    }, [initialPrompt, initialMessages?.length, sendMessage]);

    const handleFeedback = useCallback((usageLogId, rating) => {
        setMessages((prev) => prev.map((message) => (
            message.usageLogId === usageLogId
                ? { ...message, feedback: rating }
                : message
        )));
    }, []);

    const handleInputChange = (e) => {
        setInput(e.target.value);
        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
            textareaRef.current.style.height = `${Math.min(textareaRef.current.scrollHeight, 200)}px`;
        }
    };

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
            const transcript = Array.from(e.results).map((r) => r[0].transcript).join('');
            setInput(transcript);
        };
        rec.onend = () => setListening(false);
        rec.start();
        recognitionRef.current = rec;
        setListening(true);
    };

    const regenerateLast = () => {
        const lastUser = [...messages].reverse().find((m) => m.role === 'user');
        if (!lastUser || isTyping) return;
        setMessages((prev) => (prev.length ? prev.slice(0, -1) : prev));
        sendMessage(lastUser.content, { replaceLast: true });
    };

    const [showClearConfirm, setShowClearConfirm] = useState(false);
    const confirmClearChat = async () => {
        try {
            const res = await fetch(route('ai-assistant.clear'), {
                method: 'POST',
                credentials: 'same-origin',
                headers: csrfHeaders({
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }),
                body: JSON.stringify(withCsrf({ conversation_id: conversationId })),
            });
            if (res.ok) {
                const data = await res.json();
                setConversationId(data.conversationId ?? null);
            }
        } catch (_) {}

        setMessages([]);
        sessionStorage.removeItem(sessionKey);
        setShowClearConfirm(false);
    };

    const isEmpty = messages.length === 0;
    const modeLabel = activeMode === 'gemini' ? provider : 'Handbook Search';

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Nexus AI" />

            <ConfirmDialog
                open={showClearConfirm}
                onClose={() => setShowClearConfirm(false)}
                onConfirm={confirmClearChat}
                title="Clear history?"
                description="Are you sure you want to clear the conversation history?"
                confirmLabel="Yes, clear it"
                destructive
            />

            <div className="flex flex-col h-[calc(100vh-4.1rem)] lg:h-[calc(100vh-4.1rem)] bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-sans">
                <div className="flex items-center justify-between px-4 py-3 shrink-0 border-b border-slate-100 dark:border-slate-800">
                    <div className="flex items-center gap-3 flex-wrap">
                        <div className="w-9 h-9 rounded-2xl bg-gradient-to-br from-violet-500 via-indigo-500 to-cyan-400 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                            <Sparkles className="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h1 className="text-lg font-semibold tracking-tight leading-none">Nexus</h1>
                            <p className="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">OSA intelligence copilot</p>
                        </div>
                        <span className={`px-2 py-0.5 text-[10px] font-bold rounded-md uppercase tracking-wide ${
                            activeMode === 'gemini'
                                ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300'
                                : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'
                        }`}>
                            {modeLabel}
                        </span>
                        {vectorSearchReady && (
                            <span className="px-2 py-0.5 text-[10px] font-bold rounded-md uppercase tracking-wide bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300">
                                Live knowledge
                            </span>
                        )}
                    </div>
                    <div className="flex items-center gap-1">
                        {messages.length > 0 && (
                            <button
                                type="button"
                                onClick={() => setShowClearConfirm(true)}
                                className="p-2 text-slate-500 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                                title="Clear chat"
                            >
                                <Trash2 className="w-5 h-5" />
                            </button>
                        )}
                    </div>
                </div>

                {(pageContext?.student?.name || pageContext?.case?.student_name) && (
                    <div className="px-4 py-2 border-b border-purple-100 dark:border-purple-900/40 bg-purple-50/80 dark:bg-purple-900/20 text-sm text-purple-800 dark:text-purple-200">
                        Context: <span className="font-semibold">{pageContext.student?.name || pageContext.case?.student_name}</span>
                        {pageContext.case?.case_code
                            ? ` · ${pageContext.case.case_code}`
                            : pageContext.case?.id
                                ? ` · Case #${String(pageContext.case.id).padStart(4, '0')}`
                                : ''}
                    </div>
                )}

                <div ref={chatRef} className="flex-1 overflow-y-auto scroll-smooth ai-scrollbar min-h-0">
                    {isEmpty ? (
                        <div className="h-full flex flex-col items-center justify-center px-4 py-8 fade-in">
                            <div className="relative mb-7">
                                <div className="absolute inset-0 rounded-3xl bg-gradient-to-br from-violet-500/30 via-indigo-500/20 to-cyan-400/30 blur-2xl" />
                                <div className="relative w-16 h-16 rounded-3xl bg-gradient-to-br from-violet-500 via-indigo-500 to-cyan-400 flex items-center justify-center shadow-xl shadow-indigo-500/25">
                                    <Sparkles className="w-8 h-8 text-white" />
                                </div>
                            </div>
                            <h2 className="text-2xl sm:text-3xl font-semibold mb-2 text-center tracking-tight">Ask Nexus anything about OSA.</h2>
                            <p className="text-sm text-slate-500 dark:text-slate-400 mb-8 text-center max-w-md leading-relaxed">
                                Briefings, sanctions, hearings, and student records at {schoolName} — answered like a sharp advisor, not a chatbot.
                            </p>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-w-2xl">
                                {suggestedPrompts.map((item, idx) => (
                                    <button
                                        key={idx}
                                        type="button"
                                        onClick={() => sendMessage(item.q)}
                                        className="text-left px-4 py-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/70 dark:bg-slate-800/40 hover:border-indigo-300 dark:hover:border-indigo-500 hover:bg-indigo-50/60 dark:hover:bg-indigo-950/40 text-[14px] text-slate-600 dark:text-slate-300 transition-all shadow-sm"
                                    >
                                        <span className="block font-semibold mb-1 text-slate-800 dark:text-slate-100">{item.label}</span>
                                        <span className="text-slate-400 dark:text-slate-500 text-[13px] line-clamp-2 leading-snug">{item.q}</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                    ) : (
                        <div className="flex flex-col pb-4">
                            {messages.map((msg, i) => (
                                <MessageBlock
                                    key={i}
                                    msg={msg}
                                    onFeedback={handleFeedback}
                                    onRegenerate={
                                        i === messages.length - 1 && msg.role === 'bot' && !msg.isError
                                            ? regenerateLast
                                            : null
                                    }
                                />
                            ))}
                            {isTyping && (
                                <div className="w-full flex justify-center px-4 py-4">
                                    <div className="max-w-3xl w-full flex gap-4">
                                        <div className="w-8 h-8 rounded-2xl shrink-0 flex items-center justify-center mt-1 bg-gradient-to-br from-violet-500 via-indigo-500 to-cyan-400 text-white shadow-md shadow-indigo-500/30">
                                            <Sparkles className="w-4 h-4" />
                                        </div>
                                        <TypingDots />
                                    </div>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                <div className="shrink-0 pt-4 pb-24 lg:pb-6 px-4 bg-gradient-to-t from-white via-white to-transparent dark:from-slate-900 dark:via-slate-900">
                    <div className="max-w-3xl mx-auto w-full">
                        <form
                            onSubmit={(e) => { e.preventDefault(); sendMessage(); }}
                            className="relative flex items-end bg-slate-50 dark:bg-slate-800/80 rounded-[24px] overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500/40 focus-within:border-indigo-300 dark:focus-within:border-indigo-500 transition-shadow border border-slate-200 dark:border-slate-700 shadow-sm"
                        >
                            {'SpeechRecognition' in window || 'webkitSpeechRecognition' in window ? (
                                <button
                                    type="button"
                                    onClick={toggleMic}
                                    className={`p-3.5 shrink-0 ${listening ? 'text-rose-500 animate-pulse' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'}`}
                                    aria-label="Voice input"
                                >
                                    {listening ? <MicOff className="w-5 h-5" /> : <Mic className="w-5 h-5" />}
                                </button>
                            ) : <div className="w-4 shrink-0" />}

                            <textarea
                                ref={textareaRef}
                                value={input}
                                onChange={handleInputChange}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter' && !e.shiftKey) {
                                        e.preventDefault();
                                        sendMessage();
                                    }
                                }}
                                disabled={isTyping}
                                rows={1}
                                className="flex-1 py-3.5 px-2 bg-transparent border-none focus:ring-0 resize-none text-[15px] max-h-[200px] outline-none ai-scrollbar text-slate-900 dark:text-slate-100 placeholder:text-slate-400"
                                placeholder="Ask Nexus — a student, a code, or what to do next…"
                                style={{ minHeight: '52px' }}
                            />

                            <div className="p-2 shrink-0">
                                <button
                                    type="submit"
                                    disabled={!input.trim() || isTyping}
                                    className="p-2 bg-gradient-to-br from-violet-600 to-indigo-600 text-white rounded-full disabled:opacity-30 hover:brightness-110 transition-all shadow-md shadow-indigo-500/20"
                                    aria-label="Send message"
                                >
                                    {isTyping ? <RefreshCw className="w-4 h-4 animate-spin" /> : <Send className="w-4 h-4" />}
                                </button>
                            </div>
                        </form>
                        <p className="text-center text-[11px] text-slate-500 dark:text-slate-400 mt-3 font-medium">
                            Nexus cites handbook, catalog, and live records. Confirm high-stakes sanctions with OSA before acting.
                        </p>
                    </div>
                </div>
            </div>

            <style>{`
                .ai-scrollbar::-webkit-scrollbar { width: 6px; }
                .ai-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .ai-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); border-radius: 99px; }
                .dark .ai-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.15); }
                @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
                .fade-in { animation: fadeIn 0.4s ease-out forwards; }
                .ai-message-body h1.ai-h1, .ai-message-body h2.ai-h2, .ai-message-body h3.ai-h3 { font-weight: 700; margin: 1.1em 0 0.45em; letter-spacing: -0.02em; }
                .ai-message-body h3.ai-h3 { color: #4f46e5; font-size: 0.95rem; }
                .dark .ai-message-body h3.ai-h3 { color: #a5b4fc; }
                .ai-message-body ul.ai-ul { padding-left: 1.25em; margin: 0.45em 0 0.9em; list-style-type: disc; }
                .ai-message-body ol.ai-ol { padding-left: 1.25em; margin: 0.45em 0 0.9em; list-style-type: decimal; }
                .ai-message-body li { margin: 0.2em 0; }
                .ai-message-body strong { color: #0f172a; }
                .dark .ai-message-body strong { color: #f8fafc; }
                .ai-message-body pre.ai-pre { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; font-size: 0.85em; overflow-x: auto; margin: 0.8em 0; }
                .dark .ai-message-body pre.ai-pre { background: #0f172a; border-color: #334155; color: #f8fafc; }
                .ai-message-body code.ai-code { background: #eef2ff; color: #4338ca; border-radius: 6px; padding: 1px 6px; font-size: 0.84em; font-weight: 600; }
                .dark .ai-message-body code.ai-code { background: #312e81; color: #c7d2fe; }
            `}</style>
        </AuthenticatedLayout>
    );
}
