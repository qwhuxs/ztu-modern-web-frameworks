<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель таблиці. Клас Model уже реалізує інтерфейс JsonSerializable,
 * а метод jsonSerialize() нижче визначає, які саме дані побачить фронтенд.
 */
class Diagnosis extends Model
{
    /**
     * Назва таблиці в базі даних.
     *
     * @var string
     */
    protected $table = 'diagnoses';

    /**
     * Поля, які можна заповнювати масово через create() і update().
     *
     * @var string[]
     */
    protected $fillable = ['appointment_id', 'name', 'description'];

    /**
     * Повертає прийом, на якому поставлено діагноз.
     *
     * @return BelongsTo
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Повертає лікування, призначені за цим діагнозом.
     *
     * @return HasMany
     */
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
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
            'appointment' => $this->appointment ? [
                'id' => $this->appointment->id,
                'appointment_at' => $this->appointment->appointment_at?->format('Y-m-d H:i'),
                'patient_id' => $this->appointment->patient_id,
            ] : null,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}