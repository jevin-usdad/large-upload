<?php

namespace App\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;

class LargeUploadController extends Controller
{
    private function s3Client()
    {
        return new S3Client([
            'region' => config('filesystems.disks.s3.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
    }

    public function initiate(Request $request)
    {
        $key = 'uploads/' . uniqid() . '_' . $request->file_name;

        $s3 = $this->s3Client();

        $result = $s3->createMultipartUpload([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $key,
        ]);

        return response()->json([
            'uploadId' => $result['UploadId'],
            'key' => $key,
        ]);
    }

    public function getPresignedUrl(Request $request)
    {
        $s3 = $this->s3Client();

        $cmd = $s3->getCommand('UploadPart', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $request->key,
            'UploadId' => $request->uploadId,
            'PartNumber' => $request->partNumber,
        ]);

        $request = $s3->createPresignedRequest($cmd, '+15 minutes');

        return response()->json([
            'url' => (string) $request->getUri(),
        ]);
    }

    public function complete(Request $request)
    {
        $s3 = $this->s3Client();

        $result = $s3->completeMultipartUpload([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $request->key,
            'UploadId' => $request->uploadId,
            'MultipartUpload' => [
                'Parts' => $request->parts,
            ],
        ]);

        return response()->json([
            'message' => 'Upload complete',
            'location' => $result['Location'],
        ]);
    }
}
