<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Variable
{
    public static function define(App $object, $input, $flags, $options){
        $count = count($input);
        $is_variable = false;
        foreach($input as $nr => $char){
            $previous = $input[$nr - 1] ?? null;
            if(
                is_array($char) &&
                array_key_exists('value', $char)
            ){
                if($char['value'] === '$'){
                    $is_variable = $nr;
                    $name = '$';
                    for($i = $nr + 1; $i < $count; $i++){
                        if(
                            is_array($input[$i]) &&
                            array_key_exists('value', $input[$i])
                        ){
                            if(
                                in_array(
                                    $input[$i]['value'],
                                    [
                                        '_',
                                        '.'
                                    ]
                                )
                            ){
                                $name .= $input[$i]['value'];
                            } else {
                                break;
                            }
                        } else {
                            $name .= $input[$i];
                        }
                    }
                    if($name){
                        $is_reference = false;
                        if(
                            $previous !== null &&
                            is_array($previous) &&
                            array_key_exists('value', $previous)
                        ){
                            $is_reference = $previous['value'] === '&';
                            if($is_reference){
                                $input[$nr - 1] = null;
                            }
                        }
                        $input[$is_variable] = [
                            'type' => 'variable',
                            'value' => $name,
                            'name' => substr($name, 1),
                            'reference' => $is_reference
                        ];
                        $has_modifier = false;
                        $modifier = [];
                        for($i = $is_variable + 1; $i < $count; $i++){
                            $previous = $input[$i - 1] ?? null;
                            $next = $input[$i + 1] ?? null;
                            if(
                                is_array($input[$i]) &&
                                array_key_exists('value', $input[$i])
                            ){
                                if(
                                    in_array(
                                        $input[$i]['value'],
                                        [
                                            '_',
                                            '.'
                                        ]
                                    ) &&
                                    $has_modifier === false
                                ){
                                    $input[$i] = null;
                                }
                                elseif(
                                    $input[$i]['value'] === '|' &&
                                    $previous !== '|' &&
                                    $next !== '|'
                                ){
                                    $has_modifier = true;
                                }
                                elseif($has_modifier !== true) {
                                    break;
                                }
                                elseif($has_modifier === true){
                                    $modifier[] = $input[$i];
                                }
                            }
                            elseif($has_modifier !== true) {
                                $input[$i] = null;
                            }
                            elseif($has_modifier === true){
                                $modifier[] = $input[$i];
                            }
                        }
                        d($modifier);
                    }
                }
            }

        }
        return $input;
    }
}