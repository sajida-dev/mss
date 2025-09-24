<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>School Leaving Certificate</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .certificate {
            width: 1000px;
            height: 80vh;
            margin: 20px auto;
            /* padding: 50px 60px; */
            border: 8px double #000;
            position: relative;
            background: #fdfdfd;

            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .certificate-left {
            border: #000 solid 1px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .certificate-header h2 {
            font-size: 24px;
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
            bottom: 25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
        }

        /* Simulated official stamp */
        .stamp {
            position: absolute;
            bottom: 100px;
            left: 46%;
            width: 160px;
            height: 160px;
            transform: rotate(-15deg);
        }

        .signature-left {
            position: absolute;
            bottom: 80px;
            left: 100px;
            text-align: center;
        }

        .signature-right {
            position: absolute;
            bottom: 80px;
            right: 100px;
            text-align: center;
        }

        .signature-line {
            margin-top: 40px;
            border-top: 1px solid #000;
            width: 200px;
        }

        .large-section {
            width: 100%;
            height: 70%;
            border: 1px solid red;
            border-top-left-radius: 50px;

        }
    </style>
</head>

<body>
    <div class="certificate">
        <div class="certificate-left">
            <div class="large-section">

            </div>
            <img src="{{ asset($certificate->school->logo_url ?? 'default-images/default-school-logo.png') }}"
                alt="School Logo">

            <!-- Signature -->
            <div class="signature-left">
                <div class="signature-line"></div>
                <p>Head of Institution</p>
            </div>

        </div>
        <div class="certificate-right">
            <div class="certificate-header">

                <h2>{{ $certificate->school_name ?? 'School Name' }}</h2>
                <h3>School Leaving Certificate</h3>
            </div>

            <div class="certificate-body">
                <p>
                    This is to certify that <span class="highlight">{{ $student->name }}</span>,
                    son/daughter of <span class="highlight">{{ $student->father_name }}</span>,
                    having Registration No. <span class="highlight">{{ $student->registration_number }}</span>,
                    was a bonafide student of this institution.
                </p>

                <p>
                    He/She was enrolled in this school from <span
                        class="highlight">{{ $certificate->admission_date }}</span>
                    to <span class="highlight">{{ now()->format('d/m/Y') }}</span> and is currently in
                    class <span class="highlight">{{ $student->admission_class_name }}</span>. His/Her conduct during
                    the
                    stay in the school has been found
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
