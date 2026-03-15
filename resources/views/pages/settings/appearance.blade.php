<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<section class="w-full pb-5">
    <x-pages::settings.layout :heading="__('app.appearance_title')" :subheading="__('app.appearance_subtitle')">
        <div class="card shadow-sm border-0 overflow-hidden mb-4">
            <div class="card-header bg-body-tertiary border-bottom d-flex align-items-center gap-3 py-3">
                <div class="p-2 rounded shadow-sm" style="background:#e7f3ed; color:#198754">
                    <i class="bi bi-palette-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">{{ __('app.app_theme') }}</h6>
                    <small class="text-muted">{{ __('app.theme_choose_mode') }}</small>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-3" x-data="{ 
                    theme: localStorage.getItem('theme') || 'light',
                    setTheme(newTheme) {
                        this.theme = newTheme;
                        localStorage.setItem('theme', newTheme);
                        if (newTheme === 'system') {
                            const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                            document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
                        } else {
                            document.documentElement.setAttribute('data-bs-theme', newTheme);
                        }
                        // Dispatch event to inform other components if needed
                        window.dispatchEvent(new CustomEvent('theme-changed', { detail: newTheme }));
                    }
                }">
                    <button type="button" @click="setTheme('light')"
                        :class="theme === 'light' ? 'btn btn-primary shadow-sm' : 'btn btn-outline-secondary'"
                        class="d-flex align-items-center gap-2 px-4 py-2 rounded-3 fw-bold border-2">
                        <i class="bi bi-sun-fill fs-5"></i> {{ __('app.light') }}
                    </button>

                    <button type="button" @click="setTheme('dark')"
                        :class="theme === 'dark' ? 'btn btn-primary shadow-sm' : 'btn btn-outline-secondary'"
                        class="d-flex align-items-center gap-2 px-4 py-2 rounded-3 fw-bold border-2">
                        <i class="bi bi-moon-stars-fill fs-5"></i> {{ __('app.dark') }}
                    </button>

                    <button type="button" @click="setTheme('system')"
                        :class="theme === 'system' ? 'btn btn-primary shadow-sm' : 'btn btn-outline-secondary'"
                        class="d-flex align-items-center gap-2 px-4 py-2 rounded-3 fw-bold border-2">
                        <i class="bi bi-display-fill fs-5"></i> {{ __('app.system') }}
                    </button>
                </div>

                <div class="mt-4 p-3 bg-body-tertiary rounded small text-muted border border-light-subtle shadow-sm">
                    <div class="d-flex gap-2">
                        <i class="bi bi-info-circle-fill text-primary"></i>
                        <span>{{ __('app.system_mode_description') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </x-pages::settings.layout>
</section>