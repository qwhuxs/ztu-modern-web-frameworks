<div>
    <label for="itemsPerPage">Записів на сторінці</label>
    <select id="itemsPerPage" name="itemsPerPage" onchange="this.form.submit()">
        @foreach ([5, 10, 20, 50] as $option)
            <option value="{{ $option }}" @selected((int) request('itemsPerPage', 10) === $option)>{{ $option }}</option>
        @endforeach
    </select>
</div>
<div class="filter-actions">
    <button class="btn" type="submit">Знайти</button>
    <a class="btn" href="{{ url()->current() }}">Скинути</a>
</div>