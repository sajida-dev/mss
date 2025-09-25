<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Certificate</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f2f2f2;
            font-family: Arial, Helvetica, sans-serif, serif;
        }

        @page {
            margin: 0;
            size: landscape;
        }

        .certificate .header h1 {
            font-family: fantasy, serif;
            font-size: 90px !important;
            /* Force larger font */
            font-weight: 900;
            letter-spacing: 4px;
            margin: -50px !important;
            /* Remove top/bottom margin */
            line-height: 1.1;
            text-align: center;
            text-transform: uppercase;
        }

        .certificate .header h2 {
            font-size: 32px !important;
            /* Larger subtitle */
            font-weight: 600;
            color: #caa935;
            margin: 50px 0 30px 0 !important;
            letter-spacing: 4px;
            text-align: center;
        }



        .presented {
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
        }


        .name {
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 46px;
            font-weight: 600;
            font-style: italic;
            margin: 10px 0 30px 0;
        }


        .certificate {
            width: 1000px;
            height: 700px;
            background: #fff;
            margin: 40px auto;
            padding: 40px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        /* Watermark */
        .certificate::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 600px;
            /* Adjust size */
            height: 600px;
            background: url('{{ asset($certificate->school->logo_url) }}') no-repeat center;
            background-size: contain;
            opacity: 0.07;
            /* Faded effect */
            transform: translate(-50%, -50%);
            z-index: 0;
            /* Stays behind content */
        }

        /* Border */
        .certificate::before {
            content: "";
            position: absolute;
            top: 50px;
            left: 50px;
            right: 50px;
            bottom: 50px;
            border: 5px solid #d4af37;
        }

        /* Header Text */
        .certificate h1 {
            text-align: center;
            font-size: 36px;
            margin: 20px 0 5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .certificate h2 {
            text-align: center;
            font-size: 18px;
            color: #d4af37;
            letter-spacing: 2px;
            margin: 0 0 25px 0;
            font-weight: 600;
        }

        /* Candidate Name */
        .candidate {
            text-align: center;
            font-size: 32px;
            font-family: "Brush Script MT", cursive;
            margin: 15px 0;
        }

        /* Description */
        .description {
            text-align: center;
            font-size: 15px;
            line-height: 1.6;
            margin: 20px 60px;
            color: #333;
        }

        /* Footer - Date & Signature */
        .footer {
            display: flex;
            justify-content: space-between;
            margin: 100px 80px 0;
            font-size: 14px;
        }

        .footer .line {
            border-top: 1px solid #000;
            width: 200px;
            text-align: center;
            padding-top: 5px;
        }

        .ribbon-seal {
            position: relative;
            width: 160px;
            height: 220px;
            top: 10px;
            left: 50px;
        }

        /* Ribbon */
        .ribbon {
            position: absolute;
            top: 0;
            right: 30px;
            width: 80px;
            height: 220px;
            background: black;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 50% 100%, 0 90%);
            border-left: 2px solid #d4af37;
            border-right: 2px solid #d4af37;
        }

        /* Golden Seal Outer Circle */
        .seal {
            position: absolute;
            top: 30px;
            right: -5px;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, #ffd700, #b8860b);
            border: 6px solid #cdaa2c;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-weight: bold;
            text-align: center;
            flex-direction: column;
            font-family: Arial, Helvetica, sans-serif, serif;
        }

        /* Inner decorative circle */
        .seal::before {
            content: "";
            position: absolute;
            width: 95px;
            height: 95px;
            border-radius: 50%;
            border: 3px solid #00000055;
        }

        .seal span {
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .seal small {
            font-size: 12px;
            margin-top: 4px;
            text-transform: uppercase;
        }

        /* Bottom gold/black wave */
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 120px;
            background: linear-gradient(120deg, black 50%, #d4af37 50%);
            clip-path: polygon(0 40%, 100% 0, 100% 100%, 0 100%);
        }
    </style>
</head>

<body>
    @php
        // Extract first word of achievement title
        $achievementTitle = $certificate->achievement->title ?? 'Award';
        $firstWord = strtoupper(explode(' ', trim($achievementTitle))[0] ?? 'AWARD');
    @endphp

    <div class="certificate">
        <!-- Ribbon Seal -->
        <div class="ribbon-seal">
            <!-- Ribbon -->
            <div class="ribbon"></div>

            <!-- Seal -->
            <div class="seal" style="position: relative; width: 120px; height: 120px; margin: auto;">

                <!-- Curved top text -->
                <svg viewBox="0 0 300 300"
                    style="width: 120px; height: 120px; position: absolute; top: 0; left: 50%; transform: translateX(-50%);">
                    <defs>
                        <!-- Arc on TOP -->
                        <path id="topArc" d="M 60,150
                         A 90,90 0 0,1 240,150" />
                    </defs>

                    <text font-size="32" fill="#000" font-family="Poppins, serif">
                        <textPath href="#topArc" startOffset="50%" text-anchor="middle">
                            ...............
                        </textPath>
                    </text>
                </svg>

                <!-- Middle Text -->
                <span
                    style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); font-size:22px; font-weight:bold; font-family:Poppins, sans-serif;">
                    Best
                </span>

                <!-- Curved bottom text -->
                <svg viewBox="0 0 300 300"
                    style="width: 120px; height: 120px; position: absolute; bottom: -15; left: 50%; transform: translateX(-50%);">
                    <defs>
                        <!-- Arc on BOTTOM -->
                        <path id="bottomArc" d="M 60,150
                         A 90,70 0 0,0 240,150" />
                    </defs>

                    <text font-size="32" fill="#000" font-family="Poppins, serif">
                        <textPath href="#bottomArc" startOffset="50%" text-anchor="middle">
                            Award Winner
                        </textPath>
                    </text>
                </svg>
            </div>
        </div>
        <div class="certificate-body">
            <!-- Header -->
            <div class="header">
                <h1>CERTIFICATE</h1>
                <h2>OF APPRECIATION</h2>
            </div>

            <!-- Presented To -->
            <div class="presented">PROUDLY PRESENTED TO</div>
            <div class="name">{{ $student->name ?? 'Candidate Name' }}</div>

            <!-- Description -->
            <div class="description">
                {!! $certificate->details ?? "For outstanding performance and dedication in <b>{$achievementTitle}</b>." !!}
                <p>This certificate is issued on his/her request for future studies / reference.</p>

            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="line">Date</div>
                <div class="line">Signature</div>
            </div>

        </div>

    </div>
</body>

</html>
