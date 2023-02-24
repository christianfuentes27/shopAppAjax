<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;
    
    protected $table = 'image';
    protected $fillable = ['name', 'idclothes'];
    
    public function clothes() {
        return $this->belongsTo('App\Models\Clothes', 'idclothes');
    }
    
    public function saveInStorage($photo, $idclothes) {
        $date = Carbon::parse(Carbon::now()->format('YmdHis'));
        $name = $date . $photo->getClientOriginalName();
        $photo->storeAs('public/images', $name);
        $this->saveInDB($photo, $idclothes, $name);
    }
    
    public function saveInDB($photo, $idclothes, $name) {
        $this->name = $name;
        $this->idclothes = $idclothes;
        $this->save();
    }
    
}
