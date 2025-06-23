<?php

namespace App\Services;

use App\Models\UploadedFile as UploadedFileModel;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Interfaces\ImageInterface;

class FileService
{
    protected ImageManager $imageManager;

    /**
     * Khởi tạo service và ImageManager.
     */
    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function store(HttpUploadedFile $file, string $contextFolder, array $extraData = []): UploadedFileModel
    {
        // Xử lý upload, tối ưu hóa và lấy đường dẫn.
        $path = $this->handleUpload($file, $contextFolder);

        // Tạo record trong CSDL. File lúc này chưa được đính kèm vào model nào.
        return UploadedFileModel::create([
            'path' => $path,
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => Storage::disk('public')->size($path), // Lấy kích thước file sau khi tối ưu
            'disk' => config('filesystems.default'),
            'user_id' => auth()->id(),
            ...$extraData // Hợp nhất các dữ liệu bổ sung như 'type', 'alt_text'...
        ]);
    }

    /**
     * Cập nhật thông tin của một file đã tồn tại.
     *
     * @param UploadedFileModel $uploadedFile Model file cần cập nhật.
     * @param array $data Dữ liệu cần cập nhật, ví dụ: ['alt_text' => '...'].
     * @return bool
     */
    public function update(UploadedFileModel $uploadedFile, array $data): bool
    {
        return $uploadedFile->update($data);
    }

    /**
     * Xóa một file.
     * Logic xóa file vật lý được xử lý bởi event 'deleting' trong UploadedFileModel.
     *
     * @param UploadedFileModel $uploadedFile
     * @return bool|null
     */
    public function deleteFile(UploadedFileModel $uploadedFile): ?bool
    {
        // Khi gọi delete(), event 'deleting' trong model sẽ được kích hoạt
        // để xóa file vật lý trên disk trước khi xóa record trong CSDL.
        return $uploadedFile->delete();
    }
public function delete(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        // Sử dụng Storage facade để xóa file trên disk 'public'
        return Storage::disk('public')->delete($path);
    }

    /**
     * Hàm private xử lý upload, tạo thư mục, đổi tên file và tối ưu hóa ảnh.
     *
     * @param HttpUploadedFile $file
     * @param string $contextFolder
     * @return string Đường dẫn tương đối của file đã lưu.
     */
    private function handleUpload(HttpUploadedFile $file, string $contextFolder): string
    {
        // 1. Tạo đường dẫn dựa trên ngữ cảnh và ngày tháng
        // Ví dụ: "products/2025/06" hoặc "avatars/2025/06"
        $folder = "{$contextFolder}/" . now()->format('Y/m');

        // 2. Tạo tên file mới unique để tránh trùng lặp và các vấn đề bảo mật
        $filename = uniqid() . '.' . strtolower($file->getClientOriginalExtension());
        $path = "{$folder}/{$filename}";

        // 3. Kiểm tra nếu là file ảnh thì tiến hành xử lý và tối ưu
        if (str_starts_with($file->getMimeType(), 'image/')) {
            
            $image = $this->imageManager->read($file->getRealPath());

            // 4. Áp dụng quy tắc xử lý ảnh dựa trên ngữ cảnh (context-aware processing)
            $image = match ($contextFolder) {
                'products'   => $this->processProductImage($image),
                'avatars'    => $this->processAvatarImage($image),
                'banners'    => $this->processBannerImage($image),
                default      => $image->scaleDown(1920), // Mặc định cho các context khác
            };

            // 5. Encode ảnh và lưu vào disk
            $encodedImage = $image->encode(new AutoEncoder(quality: 80));
            Storage::disk('public')->put($path, (string) $encodedImage);

        } else {
            // Nếu không phải ảnh (PDF, DOCX...), chỉ lưu file gốc
            Storage::disk('public')->putFileAs($folder, $file, $filename);
        }

        return $path;
    }

    /**
     * Quy tắc xử lý cho ảnh sản phẩm.
     * @param ImageInterface $image
     * @return ImageInterface
     */
    private function processProductImage(ImageInterface $image): ImageInterface
    {
        // Resize ảnh sản phẩm, giữ tỷ lệ, chiều rộng tối đa 1200px
        return $image->scaleDown(1200);
    }

    /**
     * Quy tắc xử lý cho ảnh đại diện (avatar).
     * @param ImageInterface $image
     * @return ImageInterface
     */
    private function processAvatarImage(ImageInterface $image): ImageInterface
    {
        // Cắt ảnh thành hình vuông 300x300px
        return $image->cover(300, 300);
    }

    /**
     * Quy tắc xử lý cho ảnh banner.
     * @param ImageInterface $image
     * @return ImageInterface
     */
    private function processBannerImage(ImageInterface $image): ImageInterface
    {
        // Với banner có thể chỉ cần tối ưu chất lượng mà không cần resize
        // Hoặc resize về một kích thước cố định, ví dụ 1920x1080
        return $image->scaleDown(1920);
    }
}