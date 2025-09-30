<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use App\Helpers\PermissionHelper;

class ProfileController extends Controller
{
    /**
     * Show the profile edit page
     */
    public function edit()
    {
        $user = backpack_user();
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Update profile information
     */
    public function update(Request $request)
    {
        $user = backpack_user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'Họ và tên là bắt buộc.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'username.required' => 'Tên đăng nhập là bắt buộc.',
            'username.unique' => 'Tên đăng nhập đã được sử dụng.',
            'profile_photo.image' => 'File ảnh đại diện phải là hình ảnh.',
            'profile_photo.mimes' => 'Ảnh đại diện phải có định dạng: jpeg, png, jpg, gif.',
            'profile_photo.max' => 'Kích thước ảnh đại diện không được vượt quá 2MB.',
            'signature.image' => 'File chữ ký phải là hình ảnh.',
            'signature.mimes' => 'Chữ ký phải có định dạng: jpeg, png, jpg, gif.',
            'signature.max' => 'Kích thước chữ ký không được vượt quá 2MB.',
        ]);

        $data = $request->only(['name', 'username']);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $profilePhoto = $request->file('profile_photo');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $profilePhoto->getClientOriginalExtension();
            $path = 'profile-photos/' . $filename;
            
            // Resize and save image
            $image = Image::make($profilePhoto);
            $image->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            Storage::disk('public')->put($path, (string) $image->encode());
            $data['profile_photo_path'] = $path;
        }

        // Handle signature upload
        if ($request->hasFile('signature')) {
            // Delete old signature if exists
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }

            $signature = $request->file('signature');
            $filename = 'signature_' . $user->id . '_' . time() . '.' . $signature->getClientOriginalExtension();
            $path = 'signatures/' . $filename;
            
            // Resize and save signature
            $image = Image::make($signature);
            $image->resize(400, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            Storage::disk('public')->put($path, (string) $image->encode());
            $data['signature_path'] = $path;
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Thông tin cá nhân đã được cập nhật thành công!');
    }

    /**
     * Delete profile photo
     */
    public function deleteProfilePhoto()
    {
        $user = backpack_user();
        
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        return redirect()->back()->with('success', 'Ảnh đại diện đã được xóa!');
    }

    /**
     * Delete signature
     */
    public function deleteSignature()
    {
        $user = backpack_user();
        
        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
            $user->update(['signature_path' => null]);
        }

        return redirect()->back()->with('success', 'Chữ ký đã được xóa!');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $user = backpack_user();
        
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Mật khẩu hiện tại là bắt buộc.',
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'new_password.required' => 'Mật khẩu mới là bắt buộc.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $user->update([
            'password' => bcrypt($request->new_password)
        ]);

        return redirect()->back()->with('success', 'Mật khẩu đã được thay đổi thành công!');
    }

    /**
     * Upload profile photo only
     */
    public function uploadPhoto(Request $request)
    {
        $user = backpack_user();
        
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'profile_photo.required' => 'Vui lòng chọn ảnh đại diện.',
            'profile_photo.image' => 'File ảnh đại diện phải là hình ảnh.',
            'profile_photo.mimes' => 'Ảnh đại diện phải có định dạng: jpeg, png, jpg, gif.',
            'profile_photo.max' => 'Kích thước ảnh đại diện không được vượt quá 2MB.',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Store new photo
            $file = $request->file('profile_photo');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile-photos', $filename, 'public');

            // Resize image
            $image = \Intervention\Image\ImageManager::gd()->read($file->getRealPath());
            $image->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Save resized image
            $resizedPath = storage_path('app/public/' . $path);
            $image->save($resizedPath);

            $user->update(['profile_photo_path' => $path]);
        }

        return redirect()->back()->with('success', 'Ảnh đại diện đã được cập nhật!');
    }

    /**
     * Upload signature only
     */
    public function uploadSignature(Request $request)
    {
        $user = backpack_user();
        
        $request->validate([
            'signature' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'signature.required' => 'Vui lòng chọn ảnh chữ ký.',
            'signature.image' => 'File chữ ký phải là hình ảnh.',
            'signature.mimes' => 'Chữ ký phải có định dạng: jpeg, png, jpg, gif.',
            'signature.max' => 'Kích thước chữ ký không được vượt quá 2MB.',
        ]);

        // Handle signature upload
        if ($request->hasFile('signature')) {
            // Delete old signature if exists
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }

            // Store new signature
            $file = $request->file('signature');
            $filename = 'signature_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('signatures', $filename, 'public');

            // Resize image
            $image = \Intervention\Image\ImageManager::gd()->read($file->getRealPath());
            $image->resize(400, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Save resized image
            $resizedPath = storage_path('app/public/' . $path);
            $image->save($resizedPath);

            $user->update(['signature_path' => $path]);
        }

        return redirect()->back()->with('success', 'Chữ ký đã được cập nhật!');
    }

    /**
     * Update certificate PIN
     */
    public function updatePin(Request $request)
    {
        $user = backpack_user();
        
        $request->validate([
            'certificate_pin' => 'nullable|string|min:6|confirmed',
        ], [
            'certificate_pin.min' => 'Mã PIN phải có ít nhất 6 ký tự.',
            'certificate_pin.confirmed' => 'Xác nhận mã PIN không khớp.',
        ]);

        // Only update if PIN is provided
        if ($request->filled('certificate_pin')) {
            $user->update([
                'certificate_pin' => $request->certificate_pin
            ]);
            
            return redirect()->back()->with('success', 'Mã PIN chữ ký số đã được cập nhật thành công!');
        }

        return redirect()->back()->with('error', 'Vui lòng nhập mã PIN.');
    }
}