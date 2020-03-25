<?php

namespace Anton;

class Validator {

    public $error = [];

    public $cache = [
        'step' => [
            'name' => []
        ]
    ];

    public function validate(array $configs){
        foreach ($configs as $key => $config) {
            $result = $this->validateProjectConfig($config);

            if(!$result){
                return $result;
            }
        }

        if(count($this->error) > 0){
            echo 'Error List: '.PHP_EOL;
            echo '---------------------'.PHP_EOL;
            foreach ($this->error as $key => $value) {
                echo $key.': '.$value.' '.PHP_EOL;
            }
            echo '---------------------'.PHP_EOL;
        }

        return true;
    }

    public function hasDots(string $data){
        return !!strpos('.', $data);
    }

    public function validateProject(array $data){
        if(empty($data['name'])){
            $this->addError('Project has no name.');
        }
        else{
            if($this->hasDots($data['name'])){
                $this->addError('Project name has no dots.');
            }
        }

        if(empty($data['repo'])){
            $this->addError('Project has no repo.');
        }
        else if($this->isGitRepo($data['repo'])){
            $this->addError('Repo isnt a git repo for ssh.');
        }

        return $this->hasErrors();
    }

    public function isGitRepo(string $url){
        return !!strpos('git@', $url);
    }

    public function validateServers(array $data){
        foreach ($data as $key => $value) {
            if(empty($value['domain'])){
                // @todo check access to server ?
                $this->addError('Servers '.$key. ' has no domain.');
            }
            else if($this->hasDots($value['domain'])){
                $this->addError('Servers domain has no dots.');
            }
            if(empty($value['user'])){
                $this->addError('Servers '.$key. ' has no user.');
            }
            if(empty($value['folder'])){
                $this->addError('Servers '.$key. ' has no folder.');
            }
        }

        return $this->hasErrors();
    }

    public function validatePipelines(array $data){
        foreach ($data as $key => $value) {
            if(!is_string($value)){
                $this->addError('Pipeline '.$key. ' isnt a string.');
            }
        }

        return $this->hasErrors();
    }

    public function validateSteps(array $data){
        foreach ($data as $key => $value) {
            if(empty($value['name'])){
                $this->addError('Step '.$key. ' atrribute name missing.');
            }
            else if(!empty($this->cache['step']['name'][$value['name']])){
                $this->addError('Step name '.$value['name']. ' already in use.');
            }
            if(empty($value['command'])){
                $this->addError('Step '.$key. ' atrribute command missing.');
            }
            if(empty($value['identifier'])){
                $this->addError('Step '.$key. ' atrribute identifier missing.');
            }

            if(!empty($value['name'])){
                $this->cache['step']['name'][$value['name']] = true;
            }
        }

        return $this->hasErrors();
    }

    public function addError(string $message):void{
        // @todo add project name for messages ?
        $this->error[] = $message;
    }

    public function validateProjectConfig(array $config):bool{
        if(!empty($config['project'])){
            if(count($config['project']) > 0){
                $this->validateProject($config['project']);
            }
            else{
                $this->addError('Project items missing.');
            }
        }
        else{
            $this->addError('Project config missing.');
        }

        if(!empty($config['servers']) && count($config['servers']) > 0){
            if(count($config['servers']) > 0){
                $this->validateServers($config['servers']);
            }
            else{
                $this->addError('Server items missing.');
            }
        }
        else{
            $this->addError('Servers config missing.');
        }

        if(!empty($config['pipelines']) && count($config['pipelines']) > 0){
            if(count($config['pipelines']) > 0){
                $this->validatePipelines($config['pipelines']);
            }
            else{
                $this->addError('Pipeline items missing.');
            }
        }
        else{
            $this->addError('Pipelines config missing.');
        }

        if(!empty($config['steps'])){
            if(count($config['steps']) > 0){
                $this->validateSteps($config['steps']);
            }
            else{
                $this->addError('Step items missing.');
            }
        }
        else{
            $this->addError('Steps items missing.');
        }

        return $this->hasErrors();
    }

    public function hasErrors():bool{
        return !!count($this->error);
    }

    public function getErrors():array{
        return $this->error;
    }
}