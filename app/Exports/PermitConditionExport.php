<?php

namespace App\Exports;

use App\Models\Country;
use App\Models\IpCondition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PermitConditionExport implements FromQuery, WithHeadings, WithMapping
{
    protected ?string $itemName;
    protected ?string $category;
    protected ?string $usage;

    /**
     * code => name lookup, built once on first use rather than once per
     * row. The country table is small enough to load in full — cheaper
     * than a whereIn() query per IpCondition row.
     */
    protected ?Collection $countryLookup = null;

    public function __construct(?string $itemName = null, ?string $category = null, ?string $usage = null)
    {
        $this->itemName = $itemName;
        $this->category = $category;
        $this->usage = $usage;
    }

    protected function countryLookup(): Collection
    {
        return $this->countryLookup ??= Country::pluck('name', 'code');
    }

    /**
     * Mirrors the filters already applied client-side on the DataTable
     * (item name search, category select, usage select), so an export
     * taken while filtered only contains the filtered rows.
     */
    public function query(): QueryBuilder|Builder|Relation
    {
        return IpCondition::query()
            ->with('condcategory')
            ->when($this->itemName, fn (Builder $q) => $q->where('item_name', 'like', "%{$this->itemName}%"))
            ->when($this->category, fn (Builder $q) => $q->whereHas(
                'condcategory',
                fn (Builder $q2) => $q2->where('description', $this->category)
            ))
            ->when($this->usage, fn (Builder $q) => $q->whereJsonContains('usage', $this->usage))
            ->orderBy('item_name');
    }

    public function headings(): array
    {
        return [
            'Item Name',
            'Item Name in Bahasa',
            'Scientific Name',
            'Category',
            'Usage',
            'Country',
            'Quantity Limit',
            'Measurement Unit',
            'Start Date',
            'End Date',
            'Additional Condition',
        ];
    }

    public function map($condition): array
    {
        /** @var IpCondition $condition */
        // `usage` and `country` are cast to array on the model already.
        $usage = is_array($condition->usage) ? implode(', ', $condition->usage) : ($condition->usage ?? '-');

        $countryCodes = is_array($condition->country) ? $condition->country : ($condition->country ? [$condition->country] : []);
        $country = collect($countryCodes)
            ->map(fn ($code) => $this->countryLookup()->get($code, $code)) // fall back to the raw code if not found
            ->implode(', ') ?: '-';

        return [
            $condition->item_name,
            $condition->item_bahasa,
            $condition->scientific_name ?? '-',
            optional($condition->condcategory)->description ?? '-',
            $usage,
            $country,
            $condition->quantity_limit ?? '-',
            $condition->measurement_unit ?? '-',
            optional($condition->start_date)->format('d/m/Y') ?? '-',
            optional($condition->end_date)->format('d/m/Y') ?? '-',
            // Additional condition is HTML — strip tags so it reads cleanly in a spreadsheet cell.
            $condition->addional_condition ? strip_tags($condition->addional_condition) : '-',
        ];
    }
}