<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Security_model
 *
 * Модель для работы со списком охранников
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Security_model extends CI_Model
{
    /**
     * Таблица БД со списком охранников
     * @var string
     */
    private $table_security = 'users';

    /**
     * Первичный ключ таблицы со списком охранников
     * @var string
     */
    private $table_id = 'user_id';
    
    /**
     * id роли
     * @var int
     */
    private $role_id = 2;

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    /**
     * Получение логина охранника по его ID
     *
     * @param $security_id ID охранника
     * @return mixed Объект-логин охранника
     */
    public function get_login_by_id($security_id)
    {
        return $this->db->select('login')
            ->get_where($this->table_security, array($this->table_id => $security_id))
            ->row();
    }
        
    /**
     * Получить список охранников
     *
     * @return array массив объектов-пользователей
     */
    public function get_securities()
    {
        return $this->db->select("$this->table_id AS security_id, name, login")
                      ->where('deleted', 0)
                      ->where('role_id', 2)
                      ->order_by('name', 'ASC')
                      ->get($this->table_security)
                      ->result();
    }

    /**
     * Обновлние данных охранника
     *
     * @param $security_id id охранника
     * @param $data данные для обновления
     */
    public function edit_security($security_id, $data)
    {
        $this->db->where($this->table_id, $security_id)
                 ->update($this->table_security, $data);
    }

    /**
     * Добавление нового охранника
     *
     * @param $data данные охранника
     *
     * @return int id добавленного охранника
     */
    public function add_security($data)
    {
        $data['role_id'] = $this->role_id;
        
        // Ищем уже удаленного охранника
        $security = $this->get_id_by_name($data['name']);

        if (empty($security) === TRUE) // Не найден, добавляем нового
        {
            $this->db->insert($this->table_security, $data);

            return $this->db->insert_id();
        }
        else
        {
            // ID старой записи
            $security_id = $security->security_id;

            $data['deleted'] = 0;

            // Обновляем старые данные
            $this->db->where($this->table_id, $security_id);
            $this->db->update($this->table_security, $data);
            return $security_id;
        }
    }

    /**
     * Удаление охранника (на самом деле установка 1 в поле deleted)
     *
     * @param $security_id id охранника
     */
    public function delete_security($security_id)
    {
        $this->db->where($this->table_id, $security_id);
        $this->db->update($this->table_security, array('deleted' => 1, 'login' => ''));
    }
    
    /**
     * Получение ID пользователя по его имени
     *
     * @param $name Имя пользователя
     * @return mixed Объект-ID пользователя
     */
    public function get_id_by_name($name)
    {
        return $this->db->select('user_id AS security_id')
            ->get_where($this->table_security, array('name' => $name))
            ->row();
    }
}
