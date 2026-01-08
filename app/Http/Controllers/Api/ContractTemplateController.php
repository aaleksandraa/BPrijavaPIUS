<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContractTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ContractTemplate::all());
    }

    public function show(ContractTemplate $contractTemplate): JsonResponse
    {
        return response()->json($contractTemplate);
    }

    public function update(Request $request, ContractTemplate $contractTemplate): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $contractTemplate->update($validated);

        return response()->json($contractTemplate);
    }
}
