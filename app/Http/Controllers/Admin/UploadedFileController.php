<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UploadedFile;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UploadedFileController extends Controller
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Hiển thị thư viện media chính.
     */
    public function index(Request $request)
    {
        $statsQuery = UploadedFile::query();
        $stats = [
            'total' => $statsQuery->count(),
            'attached' => $statsQuery->clone()->whereNotNull('attachable_id')->count(),
            'unattached' => $statsQuery->clone()->whereNull('attachable_id')->count(),
        ];

        $query = UploadedFile::latest();

        if ($request->query('filter') === 'unattached') {
            $query->whereNull('attachable_id');
        }

        if ($request->filled('start_date')) {
            try {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $query->where('created_at', '>=', $startDate);
            } catch (\Exception $e) {}
        }
        if ($request->filled('end_date')) {
            try {
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                $query->where('created_at', '<=', $endDate);
            } catch (\Exception $e) {}
        }

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('original_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('alt_text', 'like', '%' . $searchTerm . '%');
            });
        }

        $files = $query->paginate(30)->appends($request->query());
        
        $files->through(fn ($file) => $file->append(['url', 'formatted_size', 'attachable_display']));
        
        if ($request->ajax()) {
            return response()->json($files);
        }

        return view('admin.media.index', compact('files', 'stats'));
    }

    /**
     * Xử lý việc upload file mới.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg,pdf|max:10240',
            'context' => ['sometimes', 'string', Rule::in(['products', 'avatars', 'banners', 'categories', 'posts', 'general'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $uploadedFilesData = [];
        $contextFolder = $request->input('context', 'general');

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    $uploadedFile = $this->fileService->store($file, $contextFolder);
                    $uploadedFile->append('url'); 
                    $uploadedFilesData[] = $uploadedFile;
                } catch (\Exception $e) {
                    Log::error('File upload failed: ' . $e->getMessage());
                    return response()->json(['message' => 'Đã có lỗi xảy ra khi tải file: ' . $file->getClientOriginalName()], 500);
                }
            }
        }

        return response()->json(['files' => $uploadedFilesData], 201);
    }

    /**
     * Chuyển một file vào thùng rác (Soft Delete).
     */
    public function destroy(UploadedFile $uploadedFile)
    {
        try {
            if (Auth::check()) {
                $uploadedFile->deleted_by = Auth::id();
                $uploadedFile->save();
            }
            $uploadedFile->delete();
            return response()->json(['message' => 'Đã chuyển file vào thùng rác.'], 200);
        } catch (\Exception $e) {
            Log::error('Soft delete file failed: ' . $e->getMessage());
            return response()->json(['message' => 'Không thể xóa file.'], 500);
        }
    }
    
    /**
     * Xử lý xóa hàng loạt, có thể là xóa tạm hoặc xóa vĩnh viễn.
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'numeric', // Kiểm tra là số
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ.'], 422);
        }

        $ids = $request->input('ids');
        $isForceDelete = $request->has('force') && $request->query('force') == '1';

        try {
            if ($isForceDelete) {
                // Xóa vĩnh viễn từ thùng rác
                $files = UploadedFile::onlyTrashed()->whereIn('id', $ids);
                // Xóa file vật lý trước khi xóa record DB
                $files->get()->each(function ($file) {
                    Storage::disk('public')->delete($file->path);
                });
                $deletedCount = $files->forceDelete();
                $message = 'Đã xóa vĩnh viễn ' . $deletedCount . ' file.';
            } else {
                // Xóa tạm (chuyển vào thùng rác)
                $query = UploadedFile::whereIn('id', $ids);
                if (Auth::check()) {
                    $query->update(['deleted_by' => Auth::id()]);
                }
                $deletedCount = $query->delete();
                $message = 'Đã chuyển ' . $deletedCount . ' file vào thùng rác.';
            }

            return response()->json(['message' => $message]);
        } catch (\Exception $e) {
            Log::error('Bulk delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Đã có lỗi xảy ra khi xóa hàng loạt.'], 500);
        }
    }


    /**
     * Hiển thị danh sách các file trong thùng rác.
     */
    public function trash(Request $request)
    {
        $query = UploadedFile::onlyTrashed()
            ->with('deletedBy') 
            ->latest('deleted_at');

        // Lọc theo ảnh chưa gán
        if ($request->query('filter') === 'unattached') {
            $query->whereNull('attachable_id');
        }

        // --- BỔ SUNG: LOGIC TÌM KIẾM ---
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('original_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('alt_text', 'like', '%' . $searchTerm . '%');
            });
        }
        // --- KẾT THÚC BỔ SUNG ---

        $trashedFiles = $query->paginate(24)->appends($request->query());

        $trashedFiles->through(fn ($file) => $file->append(['url', 'formatted_size']));

        return view('admin.media.trash', compact('trashedFiles'));
    }

    /**
     * Khôi phục một file từ thùng rác.
     */
    public function restore($id)
    {
        $file = UploadedFile::onlyTrashed()->findOrFail($id);
        
        $file->deleted_by = null;
        $file->restore(); 
        
        return redirect()->route('admin.media.trash')->with('success', 'Đã khôi phục file thành công.');
    }

    /**
     * Xóa vĩnh viễn một file khỏi CSDL và storage.
     */
    public function forceDelete($id)
    {
        $file = UploadedFile::onlyTrashed()->findOrFail($id);
        
        // Xóa file vật lý trước
        Storage::disk('public')->delete($file->path);
        
        // Xóa record trong DB
        $file->forceDelete();
        
        return redirect()->route('admin.media.trash')->with('success', 'Đã xóa vĩnh viễn file.');
    }

    /**
     * Cập nhật thông tin của file (ví dụ: alt text).
     */
    public function update(Request $request, UploadedFile $uploadedFile)
    {
        $validator = Validator::make($request->all(), [
            'alt_text' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->fileService->update($uploadedFile, $request->only('alt_text'));

        return response()->json(['message' => 'Cập nhật thông tin file thành công.']);
    }

    /**
     * Lấy danh sách file cho modal chọn media.
     */
    public function fetchForModal(Request $request)
    {
        $query = UploadedFile::latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('original_name', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('alt_text', 'like', '%' . $request->input('search') . '%');
            });
        }

        $files = $query->paginate(16); 
        $files->through(fn ($file) => $file->append(['url', 'formatted_size', 'attachable_display']));
        
        return response()->json($files);
    }
}
