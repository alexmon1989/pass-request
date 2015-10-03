<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Visitor_model
 *
 * Модель для работы со списком посетителей
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Contract_visitors_model extends CI_Model
{
    /**
     * Таблица БД с посетителями
     * @var string
     */
    private $table = 'contract_visitors';

    /**
     * Первичный ключ таблицы с посетителями
     * @var string
     */
    private $table_id = 'contract_visitor_id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Возвращает ID посетителя по его имени
     *
     * @param $name Имя посетителя
     * 
     * @return object Объект-id документа
     */
    public function get_contract_visitor($name)
    {
        return $this->db
                  ->get_where($this->table, array('name' => $name))
                  ->row();
    }

    /**
     * Добавить посетителя в БД
     *
     * @param $data данные посетителя
     * 
     * @return int id добавленной записи в таблице
     */
    public function add($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
    
    /**
     * Редактирование данных посетителя
     * 
     * @param int $contract_visitor_id id посетителя
     * @param array $data данные для обновления
     */
    public function edit($contract_visitor_id, $data)
    {
        $this->db->update($this->table, 
                        $data, 
                        array($this->table_id => $contract_visitor_id));
        
        return TRUE;
    }
}