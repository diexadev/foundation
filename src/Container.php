<?php

namespace Imagine\Foundation;

use Closure;
use ReflectionClass;
use Imagine\Ioc\IoC;
use Imagine\Request\Request;
use Imagine\Router\Router;

class Container{
	private static $instance;
	private $base;
	private $service;
	private $shared;
	private $store;
	private $resolved;
	
	public final function __clone(){throw new Exception('Feature disabled.');}
	public final function __wakeup(){throw new Exception('Feature disabled.');}
	public final function __call($functionName, $arguments){}
	public final static function __callstatic($functionName, $arguments){}
	public function __construct($dirName){
		$this->base = $dirName;
		self::$instance = $this;
		return $this;
	}
	
	public final static function getInstance($dirName){
		if(is_null(self::$instance)){
			self::$instance = new static($dirName);
		}
		
		return self::$instance;
	}
	
	
	
	
	
	// Register Services
	public function register(string $name, $service, array $methods = [], $shared = false){
		if(is_callable($service)){
			$this->addCallback($name, $service, $shared);
		}elseif(is_array($methods) && !empty($methods)){
			$this->addSetter($name, $service, $methods, $shared);
		}else{
			$this->addAlias($name, $service, $shared);
		}
		
		return $this;
	}
	// Add Service Callback
	public function addCallback(string $name, Closure $service, bool $shared){
        $this->set($name, $service, $shared);
		
		return $this;
    }
	// Add Service Setter
	public function addSetter(string $name, string $service, array $methods, bool $shared){
        $define['concrete'] = $service;
        $define['methods'] = $methods;

        $this->set($name, $define, $shared);
		
		return $this;
    }
	// Add Service Alias
	public function addAlias(string $name, string $service, bool $shared){
        $this->set($name, $service, $shared);
		
		return $this;
    }
	
	
	
	
	
	
	public function make(string $service){
		if(isset($this->store[$service]) && !is_null($this->store[$service])){
			return $this->store[$service];
		}
		
		return $this->resolve($service);
	}
	public function resolve($service){
		if($this->has($service) === false){
			throw new Exception(sprintf('%s is not defined.', $service));
		}
		
		if($objService = $this->build($this->get($service))){
			$this->store[$service] = $objService;
			$this->resolved[$service] = true;
		}
		
		return $objService;
	}
	public function build($service){
		// Create a reflection.
        $reflector = new ReflectionClass($service);
        
        // Get it's constructor.
        $constructor = $reflector->getConstructor();
        
        // Search dependency and resolve. By constructor parameters.
        $args = $this->getDependencies($constructor->getParameters());

        return $reflector->newInstanceArgs($args);
	}
	public function getDependencies($parameters){
        $args = [];

        foreach($parameters as $parameter){
            // Get class from param.
            $isClass = $parameter->getClass();
            
            // Is param a class?
            if($isClass){
                // Yes, resolve param class, create instance, call resolve.
                $type = $parameter->name;

                // Assign to args, resolve.
                $args[] = $this->resolve($type); // Recursively.
            }else{
                // Just assign default value.
                $args[] = $parameter->getDefaultValue();
            }
        }

        return $args;
    }
	
	
//fungsi bawaan
	//set service
	public function set($name, $class, $shared): void{
		$this->service[$name] = $class;
		$this->shared[$name] = $shared;
	}
	//get service
	public function get($name){
		if(isset($this->service[$name])){
			return $this->service[$name];
		}
		
		return false;
	}
	//has service
	public function has($name){
		return isset($this->service[$name]);
	}
	public function offsetExists($offset): bool{
        return $this->getContainer()->offsetExists($offset);
    }
	public function offsetGet($offset){
        return $this->getContainer()->offsetGet($offset);
    }
	public function offsetSet($offset, $value): void{
        $this->getContainer()->offsetSet($offset, $value);
    }
	public function offsetUnset($offset): void{
        $this->getContainer()->offsetUnset($offset);
    }
	
}