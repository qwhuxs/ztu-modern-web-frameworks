@extends('medical.layout')

@section('title', 'Прийом')

@section('content')
    <h1>{{ $appointment->exists ? 'Редагування' : 'Новий запис' }}: Прийом</h1>

    <form method="POST" action="{{ $appointment->exists ? route('appointments.update', $appointment) : route('appointments.store') }}">
        @csrf
        @if ($appointment->exists)
            @method('PUT')
        @endif

        <label for="patient_id">Пацієнт</label>
        <select id="patient_id" name="patient_id" required>
            <option value="">Виберіть пацієнта</option>
            @foreach ($patients as $patient)
                <option value="{{ $patient->id }}" @selected(old('patient_id', $appointment->patient_id) == $patient->id)>
                    {{ $patient->last_name }} {{ $patient->first_name }}
                </option>
            @endforeach
        </select>

        <label for="doctor_id">Лікар</label>
        <select id="doctor_id" name="doctor_id" required>
            <option value="">Виберіть лікаря</option>
            @foreach ($doctors as $doctor)
                <option value="{{ $doctor->id }}" @selected(old('doctor_id', $appointment->doctor_id) == $doctor->id)>
                    {{ $doctor->last_name }} {{ $doctor->first_name }}, {{ $doctor->specialty }}
                </option>
            @endforeach
        </select>

        <label for="appointment_at">Дата і час</label>
        <input id="appointment_at" type="datetime-local" name="appointment_at" required value="{{ old('appointment_at', $appointment->appointment_at?->format('Y-m-d\TH:i')) }}">

        <label for="complaints">Скарги</label>
        <textarea id="complaints" name="complaints" rows="3">{{ old('complaints', $appointment->complaints) }}</textarea>

        <p><button class="btn" type="submit">Зберегти</button></p>
    </form>
@endsection