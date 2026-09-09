<!DOCTYPE html>
<html lang="en-IN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>@yield('title') | Service Bazzar</title>

    <meta name="description" content="Service Bazzar is a trusted local business directory helping users find nearby services, restaurants, healthcare, repairs, and professionals easily.">
    <meta name="robots" content="{{ $seoRobots ?? 'index, follow' }}">
    
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title')">

    <link rel="shortcut icon" href="https://www.servicebazzar.com/assets/images/logo/favicon_new.png" type="image/x-icon">
    <link rel="icon" href="https://www.servicebazzar.com/assets/images/logo/favicon_new.png" type="image/x-icon">

    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
