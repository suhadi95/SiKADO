@props([
    'label',
    'variant' => 'default',
])

@php
    $variants = [
        'default' => 'bg-slate-100 text-slate-700 hover:bg-slate-200',
        'brand' => 'bg-brand-50 text-brand-700 hover:bg-brand-100',
        'amber' => 'bg-amber-50 text-amber-700 hover:bg-amber-100',
        'rose' => 'bg-rose-50 text-rose-700 hover:bg-rose-100',
    ];
    $classes = $variants[$variant] ?? $variants['default'];
@endphp

<button
    {{ $attributes->merge(['type' => 'button', 'title' => $label, 'aria-label' => $label]) }}
    class="inline-flex h-10 w-10 items-center justify-center rounded-xl transition {{ $classes }}"
>
    {{ $slot }}
</button>
