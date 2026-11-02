@extends('medical.layout')

@section('title', 'Лікування')

@section('content')
    <h1>{{ $treatment->exists ? 'Редагування' : 'Новий запис' }}: Лікування</h1>

    <form method="POST" action="{{ $treatment->exists ? route('treatments.update', $treatment) : route('treatments.store') }}">
        @csrf
        @if ($treatment->exists)
            @method('PUT')
        @endif

        <label for="diagnosis_id">Діагноз</label>
        <select id="diagnosis_id" name="diagnosis_id" required>
            <option value="">Виберіть діагноз</option>
            @foreach ($diagnoses as $diagnosis)
                <option value="{{ $diagnosis->id }}" @selected(old('diagnosis_id', $treatment->diagnosis_id) == $diagnosis->id)>
                    {{ $diagnosis->name }}, пацієнт: {{ $diagnosis->appointment->patient->last_name }}
                </option>
            @endforeach
        </select>

        <label for="prescription">Призначення</label>
        <textarea id="prescription" name="prescription" rows="3" required>{{ old('prescription', $treatment->prescription) }}</textarea>

        <label for="start_date">Дата початку</label>
        <input id="start_date" type="date" name="start_date" required value="{{ old('start_date', $treatment->start_date?->format('Y-m-d')) }}">

        <label for="end_date">Дата завершення</label>
        <input id="end_date" type="date" name="end_date" value="{{ old('end_date', $treatment->end_date?->format('Y-m-d')) }}">

        <p><button class="btn" type="submit">Зберегти</button></p>
    </form>
@endsection