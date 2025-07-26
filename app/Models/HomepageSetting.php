<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    protected $fillable = [
    'hero_headline', 'hero_tagline', 'hero_background', 'profil_image'
];
}
