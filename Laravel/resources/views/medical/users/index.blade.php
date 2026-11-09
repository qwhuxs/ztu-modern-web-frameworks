@extends('medical.layout')

@section('title', 'Користувачі')

@section('content')
    <h1>Користувачі</h1>

    <table>
        <tr><th>ID</th><th>Ім'я</th><th>Пошта</th><th>Роль</th></tr>
        @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <form method="POST" action="{{ route('users.update', $user) }}" class="actions">
                        @csrf
                        @method('PUT')
                        <select name="role" @disabled($user->is(auth()->user()))>
                            @foreach (array_keys(\App\Models\User::ROLE_LEVELS) as $role)
                                <option value="{{ $role }}" @selected($user->role === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                        @unless ($user->is(auth()->user()))
                            <button class="btn" type="submit">Зберегти</button>
                        @endunless
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
@endsection