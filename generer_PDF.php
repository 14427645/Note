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

$classe_id = $_GET['classe'] ?? null;
$matiere_id = $_GET['matiere'] ?? null;

if (!$classe_id || !$matiere_id) {
    die("Veuillez sélectionner une classe et une matière.");
}


$classe = $pdo->prepare("SELECT nom FROM classes WHERE id = ?");
$classe->execute([$classe_id]);
$classe_nom = $classe->fetchColumn();

$matiere = $pdo->prepare("SELECT nom FROM matieres WHERE id = ?");
$matiere->execute([$matiere_id]);
$matiere_nom = $matiere->fetchColumn();


$stmt = $pdo->prepare("
    SELECT e.nom AS eleve_nom, n.note1, n.note2, n.note3 
    FROM eleves e 
    LEFT JOIN notes n ON e.id = n.eleve_id AND n.matiere_id = ? 
    WHERE e.classe_id = ?
");
$stmt->execute([$matiere_id, $classe_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);


$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Système de gestion des notes');
$pdf->SetTitle('Liste des Notes');
$pdf->SetSubject('Notes des élèves');


$pdf->AddPage();


$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, "Liste des Notes", 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, "Classe: $classe_nom | Matière: $matiere_nom", 0, 1, 'C');


$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(70, 10, "Nom de l'élève", 1, 0, 'C');
$pdf->Cell(30, 10, "Note 1", 1, 0, 'C');
$pdf->Cell(30, 10, "Note 2", 1, 0, 'C');
$pdf->Cell(30, 10, "Note 3", 1, 1, 'C');


$pdf->SetFont('helvetica', '', 12);
foreach ($notes as $note) {
    $pdf->Cell(70, 10, htmlspecialchars($note['eleve_nom']), 1, 0, 'C');
    $pdf->Cell(30, 10, $note['note1'] ?? 'N/A', 1, 0, 'C');
    $pdf->Cell(30, 10, $note['note2'] ?? 'N/A', 1, 0, 'C');
    $pdf->Cell(30, 10, $note['note3'] ?? 'N/A', 1, 1, 'C');
}

$pdf->Output('notes.pdf', 'I'); 
