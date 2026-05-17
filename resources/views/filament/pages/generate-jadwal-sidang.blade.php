<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div style="margin-top: 1.5rem; margin-bottom: 2rem;">
            <x-filament::button wire:click="generate" color="primary" size="lg" style="width: 100%; justify-content: center;">
                Buat Jadwal Sidang
            </x-filament::button>
        </div>
        
        @if($scheduleOptions)
        <div style="margin-top: 2rem;">
            <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1.5rem; color: #111827;">Rekomendasi Jadwal Sidang</h2>
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                @foreach($scheduleOptions as $index => $option)
                <x-filament::section>
                    <x-slot name="heading">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <x-filament::badge color="info" size="lg">
                                    Opsi #{{ $index + 1 }}
                                </x-filament::badge>
                                <span style="font-size: 1rem; font-weight: 500; color: #4b5563;">Skor AHP: <span style="font-weight: 800; color: #f59e0b;">{{ $option['score_ahp'] }}</span></span>
                            </div>
                            <x-filament::button wire:click="simpanJadwal({{ $index }})" color="success" size="sm" icon="heroicon-m-check-circle">
                                Pilih & Simpan Jadwal Ini
                            </x-filament::button>
                        </div>
                    </x-slot>

                    <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 0.5rem;">
                        <!-- Baris 1: Waktu & Tempat -->
                        <div style="display: flex; gap: 2.5rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 1.25rem; flex-wrap: wrap;">
                            <div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Hari, Tanggal</div>
                                <div style="font-weight: bold; font-size: 1.125rem; color: #111827;">{{ \Carbon\Carbon::parse($option['tanggal'])->locale('id')->translatedFormat('l, d F Y') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Waktu (WIB)</div>
                                <div style="font-weight: bold; font-size: 1.125rem; color: #111827;">{{ \Carbon\Carbon::parse($option['jam_mulai'])->format('H:i') }} - {{ \Carbon\Carbon::parse($option['jam_selesai'])->format('H:i') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Ruangan</div>
                                <div style="font-weight: bold; font-size: 1.125rem; color: #111827;">{{ $option['ruangan'] }}</div>
                            </div>
                        </div>

                        <!-- Baris 2: Dosen -->
                        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                            <div style="min-width: 200px; background-color: #fef3c7; padding: 0.75rem; border-radius: 0.5rem; border-left: 4px solid #f59e0b;">
                                <div style="font-size: 0.75rem; color: #d97706; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Ketua Sidang</div>
                                <div style="font-weight: 600; font-size: 0.95rem; color: #111827;">{{ $option['ketua_sidang_nama'] }}</div>
                            </div>
                            <div style="min-width: 200px; background-color: #eff6ff; padding: 0.75rem; border-radius: 0.5rem; border-left: 4px solid #3b82f6;">
                                <div style="font-size: 0.75rem; color: #2563eb; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Penguji 1</div>
                                <div style="font-weight: 600; font-size: 0.95rem; color: #111827;">{{ $option['penguji1_nama'] }}</div>
                            </div>
                            <div style="min-width: 200px; background-color: #eff6ff; padding: 0.75rem; border-radius: 0.5rem; border-left: 4px solid #3b82f6;">
                                <div style="font-size: 0.75rem; color: #2563eb; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Penguji 2</div>
                                <div style="font-weight: 600; font-size: 0.95rem; color: #111827;">{{ $option['penguji2_nama'] }}</div>
                            </div>
                            @if($option['jenis_sidang'] === 'Seminar TA 2')
                            <div style="min-width: 200px; background-color: #eff6ff; padding: 0.75rem; border-radius: 0.5rem; border-left: 4px solid #3b82f6;">
                                <div style="font-size: 0.75rem; color: #2563eb; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Penguji 3</div>
                                <div style="font-weight: 600; font-size: 0.95rem; color: #111827;">{{ $option['penguji3_nama'] }}</div>
                            </div>
                            <div style="min-width: 200px; background-color: #eff6ff; padding: 0.75rem; border-radius: 0.5rem; border-left: 4px solid #3b82f6;">
                                <div style="font-size: 0.75rem; color: #2563eb; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Penguji 4</div>
                                <div style="font-weight: 600; font-size: 0.95rem; color: #111827;">{{ $option['penguji4_nama'] }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </x-filament::section>
                @endforeach
            </div>
        </div>
        @endif
        
        @if($debugLogText && !$scheduleOptions)
        <div style="margin-top: 2rem; padding: 1rem; background-color: #fef2f2; border-radius: 0.5rem; border: 1px solid #fca5a5;">
            <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.5rem; color: #991b1b;">Debug Log Algoritma</h3>
            <pre style="font-size: 0.75rem; color: #7f1d1d; overflow-x: auto; white-space: pre-wrap;">{{ $debugLogText }}</pre>
        </div>
        @endif
    </div>
</x-filament-panels::page>