<?php

namespace App\Services;

use App\Models\UploadedFile as UploadedFileModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Lưu file được upload và liên kết nó với một model cụ thể.
     *
     * @param HttpUploadedFile $file Đối tượng file từ request.
     * @param Model $model Đối tượng model cha (ví dụ: Product, User).
     * @param string $type Loại file (ví dụ: 'cover_image', 'gallery_image').
     * @param array $extraData Dữ liệu bổ sung như alt_text, order.
     * @return UploadedFileModel
     */
    public function storeAndAssociate(HttpUploadedFile $file, Model $model, string $type, array $extraData = []): UploadedFileModel
    {
        // 1. Tạo tên file duy nhất và lưu file vào storage
        $path = $file->store('images/' . date('Y/m'), 'public');

        // 2. Tạo record trong bảng uploaded_files và liên kết nó
        $uploadedFile = $model->images()->create([
            'path' => $path,
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => 'public',
            'type' => $type,
            'user_id' => auth()->id(),
            ...$extraData
        ]);

        return $uploadedFile;
    }

    /**
     * Xóa một file và record tương ứng trong DB.
     *
     * @param UploadedFileModel $uploadedFile
     * @return bool
     */
    public function deleteFile(UploadedFileModel $uploadedFile): bool
    {
        // Xóa file vật lý khỏi storage
        Storage::disk('public')->delete($uploadedFile->path);

        // Xóa record trong database
        return $uploadedFile->delete();
    }
}