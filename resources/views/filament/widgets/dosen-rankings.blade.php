<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Statistik Dosen
        </x-slot>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-family: sans-serif; font-size: 13px;">
                <thead>
                    {{-- Baris Header Utama --}}
                    <tr style="background: #ECEDF2; border-bottom: 2px solid #292C40;">
                        <th rowspan="2" style="padding: 12px 16px; text-align: left; font-weight: 700; color: #292C40; border-right: 1px solid #D8DAE5; vertical-align: middle; position: sticky; left: 0; background: #ECEDF2; z-index: 2; min-width: 200px;">
                            Nama Dosen
                        </th>
                        <th rowspan="2" style="padding: 12px 16px; text-align: left; font-weight: 700; color: #292C40; border-right: 1px solid #D8DAE5; vertical-align: middle; min-width: 180px;">
                            NIP
                        </th>
                        @if(count($months) > 0)
                            <th colspan="{{ count($months) }}" style="padding: 8px; text-align: center; font-weight: 700; color: #292C40; border-right: 1px solid #D8DAE5; border-left: 2px solid #292C40; border-bottom: 1px solid #292C40;">
                                Total Menguji
                            </th>
                        @endif
                        <th rowspan="2" style="padding: 12px 10px; text-align: center; font-weight: 800; color: #ffffff; background: #292C40; vertical-align: middle; min-width: 70px; border-left: 2px solid #1E2033;">
                            Total Keseluruhan
                        </th>
                    </tr>
                    {{-- Baris Sub-Header (Bulan) --}}
                    <tr style="background: #F0F1F5; border-bottom: 2px solid #D8DAE5;">
                        @foreach($months as $bulan)
                            <th style="padding: 8px 10px; text-align: center; font-weight: 600; color: #4A5078; font-size: 12px; border-right: 1px solid #D8DAE5; {{ $loop->first ? 'border-left: 2px solid #292C40;' : '' }}">
                                {{ \Illuminate\Support\Carbon::parse($bulan . '-01')->translatedFormat('M-y') }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($dosenData as $index => $dosen)
                        <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.15s; {{ $index % 2 === 0 ? 'background: #ffffff;' : 'background: #F8F9FC;' }}"
                            onmouseover="this.style.background='#ECEDF2'" 
                            onmouseout="this.style.background='{{ $index % 2 === 0 ? '#ffffff' : '#F8F9FC' }}'">
                            {{-- Nama Dosen --}}
                            <td style="padding: 12px 16px; font-weight: 600; color: #1f2937; border-right: 1px solid #f3f4f6; position: sticky; left: 0; {{ $index % 2 === 0 ? 'background: #ffffff;' : 'background: #F8F9FC;' }} z-index: 1;">
                                {{ $dosen['nama'] }}
                            </td>
                            {{-- NIP --}}
                            <td style="padding: 12px 16px; color: #4A5078; font-size: 12px; border-right: 1px solid #f3f4f6; font-family: monospace;">
                                {{ $dosen['nip'] }}
                            </td>
                            {{-- Per-bulan (Total gabungan) --}}
                            @foreach($months as $bulan)
                                @php $m = $dosen['months'][$bulan]; @endphp
                                <td style="padding: 12px 10px; text-align: center; font-weight: 600; color: {{ $m['total'] > 0 ? '#292C40' : '#d1d5db' }}; border-right: 1px solid #f3f4f6; {{ $loop->first ? 'border-left: 2px solid #D8DAE5;' : '' }}">
                                    {{ $m['total'] }}
                                </td>
                            @endforeach
                            {{-- Grand Total --}}
                            <td style="padding: 12px 10px; text-align: center; font-weight: 800; font-size: 16px; color: #292C40; border-left: 2px solid #D8DAE5;">
                                {{ $dosen['grand_total'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + count($months) }}" style="padding: 32px; text-align: center; color: #9ca3af; font-style: italic;">
                                Belum ada data sidang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
