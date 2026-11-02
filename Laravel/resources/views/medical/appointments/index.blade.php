@extends('medical.layout')

@section('title', 'Прийоми')

@section('content')
    <h1>Прийоми</h1>
    <a class="btn" href="{{ route('appointments.create') }}">Додати прийом</a>

    <table>
        <tr><th>Дата і час</th><th>Пацієнт</th><th>Лікар</th><th>Скарги</th><th>Дії</th></tr>
        @forelse ($appointments as $appointment)
            <tr>
                <td>{{ $appointment->appointment_at->format('d.m.Y H:i') }}</td>
                <td>{{ $appointment->patient->last_name }} {{ $appointment->patient->first_name }}</td>
                <td>{{ $appointment->doctor->last_name }} {{ $appointment->doctor->first_name }}</td>
                <td>{{ $appointment->complaints }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('appointments.edit', $appointment) }}">Змінити</a>
                    <form method="POST" action="{{ route('appointments.destroy', $appointment) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Видалити</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Записів ще немає.</td></tr>
        @endforelse
    </table>
@endsection