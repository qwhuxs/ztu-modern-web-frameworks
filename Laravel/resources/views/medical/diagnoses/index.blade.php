@extends('medical.layout')

@section('title', 'Діагнози')

@section('content')
    <h1>Діагнози</h1>
    <a class="btn" href="{{ route('diagnoses.create') }}">Додати діагноз</a>

    <table>
        <tr><th>Назва</th><th>Опис</th><th>Прийом</th><th>Дії</th></tr>
        @forelse ($diagnoses as $diagnosis)
            <tr>
                <td>{{ $diagnosis->name }}</td>
                <td>{{ $diagnosis->description }}</td>
                <td>{{ $diagnosis->appointment->appointment_at->format('d.m.Y H:i') }}, {{ $diagnosis->appointment->patient->last_name }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('diagnoses.edit', $diagnosis) }}">Змінити</a>
                    <form method="POST" action="{{ route('diagnoses.destroy', $diagnosis) }}">
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