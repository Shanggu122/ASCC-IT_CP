@props(['class' => ''])
@php($classes = trim('nav-icon nav-icon-svg ' . ($class ?? '')))
<svg {{ $attributes->merge(['class' => $classes]) }} viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 7.5l1.2 4.4a12 12 0 0 1 4.6 0l1.2-4.4 5.4 3.1-2.5 3.8a12 12 0 0 1 2.5 4.1l4.5-.5v6.2l-4.5-.5a12 12 0 0 1-2.5 4.1l2.5 3.8-5.4 3.1-1.2-4.4a12 12 0 0 1-4.6 0l-1.2 4.4-5.4-3.1 2.5-3.8a12 12 0 0 1-2.5-4.1l-4.5.5V18l4.5.5a12 12 0 0 1 2.5-4.1l-2.5-3.8z" />
    <circle cx="24" cy="24" r="5.2" />
    <path d="M24 11v-4" />
    <path d="M34.8 15.2l2.8-2.8" />
    <path d="M37 24h4" />
    <path d="M34.8 32.8l2.8 2.8" />
    <path d="M24 37v4" />
    <path d="M13.2 32.8l-2.8 2.8" />
    <path d="M11 24H7" />
    <path d="M13.2 15.2L10.4 12.4" />
</svg>
