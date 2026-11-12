<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель таблиці. Клас Model уже реалізує інтерфейс JsonSerializable,
 * а метод jsonSerialize() нижче визначає, які саме дані побачить фронтенд.
 */
class Appointment extends Model
{
    /**
     * Поля, які можна заповнювати масово через create() і update().
     *
     * @var string[]
     */
    protected $fillable = ['patient_id', 'doctor_id', 'appointment_at', 'complaints'];

    /**
     * Перетворення типів полів (дати стають об'єктами Carbon).
     *
     * @var array<string, string>
     */
    protected $casts = ['appointment_at' => 'datetime'];

    /**
     * Повертає пацієнта цього прийому.
     *
     * @return BelongsTo
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Повертає лікаря цього прийому.
     *
     * @return BelongsTo
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Повертає діагнози, поставлені на цьому прийомі.
     *
     * @return HasMany
     */
    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
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
            'patient' => $this->patient ? [
                'id' => $this->patient->id,
                'full_name' => $this->patient->last_name.' '.$this->patient->first_name,
            ] : null,
            'doctor' => $this->doctor ? [
                'id' => $this->doctor->id,
                'full_name' => $this->doctor->last_name.' '.$this->doctor->first_name,
                'specialty' => $this->doctor->specialty,
            ] : null,
            'appointment_at' => $this->appointment_at?->format('Y-m-d H:i'),
            'complaints' => $this->complaints,
        ];
    }
}