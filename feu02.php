<?php

/**
 * Consigne :
 *
 * Créez un programme qui reçoit une expression arithmétique dans une chaîne de caractères et en retourne le résultat après l’avoir calculé.
 *
 * Vous devez gérer les 5 opérateurs suivants : “+” pour l’addition, “-” pour la soustraction, “*” la multiplication, “/” la division et “%” le modulo.
 *
 * Exemple d’utilisation :
 *
 *
 * $> ruby exo.rb “4 + 21 * (1 - 2 / 2) + 38”
 * 42
 *
 *
 * Vous pouvez partir du principe que la chaîne de caractères donnée en argument sera valide.
 *
 */

/*
 * Analyse :
 * Priorité des opérateurs : *, / et % sont prioritaire sur la soustraction et l'addition.
 * la présence de parenthèses indique une priorité supérieure.
 * Il faut découper la chaine en suivant ces priorités.
 *
 * pour toutes expressions matématiques, on a un opérateur entouré de 2 opérandes qui sont des nombres ou expressions entourée par des parenthèses ou non.
 */

/* fonctions */
/**
 * Retourne la position dans la chaine donnée de la parenthèse recherchée
 *
 * @param string $string
 * @param string $parenthesis
 * @param bool $reversed
 * @return false|int
 */
function findParenthesis(string $string, string $parenthesis = '(', bool $reversed = true)
{
    if ($reversed) {
        return strrpos($string, $parenthesis);
    }

    return strpos($string, $parenthesis);
}

/**
 * retourne les opérateurs présents dans l'expression donnée
 *
 * @param string $string
 * @return string[]
 */
function findOperators(string $string) {
    preg_match_all('/([\+\-\%\*\/])/', $string, $operators);
    return $operators[0];
}

/**
 * Retourne la dernière expression entre parenthèse en partant de la droite de l'expression donnée
 *
 * @param string $string
 * @return false|string
 */
function searchStringInParenthesis(string $string)
{
    $startpos = findParenthesis($string);
    $subLength = findParenthesis(substr($string, $startpos), ')', false);

    return substr($string, $startpos, $subLength+1);
}

/**
 * Retourne le résultat d'une expression simple suivant la priorité des opérateurs
 *
 * @param string $expression
 * @return string
 */
function evaluateSimpleExpression(string $expression)
{
    $operators = findOperators($expression);
    if (!empty($operators)) {
        // d'abord les opérations prioritaire puis les autres
        while (array_intersect($operators, ['*', '/', '%'])){
            foreach ($operators as $k => $operator) {
                $subResult = 0;
                preg_match('/([0-9]+) '."\\".$operator.' ([0-9]+)/', $expression, $operands);

                if (!in_array($operator, ['*', '/', '%'])) {
                    continue;
                }

                $firstOp = intval($operands[1]);
                $secondOp = intval($operands[2]);

                switch ($operator) {
                    case '%':
                        $subResult = $firstOp % $secondOp;
                        break;

                    case '*':
                        $subResult = $firstOp * $secondOp;
                        break;

                    case '/':
                        $subResult = $firstOp / $secondOp;
                }

                $expression = str_replace($firstOp.' '.$operator.' '.$secondOp, $subResult, $expression);
                unset($operators[$k]);

            }

        }

        $operators = findOperators($expression);
        while (array_intersect($operators, ['+', '-'])){
            foreach ($operators as $k => $operator) {
                $subResult = 0;
                preg_match('/([0-9]+) '."\\".$operator.' ([0-9]+)/', $expression, $operands);

                $firstOp = intval($operands[1]);
                $secondOp = intval($operands[2]);

                switch ($operator) {
                    case '+':
                        $subResult = $firstOp + $secondOp;
                        break;

                    case '-':
                        $subResult = $firstOp - $secondOp;
                        break;
                }

                $expression = str_replace($firstOp.' '.$operator.' '.$secondOp, $subResult, $expression);
                unset($operators[$k]);
            }
        }
    }

    return $expression;
}

function calculateExpression(string $expression): string
{
    // tant qu'il y a des parenthèses, on en extrait le contenu pour l'évaluer
    while(findParenthesis($expression)) {

        $subExpression = searchStringInParenthesis($expression);
        $originStr = $subExpression;

        $subExpression = evaluateSimpleExpression($subExpression);

        $subExpression = str_replace(['(', ')'], '', $subExpression);
        $expression = str_replace($originStr, $subExpression, $expression);

    }

    // S'il n'y a plus de parenthèse, on retourne le résultat de l'expression passée
    return evaluateSimpleExpression($expression);
}


/* gestion d'erreurs */
if ($argc !== 2) {
    echo "Erreur : mauvaise utilisation.\n";
    exit;
}

/* récupération des données */
$expression = $argv[1];

/* résolution */
$result = calculateExpression($expression);

/* affichage */
echo $result."\n";
