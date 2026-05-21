<div id="pdfModale" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="pdfclose">&times;</span>
            <iframe id="openpdf" src="{{ $sample_reading_details->pdf }}" width="100%" height="100%"
                frameborder="0"></iframe>
        </div>
    </div>
</div>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        /* Prevent scrolling on the background */
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 20px;
    }

    .modal-content {
        background-color: #fefefe;
        padding: 10px;
        border: none;
        width: 100%;
        max-width: 1000px;
        height: 80vh;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .pdfclose {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        position: absolute;
        right: -15px;
        top: -15px;
        cursor: pointer;
    }

    .pdfclose:hover,
    .pdfclose:focus {
        color: black;
    }

    iframe#openpdf {
        flex-grow: 1;
        /* Ensure iframe takes up all the available height */
        width: 100%;
        border: none;
    }
</style>
