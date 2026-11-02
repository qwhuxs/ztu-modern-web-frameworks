<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Медична система</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f8; color: #1f2933; }
        nav { background: #0f766e; padding: 12px 24px; display: flex; flex-wrap: wrap; gap: 16px; }
        nav a { color: #fff; text-decoration: none; font-weight: bold; }
        main { max-width: 1000px; margin: 24px auto; padding: 24px; background: #fff; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #d9e2ec; padding: 8px; text-align: left; vertical-align: top; }
        label { display: block; margin-top: 14px; font-weight: bold; }
        input, select, textarea { width: 100%; max-width: 500px; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        .btn { display: inline-block; padding: 8px 14px; border: 0; border-radius: 4px; background: #0f766e; color: #fff; text-decoration: none; cursor: pointer; font-size: 14px; width: auto; }
        .btn-danger { background: #b42318; }
        .actions { display: flex; gap: 8px; }
        .error { background: #fee4e2; color: #912018; padding: 10px 14px; border-radius: 4px; margin: 12px 0; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ route('medical.home') }}">Головна</a>
        <a href="{{ route('patients.index') }}">Пацієнти</a>
        <a href="{{ route('doctors.index') }}">Лікарі</a>
        <a href="{{ route('appointments.index') }}">Прийоми</a>
        <a href="{{ route('diagnoses.index') }}">Діагнози</a>
        <a href="{{ route('treatments.index') }}">Лікування</a>
    </nav>
    <main>
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>