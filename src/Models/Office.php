<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $city
 * @property string $zip_code
 * @property string $country
 * @property string $email
 * @property string $phone
 * @property int $company_id
 * @property-read Company $company
 * @method static find($id)
 */
class Office extends Model
{
    protected $table = 'offices';

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isHeadOffice(): bool
    {
        return $this->company->headOffice->id === $this->id;
    }

    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->zip_code}, {$this->country}";
    }
}
