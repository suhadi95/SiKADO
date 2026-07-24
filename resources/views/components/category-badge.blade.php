@props([
    'category' => null,
    'label' => null,
])

@php
    $name = $label ?? $category?->name ?? 'Tanpa Kategori';
    $background = $category?->badgeBackgroundColor() ?? '#64748B';
    $text = $category?->badgeTextColor() ?? '#FFFFFF';
@endphp

<span
    {{ $attributes->merge([
        'class' => 'inline-flex max-w-full truncate rounded-md px-1.5 py-0.5 text-[10px] font-semibold leading-tight',
    ]) }}
    style="background-color: {{ $background }}; color: {{ $text }};"
>
    {{ $name }}
</span>
