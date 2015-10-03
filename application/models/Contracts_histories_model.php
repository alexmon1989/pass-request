<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Contracts_histories_model
 *
 * Модель для работы с таблицей Contracts_histories 
 * (там хранятся логи действий по контрактам)
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Contracts_histories_model extends CI_Model
{
    /**
     * Таблица БД со списком начальников
     * @var string
     */
    protected $table = 'contracts_histories';

    /**
     * Первичный ключ таблицы со списком начальников
     * @var string
     */
    protected $table_id = 'contract_history_id';
        
    /**
     * Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    /**
     * Получение данных одной записи
     * 
     * @param int $history_id ID записи
     * @return object Объект-запись 
     */
    public function get($history_id)
    {
        return $this->db->get_where($this->table, 
                                 array($this->table_id => $history_id))
                      ->row();        
    }
    
    /**
     * Добавить сообщение в конец
     * 
     * @param int $history_id ID записи
     * @param string $message Сообщение
     * @return boolean Результат выполнения операции по добавлению в конец
     * @throws Exception 
     */
    public function add_to_end($history_id, $message)
    {
        $contract_history = $this->get($history_id);
        if (empty($contract_history) === FALSE)
        {
            $data = array('history' => trim($contract_history->history . PHP_EOL . $message));
            $this->db->update($this->table, 
                            $data, 
                            array($this->table_id => $history_id));
            return TRUE;
        }
        else
        {
            throw new Exception('Запись в БД отсутствует!');
            return FALSE;
        }
    }
    
    /**
     * Создаёт запись в таблице
     * 
     * @return int ID добавленной записи
     */
    public function create()
    {
        $this->db->insert($this->table, array('history' => ''));
        return (int) $this->db->insert_id();
    }
}