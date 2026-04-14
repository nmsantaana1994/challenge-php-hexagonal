<?php

namespace App\Infrastructure\Http\Requests;

use App\Application\DTOs\Gif\SearchGifsInputDto;
use Illuminate\Foundation\Http\FormRequest;

class SearchGifsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'offset' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function toDto(): SearchGifsInputDto
    {
        return new SearchGifsInputDto(
            query: (string) $this->string('query'),
            limit: $this->has('limit') ? $this->integer('limit') : null,
            offset: $this->has('offset') ? $this->integer('offset') : null,
        );
    }
}
