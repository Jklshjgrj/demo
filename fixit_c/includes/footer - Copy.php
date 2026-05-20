<?php if (!isset($hide_sidebar) || !$hide_sidebar): ?>
        </div> <!-- End p-4/p-lg-5 -->
    </main> <!-- End content -->
</div> <!-- End app-shell -->
<?php endif; ?>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet.js JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Initialize Lucide Icons (safely, after DOM and scripts are ready) -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  });
</script>

<!-- App logic -->
<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>
