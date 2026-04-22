<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestmentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'property_price'      => ['required', 'integer', 'min:1'],
            'monthly_rent'        => ['required', 'integer', 'min:0'],
            'occupancy_rate'      => ['required', 'numeric', 'between:0,1'],
            'annual_expenses'     => ['required', 'integer', 'min:0'],
            'loan_amount'         => ['required', 'integer', 'min:0'],
            'interest_rate'       => ['required', 'numeric', 'min:0', 'max:100'],
            'loan_years'          => ['required', 'integer', 'between:1,50'],
            'repair_reserve_rate' => ['required', 'numeric', 'between:0,1'],
        ];
    }
}
