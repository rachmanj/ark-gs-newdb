# POWITHETA → Purchase orders upsert (header patch + one-time lines)

**Status**: Implemented (2026-03-25). Validate with SAP as needed.

**Related code**: `PowithetaController::performConvertToPo()`, `sync_from_sap()`, `truncate()` — [`app/Http/Controllers/PowithetaController.php`](../app/Http/Controllers/PowithetaController.php). Staging table: `powithetas`.

**Automated runs**: Same upsert path via `powitheta:refresh-from-sap --scheduled` — see [planned-powitheta-scheduled-sync.md](planned-powitheta-scheduled-sync.md).

**Clarifications**

1. **`purchase_orders` / `purchase_order_items`**: Created or updated **only** from **`powithetas`** staging rows (`performConvertToPo`). SAP does not write normalized PO tables directly.
2. **`powithetas` from SAP**: `SapService::executePowithetaSqlQuery()` uses `resolvePowithetaSapDateRange()` so pulls are **limited to the current calendar year** (dates clamped to Jan 1 … min(today, Dec 31) of this year).

---

## Restated business rules

### New PO (no matching row in `purchase_orders`)

- **Match key**: `purchase_orders.doc_num` = `powithetas.po_no` (empty `po_no` rows are skipped).
- **Action**: **One-time full create** from `powithetas`:
  - Create `purchase_orders` header with the full field set used in conversion (supplier, dates, totals, project, etc.).
  - Create all related `purchase_order_items` rows for that `po_no`.

### Existing PO (row already exists for that `doc_num`)

- **Action on `purchase_orders`**: **Update only** these three columns from staging (`powithetas`), first row per `po_no` ordered by `id`:
  - `po_delivery_date`
  - `po_status`
  - `po_delivery_status`
- **Action on `purchase_order_items`**: **No changes**.

### Summary

| Situation | `purchase_orders` | `purchase_order_items` |
|-----------|-------------------|-------------------------|
| First time seeing this `po_no` | Full create | Full create |
| PO already exists | Patch **only** `po_delivery_date`, `po_status`, `po_delivery_status` | Leave unchanged |

---

## Implementation notes

1. **Database**: Migration `2026_03_25_002820_add_unique_index_to_purchase_orders_doc_num_table` deduplicates existing `purchase_orders` by `doc_num` (keeps lowest `id`, removes duplicate headers and their line rows), then adds unique index `purchase_orders_doc_num_unique` on `doc_num`.

2. **`truncate()`**: Clears **`powithetas` only** (normalized PO tables are not truncated).

3. **POs removed from SAP**: Local POs are not deleted when absent from staging.

---

## Action plan

1. [x] Data audit / dedupe + unique `doc_num` (migration).
2. [x] Refactor `performConvertToPo()` (upsert rules above).
3. [x] `truncate()` staging-only + UI copy (already aligned).
4. [ ] Ongoing: monitor SAP sync + re-sync behavior in production.

---

## References

- Scheduled job: [planned-powitheta-scheduled-sync.md](planned-powitheta-scheduled-sync.md)
- Memory: [016]/[017] in `MEMORY.md`

---

*Implemented 2026-03-25. SAP current-year scope documented 2026-03-25.*
