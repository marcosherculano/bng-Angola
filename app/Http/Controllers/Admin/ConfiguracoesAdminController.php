<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageVideo;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ConfiguracoesAdminController extends Controller
{
    public function edit()
    {
        $settings = SystemSetting::query()->orderBy('key')->get()->keyBy('key');

        $videos = collect();
        if (Schema::hasTable('homepage_videos')) {
            $videos = HomepageVideo::query()
                ->orderByDesc('is_active')
                ->orderByDesc('id')
                ->get();
        }

        return view('admin.configuracoes.edit', [
            'settings' => $settings,
            'videos' => $videos,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'theme_mode' => ['nullable', 'in:light,dark'],
            'support_email' => ['nullable', 'string', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'homepage_video_url' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('admin.configuracoes.edit')->with('success', 'Configurações guardadas.');
    }

    public function uploadVideo(Request $request)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'video' => ['required', 'file', 'max:204800', 'mimetypes:video/mp4'],
        ]);

        $file = $data['video'];
        $path = $file->store('homepage-videos', 'public');

        HomepageVideo::query()->create([
            'title' => $data['title'] ?? null,
            'path' => $path,
            'mime' => (string) $file->getMimeType(),
            'size_bytes' => (int) $file->getSize(),
            'is_active' => false,
        ]);

        return redirect()->route('admin.configuracoes.edit')->with('success', 'Vídeo carregado com sucesso.');
    }

    public function activateVideo(Request $request, HomepageVideo $video)
    {
        DB::transaction(function () use ($video) {
            HomepageVideo::query()->where('is_active', true)->update(['is_active' => false]);
            $video->is_active = true;
            $video->save();
        });

        return redirect()->route('admin.configuracoes.edit')->with('success', 'Vídeo activado com sucesso.');
    }

    public function deleteVideo(Request $request, HomepageVideo $video)
    {
        $path = $video->path;
        $wasActive = (bool) $video->is_active;

        $video->delete();

        if (! empty($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        if ($wasActive) {
            $next = HomepageVideo::query()->orderByDesc('id')->first();
            if ($next) {
                $next->is_active = true;
                $next->save();
            }
        }

        return redirect()->route('admin.configuracoes.edit')->with('success', 'Vídeo eliminado com sucesso.');
    }
}
