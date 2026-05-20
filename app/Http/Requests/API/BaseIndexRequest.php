<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Base query rules for index/list endpoints.
     *
     * Child request can override allowed sortable fields.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:100',
            'status' => 'sometimes|string|max:50',
            'order_by' => 'sometimes|in:'.implode(',', $this->allowedSortFields()),
            'dir' => 'sometimes|in:asc,desc',
        ];
    }

    /**
     * Allowed sortable fields per resource.
     *
     * @return array<int, string>
     */
    abstract protected function allowedSortFields(): array;
}
