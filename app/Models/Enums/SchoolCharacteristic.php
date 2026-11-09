<?php

namespace App\Models\Enums;

use Illuminate\Support\Collection;

enum SchoolCharacteristic: int
{
    case EARLY_CHILDHOOD_ACCREDITED = 1;
    case ELEMENTARY_ACCREDITED = 2;
    case EARLY_CHILDHOOD = 3;
    case ELEMENTARY = 4;

    public function name(): string
    {
        return match ($this) {
            self::EARLY_CHILDHOOD_ACCREDITED => 'Educação infantil (credenciada)',
            self::ELEMENTARY_ACCREDITED => 'Ensino fundamental (credenciada)',
            self::EARLY_CHILDHOOD => 'Educação infantil',
            self::ELEMENTARY => 'Ensino fundamental',
        };
    }

    /**
     * @return Collection<int, string>
     */
    public static function getDescriptiveValues(): Collection
    {
        return collect(self::cases())->mapWithKeys(fn (self $type) => [$type->value => $type->name()]);
    }

    /**
     * Características das escolas credenciadas (EEIs e EEFs).
     *
     * @return list<int>
     */
    public static function accreditedValues(): array
    {
        return [
            self::EARLY_CHILDHOOD_ACCREDITED->value,
            self::ELEMENTARY_ACCREDITED->value,
        ];
    }

    /**
     * Características das escolas próprias da Prefeitura (EMEIs e EMEFs).
     *
     * @return list<int>
     */
    public static function municipalValues(): array
    {
        return [
            self::EARLY_CHILDHOOD->value,
            self::ELEMENTARY->value,
        ];
    }

    public const REPORT_FILTER_ALL = 'todos';
    public const REPORT_FILTER_ACCREDITED = 'credenciadas';
    public const REPORT_FILTER_MUNICIPAL = 'proprias';

    /**
     * @return array<string, string>
     */
    public static function reportFilterOptions(): array
    {
        return [
            self::REPORT_FILTER_ALL => 'Todas',
            self::REPORT_FILTER_ACCREDITED => 'Credenciadas (EEIs e EEFs)',
            self::REPORT_FILTER_MUNICIPAL => 'Próprias da Prefeitura (EMEFs e EMEIs)',
        ];
    }

    /**
     * Argumento de filtro para relatórios.
     * "0" = sem filtro (todos); caso contrário, lista de características.
     */
    public static function toReportFilterArg(string $type): string
    {
        return match ($type) {
            self::REPORT_FILTER_ACCREDITED => implode(',', self::accreditedValues()),
            self::REPORT_FILTER_MUNICIPAL => implode(',', self::municipalValues()),
            default => '0',
        };
    }
}
