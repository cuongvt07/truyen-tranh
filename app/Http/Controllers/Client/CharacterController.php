<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Character;

class CharacterController extends Controller
{
    /**
     * Trang công khai của 1 nhân vật: thông tin + danh sách truyện nhân vật xuất hiện.
     * Nhân vật do admin quản lý (App\Http\Controllers\Admin\CharacterController);
     * client chỉ có trang xem này. Mirror AuthorController@show, eager-load tránh N+1.
     */
    public function show(Character $character)
    {
        $articles = $character->articles()
            ->with(['authors', 'chapters'])
            ->orderByDesc('updated_at')
            ->paginate(24);

        return view('client.community.character-show', compact('character', 'articles'));
    }
}
