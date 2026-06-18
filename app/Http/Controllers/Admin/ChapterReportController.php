<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChapterReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChapterReportController extends Controller
{
    public function index(Request $request)
    {
        $onlyOpen = $request->get('filter', 'open') !== 'all';

        $reports = ChapterReport::query()
            ->select([
                'chapter_id',
                'article_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN resolved = 1 THEN 1 ELSE 0 END) as resolved_count'),
                DB::raw('SUM(CASE WHEN resolved = 0 THEN 1 ELSE 0 END) as open_count'),
                DB::raw('MAX(created_at) as last_reported_at'),
            ])
            ->with(['chapter:id,number,title,article_id', 'article:id,title'])
            ->groupBy('chapter_id', 'article_id')
            ->when($onlyOpen, fn($q) => $q->havingRaw('SUM(CASE WHEN resolved = 0 THEN 1 ELSE 0 END) > 0'))
            ->orderByRaw('SUM(CASE WHEN resolved = 0 THEN 1 ELSE 0 END) DESC, MAX(created_at) DESC')
            ->paginate(20)
            ->withQueryString();

        $openTotal     = ChapterReport::where('resolved', false)->count();
        $resolvedTotal = ChapterReport::where('resolved', true)->count();
        $totalAll      = $openTotal + $resolvedTotal;

        return view('admin.chapter-reports.index', compact(
            'reports', 'openTotal', 'resolvedTotal', 'totalAll', 'onlyOpen'
        ));
    }

    public function resolve(ChapterReport $report)
    {
        $report->update(['resolved' => true]);
        return back()->with('success', __('messages.flash.chapter_report.resolved'));
    }

    public function resolveChapter(Request $request)
    {
        $chapterId = (int) $request->input('chapter_id');
        ChapterReport::where('chapter_id', $chapterId)->where('resolved', false)
            ->update(['resolved' => true]);
        return back()->with('success', __('messages.flash.chapter_report.resolved_all'));
    }

    public function destroy(ChapterReport $report)
    {
        $report->delete();
        return back()->with('success', __('messages.flash.chapter_report.deleted'));
    }
}
