<?php $title = '500 Server Error'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — CertiMe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f8f9fa; color: #333; }
        .error-box { text-align: center; padding: 3rem; }
        .error-box .icon { font-size: 96px; color: #dc3545; }
        .error-box h1 { font-size: 5rem; font-weight: 700; color: #adb5bd; margin: 0.5rem 0; }
        .error-box h4 { font-weight: 600; margin-bottom: 1rem; }
        .error-box p { color: #6c757d; margin-bottom: 2rem; }
        .error-box a { display: inline-block; padding: 0.75rem 2rem; background: #6750a4; color: white; text-decoration: none; border-radius: 100px; font-weight: 500; }
        .error-box a:hover { background: #564494; }
    </style>
</head>
<body>
    <div class="error-box">
        <span class="material-symbols-rounded icon">error</span>
        <h1>500</h1>
        <h4>Something Went Wrong</h4>
        <p>We're experiencing a technical issue. Please try again later.</p>
        <a href="/">Go Home</a>
    </div>
</body>
</html>
