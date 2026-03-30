<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Mendoza Academy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f3f4f6;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 { color: #1a202c; margin-bottom: 5px; }
        p  { color: #6b7280; margin-bottom: 30px; }
        .logout-btn {
            background-color: #e53e3e;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        .logout-btn:hover { background-color: #c53030; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Welcome, {{ auth()->user()->name }}!</h1>
        <p>You are logged in as a <strong>Teacher</strong>.</p>

        <!-- Logout Form -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">Log Out</button>
        </form>
    </div>
</body>
</html>