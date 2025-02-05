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


if (!empty($_POST['eleve']) && !empty($_POST['matiere'])) {
    $eleve_id = $_POST['eleve'];
    $matiere_id = $_POST['matiere'];
    
   
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notes WHERE eleve_id = ? AND matiere_id = ?");
    $stmt->execute([$eleve_id, $matiere_id]);
    $existing = $stmt->fetchColumn();
    
    if ($existing == 0) {
       
        $insert = $pdo->prepare("INSERT INTO notes (eleve_id, matiere_id, note1, note2, note3) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([
            $eleve_id,
            $matiere_id,
            $_POST['note1'] !== '' ? $_POST['note1'] : null,
            $_POST['note2'] !== '' ? $_POST['note2'] : null,
            $_POST['note3'] !== '' ? $_POST['note3'] : null
        ]);
        echo "<div class='alert alert-success'>Notes enregistrées avec succès.</div>";
    } else {
        echo "<div class='alert alert-warning'>Les notes pour cette matière ont déjà été saisies.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Saisie des Notes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2 class="mb-4">Saisie des Notes</h2>
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
            <div class="mb-3">
                <label for="matiere" class="form-label">Choisir une matière :</label>
                <select name="matiere" id="matiere" class="form-select" required>
                    <option value="">Sélectionner une matière</option>
                    <?php 
                    $matieres = $pdo->query("SELECT id, nom FROM matieres")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($matieres as $matiere): ?>
                        <option value="<?= $matiere['id'] ?>"> <?= htmlspecialchars($matiere['nom']) ?> </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="note1" class="form-label">Note 1 :</label>
                <input type="number" name="note1" class="form-control" step="0.01" min="0" max="20">
            </div>
            <div class="mb-3">
                <label for="note2" class="form-label">Note 2 :</label>
                <input type="number" name="note2" class="form-control" step="0.01" min="0" max="20">
            </div>
            <div class="mb-3">
                <label for="note3" class="form-label">Note 3 :</label>
                <input type="number" name="note3" class="form-control" step="0.01" min="0" max="20">
            </div>
            <button type="submit" class="btn btn-success">Enregistrer les Notes</button>
        </form>
    <?php endif; ?>
</body>
</html>
