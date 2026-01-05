// Global AJAX handler for report forms
document.addEventListener('DOMContentLoaded', function() {
    // Handle all report action buttons (apply and reset)
    document.querySelectorAll('.report-action-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const action = this.dataset.action;
            const formId = this.dataset.form;
            const form = document.getElementById(formId);
            
            if (!form) {
                console.error(`Form with ID ${formId} not found`);
                return;
            }
            
            // Handle reset action
            if (action === 'reset') {
                // Reset form fields
                form.reset();
                
                // Reset any select2 dropdowns if they exist
                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    $(form).find('select.select2, select.form-control.select').each(function() {
                        $(this).val('').trigger('change');
                    });
                }
                
                // Get the reset URL
                const resetUrl = this.dataset.resetUrl;
                if (resetUrl) {
                    fetchReportData(resetUrl);
                }
            } 
            // Handle apply action
            else if (action === 'apply') {
                // Get form data and convert to URL parameters
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                const url = `${form.getAttribute('action')}?${params.toString()}`;
                
                fetchReportData(url);
            }
        });
    });
    
    // Function to fetch report data via AJAX
    function fetchReportData(url) {
        // Show loading indicator
        const reportContainer = document.getElementById('invoice-container') || 
                               document.querySelector('.report-container');
        
        if (reportContainer) {
            reportContainer.innerHTML = '<div class="text-center p-5"><i class="ti ti-loader animate-spin text-primary" style="font-size: 2rem;"></i><p class="mt-3">Loading report data...</p></div>';
        }
        
        // Fetch data
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Extract only the report container part from the response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newReportContainer = doc.getElementById('invoice-container') || 
                                      doc.querySelector('.report-container');
            
            if (newReportContainer && reportContainer) {
                reportContainer.innerHTML = newReportContainer.innerHTML;
                
                // Reinitialize any JS components
                initializeComponents();
                
                // Update the URL without refreshing the page
                window.history.pushState({}, '', url);
            } else {
                // If we can't find the container, just update the whole page content
                document.querySelector('.content-wrapper').innerHTML = doc.querySelector('.content-wrapper').innerHTML;
                initializeComponents();
                window.history.pushState({}, '', url);
            }
        })
        .catch(error => {
            console.error('Error fetching report data:', error);
            if (reportContainer) {
                reportContainer.innerHTML = `<div class="alert alert-danger">Error loading report data. Please try again.</div>`;
            }
        });
    }
    
    // Function to reinitialize components after AJAX load
    function initializeComponents() {
        // Reinitialize DataTables if present
        if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
            $('#report-dataTable').DataTable({
                dom: 'lBfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        title: $('#filename').val()
                    },
                    {
                        extend: 'pdf',
                        title: $('#filename').val()
                    }, {
                        extend: 'csv',
                        title: $('#filename').val()
                    }
                ]
            });
        }
        
        // Reinitialize ApexCharts if present
        if (typeof ApexCharts !== 'undefined' && document.querySelector("#chart-sales")) {
            // The chart initialization code would need to be called again
            // This would depend on your specific chart configuration
            if (window.arChart) {
                window.arChart.destroy();
            }
            
            // You'll need to make your chart initialization available globally
            // or trigger a custom event that your chart initialization code listens for
        }
        
        // Reinitialize tooltips
        if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }
});