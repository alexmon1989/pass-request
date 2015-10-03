<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Temp_requests_model
 *
 * Модель для работы со списком временных заявок
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Temp_requests_model extends CI_Model
{
    /**
     * Таблица БД
     * @var string
     */
    private $table = 'temp_requests';

    /**
     * Первичный ключ
     * @var string
     */
    private $table_id = 'id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Добавление в БД
     *
     * @param array $data массив данных
     * @return int ID добавленной записи
     */
    public function add($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
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
    public function get_all($offset, $row_count, $order_by = 'created_at', $method = 'DESC')
    {
        switch ($order_by)
        {
            case 'date_from':
            {
                $order_by = 't_r.date_from';
                break;
            }
            case 'date_to':
            {
                $order_by = 't_r.date_to';
                break;
            }
            case 'visitor_name':
            {
                $order_by = 't_r.visitor_name';
                break;
            }
            case 'pass_number':
            {
                $order_by = 'p.number';
                break;
            }
            default:
            {
                $order_by = 't_r.created_at';
                break;
            }
        }
        
        // Экранирование переменных
        $offset = $this->db->escape($offset);
        $row_count = $this->db->escape($row_count);
        
        if ($method !== 'ASC' and $method !== 'DESC')
            throw new Exception('Спроба взлому');
        
        // Запрос на получение заявок
        $query = 'SELECT t_r.*,
                         p.number AS pass_number
                  FROM temp_requests AS t_r
                  LEFT JOIN passes AS p
                          ON t_r.pass_id = p.pass_id ' .
                ' ORDER BY ' . $order_by . ' ' . $method .
                ' LIMIT ' . $offset . ', ' . $row_count;

        // Выполняем запрос
        $result = $this->db->query($query);

        return $result->result();
    }
    
    /**
     * Получение истекающих контрактов
     * 
     * @return object Объект-результат запроса
     */
    public function get_old_contracts()
    {
        $query = 'SELECT c.contract_id, c.date_from, c.date_to, c.status_id,
                          c.pass_id, c.contract_number,
                         cv.name AS visitorname, u.name AS username,
                         p.number AS pass_number
                  FROM contracts AS c
                  LEFT JOIN contract_visitors AS cv
                          ON cv.contract_visitor_id = c.contract_visitor_id
                  LEFT JOIN users AS u
                          ON u.user_id = c.applicant_id
                  LEFT JOIN passes AS p
                          ON p.pass_id = c.pass_id
                  WHERE (((DATEDIFF(NOW(), c.date_to) <= 0)
                            AND (DATEDIFF(NOW(), c.date_to) >= -3)) 
                            AND (c.status_id = 2)                     
                        OR ((DATEDIFF(NOW(), c.date_to) > 0) 
                            AND c.status_id = 2))
                  ORDER BY c.date_to ASC';
        
        // Выполняем запрос
        $result = $this->db->query($query);

        return $result->result();
    }

    /**
     * Получить число временных пропусков в БД
     *
     * @return int число заявок в БД
     */
    public function get_count()
    {
        return $this->db->select('COUNT(*) AS count')
                       ->get($this->table)
                      ->row()
                      ->count;
    }

    /**
     * Получить число временных пропусков в БД конкретного заявителя
     *
     * @param $user_id id пользователя
     * @return int число заявок в БД
     */
    public function get_request_user_count($user_id)
    {
        return $this->db->where('user_id', $user_id)
                      ->select('COUNT(*) AS count')
                      ->get($this->table)
                      ->row()
                      ->count;
    }
    
    /**
     * Получить данные контракта
     * 
     * @param int $contract_id ID контракта
     * 
     * @return object объект-результат запроса 
     */
    public function get_data($contract_id)
    {
        return $this->db->select('*, p.number AS pass_number')
                      ->join('passes AS p', 'p.pass_id = ' . $this->table . '.pass_id')
                      ->get_where($this->table, 
                                 array($this->table_id => $contract_id))
                      ->row();
    }
    
    /**
     * Изьять пропуск
     *
     * @param int $contract_id ID контракта
     */
    public function take_pass($contract_id)
    {
        $data['status_id'] = 3;
        $this->db->update($this->table, 
                        $data, 
                        array($this->table_id => $contract_id));
    }
    
    /**
     * Получить число контрактов за сегодня
     *
     * @return int Число контрактов сегодня 
     */
    public function get_today_last_contract_num()
    {
        $result = $this->db->select('contract_number')
                         ->where('date_from > \'' . date('Y-m-d 00:00:00') . '\'')
                         ->order_by('date_from', 'DESC')
                         ->limit(1)
                         ->get($this->table)
                         ->row();
        
        if (empty($result) === FALSE)
            return $result->contract_number;
        else
            return 0;
    }
    
    /**
     * Получение номера пропуска $contract_id
     * 
     * @param int $contract_id id трудового договора
     * @return int id номера пропуска
     */
    public function get_pass_id($contract_id)
    {
        $result = $this->db->select('pass_id')
                         ->where($this->table_id, $contract_id)    
                         ->get($this->table)
                         ->row()
                         ->pass_id;
        
        return (int) $result;
    }
    
    /**
     * Получить число заявок за месяц
     *
     * @return int Число заявок сегодня 
     */
    public function get_count_month()
    {
        $sql = 'SELECT COUNT(*) AS rec_count 
                 FROM ' . $this->table . 
               ' WHERE date_format(from_unixtime(created_at), \'%Y%m\') = date_format(now(), \'%Y%m\');';
        
        $result = $this->db->query($sql)
                         ->row()
                         ->rec_count;
        
        return (int) $result;
    }
    
    /**
     * Обновление данных временной заявки
     * 
     * @param int $temp_request_id id временной заявки
     * @param array $data массив данных для обновы
     */
    public function edit($temp_request_id, $data)
    {
        $this->db
            ->update($this->table, 
                    $data, 
                    array($this->table_id => $temp_request_id));
        
        return TRUE;
    }
    
    /**
     * Получить ID фото
     * 
     * @param int $contract_id
     * @return int ID фото или 0 
     */
    public function get_photo_id($contract_id)
    {
        $res = $this->db->select('photo_id')
                      ->get_where($this->table, 
                                 array($this->table_id => $contract_id))
                      ->row();
        
        return (int)$res->photo_id;
    }
}