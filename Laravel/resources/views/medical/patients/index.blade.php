@extends('medical.layout')

@section('title', 'Пацієнти')

@section('content')
    <h1>Пацієнти</h1>
    @if (auth()->user()?->hasRole('manager'))
        <a class="btn" href="{{ route('patients.create') }}">Додати пацієнта</a>
    @endif

    <form method="GET" class="filters">
        <div>
            <label for="id">ID</label>
            <input id="id" type="number" min="1" name="id" value="{{ request('id') }}">
        </div>
        <div>
            <label for="last_name">Прізвище</label>
            <input id="last_name" name="last_name" value="{{ request('last_name') }}">
        </div>
        <div>
            <label for="first_name">Ім'я</label>
            <input id="first_name" name="first_name" value="{{ request('first_name') }}">
        </div>
        <div>
            <label for="birth_date">Дата народження</label>
            <input id="birth_date" type="date" name="birth_date" value="{{ request('birth_date') }}">
        </div>
        <div>
            <label for="phone">Телефон</label>
            <input id="phone" name="phone" value="{{ request('phone') }}">
        </div>
        @include('medical._timestamps_filters')
        @include('medical._filter_actions')
    </form>

    <table>
        <tr><th>ID</th><th>Прізвище та ім'я</th><th>Дата народження</th><th>Телефон</th>@if (auth()->user()?->hasRole('manager'))<th>Дії</th>@endif</tr>
        @forelse ($patients as $patient)
            <tr>
                <td>{{ $patient->id }}</td>
                <td>{{ $patient->last_name }} {{ $patient->first_name }}</td>
                <td>{{ $patient->birth_date->format('d.m.Y') }}</td>
                <td>{{ $patient->phone }}</td>
                <td class="actions" @if (!auth()->user()->hasRole('manager')) hidden @endif>
                    @if (auth()->user()->hasRole('manager'))
                        <a class="btn" href="{{ route('patients.edit', $patient) }}">Змінити</a>
                    @endif
                    @if (auth()->user()->hasRole('admin'))
                        <form method="POST" action="{{ route('patients.destroy', $patient) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit">Видалити</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Записів не знайдено.</td></tr>
        @endforelse
    </table>

    @include('medical._pagination', ['items' => $patients])
@endsection