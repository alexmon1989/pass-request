<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Statuses_model
 *
 * Модель для работы со списком статусов заявок на пропуска
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Statuses_model extends CI_Model
{
    /**
     * Таблица БД со списком статусов
     * @var string
     */
    private $table_statuses = 'status';

    /**
     * Первичный ключ таблицы со списком статусов
     * @var string
     */
    private $table_id = 'status_id';

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Извлечь статусы из БД
     *
     * @return object массив объектов-статусов
     */
    public function get_statuses()
    {
        return $this->db->get($this->table_statuses)
                        ->result();
    }

}
