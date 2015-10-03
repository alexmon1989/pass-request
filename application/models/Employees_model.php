<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Employees_model
 *
 * Модель для работы со списком сотрудников Института, 
 * которые забывали свой пропуск дома
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Employees_model extends CI_Model
{
    /**
     * Таблица БД
     * @var string
     */
    private $table = 'employees';

    /**
     * Первичный ключ таблицы
     * @var string
     */
    private $table_id = 'employee_id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Получить ID по имени
     * 
     * @param string $employee_name имя сотрудника
     * @return object объект-результат запроса 
     */
    public function get_id_by_name($employee_name)
    {
        return $this->db->select($this->table_id)
                        ->where('name', $employee_name)
                        ->get($this->table)
                        ->row();   
    }
    
    /**
     * Добавление Сотрудника
     * 
     * @param string $employee_name имя сотрудника
     * @return int ID Добавленной записи
     */
    public function add($employee_name)
    {
        $this->db->insert($this->table, array('name' => $employee_name));
        
        return $this->db->insert_id();
    }
    
    /**
     * Выборка всех сотрудников
     * 
     * @return array Массив сотрудников
     */
    public function get_all()
    {
        return $this->db->get($this->table)
                        ->result();
    }
    
    /**
     * Удаление сотрудника
     * 
     * @param type $employee_id ID сотрудника 
     */
    public function delete($employee_id)
    {
        $this->db->where($this->table_id, $employee_id);
        $this->db->delete($this->table);
    }
    
    /**
     * Получение имён сотрудников по части имени
     *
     * @param string $term часть имени
     * @return array массив объектов 
     */
    public function get_name_by_term($term)
    {
        $query = $this->db->distinct()
                        ->select('name')
                        ->from($this->table)
                        ->like('name', $term)
                        ->get();
        
        return $query->result();
                        
        
    }
}