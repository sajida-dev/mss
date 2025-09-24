<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $paper->title ?? 'Exam Paper' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            margin: 1in 0.35in;
        }

        h1,
        h2,
        h3 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .exam-details {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 11pt;
        }

        .instructions {
            text-align: left;
            margin: 15px 0;
        }

        .instructions ol {
            padding-left: 20px;
        }

        .section {
            margin: 25px 0;
            /* border-top: 1px solid black; */
            padding-top: 10px;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;

        }

        .question-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
            page-break-inside: avoid;
        }

        .question-number {
            width: 25px;
            font-weight: bold;
        }

        .question-text {
            flex: 1;
        }

        .question-marks {
            white-space: nowrap;
        }

        .options {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-left: 40px;
            margin-top: 5px;
        }

        .option {
            font-size: 11pt;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10pt;
            padding: 5px 15px;
        }

        @page {
            size: A4;
            margin: 0.5in;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ strtoupper($paper->title ?? 'EXAMINATION') }}</h1>
        <div>
            {{ $paper->subject->name ?? 'Subject' }} ({{ $paper->subject->code ?? 'Code' }})
            &nbsp; | &nbsp; GRADE: {{ $paper->class->name ?? 'Class' }}
        </div>
    </div>

    <!-- Exam Details -->
    <div class="exam-details">
        <div><strong>Duration:</strong> {{ $paper->time_duration ?? 90 }} Minutes</div>
        <div><strong>Maximum Marks:</strong> {{ $paper->total_marks ?? $paper->questions->sum('marks') }}</div>
    </div>

    <!-- Instructions -->
    <div class="instructions">
        <h3>General Instructions</h3>
        <ol>
            <li>The Question Paper contains three sections.</li>
            <li>Section A has {{ $paper->questions->where('section', 'objective')->count() }} questions.</li>
            <li>Section B has {{ $paper->questions->where('section', 'short_questions')->count() }} questions.</li>
            <li>Section C has {{ $paper->questions->where('section', 'long_questions')->count() }} questions.</li>
            <li>All questions carry equal marks.</li>
            <li>There is no negative marking.</li>
            @if (!empty($paper->instructions))
                <li>{!! $paper->instructions !!}</li>
            @endif
        </ol>
    </div>

    <!-- Sections -->
    @php
        $sections = [
            'objective' => 'SECTION A',
            'short_questions' => 'SECTION B',
            'long_questions' => 'SECTION C',
            'essay' => 'SECTION D',
            'numerical' => 'SECTION E',
        ];
    @endphp

    @foreach ($sections as $key => $title)
        @if ($paper->questions->where('section', $key)->count() > 0)
            <div class="section">
                <div class="section-title">{{ $title }}</div>

                @foreach ($paper->questions->where('section', $key) as $index => $question)
                    <div class="question-row">
                        <div class="question-number">{{ $loop->iteration }}.</div>
                        <div class="question-text">{{ $question->text }}</div>
                        <div class="question-marks">({{ $question->marks }} marks)</div>
                    </div>

                    @if ($question->type === 'multiple_choice' && !empty($question->options))
                        <div class="options">
                            @foreach ($question->options as $i => $option)
                                <div class="option">{{ chr(97 + $i) }}) {{ $option }}</div>
                            @endforeach
                        </div>
                    @elseif($question->type === 'true_false')
                        <div class="options">
                            <div class="option">a) True</div>
                            <div class="option">b) False</div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <div>{{ $paper->school_name ?? 'School Name' }}, {{ $paper->school->address ?? 'Location' }}</div>
        <div>Page 1</div>
    </div>
</body>

</html>
