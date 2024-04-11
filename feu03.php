<?php

/**
 * Consigne :
 *
 * Créez un programme qui affiche la position de l’élément le plus en haut à gauche (dans l’ordre) d’une forme au sein d’un plateau.
 *
 *
 * Exemples d’utilisation :
 * $> cat board.txt
 * 0000
 * 1111
 * 2331
 * $> cat to_find.txt
 * 11
 *  1
 * $> cat unfindable.txt
 * 00
 * 00
 *
 * $> ruby exo.rb board.txt to_find.txt
 * Trouvé !
 * Coordonnées : 2,1
 * ----
 * --11
 * ---1
 *
 * $> ruby exo.rb board.txt unfindable.txt
 * Introuvable
 *
 * Vous devez gérer les potentiels problèmes d’arguments et de lecture de fichiers.
 *
 */

/* fonctions */
// récuperer le nombre max de caractères des lignes (largeur) du fichier et le nombre de ligne du fichier (hauteur)
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

function findPattern($board, $to_find)
{
    /* coordonnées à retourner position x,y du premier caractère du motif */

    $board_lines = getFileLines($board);
    $to_find_lines = getFileLines($to_find);

    [$board_height, $board_width] = getDimensions($board);
    [$to_find_height, $to_find_width] = getDimensions($to_find);
    $stopX = $board_width - $to_find_width;
    $stopY = $board_height - $to_find_height;

    for ($y = 0; $y <= $stopY; $y++) {
        $pattern = str_replace(' ', '.', $to_find_lines[0]);

        if (!preg_match('/'.$pattern.'/', $board_lines[$y], $matches, PREG_OFFSET_CAPTURE)) {
            continue;
        }

        $to_find_x = $matches[0][1];
        for ($x = 0; $x <= $stopX; $x++) {

            // on doit comparer chaque ligne restante du pattern
            $completePatten = true;
            for ($subY = 1; $subY < $to_find_height; $subY++) {
                $board_line = substr($board_lines[$y + $subY], $to_find_x);
                $pattern = str_replace(' ', '.', $to_find_lines[$subY]);
                $found = preg_match('/'.$pattern.'/', $board_line, $matches, PREG_OFFSET_CAPTURE);

                if (empty($found) || (int) $matches[0][1] !== $to_find_x + $x) {
                    $completePatten = false;
                    break;
                }
            }

            // s'il a entière correspondance, on retourne [$to_find_x + $x, $y]
            if ($completePatten) {
                return [$to_find_x + $x, $y];
            }
            // sinon, on décale x de 1 (continue), et on retente la full correspondance
        }

    }

    // arrivé ici, on a pas trouvé
    return false;
}

function drawPatternPosition($board_width, $board_height, $position_x, $position_y, $patternFile)
{
    $patternLines = getFileLines($patternFile);
    $patternHeight = count($patternLines);

    $output = '';
    $j = 0;
    for ($i = 0; $i < $board_height; $i++) {
        if ($i < $position_y || $i >= $position_y + $patternHeight)
        {
            $output .= str_repeat('-', $board_width)."\n";
            continue;
        }

        if ($j < $patternHeight) {
            $output .= str_repeat('-', $position_x);
            $output .= str_replace(' ', '-', $patternLines[$j]);
            $written = $position_x + strlen($patternLines[$j]);
            if ($board_width > $written) {
                $output .= str_repeat('-', $board_width - $written);
            }
            $output .= "\n";
        }
        $j++;
    }

    print $output;
}

/* gestion d'erreurs */
// il faut vérifier qu'il y a bien 2 arguments
if ($argc !== 3) {
    echo "Erreur : mauvaise utilisation.\n";
    exit;
}

for ($i = 1; $i < $argc; $i++) {
// que ces 2 arguments sont bien des fichiers lisibles
    if (!is_file($argv[$i])) {
        echo "Erreur : Impossible d'ouvrir le fichier \"$argv[$i]\" n'est pas lisible.\n";
        exit;
    }

    // que le motif à trouver n'est pas supérieur en taille au plateau à inspecter
    if (
        $i == 1
        && (
            getDimensions($argv[$i])[0] < getDimensions($argv[$i + 1])[0]
            || getDimensions($argv[$i])[1] < getDimensions($argv[$i + 1])[1]
        )
    ) {
        echo "Erreur : Le motif a rechercher est plus grand que le tableau.\n";
        exit;
    }
}


/* récupération des données */
$board = $argv[1];
$board_dimensions = getDimensions($board);
$to_find = $argv[2];

/* résolution */
$location = findPattern($board, $to_find);

/* affichage */
if (false === $location) {
    echo "Introuvable\n";
    exit;
}

echo "Trouvé !\nCoordonnées : $location[0],$location[1]\n";
drawPatternPosition($board_dimensions[1], $board_dimensions[0], $location[0], $location[1], $to_find);
