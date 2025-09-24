<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Upload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $maxSizeMB = config('app.upload_max_size', 200);
        $maxSizeKB = $maxSizeMB * 1024;

        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'mimes:mp4',
                "max:$maxSizeKB",
                function ($attribute, $value, $fail) {
                    if ($value->getMimeType() !== 'video/mp4') {
                        $fail('Only MP4 video files are allowed.');
                    }
                },
            ],
        ], [
            'file.max' => "File size must not exceed {$maxSizeMB}MB.",
            'file.mimes' => 'Only MP4 video files are allowed.',
            'file.required' => 'Please select a file to upload.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Upload validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');

        // Generate secure hashed filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $hashedName = hash('sha256', $originalName . microtime(true) . random_bytes(16));
        $filename = $hashedName . '.mp4';

        // Store in storage/app/public/uploads
        $path = $file->storeAs('uploads', $filename, 'public');

        if (!$path) {
            return response()->json([
                'message' => 'Failed to store file',
                'errors' => ['file' => ['File storage failed']]
            ], 500);
        }

        // Extract video duration using ffprobe
        $duration = $this->extractDuration(storage_path('app/public/' . $path));

        if ($duration === null) {
            return response()->json([
                'message' => 'Invalid video file or duration extraction failed',
                'errors' => ['file' => ['Could not process video file']]
            ], 422);
        }

        $upload = Upload::create([
            'user_id' => auth()->id(),
            'filename' => $file->getClientOriginalName(),
            'stored_filename' => $filename,
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'duration_sec' => $duration,
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'upload' => [
                'id' => $upload->id,
                'filename' => $upload->filename,
                'duration_sec' => $upload->duration_sec,
                'size_bytes' => $upload->size_bytes,
                'created_at' => $upload->created_at,
                'url' => asset('storage/' . $upload->path)
            ]
        ], 201);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Hosts can see all uploads, guests can only see their own
        if ($user->role === 'host') {
            $uploads = Upload::with('user')->orderBy('created_at', 'desc')->paginate(20);
        } else {
            $uploads = Upload::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return response()->json([
            'uploads' => $uploads
        ]);
    }

    private function extractDuration($filePath)
    {
        try {
            // First check if ffprobe is available
            if (!function_exists('shell_exec')) {
                \Log::warning('shell_exec not available for duration extraction');
                return null;
            }

            // Use ffprobe to extract duration
            $command = sprintf(
                'ffprobe -v quiet -show_entries format=duration -of csv=p=0 %s 2>&1',
                escapeshellarg($filePath)
            );

            $output = shell_exec($command);

            if ($output === null) {
                \Log::error('ffprobe command failed', ['command' => $command]);
                return null;
            }

            $duration = trim($output);

            // Validate the output is a number
            if (!is_numeric($duration) || floatval($duration) <= 0) {
                \Log::error('Invalid duration extracted', ['output' => $output, 'file' => $filePath]);
                return null;
            }

            return (int)round(floatval($duration));

        } catch (\Exception $e) {
            \Log::error('Duration extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
