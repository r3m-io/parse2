<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Value
{
    public static function define(App $object, $input, $flags, $options): array
    {
        $is_single_quote = false;
        $is_double_quote = false;
        $value = '';
        d($input);
        foreach($input['array'] as $nr => $char) {
            if (is_array($char)) {
                if(array_key_exists('execute', $char)){
                    $value .= $char['execute'];
                }
                elseif(array_key_exists('value', $char)){
                    if (
                        $char['value'] === '\'' &&
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ) {
                        $is_single_quote = true;
                    } elseif (
                        $char['value'] === '\'' &&
                        $is_single_quote === true &&
                        $is_double_quote === false
                    ) {
                        $is_single_quote = false;
                    }
                    elseif (
                        $char['value'] === '"' &&
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ) {
                        $is_double_quote = true;
                    } elseif (
                        $char['value'] === '"' &&
                        $is_single_quote === false &&
                        $is_double_quote === true
                    ) {
                        $is_double_quote = false;
                    }
                    $value .= $char['value'];
                }
                continue;
            }
            if (
                $char === '\'' &&
                $is_single_quote === false &&
                $is_double_quote === false
            ) {
                $is_single_quote = true;
            } elseif (
                $char === '\'' &&
                $is_single_quote === true &&
                $is_double_quote === false
            ) {
                $is_single_quote = false;
            }
            elseif (
                $char === '"' &&
                $is_single_quote === false &&
                $is_double_quote === false
            ) {
                $is_double_quote = true;
            } elseif (
                $char === '"' &&
                $is_single_quote === false &&
                $is_double_quote === true
            ) {
                $is_double_quote = false;
            }
            if(
                $is_single_quote === false &&
                $is_double_quote === false &&
                in_array(
                    $char,
                    [
                        ' ',
                        "\n",
                        "\r",
                        "\t",
                    ],
                true
                )
            ){
                ddd($value);
                $value = '';
            }
            $value .= $char;
        }
        if($value){
            switch($value){
                case 'true':
                    $input['array'] = [
                        'value' => $value,
                        'is_boolean' => true,
                        'execute' => true
                    ];
                    break;
                case 'false':
                    $input['array'] = [
                        'value' => $value,
                        'is_boolean' => true,
                        'execute' => false
                    ];
                    break;
                case 'null':
                    $input['array'] = [
                        'value' => $value,
                        'is_null' => true,
                        'execute' => null
                    ];
                    break;
                default:
                    if(
                        is_numeric($value) &&
                        strpos($value, '.') === false
                    ){
                        $input['array'] = [
                            'value' => $value,
                            'is_integer' => true,
                            'execute' => $value + 0
                        ];
                    }
                    elseif(
                        is_numeric($value) &&
                        strpos($value, '.') === true
                    ){
                        $input['array'] = [
                            'value' => $value,
                            'is_float' => true,
                            'execute' => $value + 0
                        ];
                    }
            }
        }
        return $input;
    }
}