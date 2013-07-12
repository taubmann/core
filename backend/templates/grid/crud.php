<?php

// id-translation for the main list
if (!isset($_GET['objectId'])) { $_GET['objectId'] = $_GET['id']; }

class grid_crud extends crud
{
	private function setSortBy()
	{
		// fix sorting
		if (!empty($_GET["jtSorting"]))
		{
			if (substr($_GET["jtSorting"], -4) == 'DESC')
			{
				$this->sortBy = array((substr($_GET["jtSorting"], 0, -4)) => 'desc' );
			}
			else
			{
				$this->sortBy = array((substr($_GET["jtSorting"], 0, -3)) => 'asc' );
			}
		}
	}
	
	// Getting records (listAction)
	public function getList()
	{
		
		$obj = new $this->objectName();
		$this->setSortBy();
		
		$jTableResult = array();
		
		/*
		$jTableResult['Result'] = "ERROR";
		$jTableResult['Message'] = json_encode($_GET);
		return json_encode($jTableResult);*/
		
		if (isset($_POST['q']))
		{
			for($i=0; $i<count($_POST['q']); $i++)
			{
				$this->getListFilter[] = array($_POST['opt'][$i], 'LIKE', '%'.$_POST['q'][$i].'%');
			}
		}
		
		$jTableResult['TotalRecordCount'] = DB::instance($obj::DB)->query('SELECT COUNT(*) AS c FROM `'.$this->objectName.'`')->fetch()->c;
		$jTableResult['Records'] = $obj->GetList($this->getListFilter, $this->sortBy, $_GET['jtPageSize'], $_GET['jtStartIndex']);
		$jTableResult['Result'] = 'OK';
		if (!is_array($jTableResult['Records']))
		{
			$jTableResult['Result'] = 'ERROR';
			$jTableResult['Message'] = json_encode($jTableResult['Records']);
		}
		return json_encode($jTableResult);
	}
	
	// Creating a new record (createAction)
	public function createNewContent()
	{
		$id = $this->createContent();
		$jTableResult = array();
		
		/*
		$jTableResult['Result'] = 'ERROR';
		$jTableResult['Message'] = $id;
		return json_encode($jTableResult);
		*/
		
		if (is_numeric($id))
		{
			$this->objectId = $id;
			$x = $this->saveContent();
			$jTableResult['Result'] = 'OK';
			$obj = new $this->objectName();
			$jTableResult['Record'] = $obj->Get($id);
		}
		else
		{
			$jTableResult['Result'] = 'ERROR';
			$jTableResult['Message'] = $id;
		}
		return json_encode($jTableResult);
	}
	
	// Updating a record (updateAction)
	public function updateContent()
	{
		$this->objectId = $_POST['id'];
		$jTableResult = array();
		$msg = $this->saveContent();
		
		// we have to deal with a Connection
		if (isset($_GET['referenceName']))
		{
			require_once($this->ppath.'/objects/class.'.$this->referenceName.'.php');
			switch($_GET['referenceType'])
			{
				case 's':
					$o = new $this->objectName();
					$oe = $o->Get($this->objectId);
					$r = new $this->referenceName();
					$re = $r->Get($this->referenceId);
					$n = array($this->objectName, $this->referenceName);
					natsort($n);
					$map = implode('',$n).'map';
					$m = new $map();
					$what = array('RemoveMapping','AddMapping');
					$m->{$what[intval($_POST['__connected__'])]}($oe, $re);
				break;
				case 'c':
					// todo
					
				break;
				case 'p':
					// todo
					
				break;
			}
		}// Connection END
		
		
		if(substr($msg,0,2) == '[[')
		{
			$jTableResult['Result'] = 'ERROR';
			$jTableResult['Message'] = $msg;
		}
		else
		{
			$jTableResult['Result'] = 'OK';
		}
		return json_encode($jTableResult);
	}
	
	// Deleting a record (deleteAction)
	public function removeContent()
	{
		$this->objectId = $_POST['id'];
		$jTableResult = array();
		$msg = $this->deleteContent();
		if(substr($msg,0,2) == '[[')
		{
			$jTableResult['Result'] = 'ERROR';
			$jTableResult['Message'] = $msg;
		}
		else
		{
			$jTableResult['Result'] = 'OK';
		}
		return json_encode($jTableResult);
	}
	
	//////////////////////////////////// SUB-ENTRIES ///////////////////////////////////////////
	
	public function getConnectedReferences()
	{
		$obj = new $this->objectName();
		$item = $obj->Get($this->objectId);
		require_once($this->ppath.'/objects/class.'.$this->referenceName.'.php');
		
		$ref = new $this->referenceName();
		
		if (isset($_POST['q']))
		{
			for($i=0; $i<count($_POST['q']); $i++)
			{
				//if($_POST['opt'][$i] != '') 
				$this->getAssocListFilter[] = array($_POST['opt'][$i], 'LIKE', '%'.$_POST['q'][$i].'%');
			}
		}
		
		
		$this->setSortBy();
		
		$jTableResult = array();
		//$jTableResult['TotalRecordCount'] = DB::instance($obj::DB)->query('SELECT COUNT(*) AS c FROM `'.$this->objectName.'`')->fetch()->c;
		
		//'__connected__'
		switch ($_GET['referenceType'])
		{
			case 's':
				$call = 'Get'.$this->referenceName.'List';
			break;
			case 'p':
				$call = 'Get'.$this->referenceName.'List';
			break;
			case 'c':
				$call = 'Get'.$this->referenceName;
			break;
		}
		/*$jTableResult['Result'] = 'ERROR';
		$jTableResult['Message'] = json_encode($this->sortBy);
		return json_encode($jTableResult);*/
		
		$records = $item->$call($this->getAssocListFilter, $this->sortBy);
		
		
		if (!is_array($records))
		{
			$jTableResult['Result'] = 'ERROR';
			$jTableResult['Message'] = json_encode($records);
		}
		else
		{
			$jTableResult['Result'] = 'OK';
			$jTableResult['Records'] = array();
			$cc = 0;
			$conns = array();
			
			// collect the connected references
			foreach ($records as $r)
			{
				if ($cc >= intval($_GET['jtStartIndex']) && $cc < ($_GET['jtPageSize']*($_GET['jtStartIndex']+1)))
				{
					$conns[] = $r->id;
					$jTableResult['Records'][] = array_merge(array('__connected__'=>'1'), $this->objectToArray($r));
				}
				$cc++;
			}
			
			// now collect the rest
			$refList = $ref->GetList($this->getAssocListFilter, $this->sortBy);
			foreach ($refList as $r)
			{
				if (!in_array($r->id, $conns))
				{
					if($cc >= intval($_GET['jtStartIndex']) && $cc < ($_GET['jtPageSize']*($_GET['jtStartIndex']+1)))
					{
						$jTableResult['Records'][] = array_merge(array('__connected__'=>'0'), $this->objectToArray($r));
					}
					$cc++;
				}
			}
			
			
			$jTableResult['TotalRecordCount'] = $cc;
		}
		return json_encode($jTableResult);
	}
	
	public function createSubContent ()
	{
		$jTableResult = array();
		$jTableResult['Result'] = 'ERROR';
		$jTableResult['Message'] = json_encode('not implemented atm');
		return json_encode($jTableResult);
	}
	
}
$c = new grid_crud();
