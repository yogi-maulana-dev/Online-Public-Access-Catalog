@props(['label' => 'Kirim', 'loading' => 'Memproses…'])

{{-- Requires an enclosing Alpine scope with `submitting` (form: x-data="{ submitting:false }" @submit="submitting=true"). --}}
<x-primary-button
    x-bind:disabled="submitting"
    x-bind:class="submitting ? 'opacity-70 cursor-not-allowed' : ''"
    {{ $attributes }}
>
    <svg x-show="submitting" x-cloak class="animate-spin -ml-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path>
    </svg>
    <span x-show="!submitting">{{ $label }}</span>
    <span x-show="submitting" x-cloak>{{ $loading }}</span>
</x-primary-button>
