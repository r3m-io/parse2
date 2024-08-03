<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Cast
{
    public static function define(App $object, $input, $flags, $options): array
    {
        $is_collect = false;
        $define = '';
        foreach($input['array'] as $nr => $char){
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '('
            ){
                $is_collect = $nr;
            }
            elseif(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === ')'
            ){
                if(strlen($define) > 0){
                    $is_define = false;
                    switch(strtolower($define)){
                        case 'int':
                        case 'integer':
                            $input['array'][$is_collect] = [
                                'value' => $define,
                                'is_cast' => true,
                                'cast' => 'integer'
                            ];
                            $is_define = true;
                        break;
                        case 'float':
                        case 'double':
                            $input['array'][$is_collect] = [
                                'value' => $define,
                                'is_cast' => true,
                                'cast' => 'float'
                            ];
                            $is_define = true;
                        break;
                        case 'boolean':
                        case 'bool':
                            $input['array'][$is_collect] = [
                                'value' => $define,
                                'is_cast' => true,
                                'cast' => 'boolean'
                            ];
                            $is_define = true;
                        break;
                        case 'array':
                            $input['array'][$is_collect] = [
                                'value' => $define,
                                'is_cast' => true,
                                'cast' => 'array'
                            ];
                            $is_define = true;
                        break;
                        case 'object':
                            $input['array'][$is_collect] = [
                                'value' => $define,
                                'is_cast' => true,
                                'cast' => 'object'
                            ];
                            $is_define = true;
                        break;
                        case 'clone':
                            $input['array'][$is_collect] = [
                                'value' => $define,
                                'is_cast' => true,
                                'cast' => 'clone'
                            ];
                            $is_define = true;
                        break;
                    }
                    if($is_define){
                        for($i = $is_collect + 1; $i <= $nr; $i++){
                            $input['array'][$i] = null;
                        }
                    }
                }
                $is_collect = false;
            }
            elseif(
                $is_collect !== false &&
                !is_array($char)
            ){
                if(
                    in_array(
                        $char,
                        [
                            ' ',
                            "\t",
                            "\n",
                            "\r",
                        ],
                        true
                    )
                ){
                    continue;
                }
                $define .= $char;
            }
        }
        return $input;
    }
}