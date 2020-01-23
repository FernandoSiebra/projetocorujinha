<?php

	class controller_noticia extends Controller
	{

		public function main()
		{
			!isset($_SESSION) && session_start();
			$this->view->login = $_SESSION['user_login'];
			$this->view->title = "Notícias";
			$this->view->menu3 = "class='active'";
			
			$this->model('noticia');
			$noticias = $this->model->getList();
			foreach( $noticias as &$noticia )
			{
				$noticia['criacao'] = date('d/m/Y',strtotime($noticia['criacao']));
				$noticia['destaque'] = $noticia['destaque'] == 'N' ? '' : '<i class="fa fa-check"></i>'; 
			}
			$this->view->noticias = $noticias;
			
		}

		public function get()
		{
			header('Content-Type: application/json');
			$id = (int) $this->param['id'];
			$this->model("noticia");
			$noticia = $this->model->get($id);
			die(json_encode( $noticia ));
		}

		public function uploadImagem()
		{
			$uploadDir = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/noticia-body/';
			$arquivo = md5(time()) . '-' . $numero . '.jpg';
			$uploadFile = $uploadDir . $arquivo;
			file_exists($uploadFile) && unlink($uploadFile);
			move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadFile);
		}

		public function salvar()
		{

			if( !empty($_POST) )
			{
				$this->model("noticia");
				$this->model->titulo = filter_var($_POST['titulo']);
				$this->model->descricao = filter_var($_POST['descricao']);
				$this->model->texto = filter_var($_POST['texto']); 
				$this->model->destaque = filter_var($_POST['destaque']);
				$uploadDir = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/noticia/';

				foreach( range(1,2) as $numero )
				{
					if( !empty($_FILES['imagem-'.$numero]['tmp_name']) )
					{
						$arquivo = md5(time()) . '-' . $numero . '.jpg';
						$var = 'imagem' . $numero;
						$this->model->$var = $arquivo;
						$uploadFile = $uploadDir . $arquivo;
						file_exists($uploadFile) && unlink($uploadFile);
						move_uploaded_file($_FILES['imagem-'.$numero]['tmp_name'], $uploadFile);
					}	
				}

				if( isset($_POST['id']) && !empty($_POST['id']) )
				{
					$this->model->id = $_POST['id'];
				}
				if( $this->save() || isset($_POST['id']) )
				{
					error_reporting(E_ALL);
					ini_set('display_errors','1');
					$noticia = isset($_POST['id']) ? $_POST['id'] : $this->model->lastId;
					$posicao = 1;
					$this->model('noticia_imagem');

					foreach( $_FILES as $file )
					{
						if( isset($_FILES['imagem_noticia_'.$posicao]) )
						{
							if( !empty($_FILES['imagem_noticia_'.$posicao]['tmp_name']) )
							{
								$arquivo = md5(time()) . '-' . $posicao . '.jpg';
								$var = 'imagem_noticia_' . $posicao;

								$uploadFile = $uploadDir . $arquivo;
								file_exists($uploadFile) && unlink($uploadFile);
								move_uploaded_file($_FILES['imagem_noticia_'.$posicao]['tmp_name'], $uploadFile);
								$this->model->deletePosicao($noticia,$posicao);
								$this->model->imagem = $arquivo;
								$this->model->posicao = $posicao;
								$this->model->noticia = $noticia;
								$this->save();
							}
							$this->model->setAlign($noticia,$posicao,$_POST['align_'.$posicao]);
							$posicao++;
						}
					}
					$this->message('success','Notícia foi salva');
				}	
				else
				{
					$this->message('success','Notícia não foi salva');
				}

			}

		}

		public function edit()
		{
			!isset($_SESSION) && session_start();
			$this->view->login = $_SESSION['user_login'];
			$this->view->title = "Not?cias";
			$this->view->menu3 = "class='active'";

			$this->model("noticia");
			$id = $this->param['id'];
			$noticia = $this->model->get($id);
			$this->view->titulo = $noticia->titulo;
			$this->view->descricao = $noticia->descricao;
			$this->view->texto = $noticia->texto;
			$this->view->destaqueN = $noticia->destaque == "S" ? "" : "checked";
			$this->view->destaqueS = $noticia->destaque == "S" ? "checked" : "";


			foreach( range(1,9) as $numero )
			{
				$var = 'img' . $numero;
				$this->view->$var = "";
			}

			if( !empty($noticia->imagem1) )
			{
				$this->view->img1 = sprintf('<img src="%s" alt="" width="50" height="50" />', URL_APP . '/upload/noticia/' . $noticia->imagem1 . '?' . rand(1,1000) );
			}
			if( !empty($noticia->imagem2) )
			{
				$this->view->img2 = sprintf('<img src="%s" alt="" width="50" height="50" />', URL_APP . '/upload/noticia/' . $noticia->imagem2 . '?' . rand(1,1000) );
			}

			if( !empty($_POST) )
			{

				$this->model->titulo = filter_var($_POST['titulo']);
				$this->model->descricao = filter_var($_POST['descricao']);
				$this->model->texto = filter_var($_POST['texto']);
				$this->model->destaque = filter_var($_POST['destaque']);

				if( filter_var($_POST['destaque']) == "S" )
				{
					$this->model->resetDestaque();
				}


				$uploadDir = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/noticia/';

				foreach( range(1,2) as $numero )
				{
					if( !empty($_FILES['imagem-'.$numero]['tmp_name']) )
					{
						$arquivo = md5(time()) . '-' . $numero . '.jpg';
						$var = 'imagem' . $numero;
						$this->model->$var = $arquivo;
						$uploadFile = $uploadDir . $arquivo;
						file_exists($uploadFile) && unlink($uploadFile);
						move_uploaded_file($_FILES['imagem-'.$numero]['tmp_name'], $uploadFile);
					}	
				}

				if( $this->save($id) )
				{
					to('noticia','Not?cia foi editada');
				}	
				else
				{
					to('noticia','Not?cia n?o foi editada');
				}

			}

		}

		public function delete()
		{
			error_reporting(E_ALL);
			ini_set('display_errors','1');
			$id = (int) $this->param['id'];
			$this->model('noticia');
			if( isset($this->param['posicao']) )
			{
				$posicao = $this->param['posicao'];
				$this->model->get($id);
				$var = 'imagem' . $posicao;
				$this->model->$var = "";
				if( $this->save() )
				{
					$this->message("success","Imagem foi excluída");
				}
				else
				{
					$this->message("danger","Imagem não foi excluída");
				}
			}
			else
			{
				$this->model("noticia_imagem");
				$this->model->deleteNoticia($id);
				$this->model('noticia');
				$this->model->delete($id);
				$this->message("success","Notícia foi excluída");
			}
		}

		public function imagensInternas()
		{
			$id = $this->param['id'];	
			$this->model("noticia_imagem");
			$noticias = $this->model->get('noticia',$id);
			if( is_object($noticias) )
			{
				$noticias = array($noticias);
			}
			echo json_encode($noticias);
		}

		public function imagemDelete()
		{
			$id = $this->param['id'];	
			$this->model("noticia_imagem");
			$obj = $this->model->get($id);
			var_dump($obj);
			$file = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/upload/noticia/' . $obj->imagem;
			if( file_exists($file) )
			{
				unlink($file);
			}
			$this->model->delete($id);
		}

		private function message($type,$message)
		{
			to("noticia","<div class='alert alert-" . $type . "' role='alert'>" . $message . "</div>");
		}

	}
