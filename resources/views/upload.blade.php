<!-- upload.blade.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Uploader</title>

</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">

                <form action="{{ route('upload') }}" method="post" enctype="multipart/form-data" class="mb-3">
                    @csrf
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="image" name="image" required>
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Upload Image</button>
                        </div>
                    </div>
                </form>


                @if (session('image_path'))
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Uploaded File Path:</h5>
                            <p class="card-text">{{ session('image_path') }}</p>
                            <button onclick="copyToClipboard('{{ session('image_path') }}')"
                                class="btn btn-primary">Copy Path</button>
                            <a href="javascript:history.back()" class="btn btn-secondary ml-2">Back</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>


    <script>
        function copyToClipboard(text) {
            var dummyElement = document.createElement("textarea");
            document.body.appendChild(dummyElement);
            dummyElement.value = text;
            dummyElement.select();
            document.execCommand("copy");
            document.body.removeChild(dummyElement);
            alert("Copied to clipboard: " + text);
        }
    </script>
</body>

</html>
