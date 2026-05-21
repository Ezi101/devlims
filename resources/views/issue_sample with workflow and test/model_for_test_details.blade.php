<style>
    .modal-dialog {
        width: 900px;
    }

    .modal-content {
        border-radius: 5px;
        padding: 10px 20px;
    }

    /* .modal-header{
    margin-bottom: 10px;
  } */
</style>

<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;

    }

    /* .content {
      margin: 20px;
      margin-bottom: 100px;
  } */

    h4 {
        font-weight: bold;
        text-align: center;
    }

    h5 {
        text-align: center;
        margin-top: -5px;
        /* font-weight: bold; */


    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    tr.page-break {
        page-break-before: always;
    }

    th,
    td {
        /* border: 1px solid; */
        padding: 4px;
    }

    .table>tbody>tr>td,
    .table>tbody>tr>th,
    .table>tfoot>tr>td,
    .table>tfoot>tr>th,
    .table>thead>tr>td,
    .table>thead>tr>th {
        padding: 4px;
        line-height: 1.42857143;
        vertical-align: top;
        border-top: 1px solid #ddd;
    }

    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000;
        opacity: 0.13355;
        pointer-events: none;
        /* Makes sure the watermark doesn't interfere with interactions */
    }

    .watermark img {
        max-width: 600px;
        filter: grayscale(100%);
    }


    .a4-page {
        width: 100%;
        border: 1px solid #000;
    }

    .table-header {
        background: grey;
        color: white;
        height: 30px;
        padding-top: 120px;
        font-weight: 700px;
        font-size: 15px;
    }

    /*  */
</style>

<div class="modal" id="test_detail_modal" tabindex="-1" style="overflow: scroll">
    <div class="modal-dialog">
        <div class="modal-content" id="ptrModaledata">


        </div>
    </div>
</div>

<script>
    function hideDownloadButton() {
        document.getElementById('printButton').style.display = 'none';
    }

    function showDownloadButton() {
        document.getElementById('printButton').style.display = 'block';
    }



    function printModalContent() {
        var modalContent = document.getElementById('ptrModaledata').innerHTML;
        var iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0px';
        iframe.style.height = '0px';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);

        var doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`<style>
              .modal-footer { display: none; }
               body {
            font-family: Arial, sans-serif;
            font-size: 12px;
    
        }
    
      
        h4 {
            font-weight: bold;
            text-align: center;
        }
    
        h5 {
            text-align: center;
            margin-top: -5px;
            /* font-weight: bold; */
    
    
        }
               .a4-page {
            width: 100%;
            min-height: 97.9vh;
            border: 1px solid #000;
        }

        .table-header {
            background: grey;
            color: white;
            height: 30px;
            padding-top: 120px;
            font-weight: 700px;
            font-size: 15px;
        }
    
        table {
            width: 100%;
            border-collapse: collapse;
        }
    
        tr.page-break {
            page-break-before: always;
        }
    
        th,
        td {
            /* border: 1px solid; */
            padding: 4px;
        }
    
        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            padding: 4px;
            line-height: 1.42857143;
            vertical-align: top;
            border-top: 1px solid #ddd;
        }
     
   
    
              </style>`);


        doc.write(modalContent);
        doc.close();

        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        // window.print();

        document.body.removeChild(iframe);
    }
</script>
