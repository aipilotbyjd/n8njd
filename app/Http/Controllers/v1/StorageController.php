<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Storage\StorageService;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    protected $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function upload(Request $request)
    {
        $path = $request->user()->org_id.'/'.$request->user()->id;
        $file = $request->file('file');

        return $this->storageService->upload($path, $file);
    }

    public function initMultipartUpload(Request $request)
    {
        $path = $request->user()->org_id.'/'.$request->user()->id;

        return $this->storageService->initMultipartUpload($path);
    }

    public function uploadPart(Request $request, $id)
    {
        $path = $request->user()->org_id.'/'.$request->user()->id;
        $file = $request->file('file');

        return $this->storageService->uploadPart($path, $id, $file);
    }

    public function completeMultipartUpload(Request $request, $id)
    {
        $path = $request->user()->org_id.'/'.$request->user()->id;

        return $this->storageService->completeMultipartUpload($path, $request->input('parts'));
    }

    public function getFiles(Request $request)
    {
        $path = $request->user()->org_id.'/'.$request->user()->id;

        return $this->storageService->getFiles($path);
    }

    public function getFile(Request $request, $id)
    {
        $path = $request->user()->org_id.'/'.$request->user()->id.'/'.$id;

        return $this->storageService->getFile($path);
    }

    public function deleteFile(Request $request, $id)
    {
        $path = $request->user()->org_id.'/'.$request->user()->id.'/'.$id;

        return $this->storageService->deleteFile($path);
    }

    public function downloadFile(Request $request, $id)
    {
        $path = $request->user()->org_id.'/'.$request->user()->id.'/'.$id;

        return $this->storageService->downloadFile($path);
    }

    public function shareFile(Request $request, $id)
    {
        $path = $request->user()->org_id.'/'.$request->user()->id.'/'.$id;

        return $this->storageService->shareFile($path, $request->input('user_id'), $request->user()->id, $request->input('permissions'));
    }
}
