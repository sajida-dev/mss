<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Schools\App\Models\School;

class SetActiveSchoolController extends Controller
{
    public function __invoke(Request $request)
    {
        Log::info('SetActiveSchoolController called', [
            'user_id' => Auth::id(),
            'school_id' => $request->input('school_id'),
            'request_data' => $request->all()
        ]);

        $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        // Save to session
        session(['active_school_id' => $request->school_id]);

        // Save to user's last_school_id in database
        if (Auth::check()) {
            $user = Auth::user();
            Log::info('Updating user last_school_id', [
                'user_id' => $user->id,
                'old_last_school_id' => $user->last_school_id,
                'new_last_school_id' => $request->school_id
            ]);

            DB::table('users')->where('id', $user->id)->update(['last_school_id' => $request->school_id]);

            Log::info('User last_school_id updated successfully');
        }

        return redirect()->back();
    }
}
