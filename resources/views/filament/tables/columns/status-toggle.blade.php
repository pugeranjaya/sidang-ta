<div class="fi-ta-toggle fi-align-center text-center w-full flex justify-center">
    @php
        $state = $getState();
        $record = $getRecord();
    @endphp

    <button
        type="button"
        wire:click.stop.prevent="mountTableAction('toggle_status', '{{ $record->getKey() }}')"
        wire:loading.attr="disabled"
        role="switch"
        aria-checked="{{ $state ? 'true' : 'false' }}"
        style="background-color: {{ $state ? 'var(--success-600, #16a34a)' : 'var(--danger-600, #dc2626)' }};"
        class="fi-toggle inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
    >
        <span
            aria-hidden="true"
            style="transform: {{ $state ? 'translateX(1.25rem)' : 'translateX(0)' }};"
            class="fi-toggle-thumb pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
        ></span>
    </button>
</div>
