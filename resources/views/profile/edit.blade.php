@extends('layouts.public')

@section('title', 'Profil Saya — '.\App\Models\Setting::get('site_name', 'University Press'))

@section('content')
{{-- Header --}}
<section class="relative overflow-hidden bg-brand-900">
    <div class="absolute inset-0 opacity-20" style="background-image:radial-gradient(circle at 20% 30%, #c8a45c 0, transparent 40%), radial-gradient(circle at 90% 10%, #2a9244 0, transparent 35%)"></div>
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
            <img src="{{ auth()->user()->avatar_url }}" alt="" class="h-24 w-24 rounded-full object-cover ring-4 ring-gold-400/40">
            <div class="flex-1">
                <h1 class="font-serif text-3xl font-bold text-white">{{ auth()->user()->name }}</h1>
                <p class="text-brand-200">{{ auth()->user()->email }}</p>
                <div class="mt-2 flex flex-wrap justify-center sm:justify-start gap-2">
                    @foreach(auth()->user()->getRoleNames() as $role)
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-gold-300 ring-1 ring-white/20">{{ $role }}</span>
                    @endforeach
                    @if(auth()->user()->affiliation)
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs text-brand-100 ring-1 ring-white/20">{{ auth()->user()->affiliation }}</span>
                    @endif
                </div>
            </div>
            @can('access admin')
                <a href="/admin" class="rounded-full bg-gold-500 px-5 py-2.5 text-sm font-semibold text-brand-950 hover:bg-gold-400 transition">Panel Admin</a>
            @endcan
        </div>
    </div>
</section>

<div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div class="rounded-2xl border border-stone-200 bg-white p-6 sm:p-8 shadow-sm">
        <div class="max-w-xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <div class="rounded-2xl border border-stone-200 bg-white p-6 sm:p-8 shadow-sm">
        <div class="max-w-xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <div id="keamanan" class="scroll-mt-24 rounded-2xl border border-stone-200 bg-white p-6 sm:p-8 shadow-sm">
        @include('profile.partials.two-factor-form')
    </div>

    <div class="rounded-2xl border border-rose-200 bg-rose-50/40 p-6 sm:p-8 shadow-sm">
        <div class="max-w-xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
