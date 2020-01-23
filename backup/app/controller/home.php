<?php

	class controller_home extends Controller
	{

		public function main()
		{
			//header("Location: servico");
			$this->view->title = "Projeto Corujinha";
			$this->view->menuActive1 = '';
			$this->view->menuActive2 = '';
			$this->view->menuActive3 = '';
			$this->view->menuActive4 = '';
			$this->view->pageScript = '';

		}

	}