<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'description', 
        'amount', 
        'type', 
        'date', 
        'category_id', 
        'user_id'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2'
    ];

    //RELATIONS
    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    //Filter
    public function scopeFilter($query, $filters)
    {
        // Filter by multiple categories
        if (isset($filters['category_ids'])) {
           
            //wait an array or string ids separated by coma
            $ids = is_array($filters['category_ids']) 
                ? $filters['category_ids'] 
                : explode(',', $filters['category_ids']);
            $query->whereIn('category_id', $ids);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (isset($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (isset($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        // ordering
        if (isset($filters['sort_by'])) {
            $order = $filters['order'] ?? 'desc';
            $query->orderBy($filters['sort_by'], $order);
        } else {
            $query->orderBy('date', 'desc');
        }

        return $query;
    }

}
