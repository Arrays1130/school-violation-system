/**
 * Accessibility Enhancements for School Violation System
 * WCAG 2.1 AA Compliance Improvements
 */

class AccessibilityEnhancer {
    constructor() {
        this.init();
    }

    init() {
        this.enhanceFocusManagement();
        this.enhanceFormControls();
        this.enhanceDataTables();
        this.enhanceModals();
        this.enhanceNotifications();
        this.setupKeyboardShortcuts();
        this.enhanceCharts();
        this.setupLiveRegions();
    }

    /**
     * Enhance focus management for better keyboard navigation
     */
    enhanceFocusManagement() {
        // Add focus-visible polyfill for browsers that don't support it
        if (!CSS.supports('selector(:focus-visible)')) {
            document.addEventListener('mousedown', () => {
                document.body.classList.add('using-mouse');
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    document.body.classList.remove('using-mouse');
                }
            });

            document.addEventListener('focus', (e) => {
                if (!document.body.classList.contains('using-mouse')) {
                    e.target.classList.add('focus-visible');
                }
            }, true);

            document.addEventListener('blur', (e) => {
                e.target.classList.remove('focus-visible');
            }, true);
        }

        // Trap focus in modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab' && document.querySelector('.accessible-modal:not([aria-hidden="true"])')) {
                this.trapFocus(e);
            }
        });
    }

    /**
     * Trap focus within modal
     */
    trapFocus(e) {
        const modal = document.querySelector('.accessible-modal:not([aria-hidden="true"])');
        if (!modal) return;

        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (e.shiftKey && document.activeElement === firstElement) {
            lastElement.focus();
            e.preventDefault();
        } else if (!e.shiftKey && document.activeElement === lastElement) {
            firstElement.focus();
            e.preventDefault();
        }
    }

    /**
     * Enhance form controls with better accessibility
     */
    enhanceFormControls() {
        // Add aria-describedby to form inputs with error messages
        document.querySelectorAll('.input-error').forEach((errorElement) => {
            const inputId = errorElement.getAttribute('data-input-id');
            if (inputId) {
                const input = document.getElementById(inputId);
                if (input) {
                    const errorId = `error-${inputId}`;
                    errorElement.id = errorId;
                    input.setAttribute('aria-describedby', errorId);
                    input.setAttribute('aria-invalid', 'true');
                }
            }
        });

        // Add required attribute indicators
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach((input) => {
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (label) {
                const requiredText = document.createElement('span');
                requiredText.className = 'text-rose-400 ml-1';
                requiredText.textContent = '*';
                requiredText.setAttribute('aria-hidden', 'true');
                label.appendChild(requiredText);

                const srRequired = document.createElement('span');
                srRequired.className = 'sr-only';
                srRequired.textContent = ' (required)';
                label.appendChild(srRequired);
            }
        });

        // Enhance select elements
        document.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', () => {
                select.setAttribute('aria-describedby', `selected-value-${select.id}`);
            });
        });
    }

    /**
     * Enhance data tables for screen readers
     */
    enhanceDataTables() {
        document.querySelectorAll('table').forEach((table) => {
            if (!table.getAttribute('role')) {
                table.setAttribute('role', 'table');
            }

            // Add scope to headers
            table.querySelectorAll('th').forEach((th) => {
                if (!th.getAttribute('scope')) {
                    th.setAttribute('scope', 'col');
                }
            });

            // Add row headers if first cell in row is a header
            table.querySelectorAll('tr').forEach((row) => {
                const firstCell = row.querySelector('th, td');
                if (firstCell && firstCell.tagName === 'TH') {
                    firstCell.setAttribute('scope', 'row');
                }
            });

            // Make rows focusable
            table.querySelectorAll('tr').forEach((row, index) => {
                if (!row.getAttribute('tabindex')) {
                    row.setAttribute('tabindex', '0');
                    row.setAttribute('role', 'row');
                    
                    row.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            const link = row.querySelector('a[href]');
                            if (link) {
                                link.click();
                            }
                        }
                    });
                }
            });
        });
    }

    /**
     * Enhance modals for accessibility
     */
    enhanceModals() {
        // Watch for modal openings
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const modal = mutation.target;
                    if (modal.classList.contains('accessible-modal')) {
                        const isHidden = modal.getAttribute('aria-hidden') === 'true';
                        if (!isHidden) {
                            this.openModal(modal);
                        } else {
                            this.closeModal(modal);
                        }
                    }
                }
            });
        });

        document.querySelectorAll('.accessible-modal').forEach((modal) => {
            observer.observe(modal, { attributes: true });
        });
    }

    openModal(modal) {
        modal.setAttribute('aria-hidden', 'false');
        modal.setAttribute('aria-modal', 'true');
        
        const title = modal.querySelector('.accessible-modal-title');
        if (title) {
            modal.setAttribute('aria-labelledby', title.id || this.generateId('modal-title'));
        }

        // Save current focus
        this.previousFocus = document.activeElement;
        
        // Focus first focusable element
        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length > 0) {
            setTimeout(() => focusableElements[0].focus(), 100);
        }

        // Announce modal opening to screen readers
        this.announce('Dialog opened. Use Tab to navigate, Escape to close.');
    }

    closeModal(modal) {
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        
        // Restore focus
        if (this.previousFocus) {
            setTimeout(() => this.previousFocus.focus(), 100);
        }

        // Announce modal closing
        this.announce('Dialog closed.');
    }

    /**
     * Enhance notifications for screen readers
     */
    enhanceNotifications() {
        // Watch for new notifications
        const notificationObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('accessible-notification')) {
                        this.announceNotification(node);
                    }
                });
            });
        });

        notificationObserver.observe(document.body, { childList: true, subtree: true });
    }

    announceNotification(notification) {
        const message = notification.textContent.trim();
        const role = notification.getAttribute('role') || 'status';
        const live = notification.getAttribute('aria-live') || 'polite';
        
        // Create a live region for announcements
        let liveRegion = document.getElementById('a11y-live-region');
        if (!liveRegion) {
            liveRegion = document.createElement('div');
            liveRegion.id = 'a11y-live-region';
            liveRegion.className = 'sr-only';
            liveRegion.setAttribute('aria-live', 'assertive');
            liveRegion.setAttribute('aria-atomic', 'true');
            document.body.appendChild(liveRegion);
        }

        // Announce the notification
        setTimeout(() => {
            liveRegion.textContent = message;
            setTimeout(() => {
                liveRegion.textContent = '';
            }, 1000);
        }, 100);
    }

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Skip if user is typing in an input
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
                return;
            }

            // ? - Show keyboard shortcuts help
            if (e.key === '?' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                this.showKeyboardHelp();
            }

            // / - Focus search
            if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                const searchInput = document.querySelector('input[type="search"], input[name="search"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }

            // Escape - Close modals, dropdowns
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.accessible-modal:not([aria-hidden="true"])');
                if (openModal) {
                    const closeButton = openModal.querySelector('.accessible-modal-close, [data-dismiss="modal"]');
                    if (closeButton) {
                        closeButton.click();
                    }
                }
            }
        });
    }

    showKeyboardHelp() {
        const helpContent = `
            <div class="accessible-modal" aria-hidden="false">
                <div class="accessible-modal-content">
                    <div class="accessible-modal-header">
                        <h2 class="accessible-modal-title" id="keyboard-help-title">Keyboard Shortcuts</h2>
                        <button class="accessible-modal-close" aria-label="Close help dialog">×</button>
                    </div>
                    <div class="accessible-modal-body">
                        <ul class="space-y-2">
                            <li><kbd>?</kbd> - Show this help dialog</li>
                            <li><kbd>/</kbd> - Focus search input</li>
                            <li><kbd>Escape</kbd> - Close modal or dropdown</li>
                            <li><kbd>Tab</kbd> - Navigate forward</li>
                            <li><kbd>Shift + Tab</kbd> - Navigate backward</li>
                            <li><kbd>Enter</kbd> - Activate button or link</li>
                            <li><kbd>Space</kbd> - Activate button or toggle</li>
                        </ul>
                    </div>
                </div>
            </div>
        `;

        const container = document.createElement('div');
        container.innerHTML = helpContent;
        document.body.appendChild(container.firstElementChild);

        // Add event listener to close button
        const closeButton = container.querySelector('.accessible-modal-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                container.firstElementChild.setAttribute('aria-hidden', 'true');
                setTimeout(() => container.firstElementChild.remove(), 300);
            });
        }
    }

    /**
     * Enhance charts for accessibility
     */
    enhanceCharts() {
        document.querySelectorAll('.chart-container').forEach((container) => {
            const chart = container.querySelector('canvas');
            if (chart) {
                // Add accessible data table
                this.createChartDataTable(container, chart);
                
                // Make chart focusable
                chart.setAttribute('tabindex', '0');
                chart.setAttribute('role', 'img');
                
                const title = container.querySelector('h3, h4')?.textContent || 'Chart';
                chart.setAttribute('aria-label', `${title}. Use arrow keys to navigate data points.`);
                
                // Add keyboard navigation for chart
                chart.addEventListener('keydown', (e) => {
                    this.handleChartNavigation(e, chart);
                });
            }
        });
    }

    createChartDataTable(container, chart) {
        const dataTable = document.createElement('table');
        dataTable.className = 'chart-data-table sr-only';
        dataTable.setAttribute('aria-label', 'Data table for chart');
        
        // This would be populated with actual chart data
        // For now, we'll add a placeholder
        dataTable.innerHTML = `
            <caption>Chart Data</caption>
            <tbody>
                <tr><td>Chart data available in visual form</td></tr>
            </tbody>
        `;
        
        container.appendChild(dataTable);
    }

    handleChartNavigation(e, chart) {
        // Implement arrow key navigation for chart data points
        // This would require integration with Chart.js
        console.log('Chart navigation:', e.key);
    }

    /**
     * Setup live regions for dynamic content
     */
    setupLiveRegions() {
        // Create live regions for different types of updates
        const regions = {
            'status': { live: 'polite', atomic: true },
            'alert': { live: 'assertive', atomic: true },
            'log': { live: 'polite', atomic: false }
        };

        Object.entries(regions).forEach(([id, config]) => {
            let region = document.getElementById(`a11y-live-${id}`);
            if (!region) {
                region = document.createElement('div');
                region.id = `a11y-live-${id}`;
                region.className = 'sr-only';
                region.setAttribute('aria-live', config.live);
                region.setAttribute('aria-atomic', config.atomic.toString());
                document.body.appendChild(region);
            }
        });
    }

    /**
     * Announce messages to screen readers
     */
    announce(message, type = 'status') {
        const region = document.getElementById(`a11y-live-${type}`);
        if (region) {
            region.textContent = message;
            setTimeout(() => {
                region.textContent = '';
            }, 1000);
        }
    }

    /**
     * Generate unique ID
     */
    generateId(prefix) {
        return `${prefix}-${Math.random().toString(36).substr(2, 9)}`;
    }
}

// Initialize accessibility enhancer when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.a11y = new AccessibilityEnhancer();
    });
} else {
    window.a11y = new AccessibilityEnhancer();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AccessibilityEnhancer;
}