<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Long_requests_model
 *
 * Модель для работы со списком заявок на несколько дней
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Long_requests_model extends CI_Model
{
    /**
     * Таблица БД с заяками на пропуски
     * @var string
     */
    private $table = 'long_requests';

    /**
     * Первичный ключ таблицы с заяками на пропуски
     * @var string
     */
    private $table_id = 'long_request_id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Добавление заявки  в БД
     *
     * @param array $data массив данных заявки
     * @return int ID последней записи
     */
    public function add($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
    
    /**
     * Получение заявок из БД
     *
     * @return object Объект-результат запроса
     */
    public function get_requests()
    {     
      /* return $this->db->get($this->table)
                       ->result();*/
        $query = 'SELECT l_r.long_request_id, u.name AS username, l_r.date_from, l_r.date_to,
                  CONCAT_WS(" ", v.last_name, v.first_name, v.middle_name) AS visitorname
                  FROM long_requests AS l_r
                  INNER JOIN users AS u
                    ON u.user_id = l_r.applicant_id
                  INNER JOIN visitors AS v
                  ON v.visitor_id = l_r.visitor_id
                  ORDER BY date_to DESC';        
        
        return $this->db->query($query)
                        ->result();
    }
    
    /**
     * Получение данных заявки
     *
     * @param $long_request_id id заявки
     *
     * @return object Объект-результат запроса
     */
    public function get_long_request($long_request_id)
    {
        return $this->db->get_where($this->table, array($this->table_id => $long_request_id))
                      ->row();
    }   

    /**
     * Удаление заявки из БД
     *
     * @param $request_id id заявки
     */
    public function delete($long_request_id)
    {
        $this->db->where('long_request_id', $long_request_id);
        $this->db->delete($this->table);
    }
    
    /**
     * Получение даты последнего обновления таблицы отложенных запросов
     * 
     * @return object Объект-результат запроса или пустой массив 
     */
    public function get_last_update()
    {
        $query = 'SELECT update_date
                    FROM long_requests_updates 
                  ORDER BY long_request_update_id DESC
                  LIMIT 1';
        
        return $this->db->query($query)
                        ->row();
    }
    
    /**
     * Вызывает хранимую процедуру 
     * для обновления списка заявок на несколько дней 
     */
    public function update_long_requests()
    {
        $this->db->query('CALL long_request_proc');
    }
}