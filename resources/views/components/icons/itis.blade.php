@props(['class' => ''])
@php($classes = trim('nav-icon nav-icon-svg ' . ($class ?? '')))
<svg {{ $attributes->merge(['class' => $classes]) }} viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
    <rect x="6" y="9" width="36" height="22" rx="3" ry="3" />
    <path d="M24 31v8" />
    <path d="M18 41h12" />
    <g>
        <circle cx="33" cy="17" r="7" />
        <path d="M29.2 13.2l2.4 2.4a3.5 3.5 0 0 1-1.7 5.8l-2.2 2.2" />
        <path d="M36.5 13.5l-2.3 2.3a3.5 3.5 0 0 1 1.6 5.8l-2.2 2.2" />
    </g>
</svg>
