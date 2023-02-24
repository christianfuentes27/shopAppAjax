<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Size;

class Clothes extends Model
{
    use HasFactory;
    
    protected $table = 'clothes';
    protected $fillable = ['name', 'price', 'thumbnail'];
    
    public function sizes() {
        return $this->belongsToMany(Size::class);
    }
    
    public function images() {
        return $this->hasMany('App\Models\Image', 'idclothes');
    }
}
