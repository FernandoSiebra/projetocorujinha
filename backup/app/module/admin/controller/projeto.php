<?php

	class controller_projeto extends Controller
	{

		public function __construct()
		{
			parent::__construct();
		}

		public function main()
		{
			!isset($_SESSION) && session_start();
			if( !isset($_SESSION['user_login']) ) { to('auth'); }
			$this->view->login = $_SESSION['user_login'];
			$this->view->title = "Projeto";
			$this->view->menu2 = "class='active'";
			$this->model('projeto');
			$this->view->projetos = $this->model->getList();
		}

		public function salvar()
		{
			!isset($_SESSION) && session_start();
			if( !isset($_SESSION['user_login']) ) { to('auth'); }
			$vars = array( 'nome','ano','metragem','construtora','cliente','descricao' );
			foreach( $vars as $var )
			{
				if( !isset($_POST[$var]) )
				{
					die();
				}
			}

			$this->model("projeto");
			$this->model->nome = filter_var($_POST['nome']);
			$this->model->ano = filter_var($_POST['ano']);
			$this->model->metragem = filter_var($_POST['metragem']);
			$this->model->fotografo = filter_var($_POST['fotografo']);
			$this->model->construtora = filter_var($_POST['construtora']);
			$this->model->cliente = filter_var($_POST['cliente']);
			$this->model->descricao = filter_var($_POST['descricao']);
			$this->model->categoria = isset($_POST['categoria']) && is_array($_POST['categoria']) ? implode(',',$_POST['categoria']) : '';

			

			if( isset($_POST['id']) && is_numeric($_POST['id']) )
			{
				$id = $this->model->id = filter_var($_POST['id']);
				$this->model->save();
			}
			else
			{
				$this->model->save();	
				$id = $this->model->lastId;
			}

			if( !empty($_FILES) )
			{
				$this->model("imagem");
				foreach( $_FILES as $key => $val )
				{
					
					list($x,$numero) = explode("-",$key);
					if( !empty($val['tmp_name']) )
					{
						$uploadFile = 'upload/projeto/' . md5(time()) . '-' . $numero . '.jpg';
						move_uploaded_file($_FILES[$key]['tmp_name'], $uploadFile);
						$this->model->clear();
						$this->model->delete($id,$numero);
						$this->model->clear();
						$this->model->projeto = $id;
						$this->model->posicao = $numero;
						$this->model->arquivo = basename($uploadFile);
						$this->save();
					}
					
				}
			}
			

			$this->message('success','Projeto foi salvo',$this->model->lastId);



		}

		private function uploadImagens($projetoId)
		{
			error_reporting(E_ALL);
			ini_set("display_errors",'1');
			
		}

		public function get()
		{
			!isset($_SESSION) && session_start();
			if( !isset($_SESSION['user_login']) ) { to('auth'); }
			header('Content-Type: application/json');
			$id = (int) $this->param['id'];
			$this->model("projeto");
			$projeto = $this->model->get($id);
			$projeto->descricao = str_replace(array('<br/>','<br />','<br>'), '', $projeto->descricao);
			$projeto->criacao = "";
			$this->model("imagem");
			$projeto->imagens = $this->model->getList($id);
			die(json_encode( $projeto ));
		}

		private function insert()
		{
			!isset($_SESSION) && session_start();
			if( !isset($_SESSION['user_login']) ) { to('auth'); }
			$this->model('projeto');
			$this->getPost();

			if( $this->save() )
			{

				$projetoId = $this->model->lastId;

				$this->model('imagem');

				$imagens = array();
				$uploadDir = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/projeto/';

				foreach( range(1,10) as $numero )
				{
					if( !empty($_FILES['imagem-'.$numero]['tmp_name']) )
					{
						$imagens[$numero] = md5(time()) . '-' . $numero . '.jpg';
						$uploadFile = $uploadDir . $imagens[$numero];
						file_exists($uploadFile) && unlink($uploadFile);
						move_uploaded_file($_FILES['imagem-'.$numero]['tmp_name'], $uploadFile);
					}	
				}
				foreach( $imagens as $key => $src )
				{
					$this->model->arquivo = $src;
					$this->model->posicao = filter_var($key,FILTER_SANITIZE_NUMBER_INT);
					$this->model->projeto = $projetoId;
					$this->save();
				}

				$this->message('success','Projeto foi salvo',$this->model->lastId);

			}
			else
			{
				$this->message('success','Projeto não foi salvo',$this->model->lastId);	
			}

		}

		public function delete()
		{
			!isset($_SESSION) && session_start();
			if( !isset($_SESSION['user_login']) ) { to('auth'); }
			$id = (int) $this->param['id'];

			if( !isset($this->param['posicao']) )
			{	
				
				$this->model('imagem');
				$this->model->delete($id);

				$this->model('projeto');
				if( $this->model->delete($id) )
				{
					$this->message('success','Projeto foi excluído',$this->model->lastId);
				}
				
			}
			else
			{
				$this->model('imagem');
				$posicao = (int) $this->param['posicao'];
				if ( $this->model->delete($id,$posicao) )
				{
					$this->message('success','Imagem foi excluída');
				}
				else
				{
					$this->message('success','Imagem foi excluída');
				}
			}
			
		}

		private function message($type,$message,$id=null)
		{
			//$anchor = $id == null ? '' :'#'.$id;
			to("projeto","<div class='alert alert-" . $type . "' role='alert'>" . $message . "</div>");
		}





	}