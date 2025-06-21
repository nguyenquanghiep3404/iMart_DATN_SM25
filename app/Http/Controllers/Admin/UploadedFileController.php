<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UploadedFile;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Import Rule để sử dụng validation 'in'

class UploadedFileController extends Controller
{
    protected FileService $fileService;

    // ... (phần __construct và index giữ nguyên) ...
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index(Request $request)
    {
        $files = UploadedFile::latest()->paginate(18);
        $files->through(fn ($file) => $file->append(['url', 'formatted_size', 'attachable_display']));
        if ($request->query('context') === 'modal') {
            return view('admin.media.modal-index', compact('files'));
        }
        
        return view('admin.media.index', compact('files'));
    }


    /**
     * Xử lý việc upload file mới.
     * ĐÃ ĐƯỢC CẬP NHẬT ĐỂ TƯƠNG THÍCH VỚI FILESERVICE MỚI.
     */
    public function store(Request $request)
    {
        // === THAY ĐỔI 1: THÊM VALIDATION CHO 'CONTEXT' ===
        // Validation này đảm bảo frontend phải gửi lên ngữ cảnh lưu trữ
        // và ngữ cảnh đó phải hợp lệ để tăng cường bảo mật.
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120', // Tối đa 5MB
            'context' => [
                'required',
                'string',
                Rule::in(['products', 'avatars', 'banners', 'categories', 'posts', 'general']) // Chỉ cho phép các thư mục này
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $uploadedFilesData = [];
        
        // === THAY ĐỔI 2: LẤY NGỮ CẢNH TỪ REQUEST ===
        $contextFolder = $request->input('context');

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    // === THAY ĐỔI 3: TRUYỀN NGỮ CẢNH VÀO FILESERVICE ===
                    // Bây giờ chúng ta gọi hàm store với 2 tham số bắt buộc.
                    $uploadedFile = $this->fileService->store($file, $contextFolder);
                    
                    $request->session()->push('temp_uploaded_file_ids', $uploadedFile->id);
                    $uploadedFile->append('url'); // Thêm URL vào response để JS có thể hiển thị preview
                    $uploadedFilesData[] = $uploadedFile; // Thu thập dữ liệu file đã upload
                } catch (\Exception $e) {
                    Log::error('File upload failed: ' . $e->getMessage(), [
                        'context' => $contextFolder,
                        'file' => $file->getClientOriginalName()
                    ]);
                    return response()->json(['error' => 'Đã có lỗi xảy ra khi tải file lên.'], 500);
                }
            }
        }

        // Trả về dữ liệu của các file vừa upload dưới dạng JSON để frontend cập nhật
        return response()->json(['files' => $uploadedFilesData], 201);
    }

    // ... (Các phương thức update, destroy, fetchForModal giữ nguyên vì chúng không liên quan đến logic upload) ...

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

        $files = $query->paginate(16); 
        $files->through(fn ($file) => $file->append(['url', 'formatted_size', 'attachable_display']));
        
        return response()->json($files);
    }
}