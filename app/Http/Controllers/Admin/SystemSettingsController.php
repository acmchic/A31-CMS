<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\FontService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all();
        $fontFamily = SystemSetting::getFontFamily();
        $backgroundColor = SystemSetting::getBackgroundColor();
        
        return view('admin.system-settings.index', compact('settings', 'fontFamily', 'backgroundColor'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'font_family' => 'required|string|in:Inter,Roboto,Tahoma,Helvetica,Arial',
            'background_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/'
        ]);

        // Update font family setting
        SystemSetting::setFontFamily($request->font_family);
        
        // Update background color setting
        SystemSetting::setBackgroundColor($request->background_color);
        
        // Generate new dynamic CSS file
        FontService::generateDynamicFontCss($request->font_family);
        
        // Clear all caches to apply new settings
        Cache::forget('system_settings');
        Cache::flush();
        
        return redirect()->back()->with('success', 'Cài đặt đã được cập nhật thành công! Vui lòng refresh trang để thấy thay đổi.');
    }

    public function reset()
    {
        // Reset to default settings
        SystemSetting::setFontFamily('Segoe UI');
        SystemSetting::setBackgroundColor('#f8f9fa');
        
        // Generate new dynamic CSS file
        FontService::generateDynamicFontCss('Segoe UI');
        
        Cache::forget('system_settings');
        Cache::flush();
        
        return redirect()->back()->with('success', 'Đã khôi phục cài đặt mặc định!');
    }
}
