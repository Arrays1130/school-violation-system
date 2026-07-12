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
