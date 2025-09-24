<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Modules\Admissions\App\Models\Student;
use Modules\Fees\App\Models\Fee;
use Modules\Fees\App\Models\FeeItem;
use Modules\Fees\Http\Requests\StoreFeeInstallmentRequest;
use Modules\Fees\Models\FeeInstallment;

class FeeInstallmentController extends Controller
{


    /**
     * Show the form for creating a new resource.
     */
    public function create(Fee $fee, Request $request)
    {
        $fee = Fee::with('student', 'feeItems', 'class')->findOrFail($fee->id);
        $installments = FeeInstallment::where('fee_id', $fee->id)->get();
        return Inertia::render('FeeInstallments/Create', [
            'fee' => $fee,
            'student' => $fee->student,
            'fee_items' => $fee->feeItems,
            'installments' => $installments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreFeeInstallmentRequest $request)
    {
        $validated = $request->validate([
            'fee_id' => 'required|exists:fees,id',
            'installments' => 'required|array|min:1',
            'installments.*.amount' => 'required|numeric|min:1',
            'installments.*.due_date' => 'required|date',
            'installments.*.fine_amount' => 'required|numeric|min:0',
            'installments.*.fine_due_date' => 'required|date',
            'installments.*.fee_items_breakdown' => 'required|array|min:1',
            'installments.*.fee_items_breakdown.*.type' => 'required|string',
            'installments.*.fee_items_breakdown.*.amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $fee = Fee::with('feeItems')->findOrFail($validated['fee_id']);
            $studentId = $fee->student_id;

            // Update fee type to 'installments'
            $fee->update(['type' => 'installments']);

            // Delete existing fee items from parent fee
            $fee->feeItems()->delete();

            $voucher_number = 'VCH' . strtoupper(uniqid());

            foreach ($validated['installments'] as $installmentData) {
                $installment = FeeInstallment::create([
                    'fee_id' => $fee->id,
                    'voucher_number' => $voucher_number,
                    'student_id' => $studentId,
                    'amount' => $installmentData['amount'],
                    'due_date' => $installmentData['due_date'],
                    'status' => 'unpaid',
                    'fine_amount' => $installmentData['fine_amount'] ?? 0,
                    'fine_due_date' => $installmentData['fine_due_date'] ?? null,
                ]);

                $feeItems = array_map(function ($item) use ($installment) {
                    return [
                        'fee_id' => $installment->fee_id,
                        'type' => $item['type'],
                        'amount' => $item['amount'],
                        'fee_installment_id' => $installment->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $installmentData['fee_items_breakdown']);

                FeeItem::insert($feeItems);
            }

            DB::commit();

            return redirect()->route('installments.create', $fee)->with('success', 'Installments created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            // Optional: log error for debugging
            Log::error('Installment creation failed: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors('Failed to create installments. Please try again.' . $e->getMessage());
        }
    }


    public function markAsPaid(FeeInstallment $installment, Request $request)
    {
        $request->validate([
            'paid_voucher_image' => 'required|file|image|max:2048',
        ]);
        $path = $request->file('paid_voucher_image')->store('vouchers', 'public');

        $installment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_voucher_image' => $path, // store file path in DB
        ]);

        return redirect()->route('installments.create', ['fee' => $installment->id])->with('success', 'Installment marked as paid.');
    }
    public function voucher(FeeInstallment $installment)
    {
        $installment->load('fee', 'feeItems', 'fee.student.class', 'fee.student.school');
        $installment['type'] = 'Installment';
        return view('fee.challan', [
            'fee' => $installment,
            'student' => $installment->fee->student,
        ]);
    }
}
