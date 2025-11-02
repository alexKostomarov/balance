<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Illuminate\Database\Eloquent\Relations\HasOne;
use Ramsey\Uuid\Uuid;

class User extends Model
{
    use HasFactory;

    protected $keyType = 'string';  // UUID — это строка
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
    ];

    protected static function boot()
    {
        parent::boot();

        // обработчик события creating
        static::creating(function ($model) {

            if (empty($model->id)) {
                $model->id = Uuid::uuid4()->toString();
            }
        });
    }

    public function balance(): hasOne
    {
        return $this->hasOne(Balance::class);
    }



}
