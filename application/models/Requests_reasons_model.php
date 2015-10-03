<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Requests_reasons_model
 *
 * Модель для работы со списком оснований на заявки на пропуска
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Requests_reasons_model extends CI_Model
{
    /**
     * Таблица БД
     * @var string
     */
    private $table = 'requests_reasons';

    /**
     * Первичный ключ таблицы
     * @var string
     */
    private $table_id = 'request_reason_id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Добавление основания на выдачу заявки
     *
     * @param string $reason основание для выдачи
     * @return int ID добавленной записи
     */
    public function add($reason)
    {
        $this->db->insert($this->table, array('reason' => $reason));
        return $this->db->insert_id();
    }
    
    /**
     * Получение основания по его ID
     *
     * @param int $reason_id id основания
     * @return object объект-результат запроса 
     */
    public function get($reason_id)
    {
        return  $this->db->get_where($this->table, array($this->table_id => $reason_id))
                       ->row();
    }
}