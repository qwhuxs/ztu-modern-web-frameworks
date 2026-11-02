@extends('medical.layout')

@section('title', 'Лікарі')

@section('content')
    <h1>Лікарі</h1>
    <a class="btn" href="{{ route('doctors.create') }}">Додати лікаря</a>

    <table>
        <tr><th>Прізвище та ім'я</th><th>Спеціальність</th><th>Телефон</th><th>Дії</th></tr>
        @forelse ($doctors as $doctor)
            <tr>
                <td>{{ $doctor->last_name }} {{ $doctor->first_name }}</td>
                <td>{{ $doctor->specialty }}</td>
                <td>{{ $doctor->phone }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('doctors.edit', $doctor) }}">Змінити</a>
                    <form method="POST" action="{{ route('doctors.destroy', $doctor) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Видалити</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Записів ще немає.</td></tr>
        @endforelse
    </table>
@endsection