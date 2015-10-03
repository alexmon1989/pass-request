<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Menu_top_model
*
* Модель для работы с верхним меню
*
* @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
* @version 1.0
*/
class Menu_top_model extends CI_Model
{
    /**
    * Таблица БД с верхним меню (пользовательская часть)
    * @var string
    */
    private $table_menu_users_top = 'menu_top_users';

    /**
    * Первичный ключ таблиц с верхним меню
    * @var string
    */
    private $table_id = 'menu_top_id';

    /**
     * Таблица БД с верхним меню (пользовательская часть)
     * @var string
     */
    private $table_menu_security_top = 'menu_top_security';


    /**
     *  Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Выборка меню из БД
     *
     * @return array список объектов верхних меню
     */
    public function get_top_menus($type = 'user')
    {
        if ($type === 'user')
            $table = $this->table_menu_users_top;
        else
            $table = $this->table_menu_security_top;

        return $this->db->order_by('position', 'ASC')
                        ->get($table)
                        ->result();
    }

    /**
     * Получить список верхнего меню для админ. панели
     *
     * @param int $parent_id id отцовского меню
     *
     * @return array Массив объектов-результатов запроса
     */
    public function get_menu_admin($parent_id = 0)
    {       
        return $this->db->where('parent_id', $parent_id)
                      ->order_by('position', 'ASC')
                      ->get($this->table_menu_security_top)
                      ->result();
    }
    
    /**
     * Получить список верхнего меню для админ. панели
     *
     * @return array Массив объектов-результатов запроса
     */
    public function get_menu_security($parent_id = 0)
    {       
        // ID роли пользователя (2 - роль охранника)
        $role_id = 2;
        
        return $this->db->where('parent_id', $parent_id)
                      ->where('role_id', $role_id)
                      ->order_by('position', 'ASC')
                      ->get($this->table_menu_security_top)
                      ->result();
    }
}