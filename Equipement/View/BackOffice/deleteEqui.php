<?php

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../Controller/equipementController.php');

if (isset($_GET['reference'])) {
    $reference = $_GET['reference'];
    $equipementC = new equipementController();
    $equipementC->deleteEquipement($reference);
    header('Location: showEqui.php');
    exit();
} else {
    header('Location: showEqui.php');
    exit();
}
?>