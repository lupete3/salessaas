<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-neutral-100 antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div class="bg-muted flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <!-- Language Switcher -->
        <div class="absolute top-4 right-4 z-50 flex gap-1">
            @foreach(['fr' => ['🇫🇷', 'FR'], 'en' => ['🇬🇧', 'EN'], 'sw' => ['🇹🇿', 'SW']] as $loc => $info)
                <a href="{{ route('locale.switch', $loc) }}"
                    class="px-2 py-1 text-xs rounded-md border flex items-center {{ app()->getLocale() === $loc ? 'bg-neutral-800 text-white border-neutral-800 dark:bg-white dark:text-black' : 'bg-white text-neutral-600 border-neutral-200 dark:bg-neutral-900 dark:text-neutral-400 dark:border-neutral-800' }}">
                    <span>{{ $info[0] }}</span>
                    <span class="hidden sm:inline-block ml-1">{{ $info[1] }}</span>
                </a>
            @endforeach
        </div>

        <div class="flex w-full max-w-md flex-col gap-6">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                <span class="flex h-9 w-9 items-center justify-center rounded-md">
                    <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                </span>

                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>

            <div class="flex flex-col gap-6">
                <div
                    class="rounded-xl border bg-white dark:bg-stone-950 dark:border-stone-800 text-stone-800 shadow-xs">
                    <div class="px-10 py-8">{{ $slot }}</div>
                </div>
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>