@extends('medical.layout')

@section('title', 'Діагнози')

@section('content')
    <h1>Діагнози</h1>
    <a class="btn" href="{{ route('diagnoses.create') }}">Додати діагноз</a>

    <form method="GET" class="filters">
        <div>
            <label for="id">ID</label>
            <input id="id" type="number" min="1" name="id" value="{{ request('id') }}">
        </div>
        <div>
            <label for="appointment_id">Прийом</label>
            <select id="appointment_id" name="appointment_id">
                <option value="">Усі</option>
                @foreach ($appointments as $appointment)
                    <option value="{{ $appointment->id }}" @selected(request('appointment_id') == $appointment->id)>{{ $appointment->appointment_at->format('d.m.Y H:i') }}, {{ $appointment->patient->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="name">Назва</label>
            <input id="name" name="name" value="{{ request('name') }}">
        </div>
        <div>
            <label for="description">Опис</label>
            <input id="description" name="description" value="{{ request('description') }}">
        </div>
        @include('medical._timestamps_filters')
        @include('medical._filter_actions')
    </form>

    <table>
        <tr><th>ID</th><th>Назва</th><th>Опис</th><th>Прийом</th><th>Дії</th></tr>
        @forelse ($diagnoses as $diagnosis)
            <tr>
                <td>{{ $diagnosis->id }}</td>
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
            <tr><td colspan="5">Записів не знайдено.</td></tr>
        @endforelse
    </table>

    @include('medical._pagination', ['items' => $diagnoses])
@endsection