<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy - Marian House</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #222;
        }
        p {
            font-size: 16px;
            color: #444;
        }
    </style>
</head>
<body>
    <div class="container">
        {!! $policy->content !!}
    </div>
</body>
</html>
