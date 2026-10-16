<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CaseAnalyticsDaily;
use App\Models\EditorialCase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AnalyticsController extends Controller
{
    public function store(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'event' => 'required|string|in:view_case,click_primary_source',
            'slug' => 'required_if:event,view_case,click_primary_source|string|max:128',
        ]);

        if ($validator->fails()) {
            return response()->noContent(422);
        }

        $event = $request->string('event')->toString();
        $slug = $request->string('slug')->toString();

        $case = EditorialCase::query()->published()->where('slug', $slug)->first();
        if (! $case) {
            return response()->noContent(404);
        }

        $date = Carbon::today();
        $row = CaseAnalyticsDaily::query()->firstOrCreate(
            ['case_id' => $case->id, 'metric_date' => $date],
            ['views' => 0, 'source_clicks' => 0],
        );

        if ($event === 'view_case') {
            $row->increment('views');
            $case->increment('pageviews');
        } else {
            $row->increment('source_clicks');
        }

        return response()->noContent();
    }
}
