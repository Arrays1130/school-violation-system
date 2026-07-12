/** Escape HTML entities before any user/AI content is rendered. */
export function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/** Strip HTML tags for safe plain-text display. */
export function stripHtml(html) {
    if (!html) return '';
    const doc = typeof DOMParser !== 'undefined'
        ? new DOMParser().parseFromString(String(html), 'text/html')
        : null;
    return doc ? (doc.body.textContent || '') : String(html).replace(/<[^>]*>/g, '');
}
