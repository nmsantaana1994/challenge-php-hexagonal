<?php

namespace App\Infrastructure\Http\Requests;

use App\Application\DTOs\Gif\GetGifByIdInputDto;
use Illuminate\Foundation\Http\FormRequest;

class GetGifByIdRequest extends FormRequest
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
            'id' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
        ]);
    }

    public function toDto(): GetGifByIdInputDto
    {
        return new GetGifByIdInputDto(
            gifId: (string) $this->route('id'),
        );
    }
}
