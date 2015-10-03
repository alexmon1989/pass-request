<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Auth_model
*
* Модель для работы с авторизацией
*
* @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
* @version 1.0
*/
class Auth_model extends CI_Model
{
    /**
    * Таблица БД с пользователями
    * @var string
    */
    private $table_users = 'users';    

    /**
    * Первичный ключ таблицы БД с пользователями
    * @var string
    */
    private $table_users_id = 'user_id';

    /**
     *  Конструктор класса
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Проверка успешности авторизации
     *
     * @param string $login логин пользователя
     * @param string $password пароль пользователя
     * @param string $type тип пользователя (user или security)
     *
     * @throws Exception
     * @return boolean успешность авторизации
     */
    public function check_auth($login, $password)
    {        
        // Выборка пароля пальзователя
        $result = $this->db->select('password')
                         ->get_where($this->table, array('login' => $login))
                         ->row();
        
               // var_dump($result->password); var_dump($password); exit();

        // Проверяем совпадает ли логин с паролем
        if (!is_object($result))
            return FALSE;
        elseif($result->password === $password)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * @param $user_id ID пользователя или охранника
     * @param $new_password новый пароль
     * @throws Exception
     */
    public function change_password($user_id, $new_password)
    {
        // Смена пароля
        $this->db->update($this->table_users, 
                        array('password' => $new_password), 
                        array($this->table_users_id => $user_id));
    }

    /**
     * @param $user_id ID пользователя или охранника
     * @param $old_password старый пароль
     * @return bool результат правильности ввода старого пароля
     * @throws Exception
     */
    public function check_old_password($user_id, $old_password)
    {
        // Запрос в БД
        $result =  $this->db->select($this->table_users_id)
                          ->get_where($this->table_users, 
                                     array($this->table_users_id => $user_id, 
                                     'password' => $old_password))
                          ->row();
        
        // Если результат запроса не пустой, то пароль правильныый
        if (empty($result) === TRUE)
            return FALSE;
        else
            return TRUE;
    }
    
    /**
     * Вернуть тип пользователя 
     * 
     * @param string $login Логин пользователя
     * @return mixed Тип пользователя (administrator, user, security) или FALSE 
     */
    public function get_user_type($login)
    {
        $is_admin = $this->db->get_where(
                                $this->table_administrators, 
                                array('login' => $login)
                            )
                           ->row();
        
        if (empty($is_admin) === FALSE)
            return 'administrator';
        
        $is_security = $this->db->get_where(
                                $this->table_security, 
                                array('login' => $login)
                            )
                           ->row();
        
        if (empty($is_security) === FALSE)
            return 'security';
        
        $is_user = $this->db->get_where(
                                $this->table_users, 
                                array('login' => $login)
                            )
                           ->row();
        
        if (empty($is_user) === FALSE)
            return 'user';
        
        return FALSE;
    }
    
    /**
     * Обновление даты последнего посещения
     * 
     * @param int $user_id id пользователя 
     */
    public function update_last_visit($user_id)
    {
        $this->db->update($this->table_users, 
                        array('last_visit' => date('Y-m-d H:i:s')), 
                        array($this->table_users_id => $user_id));
    }
    
    /**
     * Получение даты последнего посещения
     *
     * @param int $user_id id пользователя 
     * 
     * @return string дата последнего посещения 
     */
    public function get_last_visit($user_id)
    {
        return $this->db->select('last_visit')
                      ->get_where($this->table_users, array($this->table_users_id => $user_id))
                      ->row()
                      ->last_visit;    
    }
}