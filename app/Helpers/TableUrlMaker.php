<?php
namespace StudentList\Helpers;
/*
*
*/
class TableUrlMaker{
	public $page;
	public $sortBy;
	public $orderBy;
	public $find;
	protected $action;

	function __construct($page,$sortBy,$orderBy,$find,$action){
		$this->page=$page;
		$this->sortBy=$sortBy;
		$this->orderBy=$orderBy;
		$this->find=$find;
		$this->action=$action;
	}

	private function makeUrl(Array $params){
		$urlVars= array('page' => $this->page, 'sortBy' => $this->sortBy,'orderBy' => $this->orderBy,'find' => $this->find);
		$blockedParams=array_diff_key($urlVars,$params); //ключи что есть в $urlVars, но нет в $params
		
		$url=$this->action."?".http_build_query(array_merge($blockedParams,$params));
		return $url;
	}

	//Меняет или добавляет в текущий url значение переменоых sortBy, orderBy
	public function makeSortUrl($row){
		$sortBy=$this->sortBy;
		$orderBy=$this->orderBy;

		if ($row==$sortBy) { //если таблица уже отсортирована по этому столбцу
			$orderBy = $orderBy=='asc' ? 'desc' : 'asc'; //изменить порядок
		}

		return $this->makeUrl(array('page' => '1','sortBy'=>$row,'orderBy'=>$orderBy));
	}

	//Меняет или добавляет в текущий url значение переменой page
	public function makePageUrl($row){
		return $this->makeUrl(array('page'=>$row));
	}

	public function showSortOrder($row){
		if ($row==$this->sortBy) {
			return $this->orderBy=='asc' ? '▲' : '▼';
		}
		else{
			return false;
		}
	}

}