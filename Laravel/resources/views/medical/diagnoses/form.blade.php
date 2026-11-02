@extends('medical.layout')

@section('title', 'Діагноз')

@section('content')
    <h1>{{ $diagnosis->exists ? 'Редагування' : 'Новий запис' }}: Діагноз</h1>

    <form method="POST" action="{{ $diagnosis->exists ? route('diagnoses.update', $diagnosis) : route('diagnoses.store') }}">
        @csrf
        @if ($diagnosis->exists)
            @method('PUT')
        @endif

        <label for="appointment_id">Прийом</label>
        <select id="appointment_id" name="appointment_id" required>
            <option value="">Виберіть прийом</option>
            @foreach ($appointments as $appointment)
                <option value="{{ $appointment->id }}" @selected(old('appointment_id', $diagnosis->appointment_id) == $appointment->id)>
                    {{ $appointment->appointment_at->format('d.m.Y H:i') }}, пацієнт: {{ $appointment->patient->last_name }}, лікар: {{ $appointment->doctor->last_name }}
                </option>
            @endforeach
        </select>

        <label for="name">Назва діагнозу</label>
        <input id="name" name="name" maxlength="150" required value="{{ old('name', $diagnosis->name) }}">

        <label for="description">Опис</label>
        <textarea id="description" name="description" rows="3">{{ old('description', $diagnosis->description) }}</textarea>

        <p><button class="btn" type="submit">Зберегти</button></p>
    </form>
@endsection