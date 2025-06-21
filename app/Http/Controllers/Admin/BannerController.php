<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        // Admin thấy tất cả banner
        $banners = Banner::with(['desktopImage', 'mobileImage'])
            ->orderBy('order')
            ->paginate(10);

        return view('admin.banners.index', compact('banners'));
    }

    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }


    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'link_url' => 'nullable|url',
            'status' => 'required|in:active,inactive',
            'image_desktop' => 'nullable|image|max:2048',
            'image_mobile' => 'nullable|image|max:2048',
        ]);

        $data['created_by'] = auth()->id();
        $banner = Banner::create($data);

        if ($request->hasFile('image_desktop')) {
            $banner->desktopImage()?->delete();
            $this->saveUploadedFile($banner, $request->file('image_desktop'), 'banner_desktop');
        }

        if ($request->hasFile('image_mobile')) {
            $banner->mobileImage()?->delete();
            $this->saveUploadedFile($banner, $request->file('image_mobile'), 'banner_mobile');
        }

        return redirect()->route('admin.banners.index')->with('success', 'Tạo banner thành công');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title' => 'required',
            'link_url' => 'nullable|url',
            'status' => 'required|in:active,inactive',
            'image_desktop' => 'nullable|image|max:2048',
            'image_mobile' => 'nullable|image|max:2048',
        ]);

        $data['updated_by'] = auth()->id();
        $banner->update($data);

        if ($request->hasFile('image_desktop')) {
            $banner->desktopImage()?->delete();
            $this->saveUploadedFile($banner, $request->file('image_desktop'), 'banner_desktop');
        }

        if ($request->hasFile('image_mobile')) {
            $banner->mobileImage()?->delete();
            $this->saveUploadedFile($banner, $request->file('image_mobile'), 'banner_mobile');
        }

        return redirect()->route('admin.banners.index')->with('success', 'Cập nhật banner thành công');
    }

    public function destroy(Banner $banner)
    {
        foreach ($banner->images as $image) {
            $image->delete();
        }
        $banner->delete();
        return redirect()->route('admin.banners.index')->with('success', 'Đã xóa banner');
    }

    protected function saveUploadedFile($model, $file, $type)
    {
        $path = $file->store('banners', 'public');

        $model->images()->create([
            'path' => $path,
            'filename' => $file->hashName(),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => 'public',
            'type' => $type,
            'user_id' => auth()->id(),
        ]);
    }
}
