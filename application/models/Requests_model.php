<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Requests_model
 *
 * Модель для работы со списком заявок на пропуски
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Requests_model extends CI_Model
{
    /**
     * Таблица БД с заяками на пропуски
     * @var string
     */
    private $table_requests = 'requests';

    /**
     * Первичный ключ таблицы с заяками на пропуски
     * @var string
     */
    private $table_id = 'request_id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Добавление заявки на пропуск в БД
     *
     * @param array $data массив данных заявки
     * @return int ID последней записи
     */
    public function add_request($data)
    {
        $this->db->insert($this->table_requests, $data);
        return $this->db->insert_id();
    }
    
    /**
     * Получение заявок из БД для отчёта
     *
     * @param $offset
     * @param $row_count
     * @param string $where
     *
     * @return object Объект-результат запроса
     */
    public function get_requests_report($offset, $row_count, $where = '', 
                                    $order_by = 'date', $method = 'DESC')
    {
        
        switch ($order_by)
        {
            case 'date':
            {
                $order_by = 'r.request_date';
                break;
            }
            case 'name':
            {
                $order_by = 'v.last_name';
                break;
            }
            case 'pass_number':
            {
                $order_by = 'p.number';
                break;
            }
            case 'status':
            {
                $order_by = 's.value';
                break;
            }
            default:
            {
                $order_by = $this->db->escape($order_by);
                break;
            }
        }
        
        // Экранирование параметров запроса
        $offset = $this->db->escape($offset);
        $row_count = $this->db->escape($row_count);
        if ($method !== 'ASC' and $method !== 'DESC')
            throw new Exception('Спроба взлому');
        
        // Запрос на получение заявок
        $query = 'SELECT r.request_id, r.request_date, r.issue_date, 
                         r.request_number, d.series AS document_series, 
                         d.number AS document_number,
                         r.pass_date,
                         d_t.type AS document_type, r_r.reason,
                         CONCAT_WS(" ", v.last_name, v.first_name, v.middle_name) AS visitorname,
                         p.number AS pass_number, s.value AS status,
                         p_a.name AS patent_agent, s_e.name AS service_employee,
                         c.name AS courier,
                         rm.number AS room_number, rm.room_id, u.name AS username
                  FROM requests AS r
                  LEFT JOIN visitors AS v
                          ON v.visitor_id = r.visitor_id
                  LEFT JOIN passes AS p
                          ON p.pass_id = r.pass_id
                  LEFT JOIN status AS s
                          ON s.status_id = r.status_id
                  LEFT JOIN patent_agents AS p_a
                          ON p_a.patent_agent_id = r.patent_agent_id
                  LEFT JOIN couriers AS c
                          ON c.courier_id = r.courier_id
                  LEFT JOIN rooms AS rm
                          ON rm.room_id = r.room_id
                  LEFT JOIN users AS u
                          ON u.user_id = r.applicant_id
                  LEFT JOIN documents AS d
                          ON d.document_id = r.document_id      
                  LEFT JOIN document_types AS d_t
                          ON d_t.document_type_id = d.document_type_id  
                  LEFT JOIN requests_reasons AS r_r
                          ON r_r.request_reason_id = r.request_reason_id        
                  LEFT JOIN services_employees AS s_e
                          ON s_e.service_employee_id = r.service_employee_id' . $where .
                  ' ORDER BY ' . $order_by . ' ' . $method .
                  '  LIMIT ' . $offset . ', ' . $row_count;
        
        // Выполняем запрос
        $result = $this->db->query($query);

        return $result->result();
    }

    /**
     * Получение заявок из БД
     *
     * @param $offset
     * @param $row_count
     * @param string $where
     *
     * @return object Объект-результат запроса
     */
    public function get_requests($offset, $row_count, $where = '', 
                               $order_by = 'date', $method = 'DESC')
    {
        
        switch ($order_by)
        {
            case 'date':
            {
                $order_by = 'r.request_date';
                break;
            }
            case 'name':
            {
                $order_by = 'v.last_name';
                break;
            }
            case 'pass_number':
            {
                $order_by = 'p.number';
                break;
            }
            case 'status':
            {
                $order_by = 's.value';
                break;
            }
            default:
            {
                $order_by = $this->db->escape($order_by);
                break;
            }
        }
        
        // Экранирование параметров запроса
        $offset = $this->db->escape($offset);
        $row_count = $this->db->escape($row_count);
        if ($method !== 'ASC' and $method !== 'DESC')
            throw new Exception('Спроба взлому');
        
        // Запрос на получение заявок
        $query = 'SELECT r.request_id, r.request_number, r.request_date, r.issue_date,                          
                         CONCAT_WS(" ", v.last_name, v.first_name, v.middle_name) AS visitorname,
                         p.number AS pass_number, s.value AS status,
                         p_a.name AS patent_agent, s_e.name AS service_employee,
                         c.name AS courier,
                         d.series AS document_series, d.number AS document_number
                  FROM requests AS r
                  LEFT JOIN visitors AS v
                          ON v.visitor_id = r.visitor_id
                  LEFT JOIN passes AS p
                          ON p.pass_id = r.pass_id
                  LEFT JOIN status AS s
                          ON s.status_id = r.status_id
                  LEFT JOIN patent_agents AS p_a
                          ON p_a.patent_agent_id = r.patent_agent_id
                  LEFT JOIN couriers AS c
                          ON c.courier_id = r.courier_id
                  LEFT JOIN documents AS d
                          ON d.document_id = r.document_id
                  LEFT JOIN services_employees AS s_e
                          ON s_e.service_employee_id = r.service_employee_id WHERE 1=1 AND ' . $where .
                  ' ORDER BY ' . $order_by . ' ' . $method .
                  '  LIMIT ' . $offset . ', ' . $row_count;
        
        // Выполняем запрос
        $result = $this->db->query($query);

        return $result->result();
    }
    
    /**
     * Получение прошлых заявок из БД пользователя $user_id
     *
     * @param $offset
     * @param $row_count
     * @param $user_id ID пользователя
     *
     * @return object Объект-результат запроса
     */
    public function get_past_requests($offset, $row_count, $user_id, $where = '')
    {
        // Экранирование параметров запроса
        $offset = $this->db->escape($offset);
        $row_count = $this->db->escape($row_count);
        $method = $this->db->escape($user_id);
        
        // Запрос на получение заявок
        $query = 'SELECT r.request_id, r.request_number, r.request_date, r.issue_date,                          
                         CONCAT_WS(" ", v.last_name, v.first_name, v.middle_name) AS visitorname,
                         p.number AS pass_number, s.value AS status,
                         p_a.name AS patent_agent, s_e.name AS service_employee,
                         c.name AS courier,
                         d.series AS document_series, d.number AS document_number
                  FROM requests AS r
                  LEFT JOIN visitors AS v
                          ON v.visitor_id = r.visitor_id
                  LEFT JOIN passes AS p
                          ON p.pass_id = r.pass_id
                  LEFT JOIN status AS s
                          ON s.status_id = r.status_id
                  LEFT JOIN patent_agents AS p_a
                          ON p_a.patent_agent_id = r.patent_agent_id
                  LEFT JOIN couriers AS c
                          ON c.courier_id = r.courier_id
                  LEFT JOIN documents AS d
                          ON d.document_id = r.document_id
                  LEFT JOIN services_employees AS s_e
                          ON s_e.service_employee_id = r.service_employee_id 
                  WHERE r.applicant_id = ' . $user_id . $where . '        
                    ORDER BY r.request_date DESC ' .
                  '  LIMIT ' . $offset . ', ' . $row_count;
        
        // Выполняем запрос
        $result = $this->db->query($query);

        return $result->result();
    }

    /**
     * Получить число заявок в БД
     * 
     * @param $active_only параметр, который указывает отображать ли все заявки
     *                     или только активные 
     *
     * @return int число заявок в БД
     */
    public function get_requests_count($where)
    {     
        $sql = "SELECT COUNT(*) as count
                 FROM requests AS r
                 LEFT JOIN visitors AS v
                            ON v.visitor_id = r.visitor_id
                 LEFT JOIN patent_agents AS p_a
                            ON p_a.patent_agent_id = r.patent_agent_id
                 LEFT JOIN services_employees AS s_e
                            ON s_e.service_employee_id = r.service_employee_id
                 LEFT JOIN couriers AS c
                          ON c.courier_id = r.courier_id
                 WHERE {$where}";
                 
        return $this->db->query($sql)
                      ->row()  
                      ->count;
    }
    
    /**
     * Получить число удалённых заявок в БД
     *
     * @return int число удалённых заявок в БД
     */
    public function get_deleted_requests_count()
    {
        return $this->db->select('COUNT(*) AS count')
                      ->where('deleted', 1)
                      ->where_in('status_id', array(1,2))
                      ->get($this->table_requests)
                      ->row()
                      ->count;
    }

    /**
     * Получить число заявок в БД конкретного заявителя
     *
     * @param $user_id id пользователя
     * @return int число заявок в БД
     */
    public function get_requests_user_count($user_id)
    {
        return $this->db->where('applicant_id', $user_id)
                      ->where('visitor_id IS NOT NULL')  
                      ->select('COUNT(*) AS count')
                      ->get($this->table_requests)
                      ->row()
                      ->count;
    }

    /**
     * Получение данных заявки
     *
     * @param $request_id id заявки
     *
     * @return object Объект-результат запроса
     */
    public function get_request($request_id)
    {
        // Запрос на получение данных заявки
        $query = 'SELECT r.request_id, r.request_number, r.request_date, r.issue_date, u.name AS username,
                         rm.number AS room_number, rm.room_id, r.document_id, r.visitor_id,
                         r.applicant_id, r.pass_date,
                         v.last_name AS visitor_last_name, v.first_name AS visitor_first_name, 
                         v.middle_name AS visitor_middle_name,
                         d.series AS document_series, d.number AS document_number,
                         s.value as status, p.number AS pass_number,
                         r.status_id, p_a.name AS patent_agent, c.name AS courier,
                         s_e.name AS service_employee, d_t.type AS document_type,
                         d_t.document_type_id, r.issue_security_id,
                         r_r.reason, r.request_reason_id, r.deleted, us.name AS issue_security_name,
                         r.lost_pass, uss.name AS pass_security_name, r_h.history,
                         r.photo_id, r.request_history_id
                  FROM requests AS r
                  LEFT JOIN visitors AS v
                          ON v.visitor_id = r.visitor_id
                  LEFT JOIN passes AS p
                         ON p.pass_id = r.pass_id
                  LEFT JOIN users AS u
                          ON u.user_id = r.applicant_id
                  LEFT JOIN users AS us
                          ON us.user_id = r.issue_security_id
                  LEFT JOIN users AS uss
                          ON uss.user_id = r.pass_security_id        
                  LEFT JOIN documents AS d
                          ON d.document_id = r.document_id
                  LEFT JOIN rooms AS rm
                          ON rm.room_id = r.room_id
                  LEFT JOIN status AS s
                          ON s.status_id = r.status_id
                  LEFT JOIN patent_agents AS p_a
                          ON p_a.patent_agent_id = r.patent_agent_id
                  LEFT JOIN services_employees AS s_e
                          ON s_e.service_employee_id = r.service_employee_id
                  LEFT JOIN couriers AS c
                          ON c.courier_id = r.courier_id
                  LEFT JOIN document_types AS d_t
                          ON d_t.document_type_id = d.document_type_id
                  LEFT JOIN requests_reasons AS r_r
                          ON r_r.request_reason_id = r.request_reason_id
                   LEFT JOIN requests_histories AS r_h
                          ON r_h.request_history_id = r.request_history_id
                  WHERE r.deleted = 0 and r.request_id = ' . $this->db->escape($request_id);

        // Выполняем запрос
        $result = $this->db->query($query);
        
        return $result->row();
    }
    
    /**
     * Получить статус заявки
     * 
     * @param type $request_id id заявки на пропуск
     * @return int id статуса
     * @throws Exception Исключение, возникающее при несуществующем request_id
     */
    public function get_request_status($request_id)
    {
        $result = $this->db->select('status_id')
                         ->where($this->table_id, $request_id)
                         ->get($this->table_requests)
                         ->row();
        
        if (empty($result) === FALSE)
            return (int) $result->status_id;
        else
            throw new Exception('Даний request_id відсутній в БД!');
    }

    /**
     * Обновление данных заявки
     *
     * @param $request_id id заявки
     * @param $data
     *
     * @return bool
     */
    public function edit_request($request_id, $data)
    {
        // Запрос на обновление в БД
        $this->db->where('request_id', $request_id);
        $this->db->update($this->table_requests, $data);

        return TRUE;
    }

    /**
     * Обновление статуса заявки
     *
     * @param $request_id id заявки
     * @param $status_id id нового статуса заявки
     *
     * @return bool
     */
    public function change_request_status($request_id, $status_id)
    {
        // То, что обновляем
        $data = array('status_id' => $status_id);

        // Запрос на обновление в БД
        $this->db->where('request_id', $request_id);
        $this->db->update($this->table_requests, $data);

        return TRUE;
    }

    /**
     * Удаление заявки из БД
     *
     * @param $request_id id заявки
     */
    public function delete_request($request_id)
    {
        $this->db->where('request_id', $request_id);
        $this->db->update($this->table_requests, array('deleted' => 1));
    }
    
    /**
     * Получить число заявок за месяц
     *
     * @return int Число заявок за месяц
     */
    public function get_count_requests_month()
    {
        $sql = 'SELECT COUNT(*) AS rec_count
                 FROM requests 
                 WHERE date_format(request_date, \'%Y%m\') = date_format(now(), \'%Y%m\');';
        
        $result = $this->db->query($sql)
                         ->row()
                         ->rec_count;
        
        return (int) $result;
    }
    
    /**
     * Получение номера пропуска $request_id
     * 
     * @param int $request_id id заявки
     * @return int id номера пропуска
     */
    public function get_pass_id($request_id)
    {
        $result = $this->db->select('pass_id')
                         ->where($this->table_id, $request_id)    
                         ->get($this->table_requests)
                         ->row()
                         ->pass_id;
        
        return (int) $result;
    }
    
    public function get_history_id($request_id)
    {
        $result = $this->db
                      ->select('request_history_id')
                      ->get_where($this->table_requests, array($this->table_id => $request_id))
                      ->row();
        
        if (empty($result) === FALSE)
            return (int) $result->request_history_id;
        else
            throw new Exception('Нету записи с указанным $request_id!');
    }
    
    /**
     * Получить ID фото
     * 
     * @param int $request_id
     * @return int ID фото или 0 
     */
    public function get_photo_id($request_id)
    {
        $res = $this->db->select('photo_id')
                      ->get_where($this->table_requests, 
                                 array($this->table_id => $request_id))
                      ->row();
        
        return (int)$res->photo_id;
    }
}
