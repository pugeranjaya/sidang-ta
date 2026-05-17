<x-filament::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <h2 style="font-size: 24px; font-weight: 900; line-height: 1.2;"
                    class="tracking-tight text-gray-950 dark:text-white">
                    Selamat Datang, {{ auth()->user()->name ?? 'Admin' }}!
                </h2>
                <div class="mt-4">
                    <span
                        class="inline-flex items-center rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-700/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </span>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament::widget>