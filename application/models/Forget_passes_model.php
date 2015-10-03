<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Forget_passes_model
 *
 * Модель для работы со списком выданных пропусков сотрудникам, 
 * которые забыли свои дома
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Forget_passes_model extends CI_Model
{
    /**
     * Таблица БД
     * @var string
     */
    private $table = 'forget_passes';

    /**
     * Первичный ключ таблицы
     * @var string
     */
    private $table_id = 'forget_pass_id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Получение пропусков из БД
     *
     * @param type $offset
     * @param type $row_count
     * @param type $employee_id ID сотрудника (0 - все сотрудники)
     * @return array массив объектов-результатов запроса
     */
    public function get_passes($offset, $row_count, $employee_id = 0)
    {
        // Экранирование параметров
        $offset = $this->db->escape($offset);
        $row_count = $this->db->escape($row_count);
        $employee_id = (int)$employee_id;
        
        // Запрос
        $query = 'SELECT f_p.forget_pass_id, e.name AS employee_name, 
                         f_p.issue_date, f_p.pass_date, u.name AS admin_name,
                         p.number AS pass_number, f_p.forget_pass_number,
                         us.name AS pass_admin_name
                  FROM forget_passes AS f_p
                  INNER JOIN employees AS e
                    ON f_p.employee_id = e.employee_id
                  INNER JOIN users AS u
                    ON u.user_id = f_p.administrator_id
                  LEFT JOIN users AS us
                    ON us.user_id = f_p.pass_administrator_id
                  INNER JOIN passes AS p
                    ON p.pass_id = f_p.pass_id';
        
        // Фильтр по сотруднику
        if ($employee_id > 0)
            $where = ' WHERE f_p.employee_id = ' . $employee_id;
        else
            $where = '';
        
        $query .= $where . ' ORDER BY issue_date DESC' .
                           ' LIMIT ' . $offset . ', ' . $row_count;
        
        // Выполнение запроса и возврат результата
        return $this->db->query($query)
                        ->result();
    }
    
    /**
     * Получить данные забытого пропуска
     *
     * @param int $forget_pass_id id забытого пропуска
     * @return object Объект-забытый пропуск 
     */
    public function get_forget_pass($forget_pass_id)
    {
        return $this->db
                   ->get_where($this->table, 
                              array($this->table_id => $forget_pass_id))
                   ->row();    
    }
    
    /**
     * Обновление данных забытого пропуска
     * 
     * @param int $forget_pass_id id забытого пропуска
     * @param array $data массив данных для обновы
     */
    public function edit($forget_pass_id, $data)
    {
        $this->db
            ->update($this->table, 
                    $data, 
                    array($this->table_id => $forget_pass_id));
        
        return TRUE;
    }
    
    /**
     * Получение количества записей в таблице
     * 
     * @param type $employee_id ID сотрудника (0 - все сотрудники)
     * @return int количество записей 
     */
    public function get_passes_count($employee_id = 0)
    {
        $query = 'SELECT COUNT(*) AS count
                  FROM forget_passes';
                
        if ($employee_id > 0)
            $query .= ' WHERE employee_id = ' . $this->db->escape($employee_id);
        
        return $this->db->query($query)
                        ->row()
                        ->count;    
    }
    
    /**
     * Получить дату сдачи пропуска
     * 
     * @param type $forget_pass_id ID забытого пропуска 
     * 
     * @return object объект-результат запроса 
     */
    public function get_pass_data($forget_pass_id)
    {
        return $this->db->where($this->table_id, $forget_pass_id)
                        ->get($this->table)
                        ->row();
    }
    
    /**
     * Сдать пропуск (в поле pass_date поместить текущую дату )
     * 
     * @param int $forget_pass_id ID забытого пропуска 
     */
    public function take_pass($forget_pass_id)
    {
        $this->db->where($this->table_id, $forget_pass_id);
        $this->db->update($this->table, array('pass_date' => date('Y-m-d H:i:s')));
    }
    
    /**
     * Добавить забытый пропуск в БД
     *
     * @param type $data Данные забытого пропуска 
     */
    public function add($data)
    {
        $this->db->insert($this->table, $data);
    }
    
    /**
     * Получить количество забытых пропусков сотрудника
     * 
     * @param int $employee_id ID сотрудника
     * @return int количество пропусков
     */
    public function get_count_passes($employee_id)
    {
        $query = 'SELECT COUNT(*) AS count 
                  FROM forget_passes
                  WHERE employee_id = ' . $this->db->escape($employee_id);
        
        $result =  $this->db->query($query)
                            ->row()
                            ->count;
        
        return (int)$result;
    }
    
    /**
     * Получить число забытых пропусков за сегодня
     *
     * @return int Число забытых пропусков сегодня 
     */
    public function get_today_last_fp_num()
    {
        $result = $this->db->select('forget_pass_number')
                         ->where('issue_date > \'' . date('Y-m-d 00:00:00') . '\'')
                         ->order_by('issue_date', 'DESC')
                         ->limit(1)
                         ->get($this->table)
                         ->row();
        
        if (empty($result) === FALSE)
            return $result->forget_pass_number;
        else
            return 0;
    }
    
    /**
     * Получение номера пропуска $forget_pass_id
     * 
     * @param int $forget_pass_id id забытого пропуска
     * @return int id номера пропуска
     */
    public function get_pass_id($forget_pass_id)
    {
        $result = $this->db->select('pass_id')
                         ->where($this->table_id, $forget_pass_id)    
                         ->get($this->table)
                         ->row()
                         ->pass_id;
        
        return (int) $result;
    }
    
    /**
     * Получить число заявок за месяц
     *
     * @return int Число заявок за месяц
     */
    public function get_count_forget_passes_month()
    {
        $sql = 'SELECT COUNT(*) AS rec_count 
                 FROM ' . $this->table . 
               ' WHERE date_format(issue_date, \'%Y%m\') = date_format(now(), \'%Y%m\');';
        
        $result = $this->db->query($sql)
                         ->row()
                         ->rec_count;
        
        return (int) $result;
    }
}