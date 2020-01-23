<?php

	class controller_move extends Controller
	{

		public function move()
		{
			sleep(1);
			!isset($_SESSION) && session_start();
			if( !isset($_SESSION['user_login']) ) { to('auth'); }
			$entity = filter_var($this->param['entity']);
			$id = (int) $this->param['id'];
			$direction = filter_var($this->param['direction']);

			$this->model('projeto');
			$this->model->query(sprintf("CALL mover('%s',%s,'%s')",$entity,$id,$direction));
		}
	}