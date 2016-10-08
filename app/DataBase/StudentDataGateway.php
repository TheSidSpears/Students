<?php
namespace StudentList\DataBase;
use \StudentList\Models\Student;
/*implement

__construct
countStudents
private function getColumns
getStudents
getStudentByHash
checkEmail
addStudent
editStudent
*/
class StudentDataGateway{
    protected $db; //Объект PDO

    function __construct(\PDO $connection){
        $this->db=$connection;
    }


    /* Кол-во записей в таблице
    *
    * Если $find=NULL
    *    Вернуть кол-во всех строк таблицы
    * Иначе
    *    Вернуть кол-во строк, в которых есть вхождение $find
    *
    */
    public function countStudents($find=NULL){
        //Если задан поиск по строке $find
        if (isset($find)) {
            $rows = $this->db->prepare("
                SELECT COUNT(*) FROM `students`
                WHERE CONCAT(`name`,' ',`sname`,' ',`group_num`,' ',`points`)
                LIKE '%:search%'");
            $find='%'.$find.'%';
            $rows->bindValue(':search', $find, \PDO::PARAM_STR);
        }
        else{
            $rows = $this->db->prepare("SELECT COUNT(*) FROM `students`");
        }
        
        $rows->execute();
        $count=$rows->fetchColumn();
        return $count;
    }    
    
    
    private function getColumns(){
        $rows = $this->db->prepare("SHOW COLUMNS FROM `students`");
        $rows->execute();
        $columns=$rows->fetchAll(\PDO::FETCH_ASSOC);
        $columns = array_column($columns, 'Field');

        return $columns;
    }
    
    public function getStudents($sortBy,$orderBy,$limit,$offset,$find=NULL){
        /*
        * возвращает массив, где каждый studentsRows - объект Student
        * $limit записей, начиная с $offset
        */
        //Фильтруем данные
        if(!in_array($sortBy, $this->getColumns())){
            if ($sortBy=='hash') { //запрещаем сортировать по hash
                $sortBy='points';
            }
        }
        $orderBy= $orderBy=='asc'? 'asc' : 'desc'; //если передаётся шняга, то desc
        
        //Если задан поиск по строке $find
        if (isset($find)) {
            $rows = $this->db->prepare("
                SELECT * FROM `students`
                WHERE CONCAT(`name`,' ',`sname`,' ',`group_num`,' ',`points`)
                LIKE :search
                ORDER BY $sortBy $orderBy
                LIMIT :limit OFFSET :offset");
            $find = addCslashes($find, '\%_'); // http://phpfaq.ru/mysql/slashes#like
            $find='%'.$find.'%';
            $rows->bindValue(':search', $find, \PDO::PARAM_STR);
        }
        else{
            $rows = $this->db->prepare("
                SELECT * FROM `students`
                ORDER BY $sortBy $orderBy
                LIMIT :limit OFFSET :offset");
        }

        
        $rows->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $rows->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $rows->execute();
        
        $studentsRows=$rows->fetchAll(\PDO::FETCH_ASSOC);
        //Подготавливаем массив
        $students=array();
        foreach($studentsRows as $studentRow){
            $student=new Student();
            $student->addInfo($studentRow);
            $students[]=$student;
        }
        return $students;
    }
    
    //Принимает хеш. Возвращает строку студента
    public function getStudentByHash($hashFromCookie){
        $rows = $this->db->prepare("SELECT * FROM `students` WHERE `hash`=:hash");
        $rows->bindValue(':hash', $hashFromCookie, \PDO::PARAM_STR);
        $rows->execute();

        $studentRow=$rows->fetch(\PDO::FETCH_ASSOC);

        if ($studentRow!=NULL) {
            $student=new Student();
            $student->addInfo($studentRow);
            return $student;
        }
        else{
            return false;
        }
    }
    
    
    
    /*Проверка на существование e-mail'а
    * true - уже есть такой email
    * false - нет
    */
    public function checkEmail($email,$id=NULL){
        if ($id) {
            $rows = $this->db->prepare("SELECT COUNT(*) FROM `students` WHERE `email`=:email AND `id`<>:id");
            $rows->bindValue(':id', $id, \PDO::PARAM_STR);
        }
        else{
            $rows = $this->db->prepare("SELECT COUNT(*) FROM `students` WHERE `email`=:email");
        }

        $rows->bindValue(':email', $email, \PDO::PARAM_STR);
        $rows->execute();
        $count=$rows->fetchColumn();        

        if($count>0){
            return true;
        }
        return false;
    }
    
    //Добавляет новую строку в БД
    public function addStudent(Student $student){
        $SqlString="INSERT INTO `students`
                    (`name`,`sname`,`group_num`,`points`,`gender`,`email`,`b_year`,`is_resident`,`hash`)
                    VALUES
                    (:name,:sname,:group_num,:points,:gender,:email,:b_year,:is_resident,:hash)";
        $this->writeToTable($SqlString,$student);
    }
    
        
    public function editStudent(Student $student){
        $SqlString="UPDATE `students`
        SET
            `name`=:name,
            `sname`=:sname,
            `group_num`=:group_num,
            `points`=:points,
            `gender`=:gender,
            `email`=:email,
            `b_year`=:b_year,
            `is_resident`=:is_resident
        WHERE `hash`=:hash";
        $this->writeToTable($SqlString,$student);

    }

    protected function writeToTable($SqlString,$student){
        $rows = $this->db->prepare($SqlString);

        $rows->bindValue(':name', $student->name, \PDO::PARAM_STR);
        $rows->bindValue(':sname', $student->sname, \PDO::PARAM_STR);
        $rows->bindValue(':group_num', $student->group_num, \PDO::PARAM_STR);
        $rows->bindValue(':points', $student->points, \PDO::PARAM_INT);
        $rows->bindValue(':gender', $student->gender, \PDO::PARAM_STR);
        $rows->bindValue(':email', $student->email, \PDO::PARAM_STR);
        $rows->bindValue(':b_year', $student->b_year, \PDO::PARAM_INT);
        $rows->bindValue(':is_resident', $student->is_resident, \PDO::PARAM_STR);
        $rows->bindValue(':hash', $student->hash, \PDO::PARAM_STR);
        $rows->execute();
    }

}