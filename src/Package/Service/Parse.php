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

        $tags = Parse::tags($object, $template, $flags, $options);
        $tags = Parse::tags_remove($object, $tags, $flags, $options);
        $tags = Parse::assign($object, $tags, $flags, $options);
        ddd($tags);

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

    public static function tags(App $object, $string=''): array
    {
        $split = mb_str_split($string, 1);
        $token = [];
        $curly_count = 0;
        $line = 1;
        $column = [];
        $column[$line] = 1;
        $row = '';
        $tag = false;
        $tag_list = [];
        $is_literal = false;
        foreach($split as $nr => $char){
            if($char === '{'){
                $curly_count++;
            }
            elseif($char === '}'){
                $curly_count--;
            }
            elseif($char === "\n"){
                $line++;
                $column[$line] = 1;
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
                    $column[$line]++;
                    if(empty($tag_list[$line])){
                        $tag_list[$line] = [];
                    }
                    $explode = explode("\n", $tag);
                    $count = count($explode);
                    if($count > 1){
                        $content = trim(substr($tag, 2, -2));
                        if(
                            strtoupper($content) === 'LITERAL' ||
                            $is_literal === true
                        ){
                            $is_literal = true;
                            $record['is_literal'] = true;
                        }
                        $length = strlen($explode[0]);
                        $record = [
                            'tag' => $tag,
                            'is_multiline' => true,
                            'line' => [
                                'start' => $line - $count + 1,
                                'end' => $line
                            ],
                            'length' => [
                                'start' => $length,
                                'end' => strlen($explode[$count - 1])
                            ],
                            'column' => [
                                ($line - $count + 1) => [
                                    'start' => $column[$line - $count + 1] - $length,
                                    'end' => $column[$line - $count + 1]
                                ],
                                $line => [
                                    'start' => $column[$line] - strlen($explode[$count - 1]),
                                    'end' => $column[$line]
                                ]
                            ]
                        ];
                        $tag_list[$line][] = $record;
                    } else {
                        $length = strlen($explode[0]);
                        $record = [
                            'tag' => $tag,
                            'line' => $line,
                            'length' => $length,
                            'column' => [
                                'start' => $column[$line] - $length,
                                'end' => $column[$line]
                            ]
                        ];
                        $content = trim(substr($tag, 2, -2));
                        if(strtoupper(substr($content, 0, 3)) === 'R3M'){
                            $record['is_header'] = true;
                            $record['content'] = $content;
                        }
                        elseif(
                            strtoupper($content) === 'LITERAL' ||
                            $is_literal === true
                        ){
                            $is_literal = true;
                            $record['is_literal'] = true;
                        }
                        elseif(
                            strtoupper($content) === '/LITERAL' ||
                            $is_literal === true
                        ){
                            $is_literal = false;
                            $record['is_literal'] = true;
                        }
                        $tag_list[$line][] = $record;
                    }
                    $tag = false;
                    $column[$line]--;
                }
            }
            $row .= $char;
            if($tag){
                $tag .= $char;
            }
            if($char !== "\n"){
                $column[$line]++;
            }
        }
        return $tag_list;
    }

    public static function tags_remove(App $object, $tags, $flags, $options): array
    {
        foreach($tags as $line => $tag){
            foreach($tag as $nr => $record){
                if(
                    array_key_exists('is_header', $record) ||
                    array_key_exists('is_literal', $record)
                ){
                    unset($tags[$line][$nr]);
                    if(empty($tags[$line])){
                        unset($tags[$line]);
                    }
                }
            }
        }
        return $tags;
    }

    public static function assign(App $object, $tags, $flags, $options): array
    {
        foreach($tags as $line => $tag){
            foreach($tag as $nr => $record){
                if(
                    array_key_exists('tag', $record)
                ){
                    $content = trim(substr($record['tag'], 2, -2));
                    if(substr($content, 0, 1) === '$'){
                        //we have a variable assign
                        $length = strlen($content);
                        $data = mb_str_split($content, 1);
                        $operator = false;
                        $before = '';
                        $after = '';
                        $is_after = false;
                        for($i=0; $i < $length; $i++){
                            $char = $data[$i];
                            if(
                                in_array(
                                    $char,
                                    [
                                        '=',
                                        '.',
                                        '+',
                                        '-'
                                    ],
                                    true
                                )
                            ){
                                $operator = $char;
                                continue;
                            }
                            if($operator && $is_after === false){
                                if(
                                    $char === ' ' ||
                                    $char === "\n" ||
                                    $char === "\t"
                                ) {
                                    $is_after = true;
                                    continue;
                                } else {
                                    $operator .= $char;
                                }
                            } elseif($is_after) {
                                $after .= $char;
                            } else {
                                $before .= $char;
                            }
                        }
                        d($before);
                        d($operator);
                        d($after);
                        ddd($data);

                    }


                }
            }
        }
        return $tags;
    }
}