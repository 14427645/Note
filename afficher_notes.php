<?php
require_once('tcpdf/tcpdf.php');

$dsn = "mysql:host=localhost;dbname=ecole";
$user = "root";
$password = "";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


$classes = $pdo->query("SELECT id, nom FROM classes")->fetchAll(PDO::FETCH_ASSOC);


$eleves = [];
if (!empty($_POST['classe'])) {
    $classe_id = $_POST['classe'];
    $eleves = $pdo->prepare("SELECT id, nom FROM eleves WHERE classe_id = ?");
    $eleves->execute([$classe_id]);
    $eleves = $eleves->fetchAll(PDO::FETCH_ASSOC);
}


if (!empty($_POST['eleve'])) {
    $eleve_id = $_POST['eleve'];
    $eleve_nom = $pdo->prepare("SELECT nom FROM eleves WHERE id = ?");
    $eleve_nom->execute([$eleve_id]);
    $eleve_nom = $eleve_nom->fetchColumn();

    $notes = $pdo->prepare("SELECT m.nom as matiere, n.note1, n.note2, n.note3 FROM notes n
                            JOIN matieres m ON n.matiere_id = m.id
                            WHERE n.eleve_id = ?");
    $notes->execute([$eleve_id]);
    $notes = $notes->fetchAll(PDO::FETCH_ASSOC);

  
    $pdf = new TCPDF();
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $pdf->Cell(0, 10, "Bulletin de notes - $eleve_nom", 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->Cell(60, 10, "Matière", 1);
    $pdf->Cell(40, 10, "Note 1", 1);
    $pdf->Cell(40, 10, "Note 2", 1);
    $pdf->Cell(40, 10, "Note 3", 1);
    $pdf->Ln();

    foreach ($notes as $note) {
        $pdf->Cell(60, 10, $note['matiere'], 1);
        $pdf->Cell(40, 10, $note['note1'] !== null ? $note['note1'] : '-', 1);
        $pdf->Cell(40, 10, $note['note2'] !== null ? $note['note2'] : '-', 1);
        $pdf->Cell(40, 10, $note['note3'] !== null ? $note['note3'] : '-', 1);
        $pdf->Ln();
    }

    $pdf->Output("bulletin_$eleve_nom.pdf", 'D');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Afficher les Notes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2 class="mb-4">Afficher les Notes</h2>
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="classe" class="form-label">Choisir une classe :</label>
            <select name="classe" id="classe" class="form-select" onchange="this.form.submit()" required>
                <option value="">Sélectionner une classe</option>
                <?php foreach ($classes as $classe): ?>
                    <option value="<?= $classe['id'] ?>" <?= (!empty($_POST['classe']) && $_POST['classe'] == $classe['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($classe['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if (!empty($eleves)): ?>
        <form method="POST">
            <input type="hidden" name="classe" value="<?= $_POST['classe'] ?>">
            <div class="mb-3">
                <label for="eleve" class="form-label">Choisir un élève :</label>
                <select name="eleve" id="eleve" class="form-select" required>
                    <option value="">Sélectionner un élève</option>
                    <?php foreach ($eleves as $eleve): ?>
                        <option value="<?= $eleve['id'] ?>"> <?= htmlspecialchars($eleve['nom']) ?> </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Générer PDF</button>
        </form>
    <?php endif; ?>
</body>
</html>
