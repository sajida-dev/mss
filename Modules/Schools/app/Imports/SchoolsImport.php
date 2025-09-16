<?php

namespace Modules\Schools\Imports;

use Modules\Schools\App\Models\School;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SchoolsImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Basic validation: skip if required fields are missing
        if (empty($row['name']) || empty($row['address']) || empty($row['contact'])) {
            return null;
        }
        return new School([
            'name' => $row['name'],
            'address' => $row['address'],
            'contact' => $row['contact'],
        ]);
    }
}
