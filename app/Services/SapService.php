<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SapService
{
    public function executeGrpoSqlQuery($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        } else {
            $startDate = Carbon::parse($startDate)->format('Y-m-d');
        }

        if (!$endDate) {
            $endDate = Carbon::now()->format('Y-m-d');
        } else {
            $endDate = Carbon::parse($endDate)->format('Y-m-d');
        }

        $sql = "
            SELECT DISTINCT
                [OPOR].DocNum AS po_no,
                [OPOR].DocDate AS po_date,
                [OPOR].U_MIS_DeliveryTime AS po_delivery_date,
                [OPDN].DocDate AS grpo_date,
                [OPDN].DocNum AS grpo_no,
                CASE OPOR.U_ARK_DelivStat WHEN 'Y' THEN 'Delivered' WHEN 'N' THEN 'Not Delivered' END AS po_delivery_status,
                [OPOR].CardCode AS vendor_code,
                [PDN1].OcrCode AS dept_code,
                [PDN1].U_MIS_UnitNo AS unit_no,
                [PDN1].ItemCode AS item_code,
                [PDN1].Dscription AS description,
                [PDN1].Quantity AS qty,
                [PDN1].Currency AS grpo_currency,
                [PDN1].Price AS unit_price,
                [PDN1].Quantity * [PDN1].Price AS item_amount,
                [PDN1].unitMsr AS uom,
                [PDN1].Project AS project_code,
                [OPOR].Comments AS remarks
            FROM [OPDN]
            INNER JOIN [PDN1] ON [OPDN].DocEntry = [PDN1].DocEntry
            LEFT JOIN [OITM] ON [OITM].ItemCode = [PDN1].ItemCode
            LEFT JOIN [@MIS_CCDPT] ON [@MIS_CCDPT].Code = [PDN1].OcrCode
            LEFT JOIN [OWHS] ON [OWHS].WhsCode = [PDN1].WhsCode
            LEFT JOIN [OPRJ] ON [OPRJ].PrjCode = [PDN1].Project
            LEFT JOIN [OPOR] ON [PDN1].BaseRef = [OPOR].DocNum
            LEFT JOIN [ORDR] ON [OPOR].U_MIS_MRno = [ORDR].DocNum
            LEFT JOIN [RDR1] ON [ORDR].DocEntry = [RDR1].DocEntry
            LEFT JOIN [OSCL] ON [OPOR].U_MIS_WoNo = [OSCL].DocNum
            LEFT JOIN [ODLN] ON [RDR1].TrgetEntry = [ODLN].DocEntry
            LEFT JOIN [OWTR] ON [OPDN].DocNum = [OWTR].U_MIS_GRPONo
            LEFT JOIN [OIGE] ON [OPDN].DocNum = [OIGE].U_MIS_GRPONo
            WHERE [OPDN].DocDate >= ? 
                AND [OPDN].DocDate <= ? 
                AND [OPDN].CANCELED != 'C'
        ";

        try {
            $results = DB::connection('sap_sql')->select($sql, [
                $startDate,
                $endDate
            ]);

            return $results;
        } catch (\Exception $e) {
            throw new \Exception('Failed to execute GRPO SQL query: ' . $e->getMessage());
        }
    }

    public function executeIncomingSqlQuery($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        } else {
            $startDate = Carbon::parse($startDate)->format('Y-m-d');
        }

        if (!$endDate) {
            $endDate = Carbon::now()->format('Y-m-d');
        } else {
            $endDate = Carbon::parse($endDate)->format('Y-m-d');
        }

        $sql = "
            SELECT
                T10.DocDate AS posting_date,
                CASE 
                    WHEN T10.ObjType = 20 THEN 'GRPO'
                    WHEN T10.ObjType = 59 THEN 'GR'
                    WHEN T10.ObjType = 21 THEN 'G.Return'   
                END AS doc_type,
                T10.DocNum AS doc_no,
                T11.Project AS project_code,
                T12.OcrCode AS dept_code,
                T11.ItemCode AS item_code,
                T11.Quantity AS qty,
                T11.unitMsr AS uom
            FROM OPDN T10
            INNER JOIN PDN1 T11 ON T10.DocEntry = T11.DocEntry
            INNER JOIN OOCR T12 ON T11.OcrCode = T12.OcrCode
            WHERE T10.[DocDate] >= ? AND T10.[DocDate] <= ?
            GROUP BY T11.Project, T10.DocDate, T10.ObjType, T10.DocNum, T10.Comments, T11.DocEntry, T12.OcrCode, T11.ItemCode, T11.Dscription, T11.Quantity, T11.unitMsr
            
            UNION
            
            SELECT
                T10.DocDate AS posting_date,
                CASE 
                    WHEN T10.ObjType = 20 THEN 'GRPO'
                    WHEN T10.ObjType = 59 THEN 'GR'
                    WHEN T10.ObjType = 21 THEN 'G.Return'   
                END AS doc_type,
                T10.DocNum AS doc_no,
                T11.Project AS project_code,
                T12.OcrCode AS dept_code,
                T11.ItemCode AS item_code,
                T11.Quantity AS qty,
                T11.unitMsr AS uom
            FROM OIGN T10
            INNER JOIN IGN1 T11 ON T10.DocEntry = T11.DocEntry
            INNER JOIN OOCR T12 ON T11.OcrCode = T12.OcrCode
            WHERE T10.[DocDate] >= ? AND T10.[DocDate] <= ?
            GROUP BY T10.U_MIS_Prepared, T11.Project, T10.DocDate, T10.ObjType, T10.DocNum, T10.Comments, T11.DocEntry, T12.OcrCode, T11.ItemCode, T11.Dscription, T11.Quantity, T11.unitMsr
            
            UNION
            
            SELECT
                T10.DocDate AS posting_date,
                CASE 
                    WHEN T10.ObjType = 20 THEN 'GRPO'
                    WHEN T10.ObjType = 59 THEN 'GR'
                    WHEN T10.ObjType = 21 THEN 'G.Return'   
                END AS doc_type,
                T10.DocNum AS doc_no,
                T11.Project AS project_code,
                T12.OcrCode AS dept_code,
                T11.ItemCode AS item_code,
                T11.Quantity AS qty,
                T11.unitMsr AS uom
            FROM ORPD T10
            INNER JOIN RPD1 T11 ON T10.DocEntry = T11.DocEntry
            INNER JOIN OOCR T12 ON T11.OcrCode = T12.OcrCode
            WHERE T10.[DocDate] >= ? AND T10.[DocDate] <= ?
            GROUP BY T10.U_MIS_Prepared, T11.Project, T10.DocDate, T10.ObjType, T10.DocNum, T10.Comments, T11.DocEntry, T12.OcrCode, T11.ItemCode, T11.Dscription, T11.Quantity, T11.unitMsr
            ORDER BY doc_no
        ";

        try {
            $results = DB::connection('sap_sql')->select($sql, [
                $startDate, $endDate,
                $startDate, $endDate,
                $startDate, $endDate
            ]);

            return $results;
        } catch (\Exception $e) {
            throw new \Exception('Failed to execute Incoming SQL query: ' . $e->getMessage());
        }
    }

    public function executeMigiSqlQuery($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        } else {
            $startDate = Carbon::parse($startDate)->format('Y-m-d');
        }

        if (!$endDate) {
            $endDate = Carbon::now()->format('Y-m-d');
        } else {
            $endDate = Carbon::parse($endDate)->format('Y-m-d');
        }

        $sql = "
            SELECT
                T10.DocDate AS posting_date,
                CASE 
                    WHEN T10.ObjType = 15 THEN 'MI'
                    WHEN T10.ObjType = 60 THEN 'GI'
                END AS doc_type,
                T10.DocNum AS doc_no,
                T11.Project AS project_code,
                T12.OcrCode AS dept_code,
                T11.ItemCode AS item_code,
                T11.Quantity AS qty,
                T11.unitMsr AS uom
            FROM ODLN T10
            INNER JOIN DLN1 T11 ON T10.DocEntry = T11.DocEntry
            INNER JOIN OOCR T12 ON T11.OcrCode = T12.OcrCode
            WHERE T10.[DocDate] >= ? AND T10.[DocDate] <= ?
            GROUP BY T11.Project, T10.DocDate, T10.ObjType, T10.DocNum, T10.Comments, T11.DocEntry, T12.OcrCode, T11.ItemCode, T11.Dscription, T11.Quantity, T11.unitMsr
            
            UNION
            
            SELECT
                T10.DocDate AS posting_date,
                CASE 
                    WHEN T10.ObjType = 15 THEN 'MI'
                    WHEN T10.ObjType = 60 THEN 'GI'
                END AS doc_type,
                T10.DocNum AS doc_no,
                T11.Project AS project_code,
                T12.OcrCode AS dept_code,
                T11.ItemCode AS item_code,
                T11.Quantity AS qty,
                T11.unitMsr AS uom
            FROM OIGE T10
            INNER JOIN IGE1 T11 ON T10.DocEntry = T11.DocEntry
            INNER JOIN OOCR T12 ON T11.OcrCode = T12.OcrCode
            WHERE T10.[DocDate] >= ? AND T10.[DocDate] <= ?
            GROUP BY T10.U_MIS_Prepared, T11.Project, T10.DocDate, T10.ObjType, T10.DocNum, T10.Comments, T11.DocEntry, T12.OcrCode, T11.ItemCode, T11.Dscription, T11.Quantity, T11.unitMsr, T11.BaseRef
            ORDER BY doc_no
        ";

        try {
            $results = DB::connection('sap_sql')->select($sql, [
                $startDate, $endDate,
                $startDate, $endDate
            ]);

            return $results;
        } catch (\Exception $e) {
            throw new \Exception('Failed to execute Migi SQL query: ' . $e->getMessage());
        }
    }

    public function executePowithetaSqlQuery($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::parse('2024-12-01')->format('Y-m-d');
        } else {
            $startDate = Carbon::parse($startDate)->format('Y-m-d');
        }

        if (!$endDate) {
            $endDate = Carbon::now()->format('Y-m-d');
        } else {
            $endDate = Carbon::parse($endDate)->format('Y-m-d');
        }

        $sql = "
            SELECT DISTINCT
                A.DocNum AS po_no,
                A.DocDate AS posting_date,
                A.CreateDate AS create_date,
                A.U_MIS_DeliveryTime AS po_delivery_date,
                A.U_MIS_EstArrival AS po_eta,
                H.DocNum AS pr_no,
                A.CardCode AS vendor_code,
                A.CardName AS vendor_name,
                B.U_MIS_UnitNo AS unit_no,
                B.ItemCode AS item_code,
                B.Dscription AS description,
                B.Quantity AS qty,
                B.Currency AS po_currency,
                B.Price AS unit_price,
                B.Quantity * B.Price AS item_amount,
                A.DocTotalFC - A.VatSumFC + A.DiscSumFC AS total_po_price,
                A.DocTotalFC AS po_with_vat,
                B.unitMsr AS uom,
                B.Project AS project_code,
                cc.Code AS dept_code,
                CASE A.DocStatus 
                    WHEN 'O' THEN 'Open' 
                    WHEN 'C' THEN CASE A.Canceled WHEN 'Y' THEN 'Cancelled' ELSE 'Closed' END
                    ELSE A.DocStatus 
                END AS po_status,
                CASE A.U_ARK_DelivStat WHEN 'Y' THEN 'Delivered' WHEN 'N' THEN 'Not Delivered' END AS po_delivery_status,
                A.U_ARK_BudgetType AS budget_type
            FROM OPOR A
            INNER JOIN POR1 B ON A.DocEntry = B.DocEntry
            LEFT JOIN [@MIS_CCDPT] cc ON A.U_MIS_CCDepartement = cc.Code
            LEFT JOIN OPRQ H ON A.U_MIS_PRNo = H.DocNum
            WHERE A.DocDate >= ? AND A.DocDate <= ?
        ";

        try {
            $results = DB::connection('sap_sql')->select($sql, [
                $startDate,
                $endDate
            ]);

            return $results;
        } catch (\Exception $e) {
            throw new \Exception('Failed to execute Powitheta SQL query: ' . $e->getMessage());
        }
    }
}

