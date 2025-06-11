<?php

namespace App\Services;

use App\Models\UploadedFile as UploadedFileModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager; // Nạp thư viện Intervention Image
use Intervention\Image\Drivers\Gd\Driver; // Chọn driver, có thể là Gd hoặc Imagick

class FileService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        // Khởi tạo ImageManager khi service được tạo
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * CHỨC NĂNG MỚI: Chỉ lưu file mà không cần đính kèm model cha.
     * Hoàn hảo cho việc upload file lên Thư viện Media.
     *
     * @param HttpUploadedFile $file
     * @param string|null $type
     * @param array $extraData
     * @return UploadedFileModel
     */
    public function store(HttpUploadedFile $file, ?string $type = null, array $extraData = []): UploadedFileModel
    {
        $path = $this->handleUpload($file);

        return UploadedFileModel::create([
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
    }

    /**
     * CẢI TIẾN: Lưu file, tối ưu hóa ảnh, và liên kết với một model cụ thể.
     *
     * @param HttpUploadedFile $file
     * @param Model $model
     * @param string $type
     * @param array $extraData
     * @return UploadedFileModel
     */
    public function storeAndAssociate(HttpUploadedFile $file, Model $model, string $type, array $extraData = []): UploadedFileModel
    {
        $path = $this->handleUpload($file);

        // Sử dụng relationship để tạo record, tự động điền attachable_id và attachable_type
        return $model->images()->create([
            'path' => $path,
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => Storage::disk('public')->size($path), // Lấy kích thước sau khi đã tối ưu
            'disk' => 'public',
            'type' => $type,
            // user_id sẽ được gán tự động bởi model event
            ...$extraData
        ]);
    }

    /**
     * CHỨC NĂNG MỚI: Cập nhật thông tin của một file đã tồn tại.
     *
     * @param UploadedFileModel $uploadedFile
     * @param array $data Dữ liệu cần cập nhật, ví dụ: ['alt_text' => '...']
     * @return bool
     */
    public function update(UploadedFileModel $uploadedFile, array $data): bool
    {
        return $uploadedFile->update($data);
    }


    /**
     * CẢI TIẾN: Logic xóa được đơn giản hóa.
     * Chỉ cần gọi phương thức delete() của model, event trong model sẽ tự động xóa file vật lý.
     *
     * @param UploadedFileModel $uploadedFile
     * @return bool|null
     */
    public function deleteFile(UploadedFileModel $uploadedFile): ?bool
    {
        return $uploadedFile->delete();
    }

    /**
     * Logic xử lý upload và tối ưu hóa ảnh tập trung.
     *
     * @param HttpUploadedFile $file
     * @return string Trả về đường dẫn của file đã lưu.
     */
    private function handleUpload(HttpUploadedFile $file): string
    {
        $folder = 'images/' . date('Y/m');

        // Lấy phần mở rộng của file một cách an toàn
        $extension = strtolower($file->guessExtension() ?? 'jpg');
        $filename = uniqid() . '.' . $extension;
        $path = $folder . '/' . $filename;

        // Kiểm tra nếu là file ảnh thì mới tối ưu
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            // Đọc file ảnh
            $image = $this->imageManager->read($file->getRealPath());

            // Thay đổi kích thước (ví dụ: chiều rộng tối đa 1200px, chiều cao tự động)
            $image->scaleDown(1200);

            // === PHẦN SỬA LỖI ===
            // Encode ảnh dựa trên định dạng của nó với chất lượng phù hợp
            $encodedImage = match ($extension) {
                'jpeg', 'jpg' => $image->toJpeg(80),
                'png' => $image->toPng(), // Nén PNG là lossless
                'gif' => $image->toGif(),
                'webp' => $image->toWebp(80),
                default => $image->toJpeg(80), // Mặc định là JPEG
            };

            // Lưu ảnh đã được tối ưu hóa vào storage
            Storage::disk('public')->put($path, (string) $encodedImage);
            
        } else {
            // Nếu không phải ảnh (ví dụ: PDF, ZIP), chỉ lưu file gốc
            Storage::disk('public')->putFileAs($folder, $file, $filename);
        }

        return $path;
    }
}