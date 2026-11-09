@extends('medical.layout')

@section('title', 'Вхід')

@section('content')
    <h1>Вхід</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">Пошта</label>
        <input id="email" type="email" name="email" required value="{{ old('email') }}">

        <label for="password">Пароль</label>
        <input id="password" type="password" name="password" required>

        <p><button class="btn" type="submit">Увійти</button></p>
    </form>

    <p>Немає акаунта? <a href="{{ route('register') }}">Зареєструватися</a></p>
@endsection