<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Chiefs_model
 *
 * Модель для работы со списком начальников
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Chiefs_model extends CI_Model
{
    /**
     * Таблица БД со списком начальников
     * @var string
     */
    private $table = 'users';

    /**
     * Первичный ключ таблицы со списком начальников
     * @var string
     */
    private $table_id = 'user_id';
    
    /**
     * ID роли начальника
     * @var int
     */
    private $role_id = 3;

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Получить список заявителей
     *
     * @return array массив объектов-начальников
     */
    public function get_all()
    {
        return $this->db->select("$this->table_id AS chief_id, name, login")
                      ->where('role_id', $this->role_id)
                      ->where('deleted', 0)
                      ->order_by('name', 'ASC')
                      ->get($this->table)
                      ->result();
    }

    /**
     * Обновлние данных начальника
     *
     * @param $chief_id id пользователя
     * @param $data данные для обновления
     */
    public function edit($chief_id, $data)
    {
        $this->db->where($this->table_id, $chief_id)
                ->update($this->table, $data);
    }

    /**
     * Добавление нового начальника
     *
     * @param $data Данные пользователя
     *
     * @return int id добавленного пользователя
     */
    public function add($data)
    {
        $data['role_id'] = $this->role_id;
        
        // Ищем уже удаленного пользователя
        $user = $this->get_id_by_name($data['name']);

        if (empty($user) === TRUE) // Не найден, добавляем нового
        {
            $this->db->insert($this->table, $data);

            return $this->db->insert_id();
        }
        else
        {
            // ID старой записи
            $user_id = $user->chief_id;

            $data['deleted'] = 0;

            // Обновляем старые данные
            $this->db->where($this->table_id, $user_id);
            $this->db->update($this->table, $data);
            return $user_id;
        }
    }

    /**
     * Удаление пользователя (на самом деле установка 1 в поле deleted)
     *
     * @param $chief_id id пользователя
     */
    public function delete($chief_id)
    {
        $this->db->where($this->table_id, $chief_id);
        $this->db->update($this->table, array('deleted' => 1, 'login' => ''));
    }
    
    /**
     * Получение логина охранника по его ID
     *
     * @param $security_id ID охранника
     * @return mixed Объект-логин охранника
     */
    public function get_login_by_id($chief_id)
    {
        return $this->db->select('login')
            ->get_where($this->table, array($this->table_id => $chief_id))
            ->row();
    }
    
    /**
     * Получение ID пользователя по его имени
     *
     * @param $name Имя пользователя
     * @return mixed Объект-ID пользователя
     */
    public function get_id_by_name($name)
    {
        return $this->db->select("$this->table_id AS chief_id")
                      ->get_where($this->table, array('name' => $name))
                      ->row();
    }
}