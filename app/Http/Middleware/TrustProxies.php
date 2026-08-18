<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    // Chi Cloudflare va nginx cua chinh box moi cham toi app, khong co
    // duong nao khac vao -> tin tat ca proxy. Truoc day chi tin 172.16/12
    // nen X-Forwarded-Proto tu Cloudflare bi bo qua, request bi coi la
    // http va redirect sau login tra ve http://.
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    // KHONG tin X_FORWARDED_HOST: khi tin tat ca proxy, header nay cho phep
    // ghi de host va sinh ra URL hong kieu https://admin/articles/... .
    // Host lay tu Host header that; chi PROTO la can de biet request la https.
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO;
}
