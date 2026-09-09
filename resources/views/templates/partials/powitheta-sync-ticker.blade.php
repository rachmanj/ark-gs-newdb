<div id="powitheta-sync-ticker-host" class="d-none"
    style="position:fixed; bottom:34px; left:0; width:100%; z-index:1001; background:#856404; color:#fff; padding:7px 12px; font-size:13px; text-align:center; box-shadow:0 -2px 8px rgba(0,0,0,.25);">
    <i class="fas fa-sync fa-spin mr-2" aria-hidden="true"></i>
    <span id="powitheta-sync-ticker-text">Automatic SAP sync is running…</span>
</div>

<script>
    (function() {
        // Show one unified "running text" bar for BOTH scheduled SAP syncs:
        // POWITHETA (06:05/12:05) and staging modules GRPO/MIGI/Incoming (06:10/12:10).
        function fmtTime(iso) {
            if (!iso) return '';
            try {
                return new Date(iso).toLocaleTimeString('en-GB',
                    { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Makassar' }) + ' WITA';
            } catch (e) {
                return '';
            }
        }

        function show(msg) {
            var el = document.getElementById('powitheta-sync-ticker-host');
            if (!el) return;
            var t = document.getElementById('powitheta-sync-ticker-text');
            el.classList.remove('d-none');
            if (t && msg) t.textContent = msg;
        }

        function hide() {
            var el = document.getElementById('powitheta-sync-ticker-host');
            if (el) el.classList.add('d-none');
        }

        function checkSync() {
            if (typeof axios === 'undefined') return;
            var pending = 2;
            var shown = false;

            function done() {
                if (--pending === 0 && !shown) hide();
            }

            axios.get(@json(url('/api/powitheta-sync-status')))
                .then(function(r) {
                    if (r.data && r.data.in_progress) {
                        shown = true;
                        var at = fmtTime(r.data.started_at);
                        show('Automatic SAP sync is running… (PO With ETA' + (at ? ' · started ' + at : '') + ')');
                    }
                    done();
                })
                .catch(done);

            axios.get(@json(url('/api/staging-modules-sync-status')))
                .then(function(r) {
                    if (r.data && r.data.in_progress) {
                        shown = true;
                        var at = fmtTime(r.data.started_at);
                        show('Automatic SAP sync is running… (GRPO / MIGI / Incoming' + (at ? ' · started ' + at : '') + ')');
                    }
                    done();
                })
                .catch(done);
        }

        document.addEventListener('DOMContentLoaded', function() {
            checkSync();
            setInterval(checkSync, 12000);
        });
    })();
</script>
