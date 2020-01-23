<?php

	class controller_home extends Controller
	{

		public function main()
		{
			$this->view->title = "Admin";
			$this->view->menu1 = "class='active'";
			!isset($_SESSION) && session_start();
			$this->view->login = $_SESSION['user_login'];
			if( !isset($_SESSION['user_id']) )
			{
				to('auth');
			}

			$this->view->login = $_SESSION['user_login'];

			$this->model("home_destaque");
			$destaques = $this->model->all();

			$variaveis = array('selected_um','selected_dois','selected_tres','selected_quatro'
							   , 'selected_cinco', 'selected_seis', 'selected_sete', 'selected_oito'
							   , 'selected_nove', 'selected_dez', 'selected_onze', 'selected_doze'
							   , 'selected_treze', 'selected_quatorze', 'selected_quinze');
			$imagens = array('img_um','img_dois','img_tres','img_quatro'
							   , 'img_cinco', 'img_seis', 'img_sete', 'img_oito'
							   , 'img_nove', 'img_dez', 'img_onze', 'img_doze'
							   , 'img_treze', 'img_quatorze', 'img_quinze');

			$i = 0;
			foreach( $variaveis as $var )
			{
				$varImg = $imagens[$i];
				$this->view->$var = $destaques[$i]['destaque'];
				$this->view->$varImg = "";
				if( !is_numeric($destaques[$i]['destaque']) && !empty($destaques[$i]['destaque']) )
				{
					$this->view->$var = "imagem";
					$this->view->$varImg = sprintf("<img src='%s' alt='' />",URL_APP.'/upload/home/'.$destaques[$i]['destaque']);
				}
				$i++;
			}

			$this->model("projeto");
			$this->view->projetos = $this->model->getList();

		}

		public function update()
		{
			$this->model("home_destaque");
			$uploadDir = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/home/';
			$numero = 1;
			foreach( $_POST as $key => $val )
			{
				
				$this->model->clear();
				$this->model->posicao = $key;
				

				if( $val == "imagem" )
				{
					if( !empty($_FILES['imagem-'.$numero]['tmp_name']) )
					{
						$img = md5(time()) . '-' . $numero . '.jpg';
						$uploadFile = $uploadDir . $img;
						move_uploaded_file($_FILES['imagem-'.$numero]['tmp_name'], $uploadFile);
						$this->model->destaque = $img;
					}	
				}
				else
				{
					$this->model->destaque = $val;
				}

				$this->model->save();
				$numero++;
			}
			to('home','Projetos foram editados');
		}

	}