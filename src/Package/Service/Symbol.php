<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Symbol
{
    public static function define(App $object, $input, $flags, $options){
        $previous_nr = false;
        $is_single_quote = false;
        foreach($input as $nr => $char){
            if(
                $char === '\'' &&
                $is_single_quote === false
            ){
                $is_single_quote = true;
            }
            elseif(
                $char === '\'' &&
                $is_single_quote === true
            ){
                $is_single_quote = false;
            }
            if(
                $is_single_quote === false &&
                in_array(
                    $char,
                    [
                        '`',
                        '~',
                        '!',
                        '@',
                        '#',
                        '$',
                        '%',
                        '^',
                        '&',
                        '*',
                        '(',
                        ')',
                        '-',
                        '_',
                        '=',
                        '+',
                        '[',
                        ']',
                        '{',
                        '}',
                        '|',
                        '\\',
                        ':',
                        ';',
                        '"',
                        "'",
                        ',',
                        '.',
                        '<',
                        '>',
                        '/',
                        '?',
                    ],
                    true
                )
            ){
                if(
                    $previous_nr !== false &&
                    array_key_exists($previous_nr, $input) &&
                    is_array($input[$previous_nr]) &&
                    array_key_exists('is_symbol', $input[$previous_nr])
                ){
                    $previous_char = $input[$previous_nr]['value'];
                    $symbol = $previous_char . $char;
                    switch($symbol) {
                        case '{{':
                        case '}}':
                        case '++':
                        case '--':
                        case '<<':
                        case '>>':
                        case '<=':
                        case '>=':
                        case '==':
                        case '!=':
                        case '!!':
                        case '??':
                        case '&&':
                        case '||':
                        case '+=':
                        case '-=':
                        case '*=':
                        case '/=':
                        case '.=':
                        case '=>':
                        case '->':
                        case '::':
                        case '..':
                        case '...':
                        case '===':
                        case '<<=':
                        case '=>>':
                        case '!==':
                        case '!!!':
                        case '!!!!':
                            $input[$previous_nr] = [
                                'value' => $symbol,
                                'is_symbol' => true
                            ];
                            $input[$nr] = null;
                            break;
                        default:
                            $input[$nr] = [
                                'value' => $char,
                                'is_symbol' => true
                            ];
                    }
                } else {
                    $input[$nr] = [
                        'value' => $char,
                        'is_symbol' => true
                    ];
                }
            }
            $previous_nr = $nr;
        }
        return $input;
    }
}