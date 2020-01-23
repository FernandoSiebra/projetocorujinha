<?php

	class View extends System
	{

		private $code = "";
		public $vars = array();

		public function setCode($code)
		{
			$this->code = $code;
		}

		public function __set($var,$val)
		{
			$var = (string) $var;
			$val = $val;
			$this->vars[$var] = $val;
		}

		public function render()
		{
			$vars = $this->vars;
			$code = $this->code;
			foreach( $vars as $var => $val )
			{
				$code = str_replace('@'.$var, $val, $code);
			}

			$assetsPatters = '/@asset([^"]+)/';
			preg_match_all($assetsPatters, $code, $matches);
			$matches = $matches[0];
			if( count($matches) )
			{
				foreach( $matches as $asset )
				{
					$_asset = substr($asset,7,strlen($asset)-8);
					list($dir,$file) = explode(",",$_asset);
					if( MODULE == "" )
					{
						$_asset = sprintf('%s/app/assets/%s/%s',$this->appURL,$dir,$file);
					}
					else
					{
						$_asset = sprintf('%s/app/module/%s/assets/%s/%s',$this->appURL,MODULE,$dir,$file);	
					}
					$code = str_replace($asset, $_asset, $code);
				}
			}


			foreach( $_GET as $key => $val )
			{
				$val = (string) $val;
				$key = sprintf('@get(%s)',$key);
				$code = str_replace($key, $val, $code);
			}
			
			$code = str_replace('@location', LOCATION, $code);

			$uriPatters = '/@uri([^"]+)/';
			preg_match_all($uriPatters, $code, $matches);
		
			$matches = $matches[0];

			if( count($matches) )
			{
				foreach( $matches as $uri )
				{
					$_uri = substr($uri,5,strlen($uri)-6);
					$_uri = sprintf('%s/%s',$this->appURL.(MODULE == ''?'':"/".MODULE),$_uri);
					$code = str_replace($uri, $_uri, $code);
				}
			}

			$uriPatters = '/@foreach(([\s\S]*))([\s\S]*)@end/msU';
			preg_match_all($uriPatters, $code, $matches);
			$matches = $matches[0];
			if( count($matches) )
			{
				foreach( $matches as $match )
				{
					$var = substr($match,strpos($match,'(')+1,strpos($match,'('));
					$content = substr($match,strpos($match,')')+1,(strlen($match)-strpos($match,')')-5));
					$repeat = "";
					if( isset($this->vars[$var]) )
					{
						$array = (array) $this->vars[$var];
						foreach( $array as $registro )
						{
							$_repeat = $content;
							foreach( $registro as $key => $val )
							{
								$_repeat = str_replace('@'.$key, $val, $_repeat);
							}
							$repeat .= $_repeat;
						}

					}
					$code = str_replace($match, $repeat, $code);
				}
			}


			!isset($_SESSION) && session_start();
			if( !isset($_SESSION) )
			{
				die("System can not create session in this server");
			}

			if( empty($_POST) && isset($_SESSION['system_message']) )
			{
				$code = str_replace('@message', $_SESSION['system_message'], $code);
				unset($_SESSION['system_message']);
				die($code);
			}
			$code = str_replace('@message','', $code);

			$code = str_replace('@router', str_replace('/','-',$this->uri), $code);
			
			die($code);
		}

	}