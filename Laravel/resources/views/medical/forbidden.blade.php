@extends('medical.layout')

@section('title', 'Доступ заборонено')

@section('content')
    <h1>403: доступ заборонено</h1>
    <p>Ваша роль не дозволяє виконати цю дію.</p>
    <p>Client може переглядати записи, Manager може ще додавати й змінювати, а Admin може ще видаляти записи й керувати ролями.</p>
    <a class="btn" href="{{ url()->previous() }}">Назад</a>
@endsection