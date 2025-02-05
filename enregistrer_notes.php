<?php

$dsn = "mysql:host=localhost;dbname=ecole";
$user = "root";
$password = "";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $classe_id = $_POST['classe'];
    $matiere_id = $_POST['matiere'];
    $notes = $_POST['notes'];

    $pdo->beginTransaction();
    try {
        foreach ($notes as $eleve_id => $noteValues) {
            $note1 = !empty($noteValues[0]) ? $noteValues[0] : null;
            $note2 = !empty($noteValues[1]) ? $noteValues[1] : null;
            $note3 = !empty($noteValues[2]) ? $noteValues[2] : null;

            $stmt = $pdo->prepare("INSERT INTO notes (eleve_id, matiere_id, note1, note2, note3) VALUES (?, ?, ?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE note1 = VALUES(note1), note2 = VALUES(note2), note3 = VALUES(note3)");
            $stmt->execute([$eleve_id, $matiere_id, $note1, $note2, $note3]);
        }
        $pdo->commit();
        echo "<script>alert('Notes enregistrées avec succès'); window.location.href='saisie Note.php';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de l'enregistrement : " . $e->getMessage());
    }
}
?>
