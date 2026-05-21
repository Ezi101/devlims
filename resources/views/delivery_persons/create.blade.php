<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\App\Http\Controllers\DeliveryPersonController::class, 'store']),
            'method' => 'post',
            'id' => 'delivery_person_add_form',
            'enctype' => 'multipart/form-data',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('method.add_dp')</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __('messages.name') . ':*') !!}
                {!! Form::text('name', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('method.dp_name_holder'),
                    'style' => 'margin-bottom: 15px;',
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('cnic', __('messages.cnic') . ':*') !!}
                {!! Form::text('cnic', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('method.dp_cnic_holder'),
                    'style' => 'margin-bottom: 15px;',
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('phone', __('messages.phone') . ':*') !!}
                {!! Form::text('phone', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('method.dp_phone_holder'),
                    'style' => 'margin-bottom: 15px;',
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('picture', __('messages.picture') . ':*') !!}
                <br>
                <button type="button" class="btn btn-secondary" id="cameraButton" style="margin-bottom: 10px;">
                    <i class="fa fa-camera"></i> @lang('messages.c_picture')
                </button>
                <input type="file" name="picture" id="picture" class="form-control" accept="image/*"
                    capture="camera" style="display: none;">
                <canvas id="canvas"
                    style="display:none; max-width: 40%; margin-bottom: 10px; border-radius:20px;"></canvas>
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

        {!! Form::close() !!}
    </div>
</div>
<script>
    $(document).ready(function() {
        const cameraButton = document.getElementById('cameraButton');
        const captureButton = document.getElementById('captureButton');
        const closeCameraButton = document.getElementById('closeCameraButton');
        const canvas = document.getElementById('canvas');
        const pictureInput = document.getElementById('picture');
        const controls = document.getElementById('controls');
        const modalBody = document.querySelector('.modal-body');
        let stream;
        let video;

        // Ensure all elements exist before adding event listeners
        if (cameraButton && captureButton && closeCameraButton && canvas && pictureInput && controls &&
            modalBody) {
            cameraButton.addEventListener('click', function() {
                // Hide existing input and show canvas and controls
                pictureInput.style.display = 'none';
                canvas.style.display = 'block';
                controls.style.display = 'block';

                // Request access to the camera
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
                // Stop the video stream
                stream.getTracks().forEach(track => track.stop());
                stream = null;
                canvas.style.display = 'none';
                controls.style.display = 'none';
                pictureInput.style.display = 'none';

                // Convert the canvas to a Blob and create a File object
                canvas.toBlob(blob => {
                    const file = new File([blob], 'captured-image.png', {
                        type: 'image/png'
                    });

                    // Store the file in the form data
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    pictureInput.files = dataTransfer.files;

                    // Remove existing preview if any
                    const existingPreview = document.getElementById('img-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }

                    // Create a new image element for the preview
                    const imgPreview = document.createElement('img');
                    imgPreview.id = 'img-preview';
                    imgPreview.src = URL.createObjectURL(file);
                    imgPreview.style.maxWidth = '40%';
                    imgPreview.style.marginTop = '10px';
                    imgPreview.style.borderRadius = '20px';
                    modalBody.appendChild(imgPreview);

                    // Remove video element from DOM
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

                // Remove video element from DOM
                if (video) {
                    video.remove();
                }
            });
        } else {
            console.error('One or more required elements are missing.');
        }
    });
</script>
