<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TransactionsExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = $user->transactions()->with('category');

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
            'Type',
            'Date',
            'Category',
            'Created At',
        ];
    }
}