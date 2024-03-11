<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class EmbedCollection extends Model
{
    use HasFactory;

    public $fillable = ['name', 'meta_data'];
    public $incrementing = false;
    public $keyType = "string";

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

    public function toArray()
    {
        $this->meta_data = json_decode($this->meta_data);
        return $this;
    }
}
