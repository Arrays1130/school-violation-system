/** Read Laravel's XSRF-TOKEN cookie (not HttpOnly). */
export function getXsrfToken() {
    const row = document.cookie
        .split('; ')
        .find((part) => part.startsWith('XSRF-TOKEN='));
    if (!row) return '';
    return decodeURIComponent(row.slice('XSRF-TOKEN='.length));
}

/** Headers Laravel accepts for JSON fetch() POSTs (meta + cookie). */
export function csrfHeaders(extra = {}) {
    const meta = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const xsrf = getXsrfToken();
    return {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': meta,
        ...(xsrf ? { 'X-XSRF-TOKEN': xsrf } : {}),
        ...extra,
    };
}

/** Include _token in JSON body (survives proxies that strip CSRF headers). */
export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

export function withCsrf(payload = {}) {
    const token = csrfToken();
    return token ? { _token: token, ...payload } : payload;
}

export function syncCsrfMeta(token) {
    if (!token) return;
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) meta.setAttribute('content', token);
}

export function aiAssistantUrl({ prompt, studentId, caseId, source } = {}) {
    const params = new URLSearchParams();

    if (prompt) params.set('prompt', prompt);
    if (studentId) params.set('student_id', String(studentId));
    if (caseId) params.set('case_id', String(caseId));
    if (source) params.set('source', source);

    const query = params.toString();

    return route('ai-assistant.index') + (query ? `?${query}` : '');
}

export function pageContextPayload(pageContext) {
    if (!pageContext) return null;

    const payload = {};
    if (pageContext.student_id) payload.student_id = pageContext.student_id;
    if (pageContext.case_id) payload.case_id = pageContext.case_id;

    return Object.keys(payload).length ? payload : null;
}
