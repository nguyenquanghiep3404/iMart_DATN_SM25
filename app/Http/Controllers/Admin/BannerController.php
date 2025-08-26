<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\UploadedFile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BannerController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // Tự động áp dụng BannerPolicy cho tất cả các phương thức CRUD
        $this->authorizeResource(Banner::class, 'banner');
    }
    public function index()
    {
        $banners = Banner::with(['desktopImage', 'mobileImage'])
            ->orderBy('order')
            ->paginate(10);

        // JSON-ready version of banners
        $bannerJson = $banners->getCollection()->map(function ($b) {
            return [
                'id' => $b->id,
                'title' => $b->title,
                'link_url' => $b->link_url,
                'position' => $b->position,
                'status' => $b->status,
                'start_date' => $b->start_date,
                'end_date' => $b->end_date,
                'desktop_image' => $b->desktopImage ? ['path' => $b->desktopImage->path] : null,
                'mobile_image' => $b->mobileImage ? ['path' => $b->mobileImage->path] : null,
            ];
        })->values();

        return view('admin.banners.index', compact('banners', 'bannerJson'));
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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'image_desktop' => 'nullable|image|max:2048',
            'image_mobile' => 'nullable|image|max:2048',
        ]);

        $data['updated_by'] = auth()->id();
        $banner->update($data);

        if ($request->hasFile('image_desktop')) {
            // ❗ Xoá vĩnh viễn ảnh cũ thay vì soft delete
            $banner->desktopImage()?->forceDelete();

            $this->saveUploadedFile($banner, $request->file('image_desktop'), 'banner_desktop');
        }

        if ($request->hasFile('image_mobile')) {
            $banner->mobileImage()?->forceDelete();

            $this->saveUploadedFile($banner, $request->file('image_mobile'), 'banner_mobile');
        }


        return redirect()->route('admin.banners.index')->with('success', 'Cập nhật banner thành công');
    }

    public function destroy(Banner $banner)
    {
        if (auth()->check()) {
            $banner->deleted_by = auth()->id();
            $banner->save();
        }

        foreach ($banner->images as $image) {
            if (auth()->check()) {
                $image->deleted_by = auth()->id();
                $image->save();
            }

            $image->delete(); // Chỉ soft delete
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Đã xoá banner');
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
    public function images()
    {
        return $this->morphMany(UploadedFile::class, 'fileable')->withTrashed();
    }

    public function trash()
    {
        $banners = Banner::onlyTrashed()->with('deletedBy')->paginate(10);
        return view('admin.banners.trash', compact('banners'));
    }


    public function restore($id)
    {
        $banner = Banner::onlyTrashed()->findOrFail($id);
        $banner->restore();

        // Khôi phục các ảnh nếu có
        foreach ($banner->images()->withTrashed()->get() as $image) {
            if ($image->trashed()) {
                $image->restore();
            }
        }

        return redirect()->route('admin.banners.index')->with('success', 'Banner đã được khôi phục.');
    }

    public function forceDelete($id)
    {
        $banner = Banner::onlyTrashed()->findOrFail($id);

        // Xoá vĩnh viễn các ảnh
        foreach ($banner->images()->onlyTrashed()->get() as $image) {
            $image->forceDelete(); // Xoá khỏi DB và xóa file vật lý
        }

        $banner->forceDelete();

        return redirect()->route('admin.banners.trash')->with('success', 'Banner đã bị xoá vĩnh viễn.');
    }
}
