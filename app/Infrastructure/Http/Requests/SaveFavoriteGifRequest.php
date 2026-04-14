<?php

namespace App\Infrastructure\Http\Requests;

use App\Application\DTOs\Favorite\SaveFavoriteGifInputDto;
use Illuminate\Foundation\Http\FormRequest;

class SaveFavoriteGifRequest extends FormRequest
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
            'gif_id' => ['required', 'string'],
            'alias' => ['required', 'string', 'max:255'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function toDto(): SaveFavoriteGifInputDto
    {
        return new SaveFavoriteGifInputDto(
            userId: $this->integer('user_id'),
            gifId: (string) $this->string('gif_id'),
            alias: (string) $this->string('alias'),
        );
    }
}
