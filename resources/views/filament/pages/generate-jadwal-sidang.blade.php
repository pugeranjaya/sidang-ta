<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div style="margin-top: 1.5rem; margin-bottom: 2rem;">
            <x-filament::button wire:click="generate" color="primary" size="lg" style="width: 100%; justify-content: center;">
                Buat Jadwal Sidang
            </x-filament::button>
        </div>

        {{-- ════════════════════════════════════════════════════════
             MODE SINGLE: Tampilkan pilihan opsi jadwal
        ════════════════════════════════════════════════════════ --}}
        @if($scheduleOptions && $mode === 'single')
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
                            </div>
                            <x-filament::button wire:click="simpanJadwal({{ $index }})" color="success" size="sm" icon="heroicon-m-check-circle">
                                Pilih &amp; Simpan Jadwal Ini
                            </x-filament::button>
                        </div>
                    </x-slot>

                    <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 0.5rem;">
                        {{-- Waktu & Tempat --}}
                        <div style="display: flex; gap: 2.5rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 1.25rem; flex-wrap: wrap;">
                            <div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Hari, Tanggal</div>
                                <div style="font-weight: bold; font-size: 1.125rem; color: #111827;">{{ \Carbon\Carbon::parse($option['tanggal'])->locale('id')->translatedFormat('l, d F Y') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Waktu (WIB)</div>
                                <div style="font-weight: bold; font-size: 1.125rem; color: #111827;">{{ \Carbon\Carbon::parse($option['jam_mulai'])->format('H:i') }} – {{ \Carbon\Carbon::parse($option['jam_selesai'])->format('H:i') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Ruangan</div>
                                <div style="font-weight: bold; font-size: 1.125rem; color: #111827;">{{ $option['ruangan'] }}</div>
                            </div>
                        </div>

                        {{-- Dosen --}}
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
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
                            @if($option['jenis_sidang'] === 'Sidang TA 2')
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

        {{-- ════════════════════════════════════════════════════════
             MODE BATCH: Tampilkan hasil per mahasiswa
        ════════════════════════════════════════════════════════ --}}
        @if($batchResults && $mode === 'batch')
        <div style="margin-top: 2rem;">
            {{-- Header & tombol simpan semua --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: bold; color: #111827; margin: 0;">Hasil Batch Penjadwalan</h2>
                    <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                        {{ collect($batchResults)->filter(fn($r) => $r['success'] ?? false)->count() }} berhasil &nbsp;·&nbsp;
                        {{ collect($batchResults)->reject(fn($r) => $r['success'] ?? false)->count() }} tidak dapat dijadwalkan
                    </p>
                </div>
                @if(collect($batchResults)->filter(fn($r) => $r['success'] ?? false)->count() > 0)
                <x-filament::button wire:click="simpanSemuaBatch" color="success" size="md" icon="heroicon-m-check-circle">
                    Simpan Semua Jadwal ({{ collect($batchResults)->filter(fn($r) => $r['success'] ?? false)->count() }})
                </x-filament::button>
                @endif
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($batchResults as $mhsId => $result)
                @if($result['success'] ?? false)
                {{-- Kartu berhasil --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            <x-filament::badge color="success">✓ Terjadwal</x-filament::badge>
                            <span style="font-weight: 700; color: #111827;">{{ $result['mahasiswa_nama'] }}</span>
                            <span style="font-size: 0.875rem; color: #6b7280;">{{ $result['jenis_sidang'] }}</span>
                        </div>
                    </x-slot>

                    <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 0.5rem;">
                        {{-- Waktu & Tempat --}}
                        <div style="display: flex; gap: 2rem; flex-wrap: wrap; border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem;">
                            <div>
                                <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600;">Tanggal</div>
                                <div style="font-weight: bold; color: #111827;">{{ \Carbon\Carbon::parse($result['tanggal'])->locale('id')->translatedFormat('l, d F Y') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600;">Waktu</div>
                                <div style="font-weight: bold; color: #111827;">{{ \Carbon\Carbon::parse($result['jam_mulai'])->format('H:i') }} – {{ \Carbon\Carbon::parse($result['jam_selesai'])->format('H:i') }} WIB</div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600;">Ruangan</div>
                                <div style="font-weight: bold; color: #111827;">{{ $result['ruangan'] }}</div>
                            </div>
                        </div>

                        {{-- Dosen --}}
                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <div style="min-width: 180px; background-color: #fef3c7; padding: 0.6rem 0.75rem; border-radius: 0.375rem; border-left: 3px solid #f59e0b;">
                                <div style="font-size: 0.7rem; color: #d97706; font-weight: 800; text-transform: uppercase;">Ketua Sidang</div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #111827;">{{ $result['ketua_sidang_nama'] }}</div>
                            </div>
                            <div style="min-width: 180px; background-color: #eff6ff; padding: 0.6rem 0.75rem; border-radius: 0.375rem; border-left: 3px solid #3b82f6;">
                                <div style="font-size: 0.7rem; color: #2563eb; font-weight: 800; text-transform: uppercase;">Penguji 1</div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #111827;">{{ $result['penguji1_nama'] }}</div>
                            </div>
                            <div style="min-width: 180px; background-color: #eff6ff; padding: 0.6rem 0.75rem; border-radius: 0.375rem; border-left: 3px solid #3b82f6;">
                                <div style="font-size: 0.7rem; color: #2563eb; font-weight: 800; text-transform: uppercase;">Penguji 2</div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #111827;">{{ $result['penguji2_nama'] }}</div>
                            </div>
                            @if(($result['jenis_sidang'] ?? '') === 'Sidang TA 2')
                            <div style="min-width: 180px; background-color: #eff6ff; padding: 0.6rem 0.75rem; border-radius: 0.375rem; border-left: 3px solid #3b82f6;">
                                <div style="font-size: 0.7rem; color: #2563eb; font-weight: 800; text-transform: uppercase;">Penguji 3</div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #111827;">{{ $result['penguji3_nama'] ?? '-' }}</div>
                            </div>
                            <div style="min-width: 180px; background-color: #eff6ff; padding: 0.6rem 0.75rem; border-radius: 0.375rem; border-left: 3px solid #3b82f6;">
                                <div style="font-size: 0.7rem; color: #2563eb; font-weight: 800; text-transform: uppercase;">Penguji 4</div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #111827;">{{ $result['penguji4_nama'] ?? '-' }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </x-filament::section>
                @else
                {{-- Kartu gagal --}}
                <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #ef4444; border-radius: 0.5rem; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
                    <div>
                        <x-filament::badge color="danger">✗ Gagal</x-filament::badge>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #991b1b;">{{ $result['mahasiswa_nama'] ?? 'ID: '.$mhsId }}</div>
                        <div style="font-size: 0.875rem; color: #b91c1c; margin-top: 0.125rem;">{{ $result['error'] ?? 'Tidak ada slot yang tersedia.' }}</div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- ════════════════════════════════════════════════════════
             Debug Log (hanya muncul saat gagal)
        ════════════════════════════════════════════════════════ --}}
        @if($debugLogText && !$scheduleOptions && !$batchResults)
        <div style="margin-top: 2rem; padding: 1rem; background-color: #fef2f2; border-radius: 0.5rem; border: 1px solid #fca5a5;">
            <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 0.5rem; color: #991b1b;">Debug Log Algoritma</h3>
            <pre style="font-size: 0.75rem; color: #7f1d1d; overflow-x: auto; white-space: pre-wrap;">{{ $debugLogText }}</pre>
        </div>
        @endif
    </div>
</x-filament-panels::page>