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
            <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1.5rem; color: #292C40;">Rekomendasi Jadwal Sidang</h2>
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
                        <div style="display: flex; gap: 2.5rem; border-bottom: 1px solid #D8DAE5; padding-bottom: 1.25rem; flex-wrap: wrap;">
                            <div>
                                <div style="font-size: 0.875rem; color: #4A5078; margin-bottom: 0.25rem;">Hari, Tanggal</div>
                                <div style="font-weight: bold; font-size: 1.125rem; color: #292C40;">{{ \Carbon\Carbon::parse($option['tanggal'])->locale('id')->translatedFormat('l, d F Y') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.875rem; color: #4A5078; margin-bottom: 0.25rem;">Waktu (WIB)</div>
                                <div style="font-weight: bold; font-size: 1.125rem; color: #292C40;">{{ \Carbon\Carbon::parse($option['jam_mulai'])->format('H:i') }} – {{ \Carbon\Carbon::parse($option['jam_selesai'])->format('H:i') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.875rem; color: #4A5078; margin-bottom: 0.25rem;">Ruangan</div>
                                <div style="font-weight: bold; font-size: 1.125rem; color: #292C40;">{{ $option['ruangan'] }}</div>
                            </div>
                        </div>

                        {{-- Dosen --}}
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <div style="min-width: 200px; background-color: #ECEDF2; padding: 0.75rem; border-radius: 0.5rem; border-left: 4px solid #292C40;">
                                <div style="font-size: 0.75rem; color: #292C40; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Ketua Sidang</div>
                                <div style="font-weight: 600; font-size: 0.95rem; color: #292C40;">{{ $option['ketua_sidang_nama'] }}</div>
                            </div>
                            <div style="min-width: 200px; background-color: #E8EAF6; padding: 0.75rem; border-radius: 0.5rem; border-left: 4px solid #4A5078;">
                                <div style="font-size: 0.75rem; color: #4A5078; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Penguji 1</div>
                                <div style="font-weight: 600; font-size: 0.95rem; color: #292C40;">{{ $option['penguji1_nama'] }}</div>
                            </div>
                            <div style="min-width: 200px; background-color: #E8EAF6; padding: 0.75rem; border-radius: 0.5rem; border-left: 4px solid #4A5078;">
                                <div style="font-size: 0.75rem; color: #4A5078; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Penguji 2</div>
                                <div style="font-weight: 600; font-size: 0.95rem; color: #292C40;">{{ $option['penguji2_nama'] }}</div>
                            </div>
                            @if($option['jenis_sidang'] === 'Sidang TA 2')
                            <div style="min-width: 200px; background-color: #E8EAF6; padding: 0.75rem; border-radius: 0.5rem; border-left: 4px solid #4A5078;">
                                <div style="font-size: 0.75rem; color: #4A5078; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Penguji 3</div>
                                <div style="font-weight: 600; font-size: 0.95rem; color: #292C40;">{{ $option['penguji3_nama'] }}</div>
                            </div>
                            <div style="min-width: 200px; background-color: #E8EAF6; padding: 0.75rem; border-radius: 0.5rem; border-left: 4px solid #4A5078;">
                                <div style="font-size: 0.75rem; color: #4A5078; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Penguji 4</div>
                                <div style="font-weight: 600; font-size: 0.95rem; color: #292C40;">{{ $option['penguji4_nama'] }}</div>
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
                    <h2 style="font-size: 1.5rem; font-weight: bold; color: #292C40; margin: 0;">Hasil Batch Penjadwalan</h2>
                    <p style="font-size: 0.875rem; color: #4A5078; margin-top: 0.25rem;">
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
                            <span style="font-weight: 700; color: #292C40;">{{ $result['mahasiswa_nama'] }}</span>
                            <span style="font-size: 0.875rem; color: #4A5078;">{{ $result['jenis_sidang'] }}</span>
                        </div>
                    </x-slot>

                    <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 0.5rem;">
                        {{-- Waktu & Tempat --}}
                        <div style="display: flex; gap: 2rem; flex-wrap: wrap; border-bottom: 1px solid #D8DAE5; padding-bottom: 1rem;">
                            <div>
                                <div style="font-size: 0.75rem; color: #4A5078; text-transform: uppercase; font-weight: 600;">Tanggal</div>
                                <div style="font-weight: bold; color: #292C40;">{{ \Carbon\Carbon::parse($result['tanggal'])->locale('id')->translatedFormat('l, d F Y') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: #4A5078; text-transform: uppercase; font-weight: 600;">Waktu</div>
                                <div style="font-weight: bold; color: #292C40;">{{ \Carbon\Carbon::parse($result['jam_mulai'])->format('H:i') }} – {{ \Carbon\Carbon::parse($result['jam_selesai'])->format('H:i') }} WIB</div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: #4A5078; text-transform: uppercase; font-weight: 600;">Ruangan</div>
                                <div style="font-weight: bold; color: #292C40;">{{ $result['ruangan'] }}</div>
                            </div>
                        </div>

                        {{-- Dosen --}}
                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <div style="min-width: 180px; background-color: #ECEDF2; padding: 0.6rem 0.75rem; border-radius: 0.375rem; border-left: 3px solid #292C40;">
                                <div style="font-size: 0.7rem; color: #292C40; font-weight: 800; text-transform: uppercase;">Ketua Sidang</div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #292C40;">{{ $result['ketua_sidang_nama'] }}</div>
                            </div>
                            <div style="min-width: 180px; background-color: #E8EAF6; padding: 0.6rem 0.75rem; border-radius: 0.375rem; border-left: 3px solid #4A5078;">
                                <div style="font-size: 0.7rem; color: #4A5078; font-weight: 800; text-transform: uppercase;">Penguji 1</div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #292C40;">{{ $result['penguji1_nama'] }}</div>
                            </div>
                            <div style="min-width: 180px; background-color: #E8EAF6; padding: 0.6rem 0.75rem; border-radius: 0.375rem; border-left: 3px solid #4A5078;">
                                <div style="font-size: 0.7rem; color: #4A5078; font-weight: 800; text-transform: uppercase;">Penguji 2</div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #292C40;">{{ $result['penguji2_nama'] }}</div>
                            </div>
                            @if(($result['jenis_sidang'] ?? '') === 'Sidang TA 2')
                            <div style="min-width: 180px; background-color: #E8EAF6; padding: 0.6rem 0.75rem; border-radius: 0.375rem; border-left: 3px solid #4A5078;">
                                <div style="font-size: 0.7rem; color: #4A5078; font-weight: 800; text-transform: uppercase;">Penguji 3</div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #292C40;">{{ $result['penguji3_nama'] ?? '-' }}</div>
                            </div>
                            <div style="min-width: 180px; background-color: #E8EAF6; padding: 0.6rem 0.75rem; border-radius: 0.375rem; border-left: 3px solid #4A5078;">
                                <div style="font-size: 0.7rem; color: #4A5078; font-weight: 800; text-transform: uppercase;">Penguji 4</div>
                                <div style="font-weight: 600; font-size: 0.875rem; color: #292C40;">{{ $result['penguji4_nama'] ?? '-' }}</div>
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

    {{-- Script: Auto-clear search input setelah memilih opsi di multi-select --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Observe the whole form area for Select components
            const observer = new MutationObserver(function () {
                // Find all multi-select containers (they have badge containers)
                document.querySelectorAll('.fi-select-input-ctn').forEach(function (container) {
                    // Skip if already patched
                    if (container._searchClearPatched) return;

                    const selectBtn = container.querySelector('.fi-select-input-btn');
                    if (!selectBtn) return;

                    // Watch for badge additions (= option selected)
                    const displayObserver = new MutationObserver(function () {
                        // Find the search input in the dropdown
                        const searchInput = container.querySelector('.fi-select-input-search-ctn input');
                        if (searchInput && searchInput.value !== '') {
                            searchInput.value = '';
                            searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });

                    const valueContainer = container.querySelector('.fi-select-input-value-ctn');
                    if (valueContainer) {
                        displayObserver.observe(valueContainer, { childList: true, subtree: true });
                        container._searchClearPatched = true;
                    }
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>
</x-filament-panels::page>