<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;



class Item extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'photo',
        'unique_code',
        'condition',
    ];

    // Mendefinisikan relasi "belongsTo" ke model Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
