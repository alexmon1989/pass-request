<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     * Passes_model
     *
     * Модель для работы со списком пропусков
     *
     * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
     * @version 1.0
     */
class Passes_model extends CI_Model
{
    /**
     * Таблица БД со списком пропусков
     * @var string
     */
    private $table_passes = 'passes';

    /**
     * Первичный ключ таблицы со списком пропусков
     * @var string
     */
    private $table_id = 'pass_id';

    /**
     *  Конструктор класса
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Получение ID пропуска по его номеру
     *
     * @param $num номер пропуска
     * @return object объект-результат запроса
     */
    public function get_pass_id_by_num($num)
    {
        return $this->db->select('pass_id')
                        ->get_where($this->table_passes, array('number' => $num))
                        ->row();
    }

    /**
     * Получение номера пропуска по его номеру
     *
     * @param $pass_id ID пропуска
     * @return object объект-результат запроса
     */
    public function get_pass_num_by_id($pass_id)
    {
        return $this->db->select('number')
            ->get_where($this->table_passes, array('pass_id' => $pass_id))
            ->row();
    }

    /**
     * Добавление пропуска в БД
     *
     * @param $num номер пропуска
     * @return int id только что добавленного пропуска
     */
    public function add_pass($num, $room_id)
    {
        // Ищем уже удалённый пропуск
        $deleted_pass_id = $this->get_pass_id_by_num($num);
        
        if (empty($deleted_pass_id) === TRUE) // Не найдено - добавляем новый
        {
            // Добавление нового
            $this->db->insert($this->table_passes, array('number' => $num, 
                                                    'room_id' => $room_id)
                           );
            return $this->db->insert_id();
        }
        else // Найдено - делаем удалённый не удалённым
        {
            $deleted_pass_id = (int)$deleted_pass_id->pass_id;
            $this->edit($deleted_pass_id, array('deleted' => 0, 
                                            'room_id' => $room_id)
                      );
            return $deleted_pass_id;
        }
        
    }
    
    /**
     * Обновление данных пропуска
     * 
     * @param int $pass_id ID пропуска
     * @param array $data ассоциированный массив с данными для редактирования 
     */
    public function edit($pass_id, $data)
    {
        $this->db->update($this->table_passes, 
                        $data, 
                        array('pass_id' => $pass_id));        
    }

    /**
     * Извлекает из БД все пропуска (не удалённые)
     *
     * @return array массив объектов-результатов запросов
     */
    public function get_passes()
    {
        $res = $this->db->select($this->table_passes . '.*, rooms.number AS room')
                      ->join('rooms', 'rooms.room_id = ' . $this->table_passes . '.room_id')  
                      ->order_by($this->table_passes . '.number', 'ASC')
                      ->get_where($this->table_passes, array($this->table_passes .'.deleted' => 0))
                      ->result();
        
        return $res;
    }
    
    /**
     * Изменение статуса пропуска
     * 
     * @param int $pass_id id пропуска
     * @param int $status_id id статуса пропуска (1 - не выдано, 2 - выдано, 3 - утеряно)
     * 
     * @return boolean результат выполнения операции изменения
     */
    public function change_status($pass_id, $status_id)
    {
        $this->db->update($this->table_passes, 
                        array('pass_status_id' => $status_id), 
                        array('pass_id' => $pass_id));
        return TRUE;
    }
    
    /**
     * Получение не выданных пропусков
     * 
     * @return array 
     */
    public function get_free_passes()
    {
        $result = $this->db->select('pass_id AS value, number AS text')
                         ->where('pass_status_id', 1)
                         ->order_by('number')
                         ->get($this->table_passes)
                         ->result_array();
        
        return $result;
    }
    
    /**
     * Свободен ли пропуск
     * 
     * @param int $pass_id ID пропуска
     * @return bool   
     */
    public function is_pass_free($pass_id)
    {
        $result = (int) $this->db->select('pass_status_id')
                              ->where('pass_id', $pass_id)
                              ->get($this->table_passes)
                              ->row()
                              ->pass_status_id;
        
        if ($result === 1)
            return TRUE;
        else
            return FALSE;
    }
    
    /**
     * Удаление пропуска (на самом деле изменение поля 'deleted' на 1)
     * 
     * @param int $pass_id id пропуска
     * 
     * @return boolean результат удаления
     */
    public function delete($pass_id)
    {
        $this->db->update($this->table_passes, 
                        array('deleted' => 1), 
                        array($this->table_id => $pass_id));
        
        return TRUE;
    }
    
    /**
     * Получение данных пропуска
     * 
     * @param int $pass_id id пропуска
     * 
     * @return array массив с данными пропуска 
     */
    public function get_pass($pass_id)
    {
        $result = $this->db
                     ->get_where($this->table_passes, 
                                array($this->table_id => $pass_id))
                     ->row_array();
        
        return $result;
    }
}
