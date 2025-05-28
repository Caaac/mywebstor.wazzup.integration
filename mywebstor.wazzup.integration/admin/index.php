<script>
  const BX = window.parent.BX;
</script>

<!-- DEV -->
<!-- <script type="module" src="http://localhost/@vite/client"></script>
<script type="module" src="http://localhost/src/main.js"></script> -->

<script type="module" src="./assets/index.js?<?= filemtime(__DIR__ . "/assets/index.js") ?>"></script>
<link href="./assets/index.css?<?= filemtime(__DIR__ . "/assets/index.css") ?>" rel="stylesheet" />

<div id='app'></div>