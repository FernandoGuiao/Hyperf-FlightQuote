<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class FlightBookFormRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'priceId' => 'string',
            'travelers' => 'array',
            'travelers.name' => 'string',
            'travelers.document' => 'string',
            'travelers.email' => 'email',
        ];
    }





}
