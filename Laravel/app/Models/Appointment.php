<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    protected $fillable = ['patient_id', 'doctor_id', 'appointment_at', 'complaints'];

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
}