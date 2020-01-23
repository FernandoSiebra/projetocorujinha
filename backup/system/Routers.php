<?php

	class Routers extends System
	{

		private $routers;
		private $config;
		public $module = "";
		public $found = false;
		public $controller = "";
		public $action = "";
		public $param = array();
		public $template = "";
		public $view = "";

		public function __construct()
		{
			parent::__construct();
			$this->getConfig();
			$this->getModule();
			$this->getRouters();
			$this->readRouters();
		}

		private function getModule()
		{
			try
			{
				if( isset($this->config['modules']) )
				{

					if( property_exists($this->config['modules'], $this->uriParts[0]) )
					{
						
						$config = $this->config['modules'];
						$module = $this->uriParts[0];
						if ( $config->$module !== 'false' )
						{
							$this->module = $this->uriParts[0];
							array_shift($this->uriParts);
							$this->uri = implode("/",$this->uriParts);
							if( empty($this->uri) || $this->uri == "/" )
							{
								$this->uri = "home";
							}
						}
					}
				}
				else
				{
					throw new Exception("Config file must have modules definition");
				}
			}
			catch ( Exception $e )
			{
				$this->handleException($e);
			}
		}

		private function readRouters()
		{
			if ( $this->uri == '/' )
			{
				$this->uri = "home";
			}
			if( isset($this->routers[$this->uri]) )
			{

				$routerFound = $this->routers[$this->uri];

				if( isset($routerFound->type) )
				{
					$types = explode(",",$routerFound->type);
					if( !in_array(strtolower($_SERVER['REQUEST_METHOD']),$types) )
					{
						return false;
					}
				}

				$this->found = true;
				$this->controller = $routerFound->controller;
				$this->action = $routerFound->action;
				if( isset($routerFound->view) )
				{
					$this->view = $routerFound->view;
				}

				if( isset($routerFound->template) )
				{
					$this->template = $routerFound->template;
				}
			}
			else
			{
				if ( strpos($this->uri,'/') !== false ) 
				{
					$routerKeys = array_keys($this->routers);
					$routersMatch = array();
					foreach( $routerKeys as $router )
					{
						if( count($this->uriParts) == count($routers = explode("/",$router)) && strpos($router,':') !== false && $this->uriParts[0] == $routers[0] )
						{
							$match = true;
							$i = 0;
							foreach( $this->uriParts as $part )
							{
								if( $part !== $routers[$i] && strpos($routers[$i],':') === false )
								{
									$match = false;
								}
								$i++;
							}
							if( $match )
							{
								$routersMatch[$router] = $this->routers[$router];
							}
						}
					}
					if( count($routersMatch) !== 0 )
					{
						$this->routerComplex($routersMatch);
					}
				}
			}
		}

		private function routerComplex($routersMatch)
		{

			$routerKeys = array_keys($routersMatch);
			foreach( $routerKeys as $router )
			{
				$routerParts = explode("/",$router);
				array_shift($routerParts);
				$uriParts = explode("/",$this->uri);
				array_shift($uriParts);
				$routerObject = $routersMatch[$router];
				$x = 0;
				$validateRoute = true;
				foreach( $routerParts as $part )
				{
					if( $validateRoute )
					{
						$validatePart = true;
						$isVar = substr($part,0,1) == ':';

						$variable = substr($part,1);
						if( isset($routerObject->constraints->$variable) )
						{
							$constraint = $routerObject->constraints->$variable; 
							if( $constraint == "numeric" )
							{
								if( !is_numeric($uriParts[$x]) )
								{
									$validatePart = $validatePart ? false : true; 
								}
							}
						}
						if( $validatePart )
						{
							if( $isVar )
							{
								$this->param[$variable] = $uriParts[$x];
							}
						}
						else
						{
							$this->param = array();
							$validateRoute = false; 
						}
					}
					$x++;
				}
				if( $validateRoute )
				{
					$this->found = true;
					$this->controller = $routerObject->controller;
					$this->action = $routerObject->action;
					if( isset($routerObject->view) )
					{
						$this->view = $routerObject->view;
					}
					if( isset($routerObject->template) )
					{
						$this->template = $routerObject->template;
					}
				}
			}
			
		}

		private function getRouters()
		{
			try
			{
				$routersFile = "app/config/routers.json";
				if( !empty($this->module) )
				{
					$routersFile = sprintf("app/module/%s/%s",$this->module,substr($routersFile,3));
				}
				if( file_exists($routersFile) )
				{
					if( $this->isJson($routers = file_get_contents($routersFile)) )
					{
						$this->routers = (array) json_decode($routers);
					}
					else
					{
						throw new Exception("Routers file has systax error");
					}
				}
				else
				{
					throw new Exception('Routers File: ' . $routersFile . " do not found");
				}
			}
			catch ( Exception $e )
			{
				$this->handleException($e);
			}
		}

		private function getConfig()
		{
			try
			{
				$configFile = sprintf("app/config/config.%s.json",$this->env);
				if( file_exists($configFile) )
				{
					if( $this->isJson($config = file_get_contents($configFile)) )
					{
						$this->config = (array) json_decode($config);
					}
					else
					{
						throw new Exception("Config file has systax error");
					}
				}
				else
				{
					throw new Exception('Config File: ' . $configFile . " do not found");
				}
			}
			catch ( Exception $e )
			{
				$this->handleException($e);
			}
		}


	}