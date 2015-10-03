<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Administrators_model
 *
 * Модель для работы со списком администраторов
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Administrators_model extends CI_Model
{
    /**
     * Таблица БД со списком администраторов
     * @var string
     */
    private $table = 'users';

    /**
     * Первичный ключ таблицы со списком администраторов
     * @var string
     */
    private $table_id = 'user_id';
    
    /**
     * id роли
     * @var int
     */
    private $role_id = 1;

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Получение ID администратора по его имени
     *
     * @param $name Имя администратора
     * @return mixed Объект-ID администратора
     */
    public function get_id_by_name($name)
    {
        return $this->db->select($this->table_id . ' AS administrator_id')
            ->get_where($this->table, array('name' => $name))
            ->row();
    }

    /**
     * Получение логина администратора по его ID
     *
     * @param $administrator_id ID администратора
     * @return mixed Объект-логин администратора
     */
    public function get_login_by_id($administrator_id)
    {
        return $this->db->select('login')
            ->get_where($this->table, array($this->table_id => $administrator_id))
            ->row();
    }
    
    /**
     * Получить список администраторов
     *
     * @return array массив объектов-администраторов
     */
    public function get_administrators()
    {
        return $this->db->select('user_id AS administrator_id, name')
                      ->where('deleted', 0)
                      ->where('role_id', $this->role_id)
                      ->order_by('name', 'ASC')
                      ->get($this->table)
                      ->result();
    }   

    /**
     * Обновлние данных администратора
     *
     * @param $administrator_id id администратора
     * @param $data данные для администратора
     */
    public function edit_administrator($administrator_id, $data)
    {
        $this->db->where($this->table_id, $administrator_id)
                ->update($this->table, $data);
    }

    /**
     * Добавление нового администратора
     *
     * @param $data данные администратора
     *
     * @return int id добавленного администратора
     */
    public function add_administrator($data)
    {
        $data['role_id'] = $this->role_id;
        // Ищем уже удаленного администратора
        $administrator = $this->get_id_by_name($data['name']);

        if (empty($administrator) === TRUE) // Не найден, добавляем нового
        {
            $this->db->insert($this->table, $data);

            return $this->db->insert_id();
        }
        else
        {
            // ID старой записи
            $administrator_id = $administrator->administrator_id;

            $data['deleted'] = 0;

            // Обновляем старые данные
            $this->db->where($this->table_id, $administrator_id);
            $this->db->update($this->table, $data);
            return $administrator_id;
        }
    }

    /**
     * Удаление администратора (на самом деле установка 1 в поле deleted)
     *
     * @param $administrator_id id администратора
     */
    public function delete($administrator_id)
    {
        $this->db->where($this->table_id, $administrator_id);
        $this->db->update($this->table, array('deleted' => 1, 'login' => ''));
    }
    
    /**
     * Получение имени алмина по его логину
     * 
     * @param string $login логин админа
     * @return string Имя админа 
     */
    public function get_name_by_login($login)
    {
        return $this->db->select('name')
                      ->get_where($this->table, array('login' => $login))
                      ->row()
                      ->name;
    }
}