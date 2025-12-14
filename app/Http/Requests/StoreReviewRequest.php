<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Booking;

class StoreReviewRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'apartment_id' => 'required|exists:apartments,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->user() && $this->apartment_id && !$this->hasCompletedBooking()) {
                $validator->errors()->add('apartment_id', 'You can only review apartments you have completed bookings for.');
            }
        });
    }

    /**
     * Check if the user has a completed booking for this apartment
     */
    private function hasCompletedBooking(): bool
    {
        if (!$this->user() || !$this->apartment_id) {
            return false;
        }

        return Booking::where('user_id', $this->user()->id)
            ->where('apartment_id', $this->apartment_id)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'apartment_id.required' => 'Apartment is required',
            'apartment_id.exists' => 'Selected apartment does not exist',
            'rating.required' => 'Rating is required',
            'rating.integer' => 'Rating must be an integer',
            'rating.min' => 'Rating must be at least 1',
            'rating.max' => 'Rating must be no more than 5',
            'comment.max' => 'Comment may not be greater than 1000 characters'
        ];
    }
}