<?
/** DEV */
// require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>
<!-- DEV -->
<!-- <script type="module" src="http://localhost/@vite/client"></script>
<script type="module" src="http://localhost/src/main.js"></script> -->

<script> const BX = window.parent.BX; </script>

<script type="module" src="./assets/index.js?<?= filemtime(__DIR__ . "/assets/index.js")?>"></script>
<link href="./assets/index.css?<?= filemtime(__DIR__ . "/assets/index.css")?>" rel="stylesheet" />

<div id='app'></div>

<?
/** DEV */
// require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>