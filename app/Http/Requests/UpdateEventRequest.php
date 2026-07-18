<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('events', 'slug')->ignore($event)],
            'location' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'starts_at' => ['required', 'date'],
            'budget_allocated' => ['nullable', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'participants_count' => ['nullable', 'integer', 'min:0'],
            'registration_enabled' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
