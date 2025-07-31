<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AddressRequest;

class AddressesController extends Controller
{
    // Hiển thị danh sách địa chỉ
    public function index()
    {
        $user = Auth::user();
        $addresses = Address::with(['oldProvince', 'oldDistrict', 'oldWard', 'newProvince', 'newWard'])
            ->where('user_id', $user->id)
            ->orderByDesc('is_default_shipping')
            ->get();
        return view('users.profile.account-addresses', compact('addresses'));
    }

    // Lưu địa chỉ mới
    public function store(AddressRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        $data['is_default_shipping'] = $request->has('is_default_shipping');
        $data['address_system'] = 'old';
        // Đảm bảo chỉ 1 địa chỉ mặc định
        DB::transaction(function () use ($user, &$data) {
            if ($data['is_default_shipping']) {
                Address::where('user_id', $user->id)->update(['is_default_shipping' => false]);
            }
            Address::create($data);
        });
        return redirect()->route('addresses.index')->with('success', 'Thêm địa chỉ thành công!');
    }

    // Hiển thị form sửa (nếu dùng modal thì có thể trả về JSON)
    public function edit(Address $address)
    {
        $this->authorizeAddress($address);
        return response()->json($address);
    }

    // Cập nhật địa chỉ
    public function update(AddressRequest $request, Address $address)
    {
        $this->authorizeAddress($address);
        $data = $request->validated();
        $data['is_default_shipping'] = $request->has('is_default_shipping');
        DB::transaction(function () use ($address, &$data) {
            if ($data['is_default_shipping']) {
                Address::where('user_id', $address->user_id)->update(['is_default_shipping' => false]);
            }
            $address->update($data);
        });
        return redirect()->route('addresses.index')->with('success', 'Cập nhật địa chỉ thành công!');
    }

    // Xóa địa chỉ (không cho xóa mặc định)
    public function destroy(Address $address)
    {
        $this->authorizeAddress($address);
        if ($address->is_default_shipping) {
            return redirect()->route('addresses.index')->with('error', 'Không thể xóa địa chỉ mặc định!');
        }
        $address->delete();
        return redirect()->route('addresses.index')->with('success', 'Xóa địa chỉ thành công!');
    }

    // Đặt làm mặc định
    public function setDefault(Address $address)
    {
        $this->authorizeAddress($address);
        DB::transaction(function () use ($address) {
            Address::where('user_id', $address->user_id)->update(['is_default_shipping' => false]);
            $address->is_default_shipping = true;
            $address->save();
        });
        return redirect()->route('addresses.index')->with('success', 'Đã đặt địa chỉ làm mặc định!');
    }

    // API: Lấy danh sách tỉnh cũ
    public function getOldProvinces()
    {
        $path = base_path('database/locations/old_provinces.json');
        $data = json_decode(file_get_contents($path), true);
        return response()->json($data);
    }

    // API: Lấy danh sách huyện theo mã tỉnh
    public function getOldDistricts($province_code)
    {
        $path = base_path('database/locations/old_districts.json');
        $data = collect(json_decode(file_get_contents($path), true))
            ->where('parent_code', $province_code)
            ->values();
        return response()->json($data);
    }

    // API: Lấy danh sách xã theo mã huyện
    public function getOldWards($district_code)
    {
        $path = base_path('database/locations/old_wards.json');
        $data = collect(json_decode(file_get_contents($path), true))
            ->where('parent_code', $district_code)
            ->values();
        return response()->json($data);
    }

    // Chỉ cho phép thao tác trên địa chỉ của user đang đăng nhập
    protected function authorizeAddress(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Không có quyền truy cập địa chỉ này!');
        }
    }
}
