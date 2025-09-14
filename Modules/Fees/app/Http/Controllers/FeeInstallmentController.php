<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Admissions\App\Models\Student;
use Modules\Fees\App\Models\Fee;
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
        $request->validate([
            'fee_id' => 'required|exists:fees,id',
            'installments' => 'required|array|min:1',
            'installments.*.amount' => 'required|numeric|min:1',
            'installments.*.due_date' => 'required|date',
        ]);

        $fee = Fee::findOrFail($request->fee_id);
        $studentId = $fee->student_id;
        $fee->type = 'installments';
        $fee->update();

        foreach ($request->installments as $installment) {
            FeeInstallment::create([
                'fee_id' => $fee->id,
                'student_id' => $studentId,
                'amount' => $installment['amount'],
                'due_date' => $installment['due_date'],
                'status' => 'unpaid',
            ]);
        }


        return redirect()->route('fees.index')->with('success', 'Installments created successfully.');
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
}
