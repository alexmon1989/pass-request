<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Documents_model
 *
 * Модель для работы со списком документов
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Documents_model extends CI_Model
{
    /**
     * Таблица БД с документами
     * @var string
     */
    private $table_documents = 'documents';

    /**
     * Первичный ключ таблицы с документами
     * @var string
     */
    private $table_id = 'document_id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Возвращает данные документа по его номеру
     *
     * @param $value Значение (номер, серия) документа
     * @return object Объект-id документа
     */
    public function get_document_by_val($value)
    {
        return $this->db->get_where($this->table_documents, array('value' => $value))
                      ->result();
    }

    /**
     * Добавить документ в БД
     *
     * @param $value номер и серия документа
     * @return int id последней записи в таблице
     */
    public function add_document($data)
    {
        $this->db->insert($this->table_documents, $data);
        return $this->db->insert_id();
    }

    /**
     * Получить данные документа по его ID
     *
     * @param $document_id id документа
     * @return object результат запроса
     */
    public function get_document_by_id($document_id)
    {
        return $this->db->where($this->table_id, $document_id)
                      ->get($this->table_documents)
                      ->row();

    }

    /**
     * Редкатирование номера документа
     *
     * @param $document_id ID документа
     * @param $number новое имя посетителя
     */
    public function edit_document_number($document_id, $number)
    {
        $this->db->update($this->table_documents, 
                        array('value' => $number), 
                        array($this->table_id => $document_id));
    }
    
    /**
     * Изменить тип документа
     *
     * @param int $document_id id документа
     * @param int $document_type_id  id типа документа
     */
    public function edit_document_type($document_id, $document_type_id)
    {
        $this->db->update($this->table_documents, 
                        array('document_type_id' => $document_type_id), 
                        array($this->table_id => $document_id));
    }
    
    /**
     * Получение всех документов из БД
     * 
     * @return array массив объектов-результатов запроса 
     */
    public function get_documents()
    {
        return $this->db->get($this->table_documents)
                      ->result();
        
    }
    
    /**
     * Редактирование записи в таблице
     * 
     * @param int $documnet_id id записи
     * @param array $data ассоциативный массив для обновления
     */
    public function edit($documnet_id, $data)
    {
        $this->db->update($this->table_documents, 
                        $data, 
                        array($this->table_id => $documnet_id));
    }
}