<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>
    <h1>Login</h1>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/login') }}">
        @csrf

        <div>
            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus>
        </div>

        <div>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
        </div>



        <div>
            <button type="submit">
                Login
            </button>
        </div>
    </form>
</body>

</html>
