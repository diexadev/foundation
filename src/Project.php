<?php

namespace Nicotine\Foundation;

use Imagine\IoC\Container;
use Imagine\Http\Request;

class Project extends Container{
	private static $instance;
	private $basePath;
	
	public $service;
	protected $extenders;
	protected $shared;
	protected $store;
	protected $resolved;
	
	public final function __clone(){throw new Exception('Feature disabled.');}
	public final function __wakeup(){throw new Exception('Feature disabled.');}
	public final function __call($functionName, $arguments){}
	public final static function __callstatic($functionName, $arguments){}
	public function __construct($basePath){
		$this->basePath = $basePath;
		
		self::$instance = $this;
		
		return $this;
	}
	public final static function getInstance($basePath){
		if(is_null(self::$instance)){
			self::$instance = new static($basePath);
		}
		
		return self::$instance;
	}
	public function register(string $service, $callback = null, array $methods = [], $shared = false){
		return parent::register($service, $callback, $methods, $shared);
	}
	public function make(string $service, array $parameters = []){
		return parent::make($service, $parameters);
	}
	public function resolve(string $service, array $parameters = []){
		return parent::resolve($service, $parameters);
	}
	public function build(string $callback, array $parameters = []){
		return parent::build($callback, $parameters);
	}
	
	
	
	
	
	
	public function bind(string $var, object $callable): void{
		//print_r(get_class($callable));exit;
		$this->$var = $callable;
	}
	
	public function handle(Request $request){
		$controller = $this->router->match($request);//print_r($controller);exit;
		if(is_callable($controller)){
			return $controller();
		}
		
		if(is_string($controller)){
			$exploded = explode('@', $controller);
			if(array_key_exists(1, $exploded)){
				$class = $exploded[0];
				$method = $exploded[1];
				
				return $this->make($class)->$method();
			}
			
			return $this->make($controller);
		}
		//return $this->make($controller);
	}
	
}