<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    @auth
    <p>Logged in</p>
    <form action="/logout" method="POST">
        @csrf
        <button>Logout</button>
    </form>

    @else
    <div>
        <h2>Register</h2>
        <form action="/register" method="POST">
            @csrf
            <input name="name" type="text" placeholder = "name" id="">
            <input name="email" type="text" placeholder = "email" id="">
            <input name="password" type="password" placeholder = "password" id="">
            <button>Register</button>
        </form>
    </div>
    <div>
        <h2>Login</h2>
        <form action="/login" method="POST">
            @csrf
            <input name="loginname" type="text" placeholder = "name" id="">
            <input name="loginpassword" type="password" placeholder = "password" id="">
            <button>Login</button>
        </form>
    </div>

    @endauth

</body>
</html>