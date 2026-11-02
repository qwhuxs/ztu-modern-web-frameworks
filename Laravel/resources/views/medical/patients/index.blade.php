@extends('medical.layout')

@section('title', 'Пацієнти')

@section('content')
    <h1>Пацієнти</h1>
    <a class="btn" href="{{ route('patients.create') }}">Додати пацієнта</a>

    <table>
        <tr><th>Прізвище та ім'я</th><th>Дата народження</th><th>Телефон</th><th>Дії</th></tr>
        @forelse ($patients as $patient)
            <tr>
                <td>{{ $patient->last_name }} {{ $patient->first_name }}</td>
                <td>{{ $patient->birth_date->format('d.m.Y') }}</td>
                <td>{{ $patient->phone }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('patients.edit', $patient) }}">Змінити</a>
                    <form method="POST" action="{{ route('patients.destroy', $patient) }}">
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