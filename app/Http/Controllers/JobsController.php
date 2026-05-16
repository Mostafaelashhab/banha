<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Banha Jobs Board — surfaces listings in the `jobs` category as a dedicated
 * job-finding experience (separate from the general marketplace).
 *
 * Two listing kinds make sense here:
 *  - `sale`  = "I'm offering a job" (employer post)
 *  - `buy`   = "I'm looking for work" (worker post)
 *
 * The same underlying `listings` table powers it — no new model needed.
 */
class JobsController extends Controller
{
    public function index(Request $request)
    {
        $side = $request->query('side', 'hiring'); // hiring | seeking
        if (! in_array($side, ['hiring', 'seeking'], true)) $side = 'hiring';

        $q = Listing::query()
            ->where('status', 'active')
            ->where('category', 'jobs')
            ->with(['user:id,username,avatar_seed,avatar_url', 'zone:id,name'])
            ->latest();

        if ($side === 'hiring')  $q->where('kind', 'sale');
        if ($side === 'seeking') $q->where('kind', 'buy');

        $items = $q->limit(60)->get();

        $counts = [
            'hiring'  => Listing::query()->where('status', 'active')->where('category', 'jobs')->where('kind', 'sale')->count(),
            'seeking' => Listing::query()->where('status', 'active')->where('category', 'jobs')->where('kind', 'buy')->count(),
        ];

        return view('jobs.index', compact('items', 'side', 'counts'));
    }

    /**
     * Dedicated job-post form. Two modes, controlled by ?side=
     *   - hiring (default): the employer flow — title, employer name, type,
     *     salary range, requirements, contact.
     *   - seeking: the worker flow — name/title, headline, skills, salary
     *     expectation, contact. Stored with kind=`buy`.
     */
    public function create(Request $request)
    {
        $side = $request->query('side', 'hiring');
        if (! in_array($side, ['hiring', 'seeking'], true)) $side = 'hiring';

        $employmentTypes = Listing::EMPLOYMENT_TYPES;
        $experienceLevels = Listing::EXPERIENCE_LEVELS;

        return view('jobs.create', compact('side', 'employmentTypes', 'experienceLevels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $side = $request->input('side', 'hiring');
        if (! in_array($side, ['hiring', 'seeking'], true)) $side = 'hiring';

        $data = $request->validate([
            'title'             => ['required', 'string', 'min:4', 'max:120'],
            'description'       => ['nullable', 'string', 'max:2000'],
            'employer_name'     => ['nullable', 'string', 'max:80'],
            'employment_type'   => ['required', 'string', 'in:' . implode(',', array_keys(Listing::EMPLOYMENT_TYPES))],
            'experience_level'  => ['nullable', 'string', 'in:' . implode(',', array_keys(Listing::EXPERIENCE_LEVELS))],
            'salary_min'        => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'salary_max'        => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'requirements'      => ['nullable', 'string', 'max:1500'],
            'contact_phone'     => ['nullable', 'string', 'max:20'],
            'contact_whatsapp'  => ['nullable', 'string', 'max:20'],
        ]);

        // At least one way to reach back — phone OR whatsapp.
        if (empty($data['contact_phone']) && empty($data['contact_whatsapp'])) {
            return back()->withInput()->withErrors([
                'contact_phone' => 'محتاج طريقة تواصل (موبايل أو واتساب).',
            ]);
        }

        $banhaZoneId = Zone::where('slug', 'banha')->value('id');

        $meta = [
            'employer_name'    => $data['employer_name'] ?? null,
            'employment_type'  => $data['employment_type'],
            'experience_level' => $data['experience_level'] ?? null,
            'salary_min'       => $data['salary_min'] ?? null,
            'salary_max'       => $data['salary_max'] ?? null,
            'requirements'     => $data['requirements'] ?? null,
        ];

        // Headline salary stored in `price` for quick filtering / sorting.
        // Convention: use salary_min as the "price" surface.
        $price = $data['salary_min'] ?? null;

        $listing = Listing::create([
            'user_id'          => Auth::id(),
            'zone_id'          => $banhaZoneId,
            'kind'             => $side === 'hiring' ? 'sale' : 'buy',
            'category'         => 'jobs',
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'price'            => $price,
            'negotiable'       => true,
            'meta'             => $meta,
            'contact_phone'    => $data['contact_phone'] ?? null,
            'contact_whatsapp' => $data['contact_whatsapp'] ?? null,
            'status'           => 'active',
            'expires_at'       => now()->addDays(30),
        ]);

        return redirect()->route('jobs.index', ['side' => $side])
            ->with('flash', $side === 'hiring' ? 'الوظيفة اتنشرت — أهل بنها هيشوفوها.' : 'بوستك اتنشر — أصحاب النشاطات هيشوفوه.');
    }
}
