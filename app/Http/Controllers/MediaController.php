<?php

namespace App\Http\Controllers;

use App\Models\HomepageVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function homepageVideo(Request $request, HomepageVideo $video)
    {
        if (! Storage::disk('public')->exists($video->path)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($video->path);

        $mime = $video->mime ?: 'video/mp4';
        $size = @filesize($fullPath);
        $range = (string) $request->header('Range', '');

        if ($range !== '' && is_int($size) && $size > 0) {
            if (! preg_match('/bytes=(\d+)-(\d*)/i', $range, $matches)) {
                return response('', 416, [
                    'Content-Type' => $mime,
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            $start = (int) $matches[1];
            $end = $matches[2] !== '' ? (int) $matches[2] : ($size - 1);

            if ($start > $end || $start >= $size) {
                return response('', 416, [
                    'Content-Type' => $mime,
                    'Accept-Ranges' => 'bytes',
                    'Content-Range' => 'bytes */'.$size,
                ]);
            }

            $end = min($end, $size - 1);
            $length = ($end - $start) + 1;

            $response = new StreamedResponse(function () use ($fullPath, $start, $length) {
                $chunkSize = 1024 * 1024;
                $handle = fopen($fullPath, 'rb');
                if ($handle === false) {
                    return;
                }

                fseek($handle, $start);
                $bytesSent = 0;

                while (! feof($handle) && $bytesSent < $length) {
                    $remaining = $length - $bytesSent;
                    $read = fread($handle, (int) min($chunkSize, $remaining));
                    if ($read === false) {
                        break;
                    }
                    $bytesSent += strlen($read);
                    echo $read;
                    flush();
                }

                fclose($handle);
            }, 206);

            $response->headers->set('Content-Type', $mime);
            $response->headers->set('Cache-Control', 'public, max-age=3600');
            $response->headers->set('Accept-Ranges', 'bytes');
            $response->headers->set('Content-Length', (string) $length);
            $response->headers->set('Content-Range', 'bytes '.$start.'-'.$end.'/'.$size);

            return $response;
        }

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=3600',
            'Accept-Ranges' => 'bytes',
        ]);
    }
}
