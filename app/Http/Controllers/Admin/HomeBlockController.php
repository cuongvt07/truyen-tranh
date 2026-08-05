<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\HomeBlock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeBlockController extends Controller
{
    public function index()
    {
        $blocks = HomeBlock::with('genre')->orderBy('order')->orderBy('id')->get();

        return view('admin.home-blocks.index', compact('blocks'));
    }

    public function create()
    {
        $block = new HomeBlock([
            'source'       => 'genre',
            'variant'      => 'rail',
            'limit'        => 9,
            'show_see_all' => true,
            'is_active'    => true,
            'order'        => (int) HomeBlock::max('order') + 10,
        ]);

        return view('admin.home-blocks.form', [
            'block'  => $block,
            'genres' => $this->genres(),
        ]);
    }

    public function store(Request $request)
    {
        HomeBlock::create($this->validateData($request));

        return redirect()->route('admin.home-blocks.index')
            ->with('success', 'Đã thêm khối trang chủ.');
    }

    public function edit(HomeBlock $homeBlock)
    {
        return view('admin.home-blocks.form', [
            'block'  => $homeBlock,
            'genres' => $this->genres(),
        ]);
    }

    public function update(Request $request, HomeBlock $homeBlock)
    {
        $homeBlock->update($this->validateData($request));

        return redirect()->route('admin.home-blocks.index')
            ->with('success', 'Đã cập nhật khối trang chủ.');
    }

    public function destroy(HomeBlock $homeBlock)
    {
        $homeBlock->delete();

        return redirect()->route('admin.home-blocks.index')
            ->with('success', 'Đã xoá khối trang chủ.');
    }

    public function toggle(HomeBlock $homeBlock)
    {
        $homeBlock->update(['is_active' => !$homeBlock->is_active]);

        return back()->with('success', 'Đã đổi trạng thái khối.');
    }

    private function genres()
    {
        return Genre::orderBy('name')->get(['id', 'name']);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:190'],
            'source'       => ['required', Rule::in(array_keys(HomeBlock::SOURCES))],
            'genre_id'     => ['nullable', 'exists:genres,id', Rule::requiredIf($request->input('source') === 'genre')],
            // Trần 24: mỗi khối là một truy vấn riêng, để tự do sẽ kéo sập trang chủ.
            'limit'        => ['required', 'integer', 'min:1', 'max:24'],
            'variant'      => ['required', Rule::in(array_keys(HomeBlock::VARIANTS))],
            'url'          => ['nullable', 'string', 'max:255'],
            'order'        => ['required', 'integer', 'min:0'],
        ], [
            'genre_id.required' => 'Chọn thể loại khi nguồn là "Theo thể loại".',
        ]);

        // Nguồn khác genre thì không giữ genre_id cũ lại, tránh dữ liệu mồ côi.
        if ($data['source'] !== 'genre') {
            $data['genre_id'] = null;
        }

        $data['show_see_all'] = $request->boolean('show_see_all');
        $data['is_active']    = $request->boolean('is_active');

        return $data;
    }
}
