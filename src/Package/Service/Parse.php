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

        $tags = Parse::tags($object, $template);

        d($tags);

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

    public static function tags(App $object, $string='', $pattern='/\{\{(.*?)\}\}/'){
        // Check for matches
        $result = [];
        if (preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $matchText = $match[0];
                $startPos = $match[1];
                $lineNumber = substr_count(substr($string, 0, $startPos), "\n") + 1;
                $lineStartPos = strrpos(substr($string, 0, $startPos), "\n") + 1;
                $charPos = $startPos - $lineStartPos + 1;
                $length = strlen($matchText);
                $result[] = [
                    'match' => $matchText,
                    'line' => $lineNumber,
                    'column' => [
                        'start' => $charPos,
                        'end' => $charPos + $length
                    ]
                ];
            }
        }
        return $result;
    }
}