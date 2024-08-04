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
        $previous_nr = false;
        $is_single_quote = false;
        foreach($input['array'] as $nr => $char) {
            if (is_array($char)) {
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
        }
        d($input['array']);
        return $input;
    }
}