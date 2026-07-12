<?php

namespace App\Imports;

use App\Models\Student;
use App\Services\StudentImporter;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithLimit;
use Illuminate\Support\Facades\Log;

class StudentsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithLimit
{
    public function __construct(
        private StudentImporter $importer,
    ) {}

    public function limit(): int
    {
        return 5000;
    }

    public function collection(\Illuminate\Support\Collection $collection): void
    {
        Log::info('Import collection start.');

        $dispatcher = Student::getEventDispatcher();
        Student::unsetEventDispatcher();

        try {
            foreach ($collection as $row) {
                $this->importer->addRow($row->toArray());
            }
        } finally {
            if ($dispatcher) {
                Student::setEventDispatcher($dispatcher);
            }
        }
    }
}
