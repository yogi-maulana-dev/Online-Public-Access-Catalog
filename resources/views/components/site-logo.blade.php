@props([
    'imgClass' => 'h-11 w-11 object-contain',
    'fallbackClass' => 'h-11 w-11 text-lg',
])

@php
    $logo = \App\Models\Setting::imageUrl('site_logo');
    $fileLogo = file_exists(public_path('images/logo-uml.png'));
@endphp

@if($logo)
    <img src="{{ $logo }}" alt="Logo" class="{{ $imgClass }}">
@elseif($fileLogo)
    <img src="{{ asset('images/logo-uml.png') }}" alt="Logo" class="{{ $imgClass }}">
@else
    <span class="{{ $fallbackClass }} grid place-items-center rounded-full bg-brand-700 text-gold-300 font-serif font-bold ring-2 ring-gold-400">UP</span>
@endif
