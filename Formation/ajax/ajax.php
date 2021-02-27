<?php

// je peux récupérer le messageSaisie



$messageSaisie = $_POST["messageSaisie"] ?? "";

if ($messageSaisie != "") {
    $date = date("H:i:s");
    // echo " reçu ce message: ";
    // echo $messageSaisie;
    
    $ligne = 
    <<<x
    $date: $messageSaisie
    
    x;
    // on stocke dans un fichier
    file_put_contents("chat.txt", $ligne, FILE_APPEND);    
};
// on stocke dans un fichier

echo file_get_contents("chat.txt");