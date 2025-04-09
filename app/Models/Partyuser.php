<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partyuser extends Model
{
    protected $table = 'party_users';
    // Specify fillable fields
    protected $fillable = [
        'party_type',
        'party_value',
        'applies_to',
        'reference_id',
        'is_active',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'address',
       
    ];
    /**
     * Get the images for the party user.
     */
    public function images()
    {
        return $this->hasMany(PartyUserImage::class, 'party_user_id');
    }

}
