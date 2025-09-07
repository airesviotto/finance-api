<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TransactionsExport implements FromCollection, WithHeadings
{
    protected $user;
    protected $filters;

    public function __construct(User $user, array $filters = [])
    {
        $this->filters = $filters;
        $this->user = $user;
    }

    public function collection()
    {

        $query = $this->user->transactions()->with('category');

        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('date', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('date', '<=', $this->filters['end_date']);
        }

        return $query->get()->map(function ($transaction) {
            return [
                'ID' => $transaction->id,
                'Description' => $transaction->description,
                'Amount' => $transaction->amount,
                'Original_Amount' => $transaction->original_amount,
                'Currency' => $transaction->currency,
                'Type' => $transaction->type,
                'Date' => $transaction->date->format('Y-m-d'),
                'Category' => $transaction->category ? $transaction->category->name : null,
                'Created At' => $transaction->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Description',
            'Amount',
            'Original_Amount',
            'Currency',
            'Type',
            'Date',
            'Category',
            'Created At',
        ];
    }
}