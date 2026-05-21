 
 
 
 <!-- QR Code Modal -->
<div id="qrModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <img id="qrModalImg" src=""
            style="width: 40%; height: auto; border-radius: 8px; object-fit: cover;">
        <div id="reader" style="width: 300px; margin-top: 20px;"></div>

        <div id="scanResult"
            style="margin-top: 20px; font-size: 16px; color: blue; text-align: center;"></div>

        
        <button type="button" class="btn btn-primary" id="downloadPdfBtn" style="margin-top: 20px;">
            Download QR Code
        </button>
    </div>
</div>
<style>
    
#qrModal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 50%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0, 0, 0, 0.6); 
    backdrop-filter: blur(5px); 
}

.modal-content {
    background-color: #f9f9f9;
    margin: 10% auto; 
    padding: 20px;
    border-radius: 10px;
    width: 40%; 
    box-shadow: none; 
    text-align: center; 
    animation: fadeIn 0.3s ease-in-out; 
}


.close {
    color: #333;
    font-size: 24px;
    font-weight: bold;
    position: absolute;
    right: 20px;
    top: 15px;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close:hover {
    color: #ff0000;
}


#downloadPdfBtn {
    padding: 10px 20px;
    font-size: 14px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

#downloadPdfBtn:hover {
    background-color: #0056b3;
}


@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

</style>