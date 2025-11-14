<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Models\File;
use App\Services\FileService;
use App\Services\UrlPresigner;
use Illuminate\Filesystem\FilesystemManager;
use Throwable;

class FileController extends Controller
{
    public function __construct(
        private FilesystemManager $filesystem
    ) {
    }

    public function show(File $file)
    {
        $url = (new UrlPresigner)->getPresignedUrl(
            $file->url
        );

        return redirect($url);
    }

    public function serveIeducarFiles(string $path)
    {
        $filePath = $this->filesystem->disk('local')->path($path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Arquivo não encontrado: ' . $path);
        }
        
        $mimeType = mime_content_type($filePath);
        if (!$mimeType) {
            $mimeType = 'application/octet-stream';
        }
        
        $fileContent = file_get_contents($filePath);
        
        return response($fileContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Length', filesize($filePath))
            ->header('Cache-Control', 'private, max-age=3600');
    }

    public function serveProvasFiles(string $path)
    {
        $filePath = $this->filesystem->disk('local')->path("provas/{$path}");
        
        if (!file_exists($filePath)) {
            abort(404, 'Arquivo não encontrado: ' . $path);
        }
        
        $mimeType = mime_content_type($filePath);
        if (!$mimeType) {
            $mimeType = 'application/octet-stream';
        }
        
        $fileContent = file_get_contents($filePath);
        
        return response($fileContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Length', filesize($filePath))
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function upload(FileRequest $request, FileService $fileService)
    {
        $file = $request->file('file');

        try {
            $url = $fileService->upload($file);
        } catch (Throwable $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }

        return [
            'file_url' => $url,
            'file_size' => $file->getSize(),
            'file_extension' => $file->getClientOriginalExtension(),
            'file_original_name' => $file->getClientOriginalName(),
        ];
    }
}
