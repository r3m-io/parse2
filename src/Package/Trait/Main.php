<?php
namespace Package\R3m\Io\Parse\Trait;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Cli;
use R3m\Io\Module\Core;

use R3m\Io\Node\Model\Node;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;

use Exception;

use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

trait Main {

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public function compile($flags, $options){
        d($flags);
        ddd($options);
        $object = $this->object();
    }
}