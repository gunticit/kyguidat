<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdministrativeDivisionController extends Controller
{
    // ============ PUBLIC ENDPOINTS ============

    /**
     * Get all active provinces with their active wards
     */
    public function publicProvinces()
    {
        $provinces = Province::active()
            ->ordered()
            ->with('activeWards')
            ->get()
            ->map(function ($province) {
                return [
                    'id' => $province->id,
                    'name' => $province->name,
                    'slug' => $province->slug,
                    'wards' => $province->activeWards->map(function ($ward) {
                        return [
                            'id' => $ward->id,
                            'name' => $ward->name,
                            'type' => $ward->type,
                            'type_label' => $ward->type_label,
                        ];
                    }),
                ];
            });

        return response()->json(['data' => $provinces]);
    }

    /**
     * Get wards for a specific province by slug
     */
    public function publicWards($slug)
    {
        $province = Province::where('slug', $slug)->active()->firstOrFail();
        $wards = $province->activeWards()->get(['id', 'name', 'type', 'slug']);

        return response()->json(['data' => $wards]);
    }

    // ============ ADMIN - PROVINCES ============

    public function provinceIndex(Request $request)
    {
        $query = Province::withCount('wards')->ordered();

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $provinces = $query->get();

        return response()->json(['data' => $provinces]);
    }

    public function provinceStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $province = Province::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json(['data' => $province, 'message' => 'Tạo tỉnh/TP thành công'], 201);
    }

    public function provinceUpdate(Request $request, $id)
    {
        $province = Province::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $province->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'sort_order' => $request->sort_order ?? $province->sort_order,
            'is_active' => $request->has('is_active') ? $request->is_active : $province->is_active,
        ]);

        return response()->json(['data' => $province, 'message' => 'Cập nhật thành công']);
    }

    public function provinceDestroy($id)
    {
        $province = Province::findOrFail($id);
        $province->delete(); // cascade deletes wards

        return response()->json(['message' => 'Xóa tỉnh/TP thành công']);
    }

    // ============ ADMIN - WARDS ============

    public function wardIndex(Request $request)
    {
        $query = Ward::with('province')->ordered();

        if ($request->has('province_id') && $request->province_id) {
            $query->where('province_id', $request->province_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $wards = $query->get();

        return response()->json(['data' => $wards]);
    }

    public function wardStore(Request $request)
    {
        $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:phuong,xa,dac_khu',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $ward = Ward::create([
            'province_id' => $request->province_id,
            'name' => $request->name,
            'type' => $request->type,
            'slug' => Str::slug($request->name),
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json(['data' => $ward->load('province'), 'message' => 'Tạo xã/phường thành công'], 201);
    }

    public function wardUpdate(Request $request, $id)
    {
        $ward = Ward::findOrFail($id);

        $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:phuong,xa,dac_khu',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $ward->update([
            'province_id' => $request->province_id,
            'name' => $request->name,
            'type' => $request->type,
            'slug' => Str::slug($request->name),
            'sort_order' => $request->sort_order ?? $ward->sort_order,
            'is_active' => $request->has('is_active') ? $request->is_active : $ward->is_active,
        ]);

        return response()->json(['data' => $ward->load('province'), 'message' => 'Cập nhật thành công']);
    }

    public function wardDestroy($id)
    {
        $ward = Ward::findOrFail($id);
        $ward->delete();

        return response()->json(['message' => 'Xóa xã/phường thành công']);
    }
}
