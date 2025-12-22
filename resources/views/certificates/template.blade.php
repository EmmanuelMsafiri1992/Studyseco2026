<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $certificate->title }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .certificate-container {
            width: 100%;
            height: 100vh;
            padding: 60px;
            box-sizing: border-box;
            position: relative;
        }
        .certificate-inner {
            background: white;
            padding: 60px;
            border: 20px solid #f8f9fa;
            box-shadow: 0 0 40px rgba(0,0,0,0.1);
            height: 100%;
            position: relative;
        }
        .certificate-border {
            border: 3px solid #667eea;
            padding: 40px;
            height: calc(100% - 80px);
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        .certificate-type {
            font-size: 36px;
            color: #2d3748;
            margin: 20px 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .presented-to {
            font-size: 18px;
            color: #718096;
            margin: 30px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .student-name {
            font-size: 48px;
            color: #1a202c;
            margin: 20px 0;
            font-weight: bold;
            border-bottom: 3px solid #667eea;
            display: inline-block;
            padding: 0 40px 10px 40px;
        }
        .description {
            font-size: 18px;
            color: #4a5568;
            margin: 30px 0;
            line-height: 1.8;
            text-align: center;
        }
        .subject {
            font-weight: bold;
            color: #667eea;
        }
        .details {
            margin-top: 50px;
            display: table;
            width: 100%;
        }
        .detail-row {
            display: table-row;
        }
        .detail-cell {
            display: table-cell;
            width: 50%;
            padding: 20px 0;
            text-align: center;
        }
        .signature-line {
            border-top: 2px solid #2d3748;
            width: 200px;
            margin: 0 auto 10px auto;
        }
        .signature-label {
            font-size: 14px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .signature-name {
            font-size: 16px;
            color: #2d3748;
            font-weight: bold;
            margin-top: 5px;
        }
        .date {
            font-size: 14px;
            color: #718096;
        }
        .seal {
            position: absolute;
            bottom: 40px;
            left: 40px;
            width: 100px;
            height: 100px;
            border: 4px solid #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #667eea;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            box-sizing: border-box;
        }
        .decorative-corner {
            position: absolute;
            width: 60px;
            height: 60px;
            border: 3px solid #667eea;
        }
        .corner-tl {
            top: 0;
            left: 0;
            border-right: none;
            border-bottom: none;
        }
        .corner-tr {
            top: 0;
            right: 0;
            border-left: none;
            border-bottom: none;
        }
        .corner-bl {
            bottom: 0;
            left: 0;
            border-right: none;
            border-top: none;
        }
        .corner-br {
            bottom: 0;
            right: 0;
            border-left: none;
            border-top: none;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-inner">
            <div class="certificate-border">
                <div class="decorative-corner corner-tl"></div>
                <div class="decorative-corner corner-tr"></div>
                <div class="decorative-corner corner-bl"></div>
                <div class="decorative-corner corner-br"></div>

                <div class="header">
                    <div class="logo">StudySeco</div>
                    <div class="certificate-type">{{ $certificate->getTypeLabel() }}</div>
                </div>

                <div style="text-align: center;">
                    <div class="presented-to">This certificate is proudly presented to</div>
                    <div class="student-name">{{ $certificate->student->name }}</div>
                </div>

                <div class="description">
                    @if($certificate->description)
                        {{ $certificate->description }}
                    @else
                        For successfully completing the requirements and demonstrating exceptional
                        dedication and achievement in
                        @if($certificate->subject)
                            <span class="subject">{{ $certificate->subject->name }}</span>
                        @else
                            their studies
                        @endif
                    @endif
                </div>

                <div class="details">
                    <div class="detail-row">
                        <div class="detail-cell">
                            <div class="signature-line"></div>
                            <div class="signature-label">Issued By</div>
                            <div class="signature-name">{{ $certificate->teacher->name }}</div>
                            <div class="date">{{ $certificate->teacher->role ?? 'Teacher' }}</div>
                        </div>
                        <div class="detail-cell">
                            <div class="signature-label">Date of Issue</div>
                            <div class="signature-name">{{ $certificate->issued_at->format('F j, Y') }}</div>
                        </div>
                    </div>
                </div>

                <div class="seal">
                    OFFICIAL<br>CERTIFICATE
                </div>
            </div>
        </div>
    </div>
</body>
</html>
