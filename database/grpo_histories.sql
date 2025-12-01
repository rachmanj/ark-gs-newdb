--Query Penerimaan Barang (GRPO)
/* SELECT FROM [dbo].[OPDN] T1*/
--DECLARE @A AS DATETIME
DECLARE @B AS DATETIME
/* WHERE */
--SET @A = /* T1.DocDate 'FromDate'  */ '[%0]'
SET @B = /* T1.DocDate 'ToDate' */ '[%0]'

SELECT DISTINCT	
[OPOR].DocNum 'po_no',
[OPOR].DocDate'po_date', 
--[OPOR].CreateDate 'create_date',
[OPOR].U_MIS_DeliveryTime 'po_delivery_date',
[OPDN].DocDate 'grpo_date',
[OPDN].DocNum 'grpo_no',
--[OPOR].U_MIS_EstArrival 'po_eta',
CASE OPOR.U_ARK_DelivStat WHEN 'Y' THEN 'Delivered' WHEN 'N' THEN 'Not Delivered' END [po_delivery_status], 
[OPOR].CardCode 'vendor_code',
[PDN1].OcrCode 'dept_code',
[@MIS_CCDPT].Name 'dept_name',
[PDN1].U_MIS_UnitNo 'unit_no',
[PDN1].ItemCode 'item_code',  
[PDN1].Dscription 'description',
[PDN1].Quantity 'qty',
[PDN1].Currency 'grpo_currency',
[PDN1].Price 'unit_price',
[PDN1].Quantity*[PDN1].Price as [item_amount],
--[OPDN].DocTotalFC-[OPDN].VatSumFC+[OPDN].DiscSumFC as [total_grpo_before_tax],
--[OPDN].DocTotalFC [total_grpo_price],
[PDN1].unitMsr 'uom', 
--[PDN1].WhsCode 'whs_code',
--[OWHS].WhsName 'whs_name',
--[OPDN].U_MIS_Received 'received_by',
--[OPDN].U_MIS_Rectime 'receive_time',
[PDN1].Weight1 'weight', 
[PDN1].Project 'project_code', 
[OPOR].Comments 'remarks'
from [OPDN]
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

WHERE [OPDN].DocDate >= '01.01.2025' AND [OPDN].DocDate <= @B AND [OPDN].CANCELED != 'C'

FOR BROWSE