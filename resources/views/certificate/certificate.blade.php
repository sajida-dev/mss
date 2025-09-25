<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>School Leaving Certificate</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif, serif;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        @page {
            margin: 0;
            size: landscape;
        }

        .certificate {
            width: 90%;
            height: 77vh;
            /* margin: 20px auto; */
            padding: 50px 50px;
            /* border: 8px double #000; */
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
            position: relative;
            background: #fdfdfd;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }



        .certificate::before {
            content: "";
            position: absolute;
            top: 50px;
            left: 50px;
            right: 50px;
            bottom: 50px;
            border: 5px solid #d4af37;
        }

        .certificate-left {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 25%;
        }

        .certificate-right {
            display: flex;
            flex-direction: column;
            padding: 20px;
            width: 75%;

        }

        .certificate-header {
            text-align: center;
            /* margin-bottom: 30px; */
        }

        .certificate-header h2 {
            font-size: 42px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .certificate-header h3 {
            font-size: 18px;
            margin-top: 5px;
        }

        .certificate-body {
            font-size: 16px;
            line-height: 1.8;
            text-align: center;
        }

        .certificate-body u {
            font-weight: bold;
        }

        .highlight {
            font-weight: bold;
            text-decoration: underline;
        }

        .footer {
            position: absolute;
            bottom: 60px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
        }

        /* Simulated official stamp */
        .stamp {
            position: absolute;
            bottom: 130px;
            left: 45%;
            width: 160px;
            height: 160px;
            transform: rotate(-15deg);
        }

        .signature-left {
            position: absolute;
            bottom: 70px;
            left: 70px;
            text-align: center;
        }

        .signature-right {
            position: absolute;
            bottom: 70px;
            right: 70px;
            text-align: center;
        }

        .signature-line {
            margin-top: 40px;
            border-top: 1px solid #000;
            width: 200px;
        }

        .large-section {
            width: 80%;
            height: 50vh;
            margin-left: 15%;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;


            clip-path: polygon(0 0,
                    /* top-left */
                    100% 0,
                    /* top-right */
                    100% 80%,
                    /* bottom-right before the triangle */
                    50% 100%,
                    /* bottom-center point (triangle tip) */
                    0 80%
                    /* bottom-left before the triangle */
                );
        }

        /* Watermark */
        .certificate::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 450px;
            height: 450px;
            background: url('{{ asset($certificate->school->logo_url) }}') no-repeat center;
            background-size: cover;
            opacity: 0.07;
            transform: translate(-50%, -50%);
            z-index: 0;
        }
    </style>
</head>

<body>
    <div class="certificate">
        <div class="certificate-left">
            <div class="large-section">
                <img src="{{ asset($certificate->school->logo_url) }}" alt="School Logo">
            </div>

            <!-- Signature -->
            <div class="signature-left">
                <div class="signature-line"></div>
                <p>Head of Institution</p>
            </div>

        </div>
        <div class="certificate-right">
            <div style="position: relative; height: 100px;">
                <svg viewBox="0 0 600 150" style="width: 100%; height: 100%; position: absolute; top: 0; left: -30;">
                    <defs>
                        <!-- Flatter arc: adjust Y and radius -->
                        <path id="schoolNamePath" d="M 100,120 A 500,300 0 0,1 600,120" />
                    </defs>

                    <text font-size="36" fill="#000" font-family="Georgia, serif">
                        <textPath href="#schoolNamePath" startOffset="50%" text-anchor="middle">
                            {{ strtoupper($certificate->school_name ?? 'SCHOOL NAME') }}
                        </textPath>
                    </text>
                </svg>
            </div>
            <div class="certificate-header">
                <h3>School Leaving Certificate</h3>
            </div>

            <div class="certificate-body">
                <p>
                    This is to certify that <span class="highlight">{{ $student->name }}</span>,
                    son/daughter of <span class="highlight">{{ $student->father_name }}</span>,
                    having Registration No. <span class="highlight">{{ $student->registration_number }}</span>,
                    was a bonafide student of this institution. He/She was enrolled in this school from <span
                        class="highlight">{{ $certificate->admission_date }}</span>
                    to <span class="highlight">{{ now()->format('d/m/Y') }}</span> and is currently in
                    <span class="highlight">{{ $student->class_name }}</span>. His/Her conduct during
                    the stay in the school has been found
                    <span class="highlight">{{ $certificate->conduct ?? 'Good' }}</span>.
                </p>

                <p>
                    This certificate is issued on his/her request for future studies / reference.
                </p>
            </div>
            <!-- Official Stamp -->
            <div class="stamp">
                <img src="{{ asset('verify.png') }}" alt="Stamp">
            </div>

            <!-- Signature -->
            <div class="signature-right">
                <div class="signature-line"></div>
                <p>Head of Institution</p>
            </div>

            <div class="footer">
                Printed on: {{ now()->format('F d, Y') }}
            </div>
        </div>



    </div>
</body>

</html>
