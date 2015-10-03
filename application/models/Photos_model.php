<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Photos_model
 *
 * Модель для работы с таблицей фотографий
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Photos_model extends CI_Model
{
    /**
     * Таблица БД
     * @var string
     */
    private $table = 'photos';

    /**
     * Первичный ключ таблицы
     * @var string
     */
    private $table_id = 'photo_id';
    
    /**
     * Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    /**
     * Добавление картинки в БД
     *
     * @param string $image_path Путь к картинке на сервере
     * @return int ID только что добавленной записи 
     */
    public function add($image_path)
    {
        // "Готовим" изображение для вставки в БД
        $image = file_get_contents($image_path);
        
        $this->db
            ->insert($this->table, array('photo' => $image));
        
        return $this->db->insert_id();
    }
    
    /**
     * Извлечение фото из БД
     * 
     * @param int $photo_id ID фото
     * @return blob 
     */    
    public function get($photo_id)
    {
        $res = $this->db
                   ->get_where($this->table, array('photo_id' => $photo_id))
                   ->row();
        
        if (empty($res) === FALSE)
            return $res->photo;
        else
            return array();
    }
    
    /**
     * Обновление фото в БД
     *
     * @param type $photo_id ID обновляемой записи
     * @param type $image_path путь к файлу на серваке
     * 
     * @return boolean 
     */
    public function update($photo_id, $image_path)
    {
        // "Готовим" изображение для вставки в БД
        $image = file_get_contents($image_path);
        
        $this->db
            ->update($this->table, 
                    array('photo' => $image), 
                    array('photo_id' => $photo_id));
        
        return TRUE;
    }
}