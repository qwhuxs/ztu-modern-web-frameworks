<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель таблиці. Клас Model уже реалізує інтерфейс JsonSerializable,
 * а метод jsonSerialize() нижче визначає, які саме дані побачить фронтенд.
 */
class Treatment extends Model
{
    /**
     * Поля, які можна заповнювати масово через create() і update().
     *
     * @var string[]
     */
    protected $fillable = ['diagnosis_id', 'prescription', 'start_date', 'end_date'];

    /**
     * Перетворення типів полів (дати стають об'єктами Carbon).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Повертає діагноз, за яким призначено лікування.
     *
     * @return BelongsTo
     */
    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class);
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
            'diagnosis' => $this->diagnosis ? [
                'id' => $this->diagnosis->id,
                'name' => $this->diagnosis->name,
            ] : null,
            'prescription' => $this->prescription,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
        ];
    }
}