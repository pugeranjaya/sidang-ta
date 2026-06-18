<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MahasiswaImport implements WithMultipleSheets
{
    use Importable;

    public ?string $sheetName;
    public MahasiswaSheetImport $sheetImport;

    public function __construct(?string $sheetName = null)
    {
        $this->sheetName = $sheetName;
        $this->sheetImport = new MahasiswaSheetImport();
    }

    public function sheets(): array
    {
        if (!empty($this->sheetName)) {
            return [
                $this->sheetName => $this->sheetImport,
            ];
        }

        // Jika tidak ada nama sheet yang diinput, import sheet pertama saja
        return [
            0 => $this->sheetImport,
        ];
    }
}
