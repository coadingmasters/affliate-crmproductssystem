{{-- Print rules for the invoice document, shared by both copies. --}}
<style>
    @media print {
        /* A4 with a comfortable margin; the browser's own header and footer
           are a per-user print setting, so the sheet supplies its own. */
        @page {
            size: A4;
            margin: 12mm;
        }

        /* Brand bands are the design, not decoration, so they must print. */
        html, body {
            background: #fff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Everything that is app chrome rather than the document. The
           layout marks its own sidebar, top bar and footer, so nothing here
           depends on the page's structure — and never on a utility class
           like .flex, which would fight the document's own layout. */
        [data-print-hide],
        #sidebar-backdrop {
            display: none !important;
        }

        main {
            padding: 0 !important;
        }

        /* Let the sheet run edge to edge inside the page margin. */
        .invoice-sheet {
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .invoice-sheet * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* A long invoice repeats its column headings on every page. */
        .invoice-lines thead {
            display: table-header-group;
        }

        .invoice-lines tr,
        .invoice-due,
        .invoice-foot {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        /* The status chip reads on white once the band is behind it. */
        .invoice-status {
            border: 1px solid rgba(255, 255, 255, .45) !important;
        }
    }
</style>
