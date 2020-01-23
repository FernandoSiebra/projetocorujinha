<?php

	class System
	{

		protected $appURL;
		protected $uri;
		protected $uriParts;
		protected $server;
		protected $env;

		public function __construct()
		{
			$this->getServer();
			$this->uri = str_replace(dirname($_SERVER['SCRIPT_NAME'])."/","",$_SERVER['REQUEST_URI']); 
			if( empty($this->uri) )
			{
				$this->uri = "home";
			}
			$this->appURL = ($_SERVER['SERVER_PORT'] == "443" ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']);
			$this->moduleURL = $this->appURL;
			$this->uriParts = explode("/",$this->uri);
		}

		protected function handleException($e)
		{
			$exceptionFile = "system/exception.html";
			if( file_exists($exceptionFile) )
			{
				$exceptionFile = file_get_contents($exceptionFile);
				$exceptionFile = str_replace('@message', $e->getMessage(), $exceptionFile);
				die($exceptionFile);
			}
			else
			{
				throw new Exception('Routers File: ' . $routersFile . " do not found");
			}
		}

		private function getServer()
		{
			$serverConfigFile = "app/config/server.json";
			try
			{
				if( file_exists($serverConfigFile) )
				{
					$serverConfig = file_get_contents($serverConfigFile);
					if( $this->isJson($serverConfig) )
					{
						$serverConfig = (array) json_decode($serverConfig);
						if( isset($serverConfig[$_SERVER['SERVER_NAME']]) )
						{
							$this->server = $_SERVER['SERVER_NAME'];
							$env = $serverConfig[$_SERVER['SERVER_NAME']];	
							if( isset($env) && !empty($env) )
							{
								$this->env = filter_var(str_replace(' ', '', $env), FILTER_SANITIZE_STRING);
							}
							else
							{
								throw new Exception($serverConfigFile . " environment name of " . $this->server . " as 'server' : 'environment_name'");
							}
						}
						else
						{
							throw new Exception("This server " . $_SERVER['SERVER_NAME'] . " do not found in: " . $serverConfigFile );	
						}
					}
					else
					{
						throw new Exception($serverConfigFile . " has json syntax error");		
					}
				}
				else
				{
					throw new Exception($serverConfigFile . " do not found");
				}
			}
			catch( Exception $e )
			{
				$this->handleException($e);
			}
		}

		
		protected function isJson($string) 
		{
			$ob = json_decode($string);
		 	return $ob !== null;
		}



	}