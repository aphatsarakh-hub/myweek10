<?php

namespace App\Http\Controllers;

use App\Models\ProductClaim;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    /**
     * Show the product claim form.
     */
    public function create()
    {
        return view('claim');
    }

    /**
     * Store a new product claim.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => ['required', 'string', 'max:20', 'regex:/^SN[A-Za-z0-9]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'issue_description' => ['required', 'string', 'min:10'],
            'urgency_level' => ['required', 'in:low,medium,high'],
        ], [
            'serial_number.required' => 'กรุณากรอกรหัสสินค้า',
            'serial_number.string' => 'รหัสสินค้าต้องเป็นข้อความ',
            'serial_number.max' => 'รหัสสินค้าต้องมีความยาวไม่เกิน 20 ตัวอักษร',
            'serial_number.regex' => 'รหัสสินค้าต้องขึ้นต้นด้วย SN ตามด้วยตัวเลขหรือตัวอักษรภาษาอังกฤษ',
            'email.required' => 'กรุณากรอกอีเมลผู้ติดต่อ',
            'email.email' => 'กรุณากรอกอีเมลที่ถูกต้อง',
            'email.max' => 'อีเมลต้องมีความยาวไม่เกิน 255 ตัวอักษร',
            'issue_description.required' => 'กรุณากรอกอาการชำรุด',
            'issue_description.string' => 'อาการชำรุดต้องเป็นข้อความ',
            'issue_description.min' => 'อาการชำรุดต้องมีความยาวอย่างน้อย 10 ตัวอักษร',
            'urgency_level.required' => 'กรุณาเลือกระดับความเร่งด่วน',
            'urgency_level.in' => 'ระดับความเร่งด่วนไม่ถูกต้อง',
        ]);

        ProductClaim::create($validated);

        return redirect()->back()->with('success', 'ส่งข้อมูลแจ้งเคลมเรียบร้อยแล้ว');
    }
}
