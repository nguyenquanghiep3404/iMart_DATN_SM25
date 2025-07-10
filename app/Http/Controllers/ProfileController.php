<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Notifications\EmailChanged;
use Intervention\Image\Facades\Image;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('users.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $emailChanged = $data['email'] !== $user->email;

        $user->fill($data);

        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->save();

            // Gửi email xác minh mới (dùng hệ thống xác minh của Laravel)
            $user->sendEmailVerificationNotification();

            // ✅ Chuyển đến trang xác minh email + flash thông báo
            return redirect()->route('verification.notice')->with('email_changed', true);
        }

        $user->save();

        return Redirect::route('users.profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Cập nhật avatar của người dùng.
     * Lưu avatar vào bảng uploaded_files.
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user()->fresh(); // Load lại user từ DB

        $data = $request->validate([
            'avatar_base64' => 'required|string',
        ]);

        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data['avatar_base64']));
        $filename = 'avatar_' . uniqid() . '.png';
        $filePath = 'avatars/' . $filename;

        Storage::disk('public')->put($filePath, $imageData);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar->path);
            $user->avatar->forceDelete();
        }


        // ✅ Gắn ảnh mới vào bảng uploaded_files
        $user->avatar()->create([
            'path' => $filePath,
            'filename' => $filename, // ✅ thêm dòng này
            'type' => 'avatar',
            'disk' => 'public',
            'user_id' => $user->id,
        ]);
        return response()->json([
            'status' => 'success',
            'avatar_url' => asset('storage/' . $filePath),
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
