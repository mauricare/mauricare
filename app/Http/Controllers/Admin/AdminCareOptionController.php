<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCareOptionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => CareOption::grouped(false)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(['care_type', 'carer_type'])],
            'label' => ['required', 'string', 'max:255'],
        ]);
        $value = Str::of($validated['label'])->ascii()->snake()->limit(100, '')->toString();
        abort_if($value === '', 422, 'The label must contain letters or numbers.');
        abort_if(
            CareOption::where('category', $validated['category'])->where('value', $value)->exists(),
            422,
            'An option with the same generated value already exists.'
        );

        $option = CareOption::create([
            ...$validated,
            'value' => $value,
            'sort_order' => (CareOption::where('category', $validated['category'])->max('sort_order') ?? -1) + 1,
            'is_active' => true,
        ]);

        return response()->json(['data' => $option], 201);
    }

    public function update(Request $request, CareOption $careOption): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $careOption->update($validated);

        return response()->json(['data' => $careOption->fresh()]);
    }

    public function destroy(CareOption $careOption): JsonResponse
    {
        $careOption->update(['is_active' => false]);

        return response()->json(['message' => 'Option deactivated.']);
    }
}
