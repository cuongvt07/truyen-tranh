<?php

namespace Tests\Unit;

use App\Services\ReadingAccessService;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Tests\TestCase;

class ReadingAccessServiceTest extends TestCase
{
    public function test_guest_is_blocked_before_opening_a_sixth_story(): void
    {
        config()->set('reading_limits.guest_articles_per_day', 5);
        config()->set('reading_limits.guest_chapters_per_day', 10);

        $request = $this->requestWithSession();
        $service = new ReadingAccessService([
            'guest_articles_per_day' => 5,
            'guest_chapters_per_day' => 10,
        ]);

        foreach (range(1, 5) as $id) {
            $this->assertTrue($service->guestCanRead($request, $id, $id));
            $service->recordGuestRead($request, $id, $id);
        }

        $this->assertFalse($service->guestCanRead($request, 6, 6));
        $this->assertTrue($service->guestCanRead($request, 5, 5));
    }

    public function test_guest_is_blocked_before_opening_an_eleventh_chapter(): void
    {
        config()->set('reading_limits.guest_articles_per_day', 5);
        config()->set('reading_limits.guest_chapters_per_day', 10);

        $request = $this->requestWithSession();
        $service = new ReadingAccessService([
            'guest_articles_per_day' => 5,
            'guest_chapters_per_day' => 10,
        ]);

        foreach (range(1, 10) as $chapterId) {
            $service->recordGuestRead($request, 1, $chapterId);
        }

        $this->assertFalse($service->guestCanRead($request, 1, 11));
        $this->assertTrue($service->guestCanRead($request, 1, 10));
    }

    private function requestWithSession(): Request
    {
        $request = Request::create('/');
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));

        return $request;
    }
}
