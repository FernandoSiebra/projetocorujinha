<?php
	
	file_exists($systemFile="system/System.php") ? require($systemFile) : die($systemFile . " do not found");

	class App extends System
	{

		private $controller;
		private $action;
		private $param;
		private $template;
		private $view;
		private $module;

		public function __construct()
		{
			parent::__construct();
			require("Routers.php");
			require("Controller.php");
			require("View.php");
			require("Model.php");
			$router = new Routers();

			if( $router->found )
			{
				$this->module = $router->module;
				$this->controller = $router->controller;
				$this->action = $router->action;
				$this->param = $router->param;
				$this->view = $router->view;
				$this->template = $router->template;
				define('URL_APP',$this->appURL);
				define('URL_MODULE',$this->appURL . (empty($this->module)?'/':('/'.$this->module.'/')));
				define("MODULE",empty($this->module)?"":$this->module);
				define("LOCATION",URL_APP.$_SERVER['REQUEST_URI']);
				$_GET = array_merge($_GET, $this->param);
			}
			else
			{
				die("Page not found");
			}

			$this->callController();

		}

		

		private function callController()
		{
			$controllerFile = sprintf("app/controller/%s.php",$this->controller);
			if( !empty($this->module) )
			{
				$controllerFile = sprintf("app/module/%s/%s",$this->module,substr($controllerFile,3));
			}
			try 
			{
				if( file_exists($controllerFile) )
				{
					require($controllerFile);
					$class = 'controller_' . $this->controller;
					if( class_exists($class) )
					{
						$controller = new $class;
						if( method_exists($controller, $this->action) )
						{
							if( is_subclass_of($controller, 'Controller') )
							{
								$action = $this->action;
								$controller->setParam($this->param);
								if( !empty($this->view) )
								{
									$this->viewFile();
									$controller->setView($this->view);
								}
								$controller->$action();
								if( is_object($this->view) )
								{
									$controller->view->render();
								}
							}
							else
							{
								throw new Exception("controller file: " . $controllerFile . "  class " . $this->controller . " should extends Controller");
							}
							
						}
						else
						{
							throw new Exception("controller file: " . $controllerFile . " has no method: " . $this->action);
						}
					}
					else
					{
						throw new Exception("Controller file: " . $controllerFile . " has no class: " . $class);
					}
				}
				else
				{
					throw new Exception("Controller file: " . $controllerFile . " do not found");
				}
			}
			catch ( Exception $e )
			{

				$this->handleException($e);
			}
		}

		public function viewFile()
		{
			if( empty($this->view) )
			{
				return false;
			}
			try
			{	
				$viewCode = "";

				if( !empty($this->template) )
				{
					$templateFile = sprintf("app/template/%s.html",$this->template);
					if( !empty($this->module) )
					{
						$templateFile = sprintf("app/module/%s/%s",$this->module,substr($templateFile,3));
					}
					if( file_exists($templateFile) )
					{
						$viewCode = file_get_contents($templateFile);
					}
					else
					{
						throw new Exception("template file: " . $templateFile . " do not found");
					}
				}

				$viewFile = sprintf("app/view/%s.html",$this->view);
				if( !empty($this->module) )
				{
					$viewFile = sprintf("app/module/%s/%s",$this->module,substr($viewFile,3));
				}
				if( file_exists($viewFile) )
				{
					if( empty($viewCode) )
					{
						$viewCode = file_get_contents($viewFile);
					}
					else
					{
						$_viewCode = file_get_contents($viewFile);
						$viewCode = str_replace("@view", $_viewCode, $viewCode);
					}
				}
				else
				{
					throw new Exception("view file: " . $viewFile . " do not found");	
				}
				$view = new View();
				$view->setCode($viewCode);
				$this->view = $view;
			}
			catch( Exception $e )
			{
				$this->handleException($e);
			}
		}

	}