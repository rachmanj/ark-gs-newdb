<div id="powitheta-sync-ticker-host" class="d-none"
    style="position:fixed; bottom:34px; left:0; width:100%; z-index:1001; background:#856404; color:#fff; padding:7px 12px; font-size:13px; text-align:center; box-shadow:0 -2px 8px rgba(0,0,0,.25);">
    <i class="fas fa-sync fa-spin mr-2" aria-hidden="true"></i>
    <span id="powitheta-sync-ticker-text">Automatic SAP sync (PO With ETA) is running…</span>
</div>

<script>
    (function() {
        function checkPowithetaSync() {
            if (typeof axios === 'undefined') return;
            axios.get(@json(url('/api/powitheta-sync-status')))
                .then(function(r) {
                    var el = document.getElementById('powitheta-sync-ticker-host');
                    if (!el) return;
                    if (r.data && r.data.in_progress) {
                        el.classList.remove('d-none');
                        var t = document.getElementById('powitheta-sync-ticker-text');
                        if (t && r.data.started_at) {
                            t.textContent = 'Automatic SAP sync (PO With ETA) is running… (started ' + r.data.started_at + ')';
                        }
                    } else {
                        el.classList.add('d-none');
                    }
                })
                .catch(function() {});
        }
        document.addEventListener('DOMContentLoaded', function() {
            checkPowithetaSync();
            setInterval(checkPowithetaSync, 12000);
        });
    })();
</script>
