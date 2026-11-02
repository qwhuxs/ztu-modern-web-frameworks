<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = ['first_name', 'last_name', 'birth_date', 'phone'];

    protected $casts = ['birth_date' => 'date'];

    /**
     * Повертає прийоми цього запису (зв'язок один до багатьох).
     *
     * @return HasMany
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}