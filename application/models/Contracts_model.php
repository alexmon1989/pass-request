<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Contracts_model
 *
 * Модель для работы со списком временных пропусков
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Contracts_model extends CI_Model
{
    /**
     * Таблица БД с временными заяками на пропуски
     * @var string
     */
    private $table = 'contracts';

    /**
     * Первичный ключ таблицы с временными заяками на пропуски
     * @var string
     */
    private $table_id = 'contract_id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Добавление временного пропуска в БД
     *
     * @param array $data массив данных пропуска
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
    public function get_contracts($offset, $row_count, $where = '',
                              $order_by = 'issue_date', $method = 'DESC')
    {
        switch ($order_by)
        {
            case 'date_from':
            {
                $order_by = 'c.date_from';
                break;
            }
            case 'date_to':
            {
                $order_by = 'c.date_to';
                break;
            }
            case 'visitorname':
            {
                $order_by = 'cv.name';
                break;
            }
            case 'pass_number':
            {
                $order_by = 'p.number';
                break;
            }
            case 'username':
            {
                $order_by = 'u.name';
                break;
            }
            default:
            {
                $order_by = 'c.issue_date';
                break;
            }
        }
        
        // Экранирование переменных
        $offset = $this->db->escape($offset);
        $row_count = $this->db->escape($row_count);
        //$order_by = $this->db->escape($order_by);
        if ($method !== 'ASC' and $method !== 'DESC')
            throw new Exception('Спроба взлому');
        
        // Запрос на получение заявок
        $query = 'SELECT c.contract_id, c.date_from, c.date_to, c.contract_number,
                          c.status_id, c.pass_id,  
                          cv.name AS visitorname, u.name AS username,
                          p.number AS pass_number
                  FROM contracts AS c
                  LEFT JOIN contract_visitors AS cv
                          ON cv.contract_visitor_id = c.contract_visitor_id
                  LEFT JOIN users AS u
                          ON u.user_id = c.applicant_id
                  LEFT JOIN passes AS p
                          ON p.pass_id = c.pass_id ' . $where .
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
    public function get_contract_data($contract_id)
    {
        return $this->db->select('*, contract_visitors.name AS contract_visitor, 
                                   u.name AS applicant, us.name AS administrator, 
                                   p.number AS pass_number, uss.name AS pass_administrator,
                                   c_h.history')
                      ->join('contract_visitors', 'contract_visitors.contract_visitor_id = ' . $this->table . '.contract_visitor_id')
                      ->join('users AS u', 'u.user_id = ' . $this->table . '.applicant_id')
                      ->join('users AS us', 'us.user_id = ' . $this->table . '.administrator_id')
                      ->join('users AS uss', 'uss.user_id = ' . $this->table . '.pass_administrator_id', 'LEFT')
                      ->join('passes AS p', 'p.pass_id = ' . $this->table . '.pass_id')
                      ->join('contracts_histories AS c_h', 'c_h.contract_history_id = ' . $this->table . '.contract_history_id', 'LEFT')
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
    public function get_count_contracts_month()
    {
        $sql = 'SELECT COUNT(*) AS rec_count 
                 FROM ' . $this->table . 
               ' WHERE date_format(issue_date, \'%Y%m\') = date_format(now(), \'%Y%m\');';
        
        $result = $this->db->query($sql)
                         ->row()
                         ->rec_count;
        
        return (int) $result;
    }
    
    /**
     * Обновление данных трудового договора
     * 
     * @param int $contract_id id трудового договора
     * @param array $data массив данных для обновы
     */
    public function edit($contract_id, $data)
    {
        $this->db
            ->update($this->table, 
                    $data, 
                    array($this->table_id => $contract_id));
        
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