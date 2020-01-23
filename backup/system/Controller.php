<?php

	class Controller extends System
	{

		protected $param;
		public $view;
		private $conns = array();
		private $pdos = array();
		private $models = array();
		protected $model;

		public function __construct()
		{
			parent::__construct();
			function to($page,$message=null)
			{
				if( !is_null($message) )
				{
					!isset($_SESSION) && session_start();
					if( !isset($_SESSION) )
					{
						die("System can not create session in this server");
					}
					$_SESSION['system_message'] = $message;
				}
				header("Location: " . URL_MODULE . $page);
			}
		}

		public function __call($method,$param)
		{
			if( method_exists($this->model, $method) )
			{
				return $this->model->$method();
			}
		}

		public function model($model)
		{
			$modelClass = "model_" . $model;
			if( empty($this->conns) )
			{
				$this->getConns();
			}
			if( !class_exists($modelClass) )
			{
				try
				{
					$modelFile = sprintf('app/model/%s.php',$model);
					if( file_exists($modelFile) )
					{
						require($modelFile);
						if( class_exists($modelClass) )
						{

							$this->model = $this->models[$model] = new $modelClass();

							$tokens = token_get_all(file_get_contents($modelFile));
							$comment = array();
							foreach($tokens as $token) {
							    if($token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
							        $comment[] = $token[1];
							    }
							}
							$comments = $comment[0];

							$modelPatter = '/@model{[^}]+}/';
							preg_match_all($modelPatter, $comments, $comment);
							if( !empty($comment[0]) )
							{
								$comment = $comment[0][0];
								$comment = json_decode(substr($comment,6,strlen($comment)-6));
								if( isset($comment->conn) && isset($comment->table) )
								{
									$conn = $comment->conn;
									$this->model->setTable($comment->table);
									$this->model->setPk(isset($comment->pk)?$comment->pk:"");
								}
								else
								{
									throw new Exception("Model " . $model . " do not have the require attributes connection and table");
								}
							}
							
							if( isset($conn) )
							{
								if( isset($this->conns[$conn]) )
								{
									if( !isset($this->pdos[$conn]) )
									{
										$this->pdos[$conn] = $this->openConnection($conn,$this->conns[$conn]);
									}
									if( !isset($this->pdos[$conn]) )
									{
										throw new Exception("Connection: " . $conn . " cannot be open");
									}
									$this->model->pdo($this->pdos[$conn]);
								}
								else
								{
									throw new Exception("Connection information for " . $conn . " do not found");
								}
							}
							
						}
						else
						{
							throw new Exception($modelFile . " do not have class " . $model);
						}
					}
					else
					{
						throw new Exception($modelFile . " do not found");
					}
				}
				catch( Exception $e )
				{
					$this->handleException($e);
				}
			}
			else
			{
				$this->model = $this->models[$model];
			}
		}

		private function openConnection($name,$conn)
		{
			try 
			{
				if( isset($conn->driver) )
				{
					$drivers = array("mysql");
					if( in_array($conn->driver, $drivers) )
					{
						$props = array('host','database','user');
						foreach( $props as $prop )
						{
							if( !isset($conn->$prop) || empty($conn->$prop) )
							{
								throw new Exception($name . " do not set correctly the prop:" . $prop);
							}
						}
						$pass = isset($conn->pass) && !empty($conn->pass) ? $conn->pass : "";
						return new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8',$conn->host,$conn->database), $conn->user, $pass, array(
						    PDO::ATTR_PERSISTENT => true
						    ,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
						));
					}	
					else
					{
						throw new Exception("Sorry, the driver of connection " . $name . " is not supported, <br/> suported drivers: " . implode(", ",$drivers));		
					}
				}
				else
				{
					throw new Exception($name . " connection do not have driver definition");
				}
			} 
			catch (Exception $e) 
			{
				$this->handleException($e);	
			}

			return true;
		}

		private function getConns()
		{
			try 
			{
				$configFile = sprintf("app/config/config.%s.json",$this->env);
				if( file_exists($configFile) )
				{
					if( $this->isJson($config = file_get_contents($configFile)) )
					{
						$config = (array) json_decode($config);
						$this->conns = (array) $config['database'];
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
			catch (Exception $e) 
			{
				$this->handleException($e);		
			}
		}

		public function setParam($param)
		{
			$this->param = $param;
		}

		public function setView($view)
		{
			$this->view = $view;
		}

		public function slugify($text)
		{
		    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
		    $text = trim($text, '-');
		    if (function_exists('iconv'))
		    {
		        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		    }
		    $text = strtolower($text);
		    $text = preg_replace('~[^-\w]+~', '', $text);
		    if (empty($text))
		    {
		        return 'n-a';
		    }
		    return $text;
		}


	}