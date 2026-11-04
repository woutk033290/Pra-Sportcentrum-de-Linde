<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isBeheerder() ?? false;
    }

    /** @return array<string,array<mixed>> */
    public function rules(): array
    {
        return [
            'naam' => ['required', 'string', 'max:255'], 'beschrijving' => ['nullable', 'string', 'max:5000'],
            'starttijd' => ['required', 'date_format:Y-m-d\\TH:i'], 'eindtijd' => ['required', 'date_format:Y-m-d\\TH:i', 'after:starttijd'],
            'maximaal_aantal_deelnemers' => ['required', 'integer', 'min:1', 'max:10000'],
            'trainer_id' => ['required', Rule::exists('gebruikers', 'id')->where('rol', 'trainer')->whereNull('verwijderd_op')],
            'lestype_id' => ['required', 'exists:lestypen,id'], 'locatie_id' => ['required', 'exists:locaties,id']];
    }
}
