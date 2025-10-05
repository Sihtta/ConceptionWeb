<?php
echo "<pre>";

$file = __DIR__ . '/data/users.json';

echo "== Diagnostic d'écriture ==\n";
echo "Chemin attendu : $file\n";
echo "Fichier existe ? " . (file_exists($file) ? "✅ oui" : "❌ non") . "\n";
echo "Fichier écrivable ? " . (is_writable($file) ? "✅ oui" : "❌ non") . "\n";
echo "Contenu actuel :\n";
echo file_get_contents($file);
echo "\n\nTentative d'écriture...\n";

$data = [
    ['login' => 'test', 'password' => '123', 'nom' => 'Dupont', 'prenom' => 'Jean']
];

$result = @file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

if ($result === false) {
    echo "❌ Échec d'écriture : " . error_get_last()['message'] . "\n";
} else {
    echo "✅ Écriture réussie ($result octets)\n";
}

echo "\nContenu après écriture :\n";
echo file_get_contents($file);
