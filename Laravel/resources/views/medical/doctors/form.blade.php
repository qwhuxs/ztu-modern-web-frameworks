@extends('medical.layout')

@section('title', 'Лікар')

@section('content')
    <h1>{{ $doctor->exists ? 'Редагування' : 'Новий запис' }}: Лікар</h1>

    <form method="POST" action="{{ $doctor->exists ? route('doctors.update', $doctor) : route('doctors.store') }}">
        @csrf
        @if ($doctor->exists)
            @method('PUT')
        @endif

        <label for="last_name">Прізвище</label>
        <input id="last_name" name="last_name" maxlength="100" required value="{{ old('last_name', $doctor->last_name) }}">

        <label for="first_name">Ім'я</label>
        <input id="first_name" name="first_name" maxlength="100" required value="{{ old('first_name', $doctor->first_name) }}">

        <label for="specialty">Спеціальність</label>
        <input id="specialty" name="specialty" maxlength="100" required value="{{ old('specialty', $doctor->specialty) }}">

        <label for="phone">Телефон</label>
        <input id="phone" name="phone" maxlength="20" required value="{{ old('phone', $doctor->phone) }}">

        <p><button class="btn" type="submit">Зберегти</button></p>
    </form>
@endsection