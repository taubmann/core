<?php
/********************************************************************************
*  Copyright notice
*
*  (c) 2013 Christoph Taubmann (info@cms-kit.org)
*  All rights reserved
*
*  This script is part of cms-kit Framework. 
*  This is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License Version 3 as published by
*  the Free Software Foundation, or (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/licenses/gpl.html
*  A copy is found in the textfile GPL.txt and important notices to other licenses
*  can be found found in LICENSES.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
************************************************************************************/
ini_set( 'magic_quotes_gpc', 0 );
/** 
* this extends inc/php/class.crud.php
* 
* 
* @package crud
*/

class default_crud extends crud
{
	/**
	* create LI-Tags for Reference-Lists objectname, id|label, no dragging
	* 
	* @return 
	* $referenceName, $referenceId, $label
	* $n, $id, $lbl
	*/
	public function strLi ($referenceName, $referenceId, $label, $nodrag=false)
	{
		$label = trim(strip_tags($label));
		return 	//open the LI
				'<li id="l_'.$referenceId.'" class="ui-state-default ui-selectee">' .
				
				// 
				($nodrag ? '' : '<span class="ui-state-default ui-corner-all" style="float:left;margin-right:10px;" title="drag here"><em class="ui-icon ui-icon-arrow-2-n-s"></em></span>') .
				
				'<a title="id: '.$referenceId.'" class="lnk" data-object="'.$referenceName.'" data-id="'.$referenceId.'" href="#">' .
				
				// ensure some alternative Text if the Label is empty
				((strlen($label)>0) ? substr($label,0,100) : '[...]' ) .
				
				'</a></li>';
	}
	
	
	
	/**
	* 
	* 
	* @return 
	*/
	public function exportList()
	{
		$this->offset = 0;
		$this->limit = -1;
		$list = $this->getList(true);
		$line = array();
		$type = $_GET['type'];
		$doc = array();
		$doc['xml'] = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<list>\n";
		$doc['csv'] = '';
		
		foreach ($list as $i)
		{
			$xml = "\t<row>\n";
			$csv = array();
			foreach ($this->objects[$this->objectName]['col'] as $k => $v)
			{
				$value = str_replace( array('"',"\t","\r","\n") , array('""',' ','',' ') , $i->$k);
				$value = '"' . trim($value) . '"';
				$csv[] = $value;
				$xml  .= '		<field name="'.$k.'">' . htmlentities($i->$k) . "</field>\n";
			}
			$doc['xml'] .= $xml . "\t</row>\n";
			$doc['csv'] .= implode("\t", $csv) . "\n";
		}
		$doc['xml'] .= "</list>\n";
		
		// Set headers
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"my-data.$type\"");
		echo $doc[$type];
	}
	
	
	/**
	* 
	* 
	* @return 
	*/
	public function getPagination()
	{
		$c = intval(DB::instance()->query('SELECT COUNT(*) AS c FROM `'.$this->objectName.'`')->fetch()->c);
		
		//$html =  $c .'/'. $this->limit .'/'. $this->offset;
		$p = ceil($c/$this->limit);
		$html = '<br />';
		for($i=0; $i<$p; $i++) {
			$html .= '<span'.((($i*$this->limit)===$this->offset) ? ' style="text-decoration:underline"' : '').' onclick="setPagination('.$i.')">['.($i+1).']</span> ';
		}
		return $html . '<br />';
	}
	
	
	/**
	* 
	* 
	* @return 
	*/
	public function getListString ($returnRaw=false)
	{
		
		$obj = new $this->objectName();
		$list = $obj->GetList($this->getListFilter, $this->sortBy, $this->limit+1, $this->offset);
		
		if($returnRaw) return $list;
		
		// array to jump to a pagination containing an ID
		$this->idsInList = array();
		
		$str2 = '<ul id="mainlist" data-role="listview" class="ilist rlist">';
		$c = 0;
		$raw = array();
		
		foreach ($list as $i)
		{
			
			if ($c < $this->limit)
			{
				$this->idsInList[] = $i->id;
				$str2 .= '<li title="id: '.$i->id.'" class="ui-state-default ui-selectee" rel="'.$i->id.'">';
				// List-Label
				$l = '';
				foreach ($this->objectFields as $v)
				{
					$l .=  $i->$v . ' ';
				}
				$str2 .= substr(trim(strip_tags($l)),0,100) . '</li>';
			}
			$c++;
		}
		$str2 .= '</ul>';
		
		
		$lbl = ($this->mobile === 1) ? array($this->L('prev'),$this->L('go'),$this->L('next')) : array('.','.','.');
			
		$str = '<span id="objectWizardHtml">';
		// fill Object-Wizards
		if(isset($this->objects[$this->objectName]['url']))
		{
			$ua = $this->objects[$this->objectName]['url'];
			foreach ($ua as $label => $link)
			{
				
				$link = str_replace('///', '://', $link);
				$str .= ($this->mobile === 1) ? 
									'<li><a href="javascript:getFrame(template(\''.$link.'\',window))"><span class="ui-icon ui-icon-gear"></span> '.$label.'</a></li>' : 
									'<option value="'.$link.'">'.$label.'</option>';
			}
		}
		$str .= '</span>';
		
		// Button-Bar
		$str .= '<div id="mainlistHead"><!--lb1-->';
		// back-Button
		$str .= $this->strButton('arrowthick-1-w',false,'store[objectName][\'offset\']-=limitNumber;getList()',($this->offset > 0));
		// pagination-Button
		$str .= $this->strButton('arrowthick-2-e-w', false, 'showPagination()', ($this->offset > 0 || $c > $this->limit));
		// next-Button
		$str .= $this->strButton('arrowthick-1-e',false,'store[objectName][\'offset\']+=limitNumber;getList()',($c > $this->limit));
		$str .= '&nbsp;';
		
		// new-Button
		if(!isset($this->disallow['newbutton'])) $str .= '<button rel="plus" onclick="createContent()" title="'.$this->L('new_entry').'">'.$this->L('new_entry').'</button>';
		// sort-Button
		if(!isset($this->disallow['sortbutton'])) $str .= '<button rel="shuffle" onclick="getFrame(\'templates/default/editList.php?projectName='.$this->projectName.'&objectName='.$this->objectName.'\')" title="'.$this->L('sort').'">'.$this->L('sort').'</button>';
		
		$str .= '<!--lb2--><div id="pagination"></div></div>';
		
		
		return $str . $str2;
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function getTreeHead()
	{
		// Buttons
		$str = '<div id="mainlistHead"><!--lb1-->';
		// New-Button
		if(!isset($this->disallow['newbutton'])) $str .= $this->strButton('plus', 'new_entry', 'createContent()');
		// Sort-Button
		$str .= $this->strButton('shuffle', 'sort', 'getFrame(\'templates/default/editList.php?projectName='.$this->projectName.'&objectName='.$this->objectName.'\')');
		// Order-Button
		if(!isset($this->disallow['orderbutton'])) $str .= $this->strButton('link', 'order', 'getFrame(\'templates/default/manageTree.php?projectName='.$this->projectName.'&objectName='.$this->objectName.'\')');//'<button rel="link" onclick="getFrame(\'inc/php/manageTree.php?projectName='.$this->projectName.'&objectName='.$this->objectName.'\')" title="'.$this->L('arrange').'">'.$this->L('arrange').'</button>';
		$str .= '<!--lb2--></div>';
		$str .= '<div id="mainlist2"></div>';
		
		return $str;
	}
	
	
	/**
	* 
	* 
	* @return 
	*/
	public function getTreeList()
	{
		
		$obj = new $this->objectName();
		$pid = preg_replace('/\W/','', $_POST['id']);
		$ttype = $_GET['tType'];
		$this->limit -= 2; // we have at least two additional List-Elements
		
		// 						fcv, sort, limit, offset, parentId, depth
		$tree = $obj->GetTreeList($this->getListFilter, $this->sortBy, $this->limit+1, $this->offset, $pid, 1);
		
		$c = 0;
		$str = '<ul class="jqueryFolderTree" style="display:none">';
		
		// draw back-button
		if ($this->offset>0)
		{
			$n = $this->offset-$this->limit;
			$str .= '<li class="foldoffset' . (($this->objectId != 0) ? '' : ' master') . '" data-pid="'.$pid.'" data-offset="'.$n.'"><label class="foldico ui-icon ui-icon-arrowthick-1-w"></label><span>'.$this->L('prev').' ( '.$this->L('page').' '.(($n/$this->limit)+1).' )</span></li>';
		}
		
		foreach ($tree as $t)
		{
			// get the List-Entries
			if($c < $this->limit)
			{
				$str .= '<li' . (($this->objectId != 0) ? '' : ' class="master"') . ' data-id="'.$t->id.'">' . 
						'<label class="foldico ' . (($t->treechilds>0) ? 'ui-icon ui-icon-circle-plus' : '') . '" data-id="'.$t->id.'"></label>' . 
						'<span title="id: '.$t->id.'" data-id="'.$t->id.'" class="folder">';
				
				$lbl = '';
				foreach ($this->objectFields as $v)
				{
					$lbl .= ' ' . $t->$v;
				}
				if (strlen(trim($lbl)) == 0)
				{
					$lbl = '[...]';
				}
				$str .= $lbl.'</span></li>';
			}
			else
			{
				// draw next-button
				$str .= '<li class="foldoffset' . (($this->objectId != 0) ? '' : ' master') . '" data-pid="'.$pid.'" data-offset="'.($this->offset+$this->limit).'"><label class="foldico ui-icon ui-icon-arrowthick-1-e"></label><span>'.$this->L('next').' ( '.$this->L('page').' '.(($this->offset/$this->limit)+2).' )</span></li>';
				break;
			}
			$c++;
		}
		
		$str .= '</ul>';
		
		return $str;
		
	}
	
	/**
	* 
	* 
	* @return 
	*/
	private function processLabel ($a, &$cnt, &$tabHeads)
	{
		if (!is_array($a)) return '';
		
		$arr = $a;//clone $a;
		$str1 = $strp1 = '';
		
		
		if (isset($arr['accordionhead']))
		{
			$str1 = 	(($cnt>0)
						? '</div>'
						: '<div id="accordion">').'<h3><a href="#">' . $arr['accordionhead'] . '</a></h3><div>';
			$strp1 = '</div>';
			$cnt++;
		}
		
		if (isset($arr['tabhead']))
		{
			if ($cnt == 0)
			{
				$tabHeads = array();
			}
			$str1 = 	(($cnt>0)
						? '</div>'
						: '<div id="tabs">###TABSHEAD###') . '<div id="tabs-' . $cnt . '">';
			$strp1 = '</div>';
			$tabHeads[] = '<li><a href="#tabs-' . $cnt . '">' . $arr['tabhead'] . '</a></li>';
			$cnt++;
		}
		
		//crappy
		if (isset($arr['doc']))
		{
			$arr['label'] = '<u onclick="openDoc(\'' .  str_replace(array('LANG','PROJECT'), array($this->lang, $this->projectName), $arr['doc']) . '\')">' . $arr['label'] . '</u>';
		}
		
		if (isset($arr['tooltip']))
		{
			$arr['label'] = '<a href="#">' . $arr['label'] . '<span>' . $arr['tooltip'] . '</span></a>';
		}
		
		return array (
						'lbl' => $arr,
						'str1' => $str1,
						'strp1' => $strp1,
					 );
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function getContent()
	{
		
		$field = '';
		
		// Content-Variables
		$strp0 = $strp1 = $str0 = $str1 = $str2 = '';
		
		// if the objectz is empty (to be created)
		if ($this->objectId == 0)
		{
			$obj = new $this->objectName();
			$item = $obj->Get(0);
		}
		else
		{
			$item = $this->getElementBy($this->objectId, $this->getContentFilter);
			
			// collect Relations
			if (isset($this->objects[$this->objectName]['rel']))
			{
				foreach ($this->objects[$this->objectName]['rel'] as $rk => $rt)
				{
					$str0 .= '<option class="relType'.$rt.'" value="'.$rk.'">'.(isset($this->objects[$rk]['lang'][$this->lang]) ? trim($this->objects[$rk]['lang'][$this->lang], '.') : $rk).'</option>';
				}
			}
			
			if ( strlen($str0)>0 )
			{
				// hide this select if users shouldn't select ANY References
				$hide = (isset($this->disallow['referenceselect']) ? ' style="display:none"' : '');
				$str0 = '<select'.$hide .' id="referenceSelect" onchange="getReferences(\''.$this->objectId.'\',0)"><option value="" class="relType">'.$this->L('relations_to_this_entry').'</option>'.$str0.'</select>';
			}
			
			if ( isset($this->objects[$this->objectName]['vurl']) && !isset($this->disallow['previewbutton']) )
			{
				if (count($this->objects[$this->objectName]['vurl'])==1)
				{
					$str0 .= ' <button type="button" rel="extlink" onclick="getFrame(\''. str_replace('ID', $this->objectId, $this->objects[$this->objectName]['vurl'][0]) . '\')">'.$this->L('preview').'</button>';
				}
				else
				{
					$str0 .= '<select onchange="if(this.value.length>0){getFrame(this.value)}"><option value="">'.L('preview').'</option>';
					$c = 1;
					foreach ($this->objects[$this->objectName]['vurl'] as $v)
					{
						$h = explode(' ', $v);
						$str0 .= '<option value="'.$v[0].'">'.($v[1]?$v[1]:L('preview').' '.$c).'</option>';
						$c++;
					}
					$str0 .= '</select>';
				}
				
				
			}
			
		}
		
		$str0 .= '<!--cb1-->';
		
		$str0 .= '<div id="innerForm">';
		
		$cnt = 0;
		$tabHeads = null;
		$col = $this->objects[$this->objectName]['col'];
		
		// loop the Fields
		foreach ($col as $fk => $fv)
		{
			// dont show xxid & xxsort - Fields
			if (substr($fk,-2) == 'id' || substr($fk,-4) == 'sort')
			{
				continue;
			}
			
			// load the Field-Template
			if ($fv['tpl'])
			{
				include_once(__DIR__ . '/fields/' . $fv['tpl'] . '.php');
			}
			
			
			$lbl = $fk;
			$placeholder = '';
			
			// translated Labels
			if (isset($fv['lang'][$this->lang]['label']))
			{
				$a = $this->processLabel( $fv['lang'][$this->lang], $cnt, $tabHeads );
				
				$str1 .= $a['str1'];
				$strp .= $a['strp'];
				
				$lbl = $a['lbl']['label'];
				if (isset($a['lbl']['placeholder']))
				{
					$placeholder = $a['lbl']['placeholder'];
				}
				
			}//if lang END
			
			$fks = substr($fk, 0, 2);
			
			// simple base64-decoding
			if ($fks == 'e_')
			{
				$item->$fk = base64_decode($item->$fk);
			}
			
			// decryption
			if ($fks == 'c_')
			{
				if ( isset($_SESSION[$this->projectName]['config']['crypt'][$this->objectName][$fk]) )
				{
					require_once('crypt.php');
					// objectname, fieldname, entry_id, password
					$key  = md5($this->objectName . $fk .  $_SESSION[$this->projectName]['config']['crypt'][$this->objectName][$fk]);
					$key2 .= md5($key2 . $key);
					$type  = substr($fv['type'], -4);
					//$item->$fk =	($type=='CHAR') ?
					//				X_OR::decrypt($item->$fk, $key2) :
					$item->$fk =	Blowfish::decrypt($item->$fk, $key2, md5(Configuration::$DB_PASSWORD[0]));
				}
				else
				{
					$item->$fk = $this->L('not_decryptable');
				}
			}
			
			// Replacement-Start
			$str1 .= '<!--s_'.$fk.'-->';
			
			
			$data = '';
			if ($fv['add'])
			{
				foreach ($fv['add'] as $dk=>$dv)
				{
					$data.='data-'.$dk.'="'.$dv.'" ';
				}
			}
			// example: draw_xyz ($name, $id, $label, $val, $data)
			if (function_exists('draw_'.$fv['tpl']))
			{
				$str1 .= call_user_func( 'draw_'.$fv['tpl'], $fk, 'input_'.$fk, $lbl, $placeholder, $item->$fk, $data );
			}
									
			// end of field
			$str1 .= '<!--e_'.$fk.'-->';
			
			// if a Generic Structure is detected
			if ($fv['type'] == 'MODEL')
			{
					// load an external Model if declared
					$temp = $item->$fk;
					
					// temporary fix
					@$temp['MODEL'] = array('type'=>'HIDDENTEXT','value'=>(is_string($temp['MODEL'])?$temp['MODEL']:$temp['MODEL']['value']));// : array('type'=>'HIDDENTEXT','value'=>);
					
					// merge Data with external Model
					if (isset($temp['MODEL']) && file_exists($this->ppath.'/objects/generic/'.trim($temp['MODEL']['value']).'.php'))
					{
						$php = file_get_contents($this->ppath.'/objects/generic/'.trim($temp['MODEL']['value']).'.php');
						if ($tpl = json_decode(substr($php, 13), true))
						{
							$temp = array_replace_recursive($tpl, $temp);
						}
					}
					
					foreach ($temp as $jk => $jv)
					{
						// skip invalid fields
						if (!isset($jv['tpl']) || !isset($jv['type']) || !isset($jv['value']))
						{
							continue;
						}
						
						
						include_once (__DIR__ . '/fields/' . $jv['tpl'] . '.php');
						if ( function_exists('draw_'.$jv['tpl']) )
						{
							$jlbl = $jk;
							$placeholder = '';
							if (isset($jv['lang'][$this->lang]))
							{
								$arr = $this->processLabel( $jv['lang'][$this->lang], $cnt, $tabHeads );
								
								$str1 .= $arr['str1'];
								$strp .= $arr['strp'];
								$jlbl = $arr['lbl']['label'];
								if (isset($arr['lbl']['placeholder']))
								{
									$placeholder = $arr['lbl']['placeholder'];
								}
							}
							
							$data = '';
							foreach ($jv['add'] as $dk=>$dv)
							{
								$data.='data-'.$dk.'="'.$dv.'" ';
							}
							// example: draw_xyz ($name, $id, $label, $val, $data)
							$id = 'input_'.$fk.'_'.$jk;
							$str1 .= call_user_func( 'draw_'.$jv['tpl'],  $fk.'['.$jk.'][value]', $id, $jlbl, $placeholder, $jv['value'], $data );
						}
						else
						{
							$str1 .= '<p>Content-Type "'.$jv['type'].'" does not exist!</p>';
						}
					}
					
				
			}// Generic Model END
			
			
		}// foreach END
		
		$str1 .= '</div>';
		
		if (isset($tabHeads))
		{
			$str1 = str_replace('###TABSHEAD###', '<ul>' . implode('', $tabHeads) . '</ul>', $str1);
		}
		
		// close a possible Tab-/Accordion-Container
		if ($cnt>0)
		{
			$str1 .= '</div>';
		}
		
		// Content-Buttons (created here because they need IDs and Style-Attributes) 
		$str2 .= '<div style="clear:both"><span style="float:right"><!--cb3-->';
		if (!isset($this->disallow['deletebutton']))
		{
			$str2 .= '<button id="deleteButton" type="button" rel="trash" onclick="deleteContent(\''.$this->objectId.'\')">'.$this->L('delete_entry').'</button> ';
		}
		$str2 .= '</span><!--cb2-->';
		if (!isset($this->disallow['savebutton']))
		{
			$str2 .= '<button id="saveButton" alt="'.$this->objectId.'" type="button" rel="disk" onclick="saveContent(\''.$this->objectId.'\')">'.$this->L('save').'</button> ';
		}
		// Javascript-Slot (insert a Newline at first)
		$str2 .= '</div>
<script>
// <!--js-->
</script>';
		
		return $str0 . $strp0 . $str1 . $strp1 . $str2;
	}
	
	
	/**
	* 
	* 
	* @return 
	*/
	public function multiSelect()
	{
		$ids = explode(',', $this->objectId);
		$cnt = count($ids);
		
		$click = 'specialAction(\'crud.php?action=#####&projectName='.$this->projectName.'&objectName='.$this->objectName.'&objectId='.$this->objectId.'';
		$str = '<h2>'.$cnt.' '.$this->L('entries_selected').'</h2>';
		
		$str .= '<strong>'.$this->L('change_values_for').':</strong>
				<select class="selectbox" id="multiFieldSelect" onchange="$(\'#multiField\').html(this.value);prettify(\'multiField\');">
				<option title="" value="">'.$this->L('select_field').'</option>';
		
		foreach($this->objects[$this->objectName]['col'] as $fk => $fv)
		{
			// dont show xxid & xxsort
			if (substr($fk,-2) == 'id' || substr($fk,-4) == 'sort')
			{
				continue;
			}
			
			$type = $fv['type'];
			include_once(__DIR__ . '/../../inc/php/fieldtypes/'.$type .'.php');
			
			$lbl = ((isset($fv['lang'][$this->lang]['label'])) ? $fv['lang'][$this->lang]['label'] : $fk);
			$htm = call_user_func( '_'.$type, array($lbl, $fk, '', (isset($fv['add'])?$fv['add']:array())));
			$str .= '<option title="'.$fk.'" value="' . htmlentities(str_replace(' name="',' id="input_', $htm)) . '">' . $lbl . '</option>';
		}
		$str .= '</select><br /><br /><div id="multiField"> </div><hr />';
		
		// delete-button
		if (!$this->disallow['deletebutton']) 
		{
			$str .= '<button type="button" style="float:right" onclick="var q=confirm(\''.str_replace('%s', $cnt, $this->L('really_delete_these_entries')).'?\');if(q){' . str_replace('#####', 'multiDelete', $click) . '\', \'colMidb\')}" rel="trash">'.$this->L('delete_entries').'</button>';
		}
		
		$str .= '<button type="button" onclick="var mn=$(\'#multiFieldSelect\').find(\'option:selected\').attr(\'title\');var q=confirm(\''.str_replace('%s', $cnt, $this->L('really_change_these_entries')).'?\');if(q){' . str_replace('#####', 'multiValue', $click) . '&input=\'+mn,false,$(\'#input_\'+mn).val())}" rel="disk">'.$this->L('save').'</button>';
		
		return $str;
	}
	
	
	/**
	* 
	* 
	* @return 
	*/
	public function getConnectedReferences()
	{
		
		$obj = new $this->objectName();
		
		$item = $obj->Get($this->objectId);
		$pId = false;
		$out = '';
		
		
		// loop all Relations
		if (isset($this->objects[$this->objectName]['rel']))
		{
			foreach ($this->objects[$this->objectName]['rel'] as $rk => $rt)
			{
				$lok = strtolower($rk);
				$lokId = $lok . 'id';
				$str = '';
				require_once($this->ppath.'/objects/class.'.$lok.'.php');
				
				switch ($this->objects[$rk]['rel'][$this->objectName])
				{
					// Sibling List
					case 's':
					
						$c = 'Get'.$rk.'List';
						$sort = (isset($_SESSION[$projectName]['sort'][$rk]) ? $_SESSION[$projectName]['sort'][$rk] : array());
						$relList =  $item->$c( $this->getAssocListFilter, $sort, $this->limit+1, $this->offset );
						
					break;
					
					// Child List
					case 'p':
						$c = 'Get'.$rk.'List';
						$sort = array();
						if (isset($this->objects[$rk]['rel'][$this->objectName.'sort']))
						{
							$sort = array($this->objectName.'sort' => 'asc');
						}
						if (isset($_SESSION[$projectName]['sort'][$rk]))
						{
							$sort = $_SESSION[$projectName]['sort'][$rk];
						}
						$relList =  $item->$c($this->getAssocListFilter, $sort, $this->limit+1, $this->offset);
					break;
					
					// Parent Element
					case 'c':
						$c = 'Get'.$rk;
						$myItem = $item->$c();
						$relList = (($myItem->id) ? array($myItem) : array());
					break;
				}
				
				// define Header$this->objectName]
				$head = '<div><div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all"><span style="font-weight:bold;padding:15px">' .
						(isset($this->objects[$rk]['lang'][$this->lang]) ? trim($this->objects[$rk]['lang'][$this->lang],'.') : $rk) .
						'</span>';
				
				// if needed, add prev-button
				if ($this->offset>0)
				{
					$head .= '<button rel="arrowthick-1-w" title="'.$this->L('prev').'" onclick="getConnectedReferences(\''.$this->objectId.'\','.($this->offset-$this->limit).')">.</button>';
				}
				
				
				$str .= '<ul class="ilist rlist">';
				$labels = $_SESSION[$this->projectName]['labels'][$rk];
				
				$fields = array();
				$c = 0;
				if (is_array($relList))
				{
					foreach ($relList as $relEl)
					{
						$nn = '';
						foreach ($labels as $l)
						{
							$nn .= $relEl->$l . ' ';
						}
						
						// if needed add next-button
						if ($c>=$this->limit)
						{
							$head .= '<button rel="arrowthick-1-e" title="'.$this->L('next').'" onclick="getConnectedReferences(\''.$this->objectId.'\','.($this->offset+$this->limit).')">.</button>';
						}
						
						$c++;
						if ($c < $this->limit)
						{
							//echo $relEl->id;
							$str .= $this->strLi($rk, $relEl->id, $nn, true);
							unset($relEl->id);
						}
					}
				}
				$str .= '</ul>';
				$head .= '</div>';
				$out .= $head . $str;
			}// foreach Relations END
			
		}
		else 
		{
			$out = '';
		}
		
		return $out;
		
	}
	
	/**
	* 
	* 
	* @return 
 
	*/
	public function getReferences()
	{
		//return $this->objectName;
		
		require_once($this->ppath . '/objects/class.' . $this->objectName . '.php');
		require_once($this->ppath . '/objects/class.' . $this->referenceName . '.php');
		
		$obj  = new $this->objectName();
		$robj = new $this->referenceName();
		
		$offset1 = $this->offset;
		$offset2 = ($_GET['offset2']) ? intval($_GET['offset2']) : 0;
		
		$item = $obj->Get($this->objectId);
		
		$all  = $my = array();
		
		$pClass = '';
		$pId = false;
		$relIds = array();
		$type = $this->objects[$this->referenceName]['rel'][$this->objectName];
		
		switch ($type)
		{
			// Sibling-List
			case 's':
				$c = 'Get' . $this->referenceName . 'List';
				$sort = (isset($_SESSION[$projectName]['sort'][$rk]) ? $_SESSION[$projectName]['sort'][$rk] : array());
				$relList =  $item->$c($this->getAssocListFilter, $sort);
			break;
			// Child-List
			case 'p':
				$c = 'Get' . $this->referenceName . 'List';
				$sort = (isset($_SESSION[$projectName]['sort'][$rk]) ? $_SESSION[$projectName]['sort'][$rk] : array());
				if($this->objects[$this->referenceName]['col'][$this->objectName . 'sort'])
				{
					$sort[] = array($this->objectName . 'sort' => 'asc');
				}
				$relList =  $item->$c($this->getAssocListFilter, $sort);
				// define Name of the Parent-ID-Field
				$pId = $this->objectName . 'id';
			break;
			// Parent-Element
			case 'c':
				$c = 'Get'.$this->referenceName;
				$relList = array($item->$c());
				$pClass = ' sublistParent';
			break;
		}
		
		
		// build Relations-Lists
		$str1 = '';
		$str0 = '<input class="sbox ui-corner-all" id="referenceSearchbox" placeholder="'.$this->L('search').'" type="text" /><div>';
		
		// prev-Button
		$str0 .=  $this->strButton('arrowthick-1-w',false,'getReferences(\''.$this->objectId.'\','.($offset1-$this->limit).','.$offset2.')', ($offset1 > 0));
		
		
		
		$allFilter = $this->getAssocListFilter;
		$rel_ids = array();
		$c = 0;
		$ol = $offset1 + $this->limit;
		
		// we have to temporarily store actual shown + next connected IDs + offset into a Session
		$_SESSION[$this->projectName]['_'] = array( array(), array(), $offset1 );
		
		foreach ($relList as $i)
		{
			if (!empty($i->id))// check valid id to prevent empty Output
			{
				// fill fcv for the Rest ( equivalent for NOT IN (...) )
				$allFilter[] = array('id', '!=', $i->id);
				
				
				$nn = '';
				
				if ( $c>=$offset1 && $c<=$ol )
				{
					if ($c < $ol)
					{
						// store all IDs *currently shown* into the SESSION
						$_SESSION[$this->projectName]['_'][0][] = $i->id;
						
						foreach ($this->referenceFields as $n)
						{
							$nn .= $i->$n . ' ';
						}
						
						$str1 .= $this->strLi(
												$this->referenceName, 
												$i->id,
												$nn// . (($pId && strlen($i->$pId) > 0) ? '(!)' : '') 
											 );//('.$i->$pId.')
						
						$relIds[] = $i->id;
					}
				}
				
				// store all *further* IDs (for Sorting-Actions lateron)
				if ( $c > $ol )
				{
					$_SESSION[$this->projectName]['_'][1][] = $i->id;
				}
				
				
				$c++;
			}
		}
		$str1 .= '</ul>';
		
		// next-Button
		$str0 .=  $this->strButton('arrowthick-1-e',false,'getReferences(\''.$this->objectId.'\','.($offset1+$this->limit).','.$offset2.')',($c>=$ol));
		// new-Button 
		$str0 .= $this->strButton('plus','new_entry','window.location.hash=\'object='.$this->referenceName.'&connect_to_object='.$this->objectName.'&connect_to_id='.$this->objectId.'\'',!isset($this->disallow['newconnectbutton']));
		$str0 .=  '<!--rb1-->';
		$str0 .= 	'</div><ul id="sublist" class="ilist rlist' . $pClass . '">' .
					'<li class="ui-state-disabled">' . $this->L('connected') . '</li>' .
					$str1;
		
		
		
		
		
		////////////////////////////////////////////// get the NOT connected Entries ////////////////////////////////////////////////////////////////
		// define Tree-sorting along "treeleft"
		$sort = ($this->objects[$this->referenceName]['ttype'] == 'Tree') ? 
																			array('treeleft'=>'asc') : 
																			array();
		// 
		$allList = $robj->GetList($allFilter, $sort, $this->limit+2, $offset2);
		
		$str2 = '<ul id="sublist2" class="ilist rlist"><li class="ui-state-disabled">' . $this->L('available') . '</li>';
		
		$all_cnt = 0;
		if ($allList)
		{
			foreach ($allList as $i)
			{
				// if there are more Results, draw "next" (see limit+1)
				if ($all_cnt < $this->limit)
				{
					$nn = '';
					
					foreach ($this->referenceFields as $n)
					{
						$nn .= $i->$n . ' ';
					}
					
					if (strlen(trim($nn))==0)
					{
						$nn = '[...]';
					}
					
					// id | name [if child, parent id]
					$str2 .= $this->strLi(
									$this->referenceName, 
									$i->id,
									$nn . ((isset($i->$pId) && strlen($i->$pId) > 0) ? '(!)' : '') 
							);//('.$i->$pId.')
				}
				$all_cnt++;
			}
		}
		$str2 .= '</ul>';
		
		// create Pagination-Links
		/*$strp = '';
		
		$p = ceil($all_cnt/$this->limit);
		
		for($i=0; $i<$p; $i++)
		{
			$strp .= '<span '.
					((($i*$this->limit)===$this->offset) ? ' style="text-decoration:underline"' : '').
					'onclick="getReferences(\''.$this->objectId.'\','.($i*$this->limit).')">['.($i+1).']</span> ';
		}
		$strp .= '<br />';*/
		
		
		$str1 = '<div class="listDivider">';
		// Buttons between both Lists
		// back-Button
		$str1 .= $this->strButton('arrowthick-1-w',false,'getReferences(\''.$this->objectId.'\','.$offset1.','.($offset2-$this->limit).')', ($offset2>0));
		// pagination-Button
		//$str1 .= $this->strButton('arrowthick-2-e-w',false,'$(\'#r_pagination\').toggle()', ($offset2>0 || $all_cnt>$this->limit));
		// next-Button
		$str1 .= $this->strButton('arrowthick-1-e',false,'getReferences(\''.$this->objectId.'\','.$offset1.','.($offset2+$this->limit).')', ($all_cnt>$this->limit));
		
		$str1 .= '<!--rb2-->';
		
		// Container for Pagination-Links
		//$str1 .= '<div id="r_pagination" style="display:none"><br />'.$strp.'</div>';
		$str1 .= '</div>';
		
		return $str0 . $str1 . $str2;
	}
	
	
	
}

// init the extended class
$c = new default_crud();
