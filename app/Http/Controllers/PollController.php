<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public function vote(Poll $poll, Request $request)
    {
        if ($poll->isClosed()) {
            return back()->withErrors(['poll' => 'التصويت قفل.']);
        }

        $data = $request->validate([
            'option' => ['required', 'integer', 'min:0', 'max:3'],
        ]);

        $optionIndex = $data['option'];
        if ($optionIndex >= count($poll->options)) abort(422);

        DB::table('poll_votes')->upsert(
            [[
                'poll_id'      => $poll->id,
                'user_id'      => Auth::id(),
                'option_index' => $optionIndex,
                'created_at'   => now(),
            ]],
            ['poll_id', 'user_id'],
            ['option_index']
        );

        if ($request->wantsJson()) {
            return response()->json([
                'counts'      => $poll->counts(),
                'total'       => $poll->totalVotes(),
                'your_choice' => $optionIndex,
            ]);
        }

        return back();
    }
}
