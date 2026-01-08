<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|unique:students,email',
            'id_document_number' => 'required|string|max:50',
            'entity_type' => 'required|in:individual,company',
            'payment_method' => 'required|in:full,installments',
            'package_type' => 'required|in:pius-plus,pius-pro',
            'company_name' => 'required_if:entity_type,company|nullable|string|max:255',
            'vat_number' => 'required_if:entity_type,company|nullable|string|max:50',
            'company_address' => 'required_if:entity_type,company|nullable|string|max:255',
            'company_postal_code' => 'required_if:entity_type,company|nullable|string|max:20',
            'company_city' => 'required_if:entity_type,company|nullable|string|max:255',
            'company_country' => 'required_if:entity_type,company|nullable|string|max:255',
            'company_registration' => 'required_if:entity_type,company|nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Ime je obavezno.',
            'last_name.required' => 'Prezime je obavezno.',
            'email.required' => 'Email je obavezan.',
            'email.email' => 'Email nije valjan.',
            'email.unique' => 'Student sa ovim emailom već postoji.',
            'phone.required' => 'Telefon je obavezan.',
            'company_name.required_if' => 'Naziv firme je obavezan za pravna lica.',
            'vat_number.required_if' => 'PDV broj je obavezan za pravna lica.',
        ];
    }
}
