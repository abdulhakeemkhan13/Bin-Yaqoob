// AJAX Handler for the application
class AjaxHandler {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        this.setupEventListeners();
    }

    /**
     * Set up event listeners for forms, buttons, and other interactive elements
     * Handles AJAX form submissions, button clicks, and login toggle actions
     */
    setupEventListeners() {
        // Handle form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('form[data-ajax="true"]')) {
                e.preventDefault();
                this.handleFormSubmit(e.target);
            }
        });

        // Handle button clicks
        document.addEventListener('click', (e) => {
            if (e.target.matches('button[data-ajax="true"]') || e.target.closest('a[data-ajax="true"]')) {
                e.preventDefault();
                const element = e.target.matches('button') ? e.target : e.target.closest('a');

                // If this is an edit button, store the row reference
                if (element.getAttribute('data-title')?.includes('Edit') ||
                    element.getAttribute('data-original-title')?.includes('Edit')) {
                    const row = element.closest('tr');
                    if (row) {
                        // Store the row ID in sessionStorage
                        sessionStorage.setItem('editingRowId', row.dataset.id || '');
                        sessionStorage.setItem('editingTableId', this.findTableId(row));
                    }
                }

                // Handle statement report filters
                if (element.id === 'apply-filter') {
                    this.handleStatementFilter('apply');
                } else if (element.id === 'reset-filter') {
                    this.handleStatementFilter('reset');
                } else {
                    this.handleButtonClick(element);
                }
            }

            if (e.target.closest('a[data-ajax-login="true"]')) {
                e.preventDefault();
                const link = e.target.closest('a[data-ajax-login="true"]');
                this.handleLoginToggle(link);
                return;
            }   
        });
    }

    // Helper method to find the table ID from a row
    findTableId(row) {
        const table = row.closest('table');
        return table ? table.id : '';
    }

    async handleFormSubmit(form) {
        const url = form.getAttribute('action');
        const formData = new FormData(form);
        const method = form.getAttribute('method') || 'POST';

        try {
            console.log('Submitting form to:', url);
            console.log('Form data:', Object.fromEntries(formData));
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            });

            // Log the raw response before trying to parse it
            const responseText = await response.text();
            console.log('Raw response:', responseText);
            console.log('Response status:', response.status);
            console.log('Response headers:', Object.fromEntries([...response.headers]));
            
            // Now try to parse as JSON if possible
            let data;
            try {
                data = JSON.parse(responseText);
                console.log('Parsed JSON data:', data);
                this.handleResponse(data, form);
            } catch (jsonError) {
                console.error('Failed to parse response as JSON:', jsonError);
                this.showError('Server returned an invalid response. Please check the console for details.');
                
                // If it looks like an HTML page with a redirect, try to extract and follow it
                if (responseText.includes('<meta http-equiv="refresh"') || responseText.includes('window.location')) {
                    const redirectMatch = responseText.match(/url=([^"]*)|window\.location\s*=\s*["']([^"']*)/);
                    if (redirectMatch) {
                        const redirectUrl = redirectMatch[1] || redirectMatch[2];
                        console.log('Detected redirect to:', redirectUrl);
                        window.location.href = redirectUrl;
                    }
                }
            }
        } catch (error) {
            console.error('Network or fetch error:', error);
            this.showError('A network error occurred. Please check your connection and try again.');
        }
    }

    // async handleButtonClick(button) {
    //     const url = button.getAttribute('data-url');
    //     const method = button.getAttribute('data-method') || 'POST';

    //     try {
    //         const response = await fetch(url, {
    //             method: method,
    //             headers: {
    //                 'X-CSRF-TOKEN': this.csrfToken,
    //                 'X-Requested-With': 'XMLHttpRequest'
    //             }
    //         });

    //         const data = await response.json();
    //         this.handleResponse(data, button);
    //     } catch (error) {
    //         console.error('Error:', error);
    //         this.showError('An error occurred while processing your request');
    //     }
    // }

    async handleLoginToggle(link) {
        const url = link.getAttribute('data-url');
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            this.handleResponse(data, link);
        } catch (error) {
            console.error('Error:', error);
            this.showError('An error occurred while processing your request');
        }
    }

    handleResponse(data, element) {
        // console.log('Response Data:', data);

        if (data.success) {
            const modal = element.closest('.modal');
            if (modal) {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
            this.showSuccess(data.message || 'Operation completed successfully');

            // Handle card operations (for user cards)
            if (data.data && data.data.card_container && data.data.card_html) {
                if (data.data.action === 'edit' && data.data.card_id) {
                    this.updateCard(data.data.card_container, data.data.card_id, data.data.card_html);
                }
                else if (data.data.action === 'move' && data.data.card_id) {
                    // Destroy previous card before moving
                    this.destroyPreviousCard(data.data.card_id);
                    // Move the card
                    this.moveCard(data.data.card_container, data.data.card_id, data.data.card_html);
                } else {
                    this.insertCard(data.data.card_container, data.data.card_html);
                }
            }

            // Handle table row operations
            if (data.data && data.data.table_id && data.data.datarow) {
                // Check if this is an edit operation with row_id
                if (data.data.action == 'edit' && data.data.row_id) {
                    const table = document.getElementById(data.data.table_id);
                    if (table) {
                        const existingRow = table.querySelector(`tr[data-row_id="${data.data.row_id}"]`);
                        if (existingRow) {
                            // Replace the existing row with the new HTML
                            existingRow.outerHTML = data.data.datarow;
                            console.log(`Row successfully updated in table: ${data.data.table_id}`);
                        } else {
                            console.log(`No Entry to edit: ${data.data.row_id}`);
                            // Fallback to appending if row not found
                            // this.insertTableRow(data.data.table_id, data.data.datarow);
                        }
                    } else {
                        console.log(`No table found with ID: ${data.data.table_id}`);
                    }
                } else {
                    // Default to insert for new rows
                    this.insertTableRow(data.data.table_id, data.data.datarow);
                }
            }
        } else {
            this.showError(data.message || 'An error occurred');
        }
    }

    insertTableRow(tableId, rowHtml) {
        const table = document.getElementById(tableId);
        if (table) {
            const tableBody = table.querySelector('tbody');
            if (tableBody) {
                // Directly append HTML string to the table body
                tableBody.insertAdjacentHTML('beforeend', rowHtml);
                console.log(`Row successfully added to table: ${tableId}`);
                return true;
            } else {
                console.log(`No tbody found in table with ID: ${tableId}`);
            }
        } else {
            console.log(`No table found with ID: ${tableId}`);
        }
        return false;
    }

    insertCard(containerId, cardHtml) {
        // Find the container by data-card-container attribute
        const container = document.querySelector(`[data-card-container="${containerId}"]`);
        if (container) {
            container.insertAdjacentHTML('beforeend', cardHtml);
            this.initializeNewElements(container);
            console.log(`Card successfully added to container: ${containerId}`);
            return true;
        } else {
            console.log(`No card container found with data-card-container='${containerId}'`);
        }
        return false;
    }

    updateCard(containerId, cardId, cardHtml) {
        // Find the container by data-card-container attribute
        const container = document.querySelector(`[data-card-container="${containerId}"]`);
        if (container) {
            // Find the card wrapper by data-row_id
            const cardWrapper = container.querySelector(`[data-row_id="${cardId}"]`);
            if (cardWrapper) {
                cardWrapper.outerHTML = cardHtml;
                this.initializeNewElements(container);
                console.log(`Card successfully updated in container: ${containerId}, card_id: ${cardId}`);
                return true;
            } else {
                console.log(`No card found with data-row_id='${cardId}' in container '${containerId}'`);
            }
        } else {
            console.log(`No card container found with data-card-container='${containerId}'`);
        }
        return false;
    }

    destroyPreviousCard(cardId) {
        const previousCard = document.querySelector(`[data-row_id="${cardId}"]`);
        if (previousCard) {
            previousCard.remove();
            console.log(`Previous card removed with data-row_id='${cardId}'`);
        }
    }
    
    moveCard(cardContainer, cardId, cardHtml) {
        // Find the container by data-card-container attribute
        const container = document.getElementById(cardContainer);
        if (container) {
            container.insertAdjacentHTML('beforeend', cardHtml);
            this.initializeNewElements(container);
            console.log(`Card successfully moved to container: ${cardContainer}, card_id: ${cardId}`);
            return true;
        } else {
            console.log(`No card container found with data-card-container='${cardContainer}'`);
        }
        return false;
    }

    determineFormType(formId, formAction, dataType) {
        // First check if data-type attribute is set
        if (dataType) {
            return dataType;
        }

        // Check form ID
        if (formId) {
            // Extract form type from ID (e.g., 'leave-form' -> 'leave')
            const idMatch = formId.match(/^([a-zA-Z0-9_-]+)-form$/);
            if (idMatch && idMatch[1]) {
                return idMatch[1];
            }
        }

        // Check action URL
        if (formAction) {
            // Extract form type from URL path (e.g., '/leave' -> 'leave')
            const urlParts = formAction.split('/');
            const lastPart = urlParts[urlParts.length - 1].split('?')[0]; // Remove query params

            // If last part is numeric, use the part before it
            if (!isNaN(lastPart) && urlParts.length > 2) {
                return urlParts[urlParts.length - 2];
            }

            return lastPart;
        }

        // Default to generic
        return 'generic';
    }

    handleFormTypeSpecificActions(formType) {
        switch (formType.toLowerCase()) {
            case 'leave':
                // Handle leave form submission
                this.reloadTable('leave-table');
                break;
            case 'user':
            case 'users':
                // Handle user form submission
                this.reloadTable('user-table');
                break;
            case 'project':
            case 'projects':
                // Handle project form submission
                this.reloadTable('project-table');
                break;
            case 'event':
            case 'events':
                // Handle event form submission
                this.reloadTable('event-table');
                break;
            case 'complaint':
            case 'complaints':
                // Handle complaint form submission
                this.reloadTable('complaint-table');
                break;
            case 'transfer':
            case 'transfers':
                // Handle transfer form submission
                this.reloadTable('bank-transfer-table');
                break;
            case 'support':
            case 'supports':
                // Handle support form submission
                this.reloadTable('support-table');
                break;
            case 'resignation':
            case 'resignations':
                // Handle resignation form submission
                this.reloadTable('resignation-table');
                break;
            default:
                // For any other form type, reload the page after a delay
                console.log(`No specific handling for form type: ${formType}, will reload page`);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
        }
    }


    updateTable(data) {
        // Handle different types of table updates based on data
        const tableType = data.type;
        const tableId = data.table_id || `${tableType}-table`;

        console.log(`Updating table of type: ${tableType}, ID: ${tableId}`);

        // If HTML content is provided, update the table directly
        if (data.html) {
            const tableBody = document.querySelector(`#${tableId} tbody`);
            if (tableBody) {
                // If it's an append operation, add to existing content
                if (data.append) {
                    tableBody.insertAdjacentHTML('beforeend', data.html);
                }
                // If it's a prepend operation, add to the beginning
                else if (data.prepend) {
                    tableBody.insertAdjacentHTML('afterbegin', data.html);
                }
                // Otherwise replace the content
                else {
                    tableBody.innerHTML = data.html;
                }

                // Initialize any new elements (tooltips, etc.)
                this.initializeNewElements(tableBody);
                return true;
            }
        }

        // If no HTML content or table body not found, reload the table
        return this.reloadTable(tableId);
    }

    initializeNewElements(container) {
        // Initialize tooltips
        const tooltips = container.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });

        // Initialize popovers
        const popovers = container.querySelectorAll('[data-bs-toggle="popover"]');
        popovers.forEach(popover => {
            new bootstrap.Popover(popover);
        });

        // Add more initializations as needed
    }

    showSuccess(message) {
        const toast = document.getElementById('liveToast');
        if (toast) {
            const toastBody = toast.querySelector('.toast-body');
            toastBody.textContent = message;
            toast.classList.remove('bg-danger', 'bg-warning'); // optional cleanup
            toast.classList.add('bg-success', 'text-white');
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        } else {
            alert(message);
        }
    }
    showError(message) {
        // Use Bootstrap toast for notifications
        const toast = document.getElementById('liveToast');
        if (toast) {
            const toastBody = toast.querySelector('.toast-body');
            toastBody.textContent = message;
            toast.classList.add('bg-danger');
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        } else {
            alert(message);
        }
    }
}

// Initialize the AJAX handler when the document is ready
document.addEventListener('DOMContentLoaded', () => {
    new AjaxHandler();
});
























