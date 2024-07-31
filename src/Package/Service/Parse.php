<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\File;

use Exception;
class Parse
{

    /**
     * @throws Exception
     */
    public static function compile(App $object, $flags, $options){
        if(!property_exists($options, 'source')){
            throw new Exception('Source not found');
        }
        if(File::exist($options->source) === false){
            throw new Exception('Source not found');
        }
        // Step 1: Read the template file
        $template = File::read($options->source);

        $token = Parse::token($object, $template, $flags, $options);

        ddd($token);

        // Step 2: Define the placeholder values
        $placeholders = [
                'name' => 'John Doe',
                'age' => '30',
                // Add more placeholders and their replacements as needed
        ];
        // Step 3: Replace placeholders with actual values
        foreach ($placeholders as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        // Step 4: Output the processed template
        dd($template);
    }

    public static function token(App $object, $string=''){
        $split = mb_str_split($string, 1);
        $token = [];
        $curly_count = 0;
        $line = 1;
        $column = 1;
        $row = '';
        $tag = false;
        $tag_list = [];
        foreach($split as $nr => $char){
            if($char === '{'){
                $curly_count++;
            }
            elseif($char === '}'){
                $curly_count--;
            }
            elseif($char === "\n"){
                $line++;
                $column = 1;
                if($curly_count === 0){
                    $row = '';
                }
            }
            if(
                $curly_count === 2 &&
                $char == '{'
            ){
                $tag = '{';
            }
            elseif($curly_count === 0){
                if($tag){
                    $tag .= $char;
                    if(empty($tag_list[$line])){
                        $tag_list[$line] = [];
                    }
                    $length = strlen($tag);
                    $tag_list[$line][] = [
                        'tag' => $tag,
                        'line' => $line,
                        'length' => $length,
                        'column' => [
                            'start' => $column - $length,
                            'end' => $column
                        ]
                    ];
                    $tag = false;
                }
            }
            $row .= $char;
            if($tag){
                $tag .= $char;
            }
            $column++;
        }
        d($string);
        ddd($tag_list);
    }
}