<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bebida extends Model
{
    use HasFactory;


    protected $fillable=[
        'bebidaId',
        'precioBebida',
        'stockBebida',
        'imagenBebida'
    ];
    protected $primaryKey = 'bebidaId';
    public $incrementing=false;

}
