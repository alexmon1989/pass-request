<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Room_model
 *
 * Модель для работы со списком кабинетов
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Rooms_model extends CI_Model
{
    /**
     * Таблица БД с кабинетами
     * @var string
     */
    private $table_rooms = 'rooms';

    /**
     * Первичный ключ таблицы с кабинетами
     * @var string
     */
    private $table_id = 'room_id';

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Выборка списка кабинетов
     *
     * @param $role_id
     * 
     * @return object Список кабинетов
     */
    public function get_rooms_list()
    {
        return $this->db->order_by('number', 'ASC')
                      ->get($this->table_rooms)
                      ->result();
    }

    /**
     * Получение номера комнаты по ID пользователя
     *
     * @param $user_id ID пользователя в БД
     * @return object Объект - номер комнаты
     */
    public function get_user_room($user_id)
    {
        $this->db->select('r.number');
        $this->db->from($this->table_rooms . ' AS r');
        $this->db->join('users as u', 'r.room_id = u.room_id');
        $this->db->where('u.user_id', $user_id);

        return $this->db->row();
    }

    /**
     * Получение номера комнаты по её ID
     *
     * @param $room_id ID кабинета
     * @return object Объект-данные комнаты
     */
    public function get_number_by_id($room_id)
    {
        return $this->db->get_where($this->table_rooms, array('room_id' => $room_id))
                      ->row();
    }

    /**
     * Существует ли такой кабинет
     *
     * @param $room_id ID кабинета
     * @return bool
     */
    public function is_room_exist($room_id)
    {
        // Запрос
        $result = $this->db->get_where($this->table_rooms, array('room_id' => $room_id))
                           ->row();

        // Если что-то вернулось, значит кабинет существует
        if (empty($result) === TRUE)
            return FALSE;
        else
            return TRUE;
    }
}