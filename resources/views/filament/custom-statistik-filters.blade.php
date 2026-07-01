<style>
    /* Sembunyikan icon funnel filter bawaan Filament */
    .fi-ta-header-toolbar button[title="Filter"],
    .fi-ta-header-toolbar .fi-icon-btn[title="Filter"] {
        display: none !important;
    }

    /* Styling elegan untuk Select Dropdown agar mirip native Filament */
    .custom-select-elegant {
        width: 150px;
        padding: 0.45rem 2.5rem 0.45rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        background-color: #ffffff;
        color: #374151;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        font-size: 0.875rem;
        font-weight: 500;
        outline: none;
        appearance: none;
        background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%236b7280%22%20aria-hidden%3D%22true%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20d%3D%22M5.293%207.293a1%201%200%20011.414%200L10%2010.586l3.293-3.293a1%201%200%20111.414%201.414l-4%204a1%201%200%2001-1.414%200l-4-4a1%201%200%20010-1.414z%22%20clip-rule%3D%22evenodd%22%2F%3E%3C%2Fsvg%3E');
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }

    .custom-select-elegant:focus {
        border-color: #292C40; /* Warna Navy primary */
        box-shadow: 0 0 0 1px #292C40;
    }

    /* Support Dark Mode untuk Dropdown tersinkronisasi dengan Filament Theme (bukan OS) */
    :is(.dark) .custom-select-elegant {
        background-color: rgba(255, 255, 255, 0.05); /* Match Filament dark mode input */
        border-color: rgba(255, 255, 255, 0.1);
        color: #f3f4f6;
    }
    :is(.dark) .custom-select-elegant option {
        background-color: #1f2937;
        color: #f3f4f6;
    }
    :is(.dark) .custom-select-elegant:focus {
        border-color: #4A5078;
        box-shadow: 0 0 0 1px #4A5078;
    }
</style>

<div x-data="{ jenisPeriode: @entangle('filterJenisPeriode').live, bulan: @entangle('filterBulan').live, minggu: @entangle('filterMinggu').live, tahun: @entangle('filterTahun').live }">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-right: 0.75rem;">
        
        <div style="position: relative;">
            <select x-model="jenisPeriode" class="custom-select-elegant" style="width: 140px;">
                <option value="bulanan">Bulanan</option>
                <option value="mingguan">Mingguan</option>
            </select>
        </div>

        <div style="position: relative;">
            <select x-model="bulan" class="custom-select-elegant" style="width: 140px;">
                <option value="semua">Semua Bulan</option>
                <option value="01">Januari</option>
                <option value="02">Februari</option>
                <option value="03">Maret</option>
                <option value="04">April</option>
                <option value="05">Mei</option>
                <option value="06">Juni</option>
                <option value="07">Juli</option>
                <option value="08">Agustus</option>
                <option value="09">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>
        </div>
        
        <div style="position: relative;" x-show="jenisPeriode === 'mingguan'" style="display: none;">
            <select x-model="minggu" class="custom-select-elegant" style="width: 140px;">
                <option value="semua">Semua Mgu</option>
                <option value="1">Minggu Ke-1</option>
                <option value="2">Minggu Ke-2</option>
                <option value="3">Minggu Ke-3</option>
                <option value="4">Minggu Ke-4</option>
                <option value="5">Minggu Ke-5</option>
            </select>
        </div>

        <div style="position: relative;">
            <select x-model="tahun" class="custom-select-elegant" style="width: 140px;">
                <option value="semua">Semua Thn</option>
                @php
                    // Ambil murni hanya tahun yang ada jadwal sidangnya di database
                    $years = \App\Models\Sidang::selectRaw('DISTINCT EXTRACT(YEAR FROM tanggal) as tahun')
                        ->whereNotNull('tanggal')
                        ->orderByDesc('tahun')
                        ->pluck('tahun')
                        ->toArray();
                @endphp
                @foreach($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
</div>
