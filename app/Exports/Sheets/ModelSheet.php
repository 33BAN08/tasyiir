<?php

namespace App\Exports\Sheets;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * One backup sheet per entity. Subclasses give the query and the columns; the
 * query runs as the signed-in user, so TenantScope limits every sheet to that
 * center's rows — nothing here filters on tenant_id by hand.
 */
abstract class ModelSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    abstract protected function query(): Builder;

    /** @return array<string, callable|string> heading => attribute name or fn(model): scalar */
    abstract protected function columns(): array;

    public function collection(): Collection
    {
        return $this->query()->get();
    }

    public function headings(): array
    {
        return array_keys($this->columns());
    }

    public function map($row): array
    {
        return array_map(function ($column) use ($row) {
            $value = is_callable($column) ? $column($row) : data_get($row, $column);

            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }

            return $value;
        }, array_values($this->columns()));
    }
}
