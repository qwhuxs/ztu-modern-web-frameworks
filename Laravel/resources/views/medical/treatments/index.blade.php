@extends('medical.layout')

@section('title', 'Лікування')

@section('content')
    <h1>Лікування</h1>
    <a class="btn" href="{{ route('treatments.create') }}">Додати лікування</a>

    <table>
        <tr><th>Призначення</th><th>Діагноз</th><th>Пацієнт</th><th>Початок</th><th>Завершення</th><th>Дії</th></tr>
        @forelse ($treatments as $treatment)
            <tr>
                <td>{{ $treatment->prescription }}</td>
                <td>{{ $treatment->diagnosis->name }}</td>
                <td>{{ $treatment->diagnosis->appointment->patient->last_name }}</td>
                <td>{{ $treatment->start_date->format('d.m.Y') }}</td>
                <td>{{ $treatment->end_date?->format('d.m.Y') }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('treatments.edit', $treatment) }}">Змінити</a>
                    <form method="POST" action="{{ route('treatments.destroy', $treatment) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Видалити</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">Записів ще немає.</td></tr>
        @endforelse
    </table>
@endsection