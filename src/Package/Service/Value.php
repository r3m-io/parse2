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
        $value = '';
        foreach($input['array'] as $nr => $char) {
            if (is_array($char)) {
                if(array_key_exists('execute', $char)){
                    $value .= $char['execute'];
                }
                elseif(array_key_exists('value', $char)){
                    $value .= $char['value'];
                }
                continue;
            }
            if (
                $char === '\'' &&
                $is_single_quote === false
            ) {
                $is_single_quote = true;
            } elseif (
                $char === '\'' &&
                $is_single_quote === true
            ) {
                $is_single_quote = false;
            }
            if(
                $is_single_quote === false &&
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
            ddd($value);
        }
        return $input;
    }
}