<?php

namespace Imagine\Foundation;

//use Illuminate\Contracts\Foundation\Application;
use Imagine\Router\Router;
//use Illuminate\Routing\Pipeline;

class Kernel{
	protected $project;
	protected $router;
	protected $middleware = [];
	
	public function __construct(Foundation $project, Router $router){
		$this->project = $project;
        $this->router = $router;
	}
	public function handle($request){
		$response = $this->sendRequestThroughRouter($request);
		return $response;
	}
	protected function sendRequestThroughRouter($request){
		$this->project->instance('request', $request);
		return $this->router->dispatch($request);
	}
}