<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель таблиці. Клас Model уже реалізує інтерфейс JsonSerializable,
 * а метод jsonSerialize() нижче визначає, які саме дані побачить фронтенд.
 */
class Doctor extends Model
{
    /**
     * Поля, які можна заповнювати масово через create() і update().
     *
     * @var string[]
     */
    protected $fillable = ['first_name', 'last_name', 'specialty', 'phone'];

    /**
     * Повертає прийоми лікаря (зв'язок один до багатьох).
     *
     * @return HasMany
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Повертає дані запису у вигляді масиву для JSON-відповіді.
     * Викликається автоматично, коли модель передається в response()->json().
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'specialty' => $this->specialty,
            'phone' => $this->phone,
        ];
    }
}