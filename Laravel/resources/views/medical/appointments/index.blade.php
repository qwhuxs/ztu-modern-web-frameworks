@extends('medical.layout')

@section('title', 'Прийоми')

@section('content')
    <h1>Прийоми</h1>
    <a class="btn" href="{{ route('appointments.create') }}">Додати прийом</a>

    <form method="GET" class="filters">
        <div>
            <label for="id">ID</label>
            <input id="id" type="number" min="1" name="id" value="{{ request('id') }}">
        </div>
        <div>
            <label for="patient_id">Пацієнт</label>
            <select id="patient_id" name="patient_id">
                <option value="">Усі</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" @selected(request('patient_id') == $patient->id)>{{ $patient->last_name }} {{ $patient->first_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="doctor_id">Лікар</label>
            <select id="doctor_id" name="doctor_id">
                <option value="">Усі</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(request('doctor_id') == $doctor->id)>{{ $doctor->last_name }} {{ $doctor->first_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="appointment_at">Дата прийому</label>
            <input id="appointment_at" type="date" name="appointment_at" value="{{ request('appointment_at') }}">
        </div>
        <div>
            <label for="complaints">Скарги</label>
            <input id="complaints" name="complaints" value="{{ request('complaints') }}">
        </div>
        @include('medical._timestamps_filters')
        @include('medical._filter_actions')
    </form>

    <table>
        <tr><th>ID</th><th>Дата і час</th><th>Пацієнт</th><th>Лікар</th><th>Скарги</th><th>Дії</th></tr>
        @forelse ($appointments as $appointment)
            <tr>
                <td>{{ $appointment->id }}</td>
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
            <tr><td colspan="6">Записів не знайдено.</td></tr>
        @endforelse
    </table>

    @include('medical._pagination', ['items' => $appointments])
@endsection