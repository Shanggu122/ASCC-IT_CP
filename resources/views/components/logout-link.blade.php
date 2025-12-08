@props([
    'guard' => 'web', // 'web' | 'professor' | 'admin'
    'label' => null,
    'method' => 'GET', // could adapt later for POST logout endpoints
    'class' => 'text-red-600 hover:underline',
    'icon' => null,
])
@php
    $routeMap = [
        'web' => ['url' => route('logout', [], false), 'method' => 'GET'],
        'professor' => ['url' => route('logout-professor', [], false), 'method' => 'GET'],
    'admin' => ['url' => route('logout.admin', [], false), 'method' => 'POST'],
    ];
    $cfg = $routeMap[$guard] ?? $routeMap['web'];
    $text = $label ?? __('Logout');
    $iconClass = is_string($icon) ? trim($icon) : '';
    if ($iconClass !== '' && !preg_match('/^[a-z0-9\- ]+$/i', $iconClass)) {
        $iconClass = '';
    }
@endphp
@if($cfg['method'] === 'GET')
    <a href="{{ $cfg['url'] }}" {{ $attributes->merge(['class' => $class]) }} data-logout-guard="{{ $guard }}">
        @if($iconClass !== '')
            <i class="bx {{ $iconClass }} nav-icon" aria-hidden="true"></i>
        @endif
        <span class="nav-label">{{ $text }}</span>
    </a>
@else
    <form action="{{ $cfg['url'] }}" method="POST" style="display:inline" {{ $attributes->merge(['class' => 'inline']) }} data-logout-guard="{{ $guard }}">
        @csrf
        <button type="submit" class="{{ $class }}">
            @if($iconClass !== '')
                <i class="bx {{ $iconClass }} nav-icon" aria-hidden="true"></i>
            @endif
            <span class="nav-label">{{ $text }}</span>
        </button>
    </form>
@endif
