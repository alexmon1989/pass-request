<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Applicants_model
 *
 * Модель для работы со списком заявителей
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Applicants_model extends CI_Model
{
    /**
     * Таблица БД со списком администраторов
     * @var string
     */
    private $table = 'users';

    /**
     * Первичный ключ таблицы со списком администраторов
     * @var string
     */
    private $table_id = 'user_id';
    
    /**
     * id роли
     * @var int
     */
    private $role_id = 4;

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Получение ID заявителя по его имени
     *
     * @param $name Имя заявителя
     * @return mixed Объект-ID заявителя
     */
    public function get_id_by_name($name)
    {
        return $this->db->select($this->table_id . ' AS applicant_id')
            ->get_where($this->table, array('name' => $name))
            ->row();
    }

    /**
     * Получение логина заявителя по его ID
     *
     * @param $applicant ID заявителя
     * @return mixed Объект-логин заявителя
     */
    public function get_login_by_id($applicant)
    {
        return $this->db->select('login')
            ->get_where($this->table, array($this->table_id => $applicant))
            ->row();
    }
    
    /**
     * Получить список заявителей
     *
     * @return array массив объектов-заявителей
     */
    public function get_applicants()
    {
        return $this->db->select("$this->table_id AS applicant_id, name")
                      ->where('deleted', 0)
                      ->where('role_id', $this->role_id)
                      ->or_where('role_id', 5)
                      ->order_by('name', 'ASC')
                      ->get($this->table)
                      ->result();
    }

    /**
     * Обновлние данных заявителя
     *
     * @param $applicant_id id заявителя
     * @param $data данные для заявителя
     */
    public function edit_applicant($applicant_id, $data)
    {
        $this->db->where($this->table_id, $applicant_id)
                ->update($this->table, $data);
    }

    /**
     * Добавление нового заявителя
     *
     * @param $data данные заявителя
     *
     * @return int id добавленного заявителя
     */
    public function add_applicant($data)
    {
        //$data['role_id'] = $this->role_id;
        // Ищем уже удаленного заявителя
        $applicant = $this->get_id_by_name($data['name']);

        if (empty($applicant) === TRUE) // Не найден, добавляем нового
        {            
            $this->db->insert($this->table, $data);

            return $this->db->insert_id();
        }
        else
        {
            // ID старой записи
            $applicant_id = $applicant->applicant_id;

            $data['deleted'] = 0;

            // Обновляем старые данные
            $this->db->where($this->table_id, $applicant_id);
            $this->db->update($this->table, $data);
            return $applicant_id;
        }
    }

    /**
     * Удаление заявителя (на самом деле установка 1 в поле deleted)
     *
     * @param $applicant_id id заявителя
     */
    public function delete($applicant_id)
    {
        $this->db->where($this->table_id, $applicant_id);
        $this->db->update($this->table, array('deleted' => 1, 'login' => ''));
    }
    
    /**
     * Данные заявителя
     *
     * @param int $applicant_id ID заявителя
     * @return object 
     */
    public function get($applicant_id)
    {
        return $this->db
                   ->get_where($this->table, array($this->table_id => $applicant_id))
                   ->row();
    }
}