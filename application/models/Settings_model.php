<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Settings_model
 *
 * Модель для работы с настройками программы
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Settings_model extends CI_Model
{
    /**
     * Таблица БД со списком администраторов
     * @var string
     */
    private $table = 'settings';

    /**
     * Первичный ключ таблицы со списком администраторов
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
     * Сохранение настроек программы
     * 
     * @param array $data ассоциативный массив с настройками
     */
    public function save($data)
    {
        $this->db
            ->update($this->table, $data);
    }
    
    /**
     * Получение настроек программы
     * 
     * @return mixed Объект-результат запроса 
     */
    public function get()
    {
        $res = $this->db
                   ->get($this->table)
                   ->row(); 
        
        return $res;
    }
}