<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Package;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        return LandingPage::orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:landing_pages,slug',
            'intro_text' => 'nullable|string',
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'uuid|exists:packages,id',
            'is_active' => 'boolean',
        ]);

        $page = LandingPage::create($validated);
        return response()->json($page, 201);
    }

    public function show(LandingPage $landingPage)
    {
        return $landingPage;
    }

    public function showBySlug(string $slug)
    {
        $page = LandingPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $packages = Package::whereIn('id', $page->package_ids)->where('is_active', true)->get();

        return response()->json([
            'page' => $page,
            'packages' => $packages,
        ]);
    }

    public function update(Request $request, LandingPage $landingPage)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:landing_pages,slug,' . $landingPage->id,
            'intro_text' => 'nullable|string',
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'uuid|exists:packages,id',
            'is_active' => 'boolean',
        ]);

        $landingPage->update($validated);
        return $landingPage;
    }

    public function destroy(LandingPage $landingPage)
    {
        $landingPage->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
