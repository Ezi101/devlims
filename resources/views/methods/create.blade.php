<div class="modal-dialog" role="document">
    <div class="modal-content">
        <form action="{{ route('methods.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title" id="addMethodModalLabel">@lang('lang_v1.add_method')</h3>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label for="method_name">@lang('messages.name')</label>
                    <input type="text" class="form-control" id="method_name" name="method_name"
                        placeholder="@lang('method.method_name_holder')">
                </div>
                <div class="form-group">
                    <label for="sample_id">@lang('product.product')</label>
                    <select class="form-control select2" id="sample_id" name="sample_id" style="width: 100%;">
                        <option value="">@lang('messages.please_select')</option>
                        @foreach ($samples as $sample)
                            <option value="{{ $sample->id }}">{{ $sample->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="method_description">@lang('method.description')</label>
                    <textarea class="form-control" id="method_description" name="method_description" rows="4"
                        placeholder="@lang('method.method_description_holder')"></textarea>
                </div>
                <div class="form-group">
                    <label class="custom-file-upload" for="method_files">@lang('method.upload_files')</label>
                    <input type="file" class="form-control-file" id="method_files" name="method_files[]" multiple>
                    <span>@lang('method.no_file_selected')</span>
                </div>

                <div class="form-group">

                    <button type="button" class="btn btn-secondary" id="cameraButton" style="margin-bottom: 10px;">
                        <i class="fa fa-camera"></i> @lang('messages.c_picture')
                    </button>
                    <input type="file" name="picture" id="picture" class="form-control" accept="image/*"
                        capture="camera" style="display: none;">
                    <canvas id="canvas"
                        style="display:none; max-width: 40%; margin-bottom: 10px; border-radius:10px;"></canvas>
                    <div id="controls" style="display:none; margin-top: 10px;">
                        <button type="button" class="btn btn-primary" id="captureButton"><i
                                class="fas fa-camera"></i></button>
                        <button type="button" class="btn btn-default" id="closeCameraButton"><i
                                class="fas fa-times"></i></button>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </form>
    </div>
    <style>
        .custom-file-upload {
            display: inline-block;
            cursor: pointer;
            padding: 4px 10px;
            /* Reduced padding for less height */
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #e0e0e0;
            /* Lighter background color */
            color: #333;
            /* Text color for better readability */
            font-size: 14px;
            /* Adjust font size if needed */
        }

        .custom-file-upload:hover {
            background-color: #c0c0c0;
            /* Darker shade for hover effect */
        }

        .custom-file-upload:active {
            background-color: #9b9898;
            /* Darker shade for hover effect */
        }

        input[type="file"] {
            display: none;
        }
    </style>
</div>
<script>
    $(document).ready(function() {

        const fileInput = $('#method_files'); // Select using jQuery selector
        const label = fileInput.next(); // Get next sibling using jQuery method

        const originalLabelText = label.text(); // Get text using jQuery method

        fileInput.on('change', function(event) {
            const files = event.target.files;
            let fileNames = '';

            if (files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    fileNames += (i > 0 ? ', ' : '') + files[i]
                    .name; // Add comma and space for subsequent files
                }
                label.text(fileNames);
            } else {
                label.text(originalLabelText); // Reset label if no files selected
            }
        });

        const observer = new MutationObserver(function() {
            fileInput.val(''); // Reset value using jQuery method
            label.text(originalLabelText); // Reset text again
        });

        observer.observe(fileInput[0], { // Use raw DOM element for mutation observer
            attributes: true,
            attributeFilter: ['value']
        });



        $('#sample_id').select2({
            dropdownParent: $('#addMethodModal')
        });

        tinymce.init({
            selector: '#method_description',
            plugins: 'advlist autolink lists  charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
        });

        const cameraButton = document.getElementById('cameraButton');
        const captureButton = document.getElementById('captureButton');
        const closeCameraButton = document.getElementById('closeCameraButton');
        const canvas = document.getElementById('canvas');
        const pictureInput = document.getElementById('picture');
        const controls = document.getElementById('controls');
        const modalBody = document.querySelector('.modal-body');
        let stream;
        let video;

        if (cameraButton && captureButton && closeCameraButton && canvas && pictureInput && controls &&
            modalBody) {
            cameraButton.addEventListener('click', function() {
                pictureInput.style.display = 'none';
                canvas.style.display = 'block';
                controls.style.display = 'block';

                navigator.mediaDevices.getUserMedia({
                        video: true
                    })
                    .then(strm => {
                        stream = strm;
                        video = document.createElement('video');
                        video.style.display = 'none';
                        document.body.appendChild(video);
                        video.srcObject = stream;
                        video.play();
                        video.addEventListener('loadedmetadata', function() {
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            const context = canvas.getContext('2d');

                            function draw() {
                                if (!stream) return;
                                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                                requestAnimationFrame(draw);
                            }
                            draw();
                        });
                    })
                    .catch(err => {
                        console.error("Error accessing the camera: " + err);
                    });
            });

            captureButton.addEventListener('click', function() {
                if (!stream) return;
                stream.getTracks().forEach(track => track.stop());
                stream = null;
                canvas.style.display = 'none';
                controls.style.display = 'none';
                pictureInput.style.display = 'none';

                canvas.toBlob(blob => {
                    const file = new File([blob], 'captured-image.png', {
                        type: 'image/png'
                    });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    pictureInput.files = dataTransfer.files;

                    const existingPreview = document.getElementById('img-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }

                    const imgPreview = document.createElement('img');
                    imgPreview.id = 'img-preview';
                    imgPreview.src = URL.createObjectURL(file);
                    imgPreview.style.maxWidth = '40%';
                    imgPreview.style.marginTop = '10px';
                    imgPreview.style.borderRadius = '20px';
                    modalBody.appendChild(imgPreview);

                    if (video) {
                        video.remove();
                    }
                });
            });

            closeCameraButton.addEventListener('click', function() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                canvas.style.display = 'none';
                controls.style.display = 'none';
                pictureInput.style.display = 'none';

                if (video) {
                    video.remove();
                }
            });
        } else {
            console.error('One or more required elements are missing.');
        }
    });
</script>
