<?php
	
	class Model extends System
	{

		protected $pdo;
		protected $table;
		protected $pk;
		protected $values = array();
		public $lastId = 0;

		public function __construct()
		{
			parent::__construct();
		}


		public function __set($var,$val)
		{
			if( $var == "moduleURL" )
			{
				return false;
			}	
			$this->values[$var] = $val;	
		}

		public function pdo($pdo)
		{	
			$this->pdo = $pdo;
		}

		public function get($id,$value=null)
		{
			if( is_numeric($id) )
			{

				$id = (int) $id;
				$query = sprintf('select * from %s where %s = :id',$this->table,$this->pk);
				$query = $this->pdo->prepare($query);
				$query->bindParam(':id',$id, PDO::PARAM_INT);
				$query->execute();
				$object = $query->fetchObject();
				$this->values = (array) $object;
				return $object;
			}
			else if ( isset($value) )
			{
				$query = sprintf('SELECT * FROM %s WHERE %s = :value',$this->table,$id);
				$query = $this->pdo->prepare($query);
				if( is_numeric($value) )
				{
					$query->bindValue(':value',$value,PDO::PARAM_INT);
				}
				else
				{
					$query->bindValue(':value',$value,PDO::PARAM_STR);
				}
				$query->execute();
				if( $query->rowCount() < 2 )
				{
					$object = $query->fetchObject();
				}
				else
				{
					$object = $query->fetchAll();
				}
				$this->values = (array) $object;
				return $object;
			}
		}

		public function all()
		{
			$query = sprintf('SELECT * FROM %s',$this->table);
			$query = $this->pdo->prepare($query);
			$query->execute();
			return $query->fetchAll();
		}

		public function save()
		{
			if( !empty($this->values) && isset($this->values[$this->pk]) )
			{
				$id = $this->values[$this->pk];
				unset($this->values[$this->pk]);

				$vals = array();
				foreach( $this->values as $key => $val )
				{
					$vals[] = $key . '=:' . $key;
				}
				$vals = implode(',',$vals);

				$save = sprintf('UPDATE %s SET %s WHERE %s = :id',$this->table,$vals,$this->pk);
				$save = $this->pdo->prepare($save);
				foreach( $this->values as $key => $val )
				{
					$key = ':' . $key;
					if( is_numeric($val) )
					{
						$save->bindValue($key,$val,PDO::PARAM_INT);
					}
					else
					{
						$save->bindValue($key,$val,PDO::PARAM_STR);
					}
				}
				$save->bindValue(':id',$id);
				$save->execute();
				$this->lastId = $this->pdo->lastInsertId();

				return $save->rowCount() != 0;
			}
			else
			{
				$cols = implode(',',array_keys($this->values));
				$vals = ':' . implode(',:',array_keys($this->values));
				$insert = sprintf('INSERT INTO %s(%s) VALUES(%s)',$this->table,$cols,$vals);
				$insert = $this->pdo->prepare($insert);
				foreach( $this->values as $key => $val )
				{
					$key = ':' . $key;
					if( is_numeric($val) )
					{
						$insert->bindValue($key,$val,PDO::PARAM_INT);
					}
					else
					{
						$insert->bindValue($key,$val,PDO::PARAM_STR);
					}
				}
				
				$insert->execute();
				$this->lastId = $this->pdo->lastInsertId();
				return $insert->rowCount() !== 0;
			}

		}

		public function clear()
		{
			$this->values = array();
		}

		public function delete($id)
		{
			$id = (int) $id;
			$delete = sprintf('DELETE FROM %s WHERE %s = :id',$this->table,$this->pk);
			$delete = $this->pdo->prepare($delete);
			$delete->bindValue(':id',$id);
			$delete->execute();
			return $delete->rowCount() !== 0;
		}

		public function setTable($table)
		{
			$this->table = $table;
		}

		public function setPk($pk)
		{
			$this->pk = $pk;
		}

		public function query($query)
		{
			$st = $this->pdo->prepare($query);
			return $st->execute();
		}

	}