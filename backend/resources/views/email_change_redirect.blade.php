<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presmerovanie...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #42b883;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            color: #2c3e50;
            margin: 0;
        }
        p {
            color: #666;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h2>Presmerovanie...</h2>
        <p>Prosím počkajte, budete presmerovaní na prihlasovaciu stránku.</p>
    </div>

    <script>
        // Clear all authentication data from localStorage and sessionStorage
        localStorage.clear();
        sessionStorage.clear();

        // Also try to clear specific items
        localStorage.removeItem('token');
        localStorage.removeItem('user');

        // Clear all cookies
        document.cookie.split(";").forEach(function(c) {
            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
        });

        // Wait to ensure storage is cleared and smooth transition, then redirect
        setTimeout(function() {
            window.location.replace('{{ $redirectUrl }}');
        }, 1500);
    </script>
</body>
</html>
