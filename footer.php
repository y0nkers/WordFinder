<footer class="d-flex flex-wrap align-items-center py-2 mt-auto border-top">
    <div class="container col-md-4 d-flex align-items-center justify-content-center">
        <a href="/" class="mb-3 me-2 mb-md-0 text-muted text-decoration-none lh-1">
            <img src="/assets/img/favicon.ico" width="32" height="32" alt="Поиск слов">
        </a>
        <span class="mb-md-0 text-muted">© 2023 Word Finder</span>
    </div>

</footer>

<!-- Bootstrap and jQuery scripts -->
<script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<?php if (basename($_SERVER['SCRIPT_FILENAME']) != "panel.php") echo '<script type="text/javascript" src="assets/js/helpers.js"></script>' ?>
<?php if (basename($_SERVER['SCRIPT_FILENAME']) == "index.php") echo '<script type="text/javascript" src="assets/js/main.js"></script>' ?>
<?php if (basename($_SERVER['SCRIPT_FILENAME']) == "rhyme.php") echo '<script type="text/javascript" src="assets/js/rhyme.js"></script>' ?>

</body>
</html>