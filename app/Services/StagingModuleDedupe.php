<?php

namespace App\Services;

use App\Models\Grpo;
use App\Models\Incoming;
use App\Models\Migi;
use Carbon\Carbon;

class StagingModuleDedupe
{
    /**
     * Natural key: one GRPO line from SAP (PO + GRPO + item).
     */
    public static function grpoRowExists($poNo, $grpoNo, $itemCode): bool
    {
        $poNo = $poNo !== null && $poNo !== '' ? (string) $poNo : null;
        $grpoNo = $grpoNo !== null && $grpoNo !== '' ? (string) $grpoNo : null;
        $itemCode = $itemCode !== null && $itemCode !== '' ? (string) $itemCode : null;

        $q = Grpo::query();
        self::whereNullableString($q, 'po_no', $poNo);
        self::whereNullableString($q, 'grpo_no', $grpoNo);
        self::whereNullableString($q, 'item_code', $itemCode);

        return $q->exists();
    }

    /**
     * Natural key: one MIGI / Incoming line (posting date + doc + item + project + dept).
     */
    public static function migiStyleRowExists(string $modelClass, $postingDate, $docType, $docNo, $itemCode, $projectCode, $deptCode): bool
    {
        $q = $modelClass::query();

        if ($postingDate === null || $postingDate === '') {
            $q->whereNull('posting_date');
        } else {
            $q->whereDate('posting_date', self::normalizeDate($postingDate));
        }

        $docType = $docType !== null && $docType !== '' ? (string) $docType : null;
        $docNo = $docNo !== null && $docNo !== '' ? (string) $docNo : null;
        $itemCode = $itemCode !== null && $itemCode !== '' ? (string) $itemCode : null;
        $projectCode = $projectCode !== null && $projectCode !== '' ? (string) $projectCode : null;
        $deptCode = $deptCode !== null && $deptCode !== '' ? (string) $deptCode : null;

        self::whereNullableString($q, 'doc_type', $docType);
        self::whereNullableString($q, 'doc_no', $docNo);
        self::whereNullableString($q, 'item_code', $itemCode);
        self::whereNullableString($q, 'project_code', $projectCode);
        self::whereNullableString($q, 'dept_code', $deptCode);

        return $q->exists();
    }

    private static function normalizeDate($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::parse($value)->format('Y-m-d');
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value)->format('Y-m-d');
        }

        return Carbon::now()->format('Y-m-d');
    }

    private static function whereNullableString($query, string $column, ?string $value): void
    {
        $v = $value === '' ? null : $value;
        if ($v === null) {
            $query->where(function ($q) use ($column) {
                $q->whereNull($column)->orWhere($column, '');
            });
        } else {
            $query->where($column, $v);
        }
    }
}
