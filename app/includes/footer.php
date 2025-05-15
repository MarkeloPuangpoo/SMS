    </div> <!-- ปิด main-content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // เปิดปิด submenu ถ้ามี
        document.querySelectorAll('.nav-link').forEach(link => {
            if(link.nextElementSibling && link.nextElementSibling.classList.contains('submenu')) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    link.nextElementSibling.classList.toggle('show');
                });
            }
        });
    </script>
</body>
</html>