<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Documents_type_model
 *
 * Модель для работы со списком документов
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Documents_type_model extends CI_Model
{
    /**
     * Таблица БД с типами документов
     * @var string
     */
    private $table = 'document_types';

    /**
     * Первичный ключ таблицы с типами документов
     * @var string
     */
    private $table_id = 'document_type_id';

    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Выборка типов документов из БД
     * 
     * @return array массив объектов-результатов запроса 
     */
    public function get_document_types()
    {
        return $this->db->get($this->table)
                      ->result();  
    }
    
    /**
     * Получение типа документа по его ID
     * 
     * @param int $document_type_id ID типа документа
     * 
     * @return array массив объектов-результатов запроса 
     */
    public function get_document_type($document_type_id)
    {
        return $this->db->get_where($this->table, 
                               array($this->table_id => $document_type_id))
                      ->row();        
    }
    
    /**
     * Удаление типа документа
     *
     * @param type $document_type_id ID типа документа
     * 
     * @return bool результат удаления 
     * (FALSE, если какому-то док-ту соответствует этот тип) 
     */
    public function delete($document_type_id)
    {
        $this->load->model('documents_model');
        
        // Список документов в БД
        $documents = $this->documents_model->get_documents();
        
        // Имеет ли хоть один док. удаляемый тип
        foreach ($documents as $document)
            if ((int)$document->document_type_id === $document_type_id)
                return FALSE;
            
        $this->db->delete($this->table, array($this->table_id => $document_type_id));
        return TRUE;
    }
    
    /**
     * Добавление нового типа документа
     *
     * @param type $document_type тип документа
     * 
     * @return int id добавленной записи 
     */
    public function add($document_type)
    {
        $this->db->insert($this->table, array('type' => $document_type));
        
        return $this->db->insert_id();
    }
    
    /**
     * Редактирование типа документа
     *
     * @param type $document_type_id ID типа документа, который редактируется
     * @param type $new_document_type новое название типа документа
     */
    public function edit($document_type_id, $new_document_type)
    {
        $this->db->update($this->table, 
                        array('type' => $new_document_type), 
                        array($this->table_id => $document_type_id));
    }
}