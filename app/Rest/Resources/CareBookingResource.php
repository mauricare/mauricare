<?php

namespace App\Rest\Resources;

use App\Enums\BookingStatus;
use App\Models\CareBooking;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lomkit\Rest\Http\Requests\MutateRequest;
use Lomkit\Rest\Http\Requests\RestRequest;
use Lomkit\Rest\Relations\BelongsTo;
use Lomkit\Rest\Relations\HasOne;

class CareBookingResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static $model = CareBooking::class;

    public function fields(RestRequest $request): array
    {
        return [
            'id',
            'user_id',
            'care_giver_id',
            'scheduled_date',
            'start_time',
            'duration_hours',
            'care_type',
            'description',
            'preferred_carer_type',
            'address',
            'contact_phone',
            'status',
            'amount_due',
            'amount_paid',
            'payment_method',
            'payment_reference',
            'created_at',
            'updated_at',
        ];
    }

    public function relations(RestRequest $request): array
    {
        return [
            BelongsTo::make('user', UserResource::class),
            BelongsTo::make('careGiver', UserResource::class),
            HasOne::make('review', ReviewResource::class),
        ];
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
            'address' => ['sometimes', 'string', 'max:255'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'status' => ['sometimes', 'string', Rule::in([
                BookingStatus::Cancelled->value,
            ])],
            'user_id' => ['prohibited'],
            'care_giver_id' => ['prohibited'],
            'amount_due' => ['prohibited'],
            'amount_paid' => ['prohibited'],
            'payment_method' => ['prohibited'],
            'payment_reference' => ['prohibited'],
        ];
    }

    public function createRules(RestRequest $request): array
    {
        return [
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration_hours' => ['required'],
            'care_type' => ['required'],
            'description' => ['required'],
            'preferred_carer_type' => ['required'],
            'address' => ['required'],
        ];
    }

    public function updateRules(RestRequest $request): array
    {
        return [];
    }

    public function searchQuery(RestRequest $request, Builder $query)
    {
        $user = $request->user();

        if ($user->hasRole('care_giver') || $user->careGiverProfile()->exists()) {
            $isActive = (bool) $user->careGiverProfile?->is_active;

            return $query->where(function (Builder $query) use ($user, $isActive) {
                $query->where('care_giver_id', $user->id);

                if ($isActive) {
                    $query->orWhere(function (Builder $query) {
                        $query->whereNull('care_giver_id')
                            ->where('status', BookingStatus::Open->value);
                    });
                }
            });
        }

        return $query->where('user_id', $user->id);
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
            $attributes = $requestBody['attributes'];
            $scheduledAt = Carbon::createFromFormat(
                'Y-m-d H:i',
                "{$attributes['scheduled_date']} {$attributes['start_time']}",
                config('app.timezone'),
            );

            if ($scheduledAt->isPast()) {
                throw ValidationException::withMessages([
                    'start_time' => 'The selected booking date and time must not be in the past.',
                ]);
            }

            $model->user_id = $request->user()->id;
            $model->status = BookingStatus::Open;
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
