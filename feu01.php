<?php

/**
 * Consigne :
 *
 * Créez un programme qui affiche un rectangle dans le terminal.
 *
 *
 * Exemples d’utilisation :
 * $> python exo.py 5 3
 * o---o
 * |   |
 * o---o
 *
 * $> python exo.py 5 1
 * o---o
 *
 * $> python exo.py 1 1
 * o
 *
 *
 * Gérer les problèmes potentiels d’arguments.
 *
 */

/* fonctions */
function isNumeric(string $string) {
    return preg_match('/[0-9]+/', $string) && !preg_match('/\D/', $string);
}

function selectCharToDraw($x, $y, $col, $lines) {
    // les coins
    if (in_array($x, [0, $col -1]) && in_array($y, [0, $lines -1])) {
            return 'o';
    }

    // première ligne et dernière ligne
    if (in_array($y, [0, $lines -1])) {
        return '-';
    }

    // première et dernière colonne
    if (in_array($x, [0, $col -1]) && !in_array($y, [0, $lines -1])) {
        return '|';
    }

    // le centre du rectangle
    return ' ';
}

function draw(int $col, int $lines) {
    $output = [];
    for ($i = 0; $i < $col; $i++) {
        for ($j = 0; $j < $lines; $j++) {
            if (!isset($output[$j])) {
                $output[$j] = '';
            }

            $char = selectCharToDraw($i, $j, $col, $lines);
            $output[$j].=$char;
        }
    }

    return $output;
}

/* gestion d'erreurs */
if ($argc < 3) {
    print "Erreur : paramètres manquants.\n";
    exit;
}

if ($argc > 3) {
    print "Erreur : Trop de paramètre.\n";
    exit;
}

for ($i = 1; $i < $argc; $i++) {
    if (!isNumeric($argv[$i])) {
        print "Erreur : le paramètre \"$argv[$i]\" n'est pas un entier positif.\n";
        exit;
    }
}

/* récupération des données */
$colNumber = $argv[1];
$lineNumber = $argv[2];

/* résolution */
$lines = draw($colNumber, $lineNumber);

/* affichage */
echo implode("\n", $lines)."\n";
