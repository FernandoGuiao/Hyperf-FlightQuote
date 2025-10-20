<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class FlightPriceFormRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quoteId' => 'string',
            'flightId' => 'string',
            'fareId' => 'string',
        ];
    }


}
