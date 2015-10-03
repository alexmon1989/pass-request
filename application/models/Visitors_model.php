<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Visitor_model
 *
 * Модель для работы со списком посетителей
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Visitors_model extends CI_Model
{
    /**
     * Таблица БД с посетителями
     * @var string
     */
    private $table_visitors = 'visitors';

    /**
     * Первичный ключ таблицы с посетителями
     * @var string
     */
    private $table_id = 'visitor_id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Возвращает ID посетителя по его имени
     *
     * @param $last_name фамилия
     * @param $first_name имя
     * @param $middle_name
     * @return object Объект-id документа
     */
    public function get_visitor_by_val($last_name, $first_name, $middle_name)
    {
        return $this->db->select($this->table_id)
            ->get_where($this->table_visitors, array('last_name' => $last_name, 'first_name' => $first_name, 'middle_name' => $middle_name))
            ->row();
    }

    /**
     * Добавить посетителя в БД
     *
     * @param $last_name фамилия
     * @param $first_name имя
     * @param $middle_name отчество
     * @return int id последней записи в таблице
     */
    public function add_visitor($last_name, $first_name, $middle_name)
    {
        $this->db->insert($this->table_visitors, array('last_name' => $last_name, 'first_name' => $first_name, 'middle_name' => $middle_name));
        return $this->db->insert_id();
    }

    /**
     * Извлекает из БД посетителей
     *
     * @return array массив объектов-результатов запроса
     */
    public function get_visitors()
    {
        $query = 'SELECT visitor_id, CONCAT_WS(" ", last_name, first_name, middle_name) AS visitorname
                  FROM visitors
                  ORDER BY last_name';

        return $this->db->query($query)->result();
    }

    /**
     * Получить данные посетителя по его ID
     *
     * @param $visitor_id id посетителя
     * @return object результат запроса
     */
    public function get_visitor_by_id($visitor_id)
    {
        return $this->db->where($this->table_id, $visitor_id)
                        ->get($this->table_visitors)
                        ->row();

    }

    /**
     * Редкатирование имени посетителя
     *
     * @param $visitor_id ID посетителя
     * @param $name новое имя посетителя
     */
    public function edit_visitor_name($visitor_id, $name)
    {
        $this->db->update($this->table_visitors, $name, array($this->table_id => $visitor_id));
    }
    
    /**
     * Получение данных посетителя (по части его фамилии), 
     * на которого отправлял заявку $user_id
     *
     * @param type $part Часть фамилии посетителя
     * @return array Массив объектов-результатов запроса 
     */
    public function get_last_visitor_by_user($part)
    {
        // Запрос
        $query = 'SELECT DISTINCT v.visitor_id, v.last_name, v.first_name, v.middle_name, 
                          d.number AS doc_num, d.series AS doc_ser, 
                          d.document_type_id,
                          rm.room_id, r.applicant_id, r.photo_id	
                   FROM requests AS r
                   INNER JOIN visitors AS v
                    ON r.visitor_id = v.visitor_id
                   INNER JOIN users AS u
                    ON r.applicant_id = u.user_id
                   INNER JOIN documents AS d
                    ON d.document_id = r.document_id
                   INNER JOIN rooms AS rm
                    ON rm.room_id = r.room_id
                   WHERE (v.last_name LIKE \'%' . $part . '%\') ORDER BY r.photo_id DESC, v.last_name ASC, r.request_id DESC
                   LIMIT 10';  
                
        $visitors = $this->db
                       ->query($query)
                       ->result();
        
        // Убираем повторяющиеся фамилии
        $names = array();
        $uniq_visitors = array();
        
        foreach ($visitors as $visitor)
        {
            if (in_array($visitor->last_name . ' ' . $visitor->first_name . ' ' . $visitor->middle_name, $names) === FALSE)
            {
                $names[] = $visitor->last_name . ' ' . $visitor->first_name . ' ' . $visitor->middle_name;
                $uniq_visitors[] = $visitor;
            }
        }
        //return $visitors;
        return $uniq_visitors;
    }
    
    public function get_visitors_report()
    {
        $query = 'SELECT DISTINCT v.visitor_id, CONCAT_WS(" ", v.last_name, v.first_name, v.middle_name) AS visitorname
                   FROM visitors AS v
                   INNER JOIN requests AS r
                    ON r.visitor_id = v.visitor_id
                   GROUP BY visitorname
                   ORDER BY v.last_name';
        
         return $this->db->query($query)
                       ->result();
    }
}
