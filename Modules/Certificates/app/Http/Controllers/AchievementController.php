<?php

namespace Modules\Certificates\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Modules\Certificates\App\Models\Achievement;

class AchievementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $achievements = Achievement::all();

        return Inertia::render('Certificates/AchievementIndex', [
            'achievements' => $achievements,

        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'description'    => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated) {

                Achievement::create($validated);
            });
            return redirect()->back()->with('success', 'Achievement created.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Achievement creation failed.' . $e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Modules\Certificate\App\Models\Achievement  $achievement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Achievement $achievement)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'description'    => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated, $achievement) {
                $achievement->update($validated);
            });
            return redirect()->back()->with('success', 'Achievement updated.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Achievement update failed.' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Modules\Certificate\App\Models\Achievement  $achievement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Achievement $achievement)
    {
        try {
            DB::transaction(function () use ($achievement) {
                $achievement->delete();
            });
            return redirect()->back()->with('success', 'Achievement deleted.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Achievement deletion failed.' . $e->getMessage());
        }
    }
}
