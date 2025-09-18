<?php
declare(strict_types=1);

/** Exception personnalisée pour le seed. */
class SeedException extends RuntimeException {}

/** Valide un article minimal (titre + slug). */
function validateArticle(array $a): void {
  if (!isset($a['title']) || !is_string($a['title']) || $a['title'] === '') {
    throw new DomainException("Article invalide: 'title' requis.");
  }
  if (!isset($a['slug']) || !is_string($a['slug']) || $a['slug'] === '') {
    throw new DomainException("Article invalide: 'slug' requis.");
  }
}

/** Charge et décode un JSON en tableau associatif avec gestion d’erreurs. */
function loadJson(string $path): array {
  $raw = @file_get_contents($path);
  if ($raw === false) {
    throw new SeedException("Fichier introuvable ou illisible: $path");
  }

  try {
    /** @var array $data */
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
  } catch (JsonException $je) {
    throw new SeedException("JSON invalide: $path", previous: $je);
  }

  if (!is_array($data)) {
    throw new UnexpectedValueException("Le JSON doit contenir un tableau racine.");
  }
  return $data;
}

/** Point d’entrée CLI : attraper TOUT et retourner un code de sortie propre. */
function main(array $argv): int {
  $path = $argv[1] ?? 'storage/seeds/articles.input.json';

  $articles = loadJson($path);              // peut lever SeedException
  foreach ($articles as $i => $a) {
    validateArticle($a);                    // peut lever DomainException
  }

  echo "[OK] $path: " . count($articles) . " article(s) valides." . PHP_EOL;
  return 0;
}

try {
  exit(main($argv));
} catch (Throwable $e) {
  $message = "[ERR] " . $e->getMessage() . PHP_EOL;
  fwrite(STDERR, $message);

  // Optionnel : contexte dev (cause imbriquée)
  if ($e->getPrevious()) {
    fwrite(STDERR, "Cause: " . get_class($e->getPrevious()) . " — " . $e->getPrevious()->getMessage() . PHP_EOL);
  }

  // Journalisation dans storage/logs/seed.log
  @mkdir('storage/logs', 0777, true);
  error_log(date('[Y-m-d H:i:s] ') . $message, 3, 'storage/logs/seed.log');

  exit(1);
}


// A SeedException is not a standard PHP class, 
// but rather a specific exception defined by the SeedStack framework 
// to handle configuration or injection errors within its ecosystem. 


/**
 * Documentation:
 * Ce code PHP est un script autonome (standalone) conçu pour être exécuté en ligne de commande (CLI).
 * Il s'agit d'un outil de validation et de préparation de données
 * pour un processus appelé "seed" (semis ou ensemencement de base de données).
 * En gros, il charge un fichier JSON contenant des données d'articles,
 * vérifie leur validité minimale (titre et slug obligatoires), et affiche un message de succès ou d'erreur.
 * Si tout va bien, il retourne un code de sortie 0 (succès) ; sinon, 1 (erreur).
 */