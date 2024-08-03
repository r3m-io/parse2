<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Symbol
{
    public static function define(App $object, $input, $flags, $options): array
    {
        $previous_nr = false;
        $is_single_quote = false;
        foreach($input['array'] as $nr => $char){
            if(is_array($char)){
                continue;
            }
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
                (
                    $is_single_quote === false ||
                    (
                        $char === '\'' &&
                        $is_single_quote === true
                    )
                )
                &&
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
                    array_key_exists($previous_nr, $input['array']) &&
                    is_array($input['array'][$previous_nr]) &&
                    array_key_exists('is_symbol', $input['array'][$previous_nr])
                ){
                    $previous_char = $input['array'][$previous_nr]['value'];
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
                            $input['array'][$previous_nr] = [
                                'value' => $symbol,
                                'is_symbol' => true
                            ];
                            $input[$nr] = null;
                            break;
                        default:
                            $input['array'][$nr] = [
                                'value' => $char,
                                'is_symbol' => true
                            ];
                    }
                } else {
                    $input['array'][$nr] = [
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