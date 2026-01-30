<!DOCTYPE html>
<html>
<head>
    <title>S3 Multipart Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/s3-upload.css') }}">
</head>
<body>

<div class="card">
    <h2>Upload Large File to S3</h2>

   <input type="file" id="fileInput" accept=".jpg,.jpeg,.png,.pdf,.mp4">
    <button id="uploadBtn">Upload</button>

    <div class="progress">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <div class="status" id="status"></div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('assets/js/s3-upload.js') }}"></script>

</body>
</html>
