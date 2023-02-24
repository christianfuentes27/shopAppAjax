<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Clothes;

class Size extends Model
{
    use HasFactory;
    
    protected $table = 'size';
    protected $fillable = ['name'];
    
    public function clothes() {
        return $this->belongsToMany(Clothes::class);
    }
    
}
