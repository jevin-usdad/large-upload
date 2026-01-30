$(document).ready(function () {

    $('#uploadBtn').on('click', function () {
        uploadFile();
    });

    async function uploadFile() {

        const file = $('#fileInput')[0].files[0];

        if (!file) {
            alert("Please select a file.");
            return;
        }

        const maxSize = 2 * 1024 * 1024 * 1024; // 2GB

        if (file.size > maxSize) {
            alert("File is too large. Maximum allowed size is 2GB.");
            return;
        }

        const progressBar = $('#progressBar');
        const status = $('#status');
        const uploadBtn = $('#uploadBtn');
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        uploadBtn.prop('disabled', true);
        progressBar.css('width', '0%');
        status.text("Initiating upload...");

        try {

            // Step 1: Initiate multipart upload
            const initiateResponse = await $.ajax({
                url: '/upload/initiate',
                type: 'POST',
                data: {
                    file_name: file.name,
                    _token: csrfToken
                }
            });

            const uploadId = initiateResponse.uploadId;
            const key = initiateResponse.key;

            const chunkSize = 10 * 1024 * 1024; // 10MB
            const parts = [];
            let partNumber = 1;

            status.text("Uploading parts...");

            for (let start = 0; start < file.size; start += chunkSize) {

                const chunk = file.slice(start, start + chunkSize);

                // Step 2: Get presigned URL for this part
                const presignResponse = await $.ajax({
                    url: '/upload/presign',
                    type: 'POST',
                    data: {
                        key: key,
                        uploadId: uploadId,
                        partNumber: partNumber,
                        _token: csrfToken
                    }
                });

                // Step 3: Upload chunk to S3
                const etag = await uploadPart(presignResponse.url, chunk, partNumber);

                parts.push({
                    ETag: etag,
                    PartNumber: partNumber
                });

                // Update progress
                const percent = Math.min(((start + chunk.size) / file.size) * 100, 100);
                progressBar.css('width', percent + "%");

                partNumber++;
            }

            // AWS requires parts sorted
            parts.sort((a, b) => a.PartNumber - b.PartNumber);

            if (parts.length === 0) {
                throw new Error("No parts uploaded.");
            }

            status.text("Finalizing upload...");

            // Step 4: Complete multipart upload
            await $.ajax({
                url: '/upload/complete',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    key: key,
                    uploadId: uploadId,
                    parts: parts,
                    _token: csrfToken
                })
            });

            progressBar.css('width', "100%");
            status.text("Upload Complete ");

        } catch (error) {
            console.error(error);
            status.text("Upload Failed: " + (error.message || "Unknown error"));
        }

        uploadBtn.prop('disabled', false);
    }

    // Separate function for uploading part
    function uploadPart(url, chunk, partNumber) {
        return new Promise((resolve, reject) => {

            const xhr = new XMLHttpRequest();

            xhr.open("PUT", url, true);
            xhr.setRequestHeader("Content-Type", "application/octet-stream");

            xhr.onload = function () {
                if (xhr.status === 200) {
                    const etag = xhr.getResponseHeader("ETag");

                    if (!etag) {
                        reject(new Error("ETag missing for part " + partNumber));
                        return;
                    }

                    resolve(etag.replace(/"/g, ''));
                } else {
                    reject(new Error("Failed to upload part " + partNumber));
                }
            };

            xhr.onerror = function () {
                reject(new Error("Network error on part " + partNumber));
            };

            xhr.send(chunk);
        });
    }

});
