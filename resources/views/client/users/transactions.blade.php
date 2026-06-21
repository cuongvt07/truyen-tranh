@extends('client.users.profile')
@section('template_title', __('messages.pay.coins_and_transactions'))

@section('user_content')
@php
    $balance = $user->points ?? 0;
    $vipDays = $activeVipDays ?? 0;
@endphp

<div class="block">
    <h1 class="page-title" style="margin-bottom:16px">{{ __('messages.pay.balance') }} {{ number_format($balance) }} {{ coin_name() }}</h1>
    @auth
    @if(auth()->id() === $user->id)
        <div class="btn-group-inline" style="margin-top:8px">
            <a href="{{ route('pages.pricing') }}" class="btn btn-primary">
                <i class="fa fa-store"></i> {{ __('messages.pay.store') }}
            </a>
        </div>
    @endif
    @endauth
</div>

<div class="block premium-settings-stats" style="margin-top:16px">
    <h2 class="user-tab-title" style="margin-bottom:12px">{{ __('messages.pay.premium') }}: <span style="color:{{ $vipDays > 0 ? '#4caf50' : 'var(--meta-color)' }}">{{ $vipDays > 0 ? __('messages.pay.active') : __('messages.pay.inactive') }}</span></h2>
    <table class="trans-table">
        <tr><td>{{ __('messages.pay.days_remaining') }}</td><td>{{ $vipDays }}d</td></tr>
        <tr><td>{{ __('messages.pay.daily_reward') }}</td><td>{{ $vipDays > 0 ? __('messages.pay.yes') : __('messages.pay.no') }}</td></tr>
    </table>
</div>

<div class="block" style="margin-top:16px;overflow-x:auto">
    <h2 class="user-tab-title" style="margin-bottom:12px">{{ __('messages.pay.transaction_history') }}</h2>
    @if($deposits->isEmpty())
        <div class="nothing" style="padding:30px 0;text-align:center;color:var(--meta-color)">{{ __('messages.pay.no_transactions') }}</div>
    @else
        <table class="trans-table full">
            <thead>
                <tr><th>{{ __('messages.pay.transaction_id_short') }}</th><th>{{ __('messages.pay.amount_col') }}</th><th>{{ __('messages.pay.method') }}</th><th>{{ __('messages.pay.status') }}</th><th>{{ __('messages.pay.time') }}</th></tr>
            </thead>
            <tbody>
                @foreach($deposits as $d)
                    <tr>
                        <td>{{ $d->transaction_id ?? $d->id }}</td>
                        <td>{{ number_format($d->amount) }}$</td>
                        <td>{{ $d->payment_method ?? '—' }}</td>
                        <td>
                            @php $st = ['pending'=>[__('messages.pay.status_pending'),'#e0a020'],'completed'=>[__('messages.pay.status_completed'),'#4caf50'],'failed'=>[__('messages.pay.status_failed'),'#e84040']][$d->status] ?? [$d->status,'#888']; @endphp
                            <span style="color:{{ $st[1] }}">{{ $st[0] }}</span>
                        </td>
                        <td>{{ optional($d->created_at)->format('d.m.Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:14px">{{ $deposits->links() }}</div>
    @endif
</div>

<style>
.trans-table { width:100%; border-collapse:collapse; }
.trans-table td, .trans-table th { padding:8px 12px; text-align:left; }
.trans-table.full th { border-bottom:2px solid var(--border,#2a2a3e); font-size:13px; color:var(--meta-color); }
.trans-table.full td { border-bottom:1px solid var(--border,#2a2a3e); font-size:14px; }
.btn-group-inline { display:flex; gap:10px; }
.premium-settings-stats table td:first-child { color:var(--meta-color); }
</style>
@endsection
