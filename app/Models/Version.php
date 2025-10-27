<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Version extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Mendefinisikan relasi bahwa satu Version memiliki banyak EmployeeHistory.
     */
    public function history(): HasMany
    {
        return $this->hasMany(EmployeeHistory::class);
    }
}