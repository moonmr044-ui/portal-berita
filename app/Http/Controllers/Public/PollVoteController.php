<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollVoteController extends Controller
{
    public function store(Request $request, Poll $poll): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'poll_option_id' => ['required', 'integer'],
        ]);

        /** @var PollOption|null $selectedOption */
        $selectedOption = $poll->options()->whereKey((int) $validated['poll_option_id'])->first();

        if (! $selectedOption) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pilihan survei tidak valid.'], 422);
            }

            return back()->with('error', 'Pilihan survei tidak valid.');
        }

        $voterToken = self::resolveVoterToken($request);

        DB::transaction(function () use ($poll, $selectedOption, $voterToken, $request): void {
            $existingVote = PollVote::query()
                ->where('poll_id', $poll->id)
                ->where('voter_token', $voterToken)
                ->lockForUpdate()
                ->first();

            if ($existingVote) {
                $existingVote->update([
                    'poll_option_id' => $selectedOption->id,
                    'user_id' => $request->user()?->id,
                    'ip_address' => $request->ip(),
                ]);
            } else {
                PollVote::query()->create([
                    'poll_id' => $poll->id,
                    'poll_option_id' => $selectedOption->id,
                    'user_id' => $request->user()?->id,
                    'voter_token' => $voterToken,
                    'ip_address' => $request->ip(),
                ]);
            }

            // Sinkronisasi akurat jumlah suara setiap opsi dari tabel poll_votes
            foreach ($poll->options()->get() as $option) {
                $count = PollVote::query()
                    ->where('poll_id', $poll->id)
                    ->where('poll_option_id', $option->id)
                    ->count();

                $option->update(['votes_count' => $count]);
            }
        });

        $poll->load('options');
        $totalVotes = $poll->totalVotes();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Suara Anda berhasil disimpan ke database.',
                'total_votes' => $totalVotes,
                'selected_option_id' => $selectedOption->id,
                'options' => $poll->options->map(fn (PollOption $opt) => [
                    'id' => $opt->id,
                    'label' => $opt->label,
                    'votes_count' => $opt->votes_count,
                    'percentage' => $opt->percentage($totalVotes),
                ])->values(),
            ]);
        }

        return back()->with('status', 'Terima kasih! Suara Anda pada Survei Pembaca telah tercatat.');
    }

    public static function resolveVoterToken(Request $request): string
    {
        if ($request->user()) {
            return 'user_' . $request->user()->id;
        }

        $sessionId = $request->hasSession() ? $request->session()->getId() : '';
        $ip = (string) $request->ip();

        return hash('sha256', $sessionId . '|' . $ip);
    }
}
