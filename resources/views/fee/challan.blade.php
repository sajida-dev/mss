{{-- resources/views/fee/challan.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $fee->voucher_number }} - {{ $fee->student_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 5px;
        }

        @page {
            margin: 0;
            size: landscape;
        }

        .container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
        }

        .challan {
            padding: 5px;
        }

        .challan h3 {
            text-align: center;
            margin: 5px 0;
            font-size: 12px;
            text-decoration: underline;
        }

        .challan p {
            margin: 2px 0;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        .underline {
            text-decoration: underline;
        }

        .print-btn {
            margin: 20px 0;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .print-btn:hover {
            background: #0056b3;
        }

        .note {
            font-size: 12px;
        }

        .note p {
            line-height: 0.3;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                margin: 5px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        @php
            $schoolInitials = $fee->student->school->initials;
            $schoolName = $fee->student->school_name;

            $copies = [
                'Bank Copy',
                'Bank Copy (' . $schoolInitials . ' Treasurer)',
                $schoolName . ' Copy',
                $schoolName . ' Copy',
                'Student Copy',
            ];
        @endphp


        @foreach ($copies as $copy)
            <div class="challan">
                <h3>{{ $copy }}<br>The Bank of the Punjab</h3>
                <p><b>University of Education, Lahore</b></p>
                <p><b>Date:</b> <span class="underline">{{ now()->format('F j, Y') }}</span></p>
                <p><b>Challan#:</b> <span class="underline">{{ $fee->voucher_number }}</span></p>
                {{-- <p><b>1 Bill No.:</b> <span class="underline">{{ $fee->bill_no }}</span></p> --}}
                <p><b>Name:</b> <span class="underline">{{ $fee->student_name }}</span></p>
                <p><b>School:</b> <span class="underline">{{ $fee->student->school_name }}</span></p>
                <p><b>Class:</b> <span class="underline">{{ $fee->student->class_name }}</span></p>
                <p><b>Section:</b> {{ $fee->student->section_name ?? 'N/A' }} &nbsp;&nbsp; <b>Quota:</b>
                    <span class="underline">{{ $fee->student->quota }}</span>
                </p>
                {{-- <p><b>Semester:</b> <span class="underline">{{ $fee->semester }}</span> &nbsp;&nbsp;
                    <b>Session:</b> <span class="underline">{{ $fee->student->session }}</span>
                </p> --}}
                <p><b>Student Id:</b> <span class="underline">{{ $fee->student->registration_number }}</span></p>
                <p><b>Due Date:</b> <span class="underline">{{ $fee->due_date }}</span></p>
                <p><b>Fine Due Date:</b> <span class="underline">{{ $fee->fine_due_date }}</span></p>
                <p><b>Fee Type:</b> <span class="underline">{{ $fee->type }}</span></p>

                <table>
                    <tr>
                        <th>Sr#</th>
                        <th>Description</th>
                        <th>Rs.</th>
                    </tr>
                    @foreach ($fee->feeItems as $index => $detail)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $detail->type }}</td>
                            <td>{{ number_format($detail->amount) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2">Amount (within due date):</td>
                        <td>{{ number_format($fee->amount) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3">{{ $fee->amount_in_words }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Fine Amount:</td>
                        <td>{{ number_format($fee->fine_amount) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Amount (after due date):</td>
                        <td>{{ number_format($fee->total_amount + $fee->fine_amount) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3">{{ $fee->fine_amount_in_words }}</td>
                    </tr>
                </table>
            </div>
        @endforeach
    </div>

    <div class="note">
        <p><b>i)</b> Depositors will receive the system generated deposit slip from the bank as proof of deposit.</p>
        <p><b>ii)</b> This Voucher may please be deposited into any Branch of BOP.</p>
        <p><b>iii)</b> All Bankers are requested to post this Voucher to the (University of Education New Collection
            Account).</p>
    </div>

</body>

</html>
