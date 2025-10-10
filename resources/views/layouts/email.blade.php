<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DriveLink')</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
        }
        
        .header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            text-align: center;
            padding: 20px;
        }
        
        .content {
            padding: 30px;
        }
        
        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
            }
            
            .content {
                padding: 20px !important;
            }
        }
        
        @yield('styles')
    </style>
</head>
<body>
    @yield('content')
</body>
</html>