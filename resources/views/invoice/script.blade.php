<script src="{{ asset('js/jquery.min.js') }} "></script>
<script>
  // Align the side note with the "Due Amount" row
  function alignSideNote() {
    var totalsTable = document.querySelector('.total-table');
    var targetRow   = document.getElementById('due-amount-row');   // <-- changed
    var sideNote    = document.getElementById('invoice-side-note');

    if (totalsTable && targetRow && sideNote) {
      var top = targetRow.getBoundingClientRect().top - totalsTable.getBoundingClientRect().top;
      sideNote.style.top = top + 'px';
    }
  }

  document.addEventListener('DOMContentLoaded', alignSideNote);
  window.addEventListener('load', alignSideNote);
</script>


<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    function closeScript() {
        setTimeout(function () {
            window.open(window.location, '_self').close();
        }, 1000);
    }

    $(window).on('load', function() {
        var element = document.getElementById('boxes');
        var opt = {
            filename: '{{ Utility::customerInvoiceNumberFormat($invoice->invoice_id) }}',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A4'
            }
        };
        html2pdf().set(opt).from(element).save().then(closeScript);
    });
</script>
