--DECLARE @Date AS DATETIME
DECLARE @EndDate AS DATETIME
--SET @Date = /* T10.DocDate */ '[%0]'
SET @EndDate = /* T10.DocDate */ '[%0]'

SELECT

T10.DocDate as 'posting_date',
CASE 
WHEN T10.ObjType = 15 THEN 'MI'
WHEN T10.ObjType = 60 THEN 'GI'
END as 'doc_type',
T10.DocNum as 'doc_no',
T11.Project as 'project_code',
T12.OcrCode as 'dept_code',
T11.ItemCode as 'item_code',
T11.Quantity as 'qty',
T11.unitMsr as 'uom'
FROM ODLN T10
INNER JOIN DLN1 T11 ON T10.DocEntry = T11.DocEntry
INNER JOIN OOCR T12 ON T11.OcrCode = T12.OcrCode
WHERE T10.[DocDate] >= '01.01.2025' AND T10.[DocDate]  <= @EndDate
GROUP BY T11.Project, T10.DocDate, T10.ObjType, T10.DocNum, T10.Comments, T11.DocEntry, T12.OcrCode, T11.ItemCode, T11.Dscription, T11.Quantity, T11.unitMsr

UNION

SELECT
T10.DocDate as 'posting_date',
CASE 
WHEN T10.ObjType = 15 THEN 'MI'
WHEN T10.ObjType = 60 THEN 'GI'
END as 'doc_type',
T10.DocNum as 'doc_no',
T11.Project as 'project_code',
T12.OcrCode as 'dept_code',
T11.ItemCode as 'item-code',
T11.Quantity as 'qty',
T11.unitMsr as 'uom'
FROM OIGE T10
INNER JOIN IGE1 T11 ON T10.DocEntry = T11.DocEntry
INNER JOIN OOCR T12 ON T11.OcrCode = T12.OcrCode
WHERE T10.[DocDate] >= '01.01.2025' AND T10.[DocDate]  <= @EndDate
GROUP BY T10.U_MIS_Prepared, T11.Project, T10.DocDate, T10.ObjType, T10.DocNum, T10.Comments, T11.DocEntry, T12.OcrCode, T11.ItemCode, T11.Dscription, T11.Quantity, T11.unitMsr, T11.BaseRef
ORDER BY T10.DocNum