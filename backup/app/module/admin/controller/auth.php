<?php

	class controller_auth extends Controller
	{

		public function main()
		{
			$this->view->title = "Admin";
			
		}

		public function validate()
		{
			$this->model("auth");
			$login = isset($_POST['login']) ? filter_var($_POST['login']) : die();
			$pass = isset($_POST['pass']) ? filter_var($_POST['pass']) : die();
			
			$auth = $this->model->auth($login,$pass);
			if( isset($auth->error) )
			{	
				to("auth",$auth->error);
			}
			else
			{
				to('home');
			}
		}

		public function edit()
		{
			!isset($_SESSION) && session_start();
			if( empty($_POST) )
			{
				$this->view->title = "Editar Dados";
				$this->view->login = $_SESSION['user_login'];
			}
			else
			{
				$pass = filter_var($_POST['pass']);
				$this->model("auth");
				$this->model->updatePass($pass);
				$this->logout();
			}
		}

		public function logout()
		{
			$this->model("auth");
			$this->model->logout();
			to('auth');
		}

	}