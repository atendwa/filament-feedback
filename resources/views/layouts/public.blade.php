<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="fi font-secondary font-normal leading-6 antialiased">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="robots" content="noindex, nofollow" />
        <title>{{ $title ?? 'Feedback' }} - {{ config('app.name') }}</title>

        @filamentStyles
        @if (filled(config('feedback.public.vite')))
            @vite(config('feedback.public.vite'))
        @endif
        @if (filled(config('feedback.public.head')))
            <x-dynamic-component :component="config('feedback.public.head')" />
        @endif
    </head>

    <body class="fi-body grid h-lvh grid-cols-12 overflow-hidden overscroll-none text-gray-950 antialiased dark:text-white" style="background-color: var(--brand-wash, rgb(249 250 251))">
        <main @class(['col-span-12 overflow-y-auto border-t-4', 'lg:col-span-7 lg:border-0' => filled(config('feedback.public.decoration'))]) style="border-color: var(--brand-primary, var(--primary-600))">
            <div class="mx-auto flex min-h-lvh w-full max-w-2xl flex-col gap-6 px-4 py-10 sm:py-14">
                <header class="flex flex-col gap-2">
                    @if (filled(config('feedback.public.logo')))
                        <img src="{{ asset(config('feedback.public.logo')) }}" alt="{{ config('app.name') }}" width="165" height="53" class="h-[3.3rem] w-auto self-start" fetchpriority="high" />
                    @else
                        <p class="text-xl font-semibold">{{ config('app.name') }}</p>
                    @endif
                    <p class="text-xs text-gray-500">{{ $subtitle ?? 'Your feedback helps us improve' }}</p>
                </header>

                {{ $slot }}

                <footer class="mt-auto pb-6 text-center text-xs text-gray-500">
                    Feedback is read by the {{ config('app.name') }} team only.
                </footer>
            </div>
        </main>

        @if (filled(config('feedback.public.decoration')))
            <div class="col-span-5 hidden lg:block" aria-hidden="true">
                <x-dynamic-component :component="config('feedback.public.decoration')" class="h-lvh" />
            </div>
        @endif

        @filamentScripts
    </body>
</html>
