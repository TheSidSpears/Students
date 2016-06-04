<?php
/*
* Анализирует текущий url:
*	Отделяет адрес от переменных
*	Переменные заносит в массив
* По запросу меняет значения переменных на нужные и возращает url
*/
class Linker{

	private $get=array();




	function __construct(Array $get){
		$this->get=$get;
	}



	function makeUrl(Array $params){
		$get=$this->get;
		$blocked_params=array_diff_key($get,$params); //ключи что есть в $get, но нет в $params
		//возможно эти 2 строчки нужно в main.php и передавать уже строку в конструктор
		$router=new Router();
		$module=$router->getModule();

		$url=$module."?";
		
		//вставляем гет-переменные, которые не нужно менять
		foreach ($blocked_params as $key => $value) {
				$url.=$key."=".$value."&";		
		}
		//вставляем гет-переменные с новыми значениями
		$url.=http_build_query($params);

		return html($url);
	}






	function makeSortUrl($row){
		$sortBy=$this->get['sortBy'];
		$orderBy=$this->get['orderBy'];

		if ( (isset($sortBy)) and (isset($orderBy)) ) {
			if ($row==$sortBy) { //если таблица уже отсортирована по этому столбцу
				$orderBy = $orderBy=='asc' ? 'desc' : 'asc'; //изменить порядок
			}
		}
		else{
			$orderBy='desc';
		}


		return $this->makeUrl(array('sortBy'=>$row,'orderBy'=>$orderBy));
	}






	function makePageUrl($row){
		return $this->makeUrl(array('page'=>$row));
	}







	function getVisual($row){
		if ($row==$this->get['sortBy']) {
			if ($this->get['orderBy']=='asc') {
				echo '[▲]';
			}
			else{
				echo '[▼]';
			}
		}
	}

}