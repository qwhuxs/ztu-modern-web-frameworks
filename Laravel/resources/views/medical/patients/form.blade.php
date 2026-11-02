@extends('medical.layout')

@section('title', 'Пацієнт')

@section('content')
    <h1>{{ $patient->exists ? 'Редагування' : 'Новий запис' }}: Пацієнт</h1>

    <form method="POST" action="{{ $patient->exists ? route('patients.update', $patient) : route('patients.store') }}">
        @csrf
        @if ($patient->exists)
            @method('PUT')
        @endif

        <label for="last_name">Прізвище</label>
        <input id="last_name" name="last_name" maxlength="100" required value="{{ old('last_name', $patient->last_name) }}">

        <label for="first_name">Ім'я</label>
        <input id="first_name" name="first_name" maxlength="100" required value="{{ old('first_name', $patient->first_name) }}">

        <label for="birth_date">Дата народження</label>
        <input id="birth_date" type="date" name="birth_date" required value="{{ old('birth_date', $patient->birth_date?->format('Y-m-d')) }}">

        <label for="phone">Телефон</label>
        <input id="phone" name="phone" maxlength="20" required value="{{ old('phone', $patient->phone) }}">

        <p><button class="btn" type="submit">Зберегти</button></p>
    </form>
@endsection