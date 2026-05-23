    <!-- Footer -->
    <footer class="app-footer text-center">
        <p class="text-xs text-text-muted">&copy; <?= date('Y') ?> SMP Negeri 6 Kendari. All rights reserved.</p>
    </footer>

    <!-- Custom JavaScript -->
    <script src="assets/js/app.js"></script>
    
    <?php if (isset($extraJS)): ?>
        <?= $extraJS ?>
    <?php endif; ?>
</body>
</html>
