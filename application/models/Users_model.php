<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Users_model
 *
 * Модель для работы со списком пользователей
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Users_model extends CI_Model
{
    /**
     * Таблица БД со списком пользователей
     * @var string
     */
    private $table_users = 'users';

    /**
     * Таблица БД со списком запросов на заявки
     * @var string
     */
    private $table_requests = 'requests';

    /**
     * Первичный ключ таблицы со списком пользователей
     * @var string
     */
    private $table_requests_id = 'request_id';

    /**
     * Первичный ключ таблицы со списком пользователей
     * @var string
     */
    private $table_id = 'user_id';

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    /**
     * Получение данных пользователя по его логину
     *
     * @param string $login Логин пользователя
     * @return object Объект пользователя
     */
    public function get_user_by_login($login)
    {
        return $this->db->get_where($this->table_users, array('login' => $login))
                      ->row();
    }
        
    /**
     * Получение id роли пользователя
     *
     * @param string $login Логин пользователя
     * @return int id роли пользователя 
     */
    public function get_user_role_id($login)
    {
        return (int) $this->db->select('role_id')
                            ->where('login', $login)
                            ->get($this->table_users)
                            ->row()
                            ->role_id;
    }
           

    /**
     * Получение имени пользователя по его логину
     *
     * @param $login Логин пользователя
     * @return mixed Объект-имя пользователя
     */
    public function get_name_by_login($login)
    {
        return $this->db->select('user_id, name')
                        ->get_where($this->table_users, array('login' => $login))
                        ->row();
    }

    /**
     * Получение имени пользователя по его логину
     *
     * @param $user_id
     * @return mixed Объект-имя пользователя
     */
    public function get_name_by_id($user_id)
    {
        return $this->db->select('name')
                        ->get_where($this->table_users, array($this->table_id => $user_id))
                        ->row();
    }

    /**
     * Получение ID номера комнаты по ID пользвателя
     *
     * @param $user_id ID пользователя в БД
     * @return object Объект - номер комнаты
     */
    public function get_user_room_id($user_id)
    {
        return $this->db->select('room_id')
                        ->order_by($this->table_requests_id, 'desc')
                        ->get_where($this->table_requests, array('applicant_id' => $user_id), 1)
                        ->row();
    }

    /**
     * Получение ID пользователя по его логину
     *
     * @param $login Логин пользователя
     * @return mixed Объект-ID пользователя
     */
    public function get_id_by_login($login)
    {
        return $this->db->select('user_id')
            ->get_where($this->table_users, array('login' => $login))
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
        return $this->db->select('user_id')
            ->get_where($this->table_users, array('name' => $name))
            ->row();
    }

    /**
     * Получение логина пользователя по его ID
     *
     * @param $user_id ID пользователя
     * @return mixed Объект-логин пользователя
     */
    public function get_login_by_id($user_id)
    {
        return $this->db->select('login')
            ->get_where($this->table_users, array($this->table_id => $user_id))
            ->row();
    }

    /**
     * Получить список пользователей
     * 
     * @param $role_id ID роли пользователя
     * 
     * @return array массив объектов-пользователей
     */
    public function get_users($role_id)
    {
        return $this->db->where('role_id', $role_id)
                      ->where('deleted', 0)
                      ->order_by('name', 'ASC')
                      ->get($this->table_users)
                      ->result();
    }
    
    /**
     * Получить список заявителей с ролями 4,3
     * 
     * @param $role_id ID роли пользователя
     * 
     * @return array массив объектов-пользователей
     */
    public function get_applicants()
    {
        return $this->db->where_in('role_id', array(3, 4))
                      ->where('deleted', 0)
                      ->order_by('name', 'ASC')
                      ->get($this->table_users)
                      ->result();
    }

    /**
     * Обновлние данных пользователя
     *
     * @param $user_id id пользователя
     * @param $data данные для обновления
     */
    public function edit_user($user_id, $data)
    {
        $this->db->where($this->table_id, $user_id)
                 ->update($this->table_users, $data);
    }

    /**
     * Добавление нового пользователя
     *
     * @param $data Данные пользователя
     *
     * @return int id добавленного пользователя
     */
    public function add_user($data)
    {
        // Ищем уже удаленного пользователя
        $user = $this->get_id_by_name($data['name']);

        if (empty($user) === TRUE) // Не найден, добавляем нового
        {
            $this->db->insert($this->table_users, $data);

            return $this->db->insert_id();
        }
        else
        {
            // ID старой записи
            $user_id = $user->user_id;

            $data['deleted'] = 0;

            // Обновляем старые данные
            $this->db->where($this->table_id, $user_id);
            $this->db->update($this->table_users, $data);
            return $user_id;
        }
    }

    /**
     * Удаление пользователя (на самом деле установка 1 в поле deleted)
     *
     * @param $user_id id пользователя
     */
    public function delete_user($user_id)
    {
        $this->db->where($this->table_id, $user_id);
        $this->db->update($this->table_users, array('deleted' => 1, 'login' => ''));
    }

    /**
     * Проверяет есть ли пользователь в БД
     *
     * @param $user_id id пользователя
     *
     * @return bool результат наличия в БД пользователя
     */
    public function is_user_exist($user_id)
    {
        // Запрос в БД
        $result = $this->get_name_by_id($user_id);

        // Проверяем результат запроса
        if (empty($result) === FALSE)
            return TRUE;
        else
            return FALSE;
    }
}