<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UploadedFile;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UploadedFileController extends Controller
{
    protected FileService $fileService;

    // Inject FileService qua hàm khởi tạo
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Hiển thị trang quản lý media.
     */
    public function index(Request $request)
        {
            $files = UploadedFile::latest()->paginate(30);
            $files->through(fn ($file) => $file->append(['url', 'formatted_size', 'attachable_display']));
            // Nếu có tham số context=modal, trả về một layout khác
            if ($request->query('context') === 'modal') {
                return view('admin.media.modal-index', compact('files'));
            }
            
            return view('admin.media.index', compact('files'));
        }

    /**
     * Xử lý việc upload file mới.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120', // Tối đa 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $uploadedFilesData = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    // Dùng hàm store() trong service để upload độc lập
                    $uploadedFile = $this->fileService->store($file);
                    $request->session()->push('temp_uploaded_file_ids', $uploadedFile->id);
                    $uploadedFilesData[] = $uploadedFile; // Thu thập dữ liệu file đã upload
                } catch (\Exception $e) {
                    Log::error('File upload failed: ' . $e->getMessage());
                    return response()->json(['error' => 'Đã có lỗi xảy ra khi tải file lên.'], 500);
                }
            }
        }

        // Trả về dữ liệu của các file vừa upload dưới dạng JSON để frontend cập nhật
        return response()->json(['files' => $uploadedFilesData], 201);
    }

    /**
     * Cập nhật thông tin của một file (ví dụ: alt text).
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
     * Xóa một file.
     */
    public function destroy(UploadedFile $uploadedFile)
    {
        try {
            $this->fileService->deleteFile($uploadedFile);
            return response()->json(['message' => 'Xóa file thành công.'], 200);
        } catch (\Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Không thể xóa file.'], 500);
        }
    }
    public function fetchForModal(Request $request)
    {
        $query = UploadedFile::latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('original_name', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('alt_text', 'like', '%' . $request->input('search') . '%');
            });
        }

        // Phân trang với số lượng nhỏ hơn cho modal
        $files = $query->paginate(16); 

        // Quan trọng: Thêm thuộc tính 'url' và các thuộc tính ảo khác vào mỗi item
        $files->through(fn ($file) => $file->append(['url', 'formatted_size', 'attachable_display']));
        
        return response()->json($files);
    }
}