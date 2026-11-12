@extends('medical.layout')

@section('title', 'Лікування')

@section('content')
    <h1>Лікування</h1>
    @if (auth()->user()?->hasRole('manager'))
        <a class="btn" href="{{ route('treatments.create') }}">Додати лікування</a>
    @endif

    <form method="GET" class="filters">
        <div>
            <label for="id">ID</label>
            <input id="id" type="number" min="1" name="id" value="{{ request('id') }}">
        </div>
        <div>
            <label for="diagnosis_id">Діагноз</label>
            <select id="diagnosis_id" name="diagnosis_id">
                <option value="">Усі</option>
                @foreach ($diagnoses as $diagnosis)
                    <option value="{{ $diagnosis->id }}" @selected(request('diagnosis_id') == $diagnosis->id)>{{ $diagnosis->name }}, {{ $diagnosis->appointment->patient->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="prescription">Призначення</label>
            <input id="prescription" name="prescription" value="{{ request('prescription') }}">
        </div>
        <div>
            <label for="start_date">Дата початку</label>
            <input id="start_date" type="date" name="start_date" value="{{ request('start_date') }}">
        </div>
        <div>
            <label for="end_date">Дата завершення</label>
            <input id="end_date" type="date" name="end_date" value="{{ request('end_date') }}">
        </div>
        @include('medical._timestamps_filters')
        @include('medical._filter_actions')
    </form>

    <table>
        <tr><th>ID</th><th>Призначення</th><th>Діагноз</th><th>Пацієнт</th><th>Початок</th><th>Завершення</th>@if (auth()->user()?->hasRole('manager'))<th>Дії</th>@endif</tr>
        @forelse ($treatments as $treatment)
            <tr>
                <td>{{ $treatment->id }}</td>
                <td>{{ $treatment->prescription }}</td>
                <td>{{ $treatment->diagnosis->name }}</td>
                <td>{{ $treatment->diagnosis->appointment->patient->last_name }}</td>
                <td>{{ $treatment->start_date->format('d.m.Y') }}</td>
                <td>{{ $treatment->end_date?->format('d.m.Y') }}</td>
                <td class="actions" @if (!auth()->user()->hasRole('manager')) hidden @endif>
                    @if (auth()->user()->hasRole('manager'))
                        <a class="btn" href="{{ route('treatments.edit', $treatment) }}">Змінити</a>
                    @endif
                    @if (auth()->user()->hasRole('admin'))
                        <form method="POST" action="{{ route('treatments.destroy', $treatment) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit">Видалити</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7">Записів не знайдено.</td></tr>
        @endforelse
    </table>

    @include('medical._pagination', ['items' => $treatments])
@endsection