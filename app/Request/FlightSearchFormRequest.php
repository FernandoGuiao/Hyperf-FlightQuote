<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class FlightSearchFormRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origin' => 'string',
            'destination' => 'string',
            'departureDate' => 'date:Y-m-d',
            'returnDate' => 'date:Y-m-d,after:departureDate',
            'adults' => 'integer|min:1',
            'children' => 'integer|min:0',
            'infants' => 'integer|min:0',
            'tripType' => 'string|in:OW,RT'
        ];
    }


}
