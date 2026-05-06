<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'requests' => 'nullable|string|max:1000',
            
            // For Cart booking
            'cart_ids' => 'nullable|array',
            'cart_ids.*' => 'exists:carts,id',
            
            // For Direct booking
            'room_id' => 'required_without:cart_ids|exists:rooms,id',
            // Chấp nhận cả tên cũ và mới
            'checkin' => 'required_without_all:cart_ids,checkin_date|date|after_or_equal:today',
            'checkin_date' => 'required_without_all:cart_ids,checkin|date|after_or_equal:today',
            'checkout' => 'required_without_all:cart_ids,checkout_date|date|after:checkin|after:checkin_date',
            'checkout_date' => 'required_without_all:cart_ids,checkout|date|after:checkin|after:checkin_date',
            'guest_number' => 'required_without_all:cart_ids,number_of_guests|integer|min:1',
            'number_of_guests' => 'required_without_all:cart_ids,guest_number|integer|min:1',
            
            'coupon_code' => 'nullable|string'
        ];
    }
}
