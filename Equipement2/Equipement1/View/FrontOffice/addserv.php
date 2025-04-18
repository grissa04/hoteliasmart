<?php
include_once 'C:\xampp\htdocs\hootelia\Equipement\config.php'; 
include_once 'C:\xampp\htdocs\hootelia\Equipement\Controller\servivecontrolle.php'; 

$pdo = config::getConnexion();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the values from the POST request
    $title = $_POST['name']; 
    $price = $_POST['price']; 
    $service = $_POST['service']; 
    $type = $_POST['type']; 

    // Initialize errors array
    $errors = [];

    // Validate common inputs
    if (!is_numeric($price) || $price <= 0) {
        $errors[] = "Please enter a valid price.";
    }

    // Service-specific validation
    if ($service == 1) {
        $solarNumber = $_POST['solarNumber'] ?? null;

        if (!is_numeric($solarNumber)) {
            $errors[] = "Please enter a valid number for the number of solar panels.";
        }

        if (empty($errors)) {
            $result = addService($pdo, $title, $solarNumber, $price, $service, $type);
            if ($result) {
                header('Location: home.php'); 
                exit();
            } else {
                echo "Error: Unable to add the data.";
            }
        }

    } elseif ($service == 2) {
        $taps = $_POST['taps'];
        $shower = $_POST['showerheads'];
        $toilet = $_POST['toilets'];

        if (!is_numeric($taps) || !is_numeric($shower) || !is_numeric($toilet)) {
            $errors[] = "Please enter valid numbers for taps, showerheads, and toilets.";
        }

        if (empty($errors)) {
            $total = $taps + $shower + $toilet;
            $result = addService($pdo, $title, $total, $price, $service, $type);
            if ($result) {
                header('Location: home.php'); 
                exit();
            } else {
                echo "Error: Unable to add the data.";
            }
        }

    } elseif ($service == 3) {
        $eco_soap = $_POST['eco_soap'];
        $disinfectant = $_POST['disinfectant'];

        $total = $eco_soap + $disinfectant;

        if (empty($errors)) {
            $result = addService($pdo, $title, $total, $price, $service, $type);
            if ($result) {
                header('Location: home.php');
                exit();
            } else {
                echo "Erreur : impossible d'ajouter le service.";
            }
        }

    } elseif ($service == 4) {
        $personne = $_POST['personne'];

        if (!is_numeric($personne) || $personne <= 0) {
            $errors[] = "Please enter a valid number of people for Smart Waste Management.";
        }

        if (empty($errors)) {
            $result = addService($pdo, $title, $personne, $price, $service, $type);
            if ($result) {
                header('Location: home.php');
                exit();
            } else {
                echo "Erreur : impossible d'ajouter le service.";
            }
        }

    } elseif ($service == 5) {
        $chambres = $_POST['chambres'];

        if (!is_numeric($chambres) || $chambres <= 0) {
            $errors[] = "Veuillez entrer un nombre valide de chambres pour Smart Technology Integration.";
        }

        if (empty($errors)) {
            $result = addService($pdo, $title, $chambres, $price, $service, $type);
            if ($result) {
                header('Location: home.php');
                exit();
            } else {
                echo "Erreur : impossible d'ajouter le service.";
            }
        }

    } elseif ($service == 6) {
        // ✅ Smart Jardin
        $jardin = $_POST['jardin'];

      

        if (!is_numeric($jardin) || $jardin <= 0) {
            $errors[] = "Veuillez entrer un nombre valide de jardins pour Smart Jardin.";
        }

        if (empty($errors)) {
            $result = addService($pdo, $title, $jardin, $price, $service, $type);
            if ($result) {
                header('Location: home.php');
                exit();
            } else {
                echo "Erreur : impossible d'ajouter le service Smart Jardin.";
            }
        }
    }

    // If there are validation errors, display them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}
?>
