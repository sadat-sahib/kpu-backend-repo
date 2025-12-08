
{{-- 1th  --}}
{{-- <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>یادآوری تاریخ برگرداندن کتاب</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; margin:20px 0;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#2c3e50; padding:20px; text-align:center;">
                            <h1 style="color:white; margin:0; font-family:Tahoma,Arial,sans-serif; font-size:24px;">یادآوری کتابخانه</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding:30px; font-family:Tahoma,Arial,sans-serif; line-height:1.6;">
                            <table width="100%" dir="rtl" style="text-align:right;">
                                <tr>
                                    <td>
                                        <p>کاربر محترم <strong>{{ $name }}</strong> عزیز,</p>
                                        
                                        <table width="100%" style="background-color:#e8f4fd; padding:20px; margin:20px 0; border:1px solid #3498db;">
                                            <tr>
                                                <td>
                                                    <p>این یک یادآوری است که کتاب قرض گرفته شده:</p>
                                                    <h3 style="color:#2c3e50; margin:10px 0;">«{{ $book }}»</h3>
                                                    <p>تا تاریخ <strong style="color:#e74c3c;">{{ $dueDate }}</strong> باید برگردانده شود.</p>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <p>لطفاً کتاب را به کتابخانه برگردانید تا از جریمه احتمالی جلوگیری کنید.</p>
                                        
                                        <!-- Footer -->
                                        <table width="100%" style="margin-top:30px; padding-top:20px; border-top:1px solid #ddd;">
                                            <tr>
                                                <td style="text-align:center; color:#666;">
                                                    <p>با تشکر،<br>کتابخانه دیجیتالی دانشگاه</p>
                                                    <p>KPU Digital Library</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html> --}}


 {{-- 2th --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>یادآوری تاریخ برگرداندن کتاب</title>
    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .book-info {
            background-color: #e8f4fd;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #3498db;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
        }
        /* RTL specific styles */
        .rtl-text {
            direction: rtl;
            text-align: right;
        }
        .book-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        .due-date {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0; font-size:24px;">یادآوری کتابخانه</h1>
        </div>
        
        <div class="content">
            <div class="rtl-text">
                <p>کاربر محترم <strong>{{ $name }}</strong> عزیز,</p>
                
                <div class="book-info rtl-text">
                    <p>این یک یادآوری است که کتاب قرض گرفته شده:</p>
                    <div class="book-title">«{{ $book }}»</div>
                    <p>تا تاریخ <span class="due-date">{{ $dueDate }}</span> باید برگردانده شود.</p>
                </div>
                
                <p>لطفاً کتاب را به تاریخ ذکر شده به کتابخانه پوهنتون برگردانید تا از جریمه احتمالی جلوگیری کنید.</p>
                
                <div class="footer rtl-text">
                    <p>با تشکر،<br>کتابخانه دیجیتالی پوهنتون پولی تخنیک کابل</p>
                    <p>KPU Digital Library</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
