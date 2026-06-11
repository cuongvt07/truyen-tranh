<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChapterReport;
use Illuminate\Http\Request;

class ChapterReportController extends Controller
{
    /** Danh sách báo cáo lỗi chương. */
    public function index(Request $request)
    {
        $onlyOpen = $request->get('filter', 'open') !== 'all';

        $reports = ChapterReport::query()
            ->with([
                'user:id,name,username',
                'article:id,title',
                'chapter:id,number,title,article_id',
            ])
            ->when($onlyOpen, fn ($q) => $q->where('resolved', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $openTotal = ChapterReport::where('resolved', false)->count();

        return view('admin.chapter-reports.index', compact('reports', 'openTotal', 'onlyOpen'));
    }

    /** Đánh dấu báo cáo đã xử lý. */
    public function resolve(ChapterReport $report)
    {
        $report->update(['resolved' => true]);

        return back()->with('success', 'Đã đánh dấu xử lý báo cáo.');
    }

    public function destroy(ChapterReport $report)
    {
        $report->delete();

        return back()->with('success', 'Đã xoá báo cáo.');
    }
}
