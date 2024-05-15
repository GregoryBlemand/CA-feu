<?php

/**
 * Consigne :
 *
 * Créez un programme qui remplace les caractères vides par des caractères plein pour représenter le plus grand carré possible sur un plateau.
 * Le plateau sera transmis dans un fichier.
 * La première ligne du fichier contient les informations pour lire la carte :
 * - nombre de lignes du plateau,
 * - caractères pour “vide”,
 * - “obstacle” et “plein”.
 *
 *
 * Exemples d’utilisation :
 * $> cat plateau
 * 9.xo
 * ...........................
 * ....x......................
 * ............x..............
 * ...........................
 * ....x......................
 * ...............x...........
 * ...........................
 * ......x..............x.....
 * ..x.......x................
 * $> ruby exo.rb plateau
 * .....ooooooo...............
 * ....xooooooo...............
 * .....ooooooox..............
 * .....ooooooo...............
 * ....xooooooo...............
 * .....ooooooo...x...........
 * .....ooooooo...............
 * ......x..............x.....
 * ..x.......x................
 *
 * Vous devez gérer les potentiels problèmes d’arguments, de fichiers, ou de carte invalide.
 *
 * Une carte est valide uniquement si :
 * - les lignes ont toute la même longueur,
 * - il y a au moins une ligne d’une case,
 * - les lignes sont séparées d’un retour à la ligne,
 * - les caractères présents dans la carte sont uniquement ceux de la première ligne
 *
 * En cas de plusieurs solutions, le carré le plus en haut à gauche sera choisi.
 *
 */

/*
 * analyse :
 *
 * Un carré = aucun obstacle dans la zone longueur au carré
 * stocker la liste des carrés trouvé
 * - coordonnées du coin supérieur gauche
 * - longueur max
 *
 * finir par garder la plus grande superficie possible
 * s'il y en a plusieurs, prendre celui qui a la plus petite coordonnée en x et en y
 *
 * il faut parcourir la map en diagonale
 * on part du coin en haut a gauche et on recherche la plus grande longueur dispo sans obstacle
 *
 */

class Square {

    public $x = 0;
    public $y = 0;

    public $length = 0;

    public function __construct($x = 0, $y = 0, $length = 0) {
        $this->x = $x;
        $this->y = $y;
        $this->length = $length;
    }

}

/* fonctions */
function getMaxSquare($obstacle, $map)
{
    $currentSquare = new Square();
    $maxSquare = new Square();

    $maxX = count($map[0]);
    $maxY = count($map);

    for ($i = 0; $i < $maxY; $i++) {
        for ($j = 0; $j < $maxX; $j++) {
            growSquare($obstacle, $map, $currentSquare);

            // je saute au prochaine coordonnées à vérifier
            if ($currentSquare->length > $maxSquare->length) {
                $maxSquare = $currentSquare;

                $j = $currentSquare->x + $maxSquare->length + 1;

                $currentSquare = new Square($j, $i);
            }
        }
    }

    return $maxSquare;
}

function growSquare($obstacle, $map, &$square)
{
    $maxTestLength = count($map);
    $noObstacle = true;

    // tant qu'il n'y a pas d'obstacle dans la zone, on augmente la superficie du carré pour retourner le plus grand possible
    while ($noObstacle) {
        for ($y = $square->y; $y < $square->y + $square->length + 1 && $y < $maxTestLength; $y++) {
            for ($x = $square->x; $x < $square->x + $square->length + 1; $x++) {
                if ($map[$y][$x] == $obstacle) {
                    $noObstacle = false;
                }
            }
        }

        if ($noObstacle) {
            $square->length++;
        }
    }
}

function getModifiedMap($map, $maxSquare, $fillCharacter)
{
    $modifiedMap = '';
    foreach ($map as $y => $line) {
        foreach ($line as $x => $char) {
            if (
                $y >= $maxSquare->y
                && $y < $maxSquare->y + $maxSquare->length
                && $x >= $maxSquare->x
                && $x < $maxSquare->x + $maxSquare->length
            ) {
                $modifiedMap .= $fillCharacter;
                continue;
            }

            $modifiedMap .= $char;
        }
        $modifiedMap.= "\n";
    }

    return $modifiedMap;
}

function getFileLines(string $path): array
{
    $file = file_get_contents($path);
    return explode("\n", $file);
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

$lines = getFileLines($argv[1]);
$nbFileLines = count($lines);
if (strlen($lines[0]) !== 4) {
    echo "Erreur : la ligne de paramétrage n'est pas valide.\n";
    exit;
}

if (count(array_unique(str_split($lines[0]))) !== 4) {
    echo "Erreur : il y a des doublons dans la ligne de paramétrage.\n";
    exit;
}

$parameters = str_split($lines[0]);
if (intval($parameters[0]) !== $nbFileLines - 1) {
    echo "Erreur : Le nombre de lignes ne correspond pas au paramêtre de nombre de lignes.\n";
    exit;
}

if (intval($parameters[0]) < 1) {
    echo "Erreur : Le nombre de lignes n'est pas valide'.\n";
    exit;
}

$regex = '/[';
for ($j = 1; $j < 4; $j++) {
    $regex.='^';
    if ($parameters[$j] == '.') $regex .= '\\';
    $regex .= $parameters[$j];
}
$regex .= ']/';

$length = 0;
for ($i = 1; $i < $nbFileLines; $i++) {
    if ($i == 1) {
        $length = strlen($lines[$i]);
    }

    if ($length != strlen($lines[$i])) {
        echo "Erreur : les lignes n'ont pas toutes la même taille.\n";
        exit;
    }

    if (preg_match($regex, $lines[$i])) {
        echo "Erreur : Des caractères invalides sont présents.\n";
        exit;
    }
}

if ($length == 0) {
    echo "Erreur : La carte doit comporter au moins une ligne d'une case.\n";
    exit;
}

/* récupération des données */
[$nbLines, $empty, $obstacle, $full] = str_split($lines[0]);

$map = [];
foreach ($lines as $k => $line) {
    if ($k == 0) continue;

    $map[] = str_split($line);
}

/* résolution */
$maxSquare = getMaxSquare($obstacle, $map);

/* affichage */
$result = getModifiedMap($map, $maxSquare, $full);

print $result;