<?php

namespace App\Rest\Resources;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Lomkit\Rest\Http\Requests\MutateRequest;
use Lomkit\Rest\Http\Requests\RestRequest;

class CareBookingResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    public static $model = \App\Models\CareBooking::class;

    public function fields(RestRequest $request): array
    {
        return [
            'id',
            'scheduled_date',
            'start_time',
            'duration_hours',
            'care_type',
            'description',
            'preferred_carer_type',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public function relations(RestRequest $request): array
    {
        return [];
    }

    public function scopes(RestRequest $request): array
    {
        return [];
    }

    public function limits(RestRequest $request): array
    {
        return [
            10,
            25,
            50,
        ];
    }

    public function rules(RestRequest $request): array
    {
        return [
            'scheduled_date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'duration_hours' => ['sometimes', 'integer', 'in:1'],
            'care_type' => ['sometimes', 'string', Rule::in([
                'personal_care',
                'nursing_care',
                'physiotherapy',
                'post_hospital_recovery',
                'respite_care',
                'companionship',
                'wound_care',
                'home_icu_support',
                'other',
            ])],
            'description' => ['sometimes', 'string', 'max:2000'],
            'preferred_carer_type' => ['sometimes', 'string', Rule::in([
                'doctor',
                'nurse',
                'carers',
                'physiotherapist',
                'other',
            ])],
            'status' => ['sometimes', 'string', Rule::in([
                'pending',
                'confirmed',
                'cancelled',
                'completed',
            ])],
        ];
    }

    public function createRules(RestRequest $request): array
    {
        return [
            'scheduled_date' => ['required'],
            'start_time' => ['required'],
            'duration_hours' => ['required'],
            'care_type' => ['required'],
            'description' => ['required'],
            'preferred_carer_type' => ['required'],
        ];
    }

    public function updateRules(RestRequest $request): array
    {
        return [];
    }

    public function searchQuery(RestRequest $request, Builder $query)
    {
        return $query->where('user_id', $request->user()->id);
    }

    public function mutateQuery(RestRequest $request, Builder $query)
    {
        return $query->where('user_id', $request->user()->id);
    }

    public function destroyQuery(RestRequest $request, Builder $query)
    {
        return $query->where('user_id', $request->user()->id);
    }

    public function mutating(MutateRequest $request, array $requestBody, Model $model): void
    {
        if ($requestBody['operation'] === 'create') {
            $model->user_id = $request->user()->id;
            $model->status = 'pending';
        }
    }

    public function actions(RestRequest $request): array
    {
        return [];
    }

    public function instructions(RestRequest $request): array
    {
        return [];
    }
}
