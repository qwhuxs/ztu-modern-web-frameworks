@extends('medical.layout')

@section('title', 'Лікарі')

@section('content')
    <h1>Лікарі</h1>
    @if (auth()->user()?->hasRole('manager'))
        <a class="btn" href="{{ route('doctors.create') }}">Додати лікаря</a>
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
            <label for="specialty">Спеціальність</label>
            <input id="specialty" name="specialty" value="{{ request('specialty') }}">
        </div>
        <div>
            <label for="phone">Телефон</label>
            <input id="phone" name="phone" value="{{ request('phone') }}">
        </div>
        @include('medical._timestamps_filters')
        @include('medical._filter_actions')
    </form>

    <table>
        <tr><th>ID</th><th>Прізвище та ім'я</th><th>Спеціальність</th><th>Телефон</th>@if (auth()->user()?->hasRole('manager'))<th>Дії</th>@endif</tr>
        @forelse ($doctors as $doctor)
            <tr>
                <td>{{ $doctor->id }}</td>
                <td>{{ $doctor->last_name }} {{ $doctor->first_name }}</td>
                <td>{{ $doctor->specialty }}</td>
                <td>{{ $doctor->phone }}</td>
                <td class="actions" @if (!auth()->user()->hasRole('manager')) hidden @endif>
                    @if (auth()->user()->hasRole('manager'))
                        <a class="btn" href="{{ route('doctors.edit', $doctor) }}">Змінити</a>
                    @endif
                    @if (auth()->user()->hasRole('admin'))
                        <form method="POST" action="{{ route('doctors.destroy', $doctor) }}">
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

    @include('medical._pagination', ['items' => $doctors])
@endsection