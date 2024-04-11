<?php

/**
 * Consigne :
 *
 * Créez un programme qui trouve et affiche la solution d’un Sudoku.
 *
 *
 * Exemples d’utilisation :
 * $> cat s.txt
 * 1957842..
 * 3.6529147
 * 4721.3985
 * 637852419
 * 8596.1732
 * 214397658
 * 92.418576
 * 5.8976321
 * 7612358.4
 *
 * $> ruby exo.rb s.txt
 * 195784263
 * 386529147
 * 472163985
 * 637852419
 * 859641732
 * 214397658
 * 923418576
 * 548976321
 * 761235894
 *
 * Afficher error et quitter le programme en cas de problèmes d’arguments.
 *
 */

/* fonctions */
function getDimensions(string $path): array
{
    $lines = getFileLines($path);
    $height = count($lines);
    $width = 0;

    foreach ($lines as $line) {
        if (strlen($line) > $width) {
            $width = strlen($line);
        }
    }

    return [$height, $width];
}

function getFileLines(string $path): array
{
    $file = file_get_contents($path);
    return explode("\n", $file);
}

function getBlocIndex($x, $y): int
{
    if ($y < 3) {
        if ($x < 3) return 1;
        elseif($x < 6) return 2;
        else return 3;
    } elseif ($y < 6) {
        if ($x < 3) return 4;
        elseif($x < 6) return 5;
        else return 6;
    } else {
        if ($x < 3) return 7;
        elseif($x < 6) return 8;
        else return 9;
    }
}

function createGridFromFile(string $path): array
{
    global $grid, $existsInLine, $existsInColumn, $existsInBloc;

    $lines = getFileLines($path);
    $n = pow(count($lines), 0.5);
    $listPositions = [];

    foreach ($lines as $y => $line) {
        $chars = str_split($line);
        foreach ($chars as $x => $char) {
            $bIndex = getBlocIndex($x, $y);
            $grid[$y][$x] = $char;
            if (preg_match('/[1-9]/', $char)) {
                $existsInLine[$y][$char] = true;
                $existsInColumn[$x][$char] = true;
                $existsInBloc[$bIndex][$char] = true;
            } else {
                $position= new stdClass();
                $position->x = $x;
                $position->y = $y;
                $position->possible = 0;
                $position->next = null;

                $listPositions[]= $position;
            }
        }
    }

    foreach ($listPositions as &$position) {
        for ($i = 0; $i < 9; $i++){
            if (isPossible($n, $position->x, $position->y, $i)) {
                $position->possible++;
            }
        }
    }

    /*var_dump($listPositions);*/
    $nbPos = count($listPositions);
    usort($listPositions, fn($a, $b) => $a->possible > $b->possible);

    for ($j = 0; $j < $nbPos - 1; $j++) {
        $listPositions[$j]->next = $listPositions[$j +1];
    }

    return [$grid, $listPositions];
}

function isPossible($n, $x, $y, $i)
{
    global $existsInLine, $existsInColumn, $existsInBloc;

    return empty($existsInColumn[$x][$i+1])
        && empty($existsInLine[$y][$i+1])
        && empty($existsInBloc[getBlocIndex($x, $y)][$i+1]);
}

function isValid(&$grid, $position)
{
    global $existsInLine, $existsInColumn, $existsInBloc;
    $n = pow(count($grid), 0.5);

    $x = $position->x;
    $y = $position->y;
    $bIndex = getBlocIndex($x, $y);

    for ($i = 0; $i < 9; $i++) {

        if (
            isPossible($n, $x, $y, $i)
        ) {
            $existsInColumn[$x][$i+1] = $existsInLine[$y][$i+1] = $existsInBloc[$bIndex][$i+1] = true;
            $grid[$y][$x] = $i+1;

            if ($position->next == null || isValid($grid, $position->next)){
                return true;
            }
            $existsInColumn[$x][$i+1] = $existsInLine[$y][$i+1] = $existsInBloc[$bIndex][$i+1] = false;
        }
    }

    return false;
}

/* gestion d'erreurs */
if ($argc != 2) {
    echo "Erreur : mauvaise utilisation\n";
    exit;
}

if (!is_file($argv[1])) {
    echo "Erreur : le fichier \"$argv[1]\n n'est pas lisible.\n";
    exit;
}

[$height, $width] = getDimensions($argv[1]);
if ($height != $width) {
    echo "Erreur : La grille fournie n'est pas carré.\n";
    exit;
}

/* récupération des données */
$gridFile = $argv[1];
[$grid, $listPositions] = createGridFromFile($gridFile);

/* résolution */
$valid = isValid($grid, $listPositions[0]);

/* affichage */
foreach ($grid as $row) {
    print implode(' ', $row)."\n";
}

