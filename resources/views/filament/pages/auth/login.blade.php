<x-filament-panels::page.simple>
    <div class="space-y-8">
        <div class="space-y-2 text-center">
            <h2 class="text-2xl font-bold tracking-tight text-amber-600">
                {{ __('Selamat Datang') }}
            </h2>
            
            <div class="text-gray-500 dark:text-gray-400 text-base">
                {{ __('Sistem Monitoring Perawatan Peralatan') }}
            </div>
        </div>
        
        @if (filament()->hasRegistration())
            <div class="text-center">
                {{ __('filament-panels::pages/auth/login.actions.register.before') }}

                {{ $this->registerAction }}
            </div>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

        <x-filament-panels::form id="form" wire:submit="authenticate" class="space-y-6">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
        
        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
            {{ __('Silahkan masukkan kredensial akun Anda untuk masuk ke sistem.') }}
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page.simple> 