<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Auth
 *
 * Библиотека авторизации
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Auth_lib
{
    /**
     * Конструктор класса
     */
    public function __construct()
    {
//show_error('Обновления на сервере, зайдите через пару минут! Извините за неудобства');
        // Суперобъект CodeIgniter
        $CI = &get_instance();

        // Грузим модель авторизации
        $CI->load->model('Auth_model');
    }

    /**
     * Метод, осуществляющий авторизацию пользователя
     *
     * @param $login логин пользователя
     * @param $password пароль пользователя
     * @param $md5_pass
     *
     * @return bool результат авторизации
     */
    public function login($login, $password, $md5_pass = FALSE)
    {      
        $this->logout();
        
        // Суперобъект CodeIgniter
        $CI = &get_instance();
                                
        // Данные пользователя с логином $login
        $CI->load->model('Users_model');
        $user = $CI->Users_model->get_user_by_login($login);
        
        // Если пользователя с логином $login не существует
        if (!empty($user) && !$user->deleted) {
            // Зашифрован ли пароль?
            if ($md5_pass === FALSE)
                $password = md5($password);

            // Проверяем правильно ли введён пароль
            if ($user->password === $password) {
                // Записываем данные о входе в сессию и в куки
                $this->save_auth_data($user);
                return TRUE;
            } else
                return FALSE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Сохранение данных о пользователе в сессию и в куки
     * 
     * @param Object $user объект-пользователь
     */
    private function save_auth_data($user)
    {
        // Суперобъект CodeIgniter
        $CI = &get_instance();
        
        $data = array(
                'login'  => $user->login,
                'logged_in' => TRUE,
                'role_id' => $user->role_id,
            );
        
        $CI->session->set_userdata($data);
        // Для запоминания авторизации на месяц
        setcookie('login', $user->login, time() + 3600*24*30, '/');
        setcookie('password', $user->password, time() + 3600*24*30, '/');   
    }
    
    /**
     * Получение роли пользователя
     *
     * @param string $login логин пользователя
     * @return string Роль пользователя 
     */
    public function get_user_role_id($login)
    {
        // Суперобъект CodeIgniter
        $CI = &get_instance();                                
        $CI->load->model('Users_model');
        
        $role_id = $CI->Users_model->get_user_role_id($login);       
        
        return $role_id;
    }
    
    /**
     * Получение id роли пользователя из сессии
     *
     * @param string $login логин пользователя
     * @return int Роль пользователя 
     */
    public function get_user_role_id_from_sess()
    {
        // Суперобъект CodeIgniter
        $CI = &get_instance();    
        
        $role_id = $CI->session->userdata('role_id');       
        
        return (int) $role_id;
    }

    /**
     * Выход пользователя
     */
    public function logout()
    {
        // Суперобъект CodeIgniter
        $CI = &get_instance();

        // Стереть ключи из сессийных данных
        $CI->session->unset_userdata('login');
        $CI->session->unset_userdata('role_id');   
        $CI->session->unset_userdata('logged_in');  
        $CI->session->unset_userdata('applicant_id');
        $CI->session->unset_userdata('filter_name');
        $CI->session->unset_userdata('request_num_filter');
        $CI->session->unset_userdata('order_by');
        $CI->session->unset_userdata('method');
        $CI->session->unset_userdata('contract_order_by');
        $CI->session->unset_userdata('contract_method');
        $CI->session->unset_userdata('filter_forget_passes');
        setcookie('login','', time() - 3600, '/');
        setcookie('password', '', time() - 3600, '/');
    }

    /**
     * Метод проверки авторизации пользователя
     *
     * @return bool залогинен ли пользователь
     */
    public function is_user_logged()
    {
        // Суперобъект CodeIgniter
        $CI = &get_instance();
        
        // Если ключ logged_in в сессии равен TRUE - пользователь залогинен
        $logged_in = $CI->session->userdata('logged_in');
        
        // Если не залогинен
        if ($logged_in === TRUE)
            return TRUE;
        else
        {
            // Пытаемся залогинится, используя данные из $_COOKIE
            if (isset($_COOKIE['login']) and isset($_COOKIE['password']))
            {
                $login_result = $this->login($_COOKIE['login'], $_COOKIE['password'], TRUE);
                
                return $login_result;
            }
            return FALSE;
        }
    }

    /**
     * Проверка правильности ввода своего пароля
     *
     * @param type $password вводимый пароль
     *
     * @return bool результат сравнения
     */
    public function check_pass($password)
    {
        $pass_from_cookie = $_COOKIE['password'];

        if (md5($password) === $pass_from_cookie)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Проверка правильности своего пароля из БД
     *
     * @param type $password вводимый пароль
     *
     * @return bool результат сравнения
     */
    public function check_pass_db($password)
    {
        // Суперобъект CodeIgniter
        $CI = &get_instance();
        $CI->load->model('Users_model');
        $login = $CI->session->userdata('login');
        $user = $CI->Users_model->get_user_by_login($login);

        return md5($password) == $user->password;
    }
    
    /**
     * Получение id пользователя
     * 
     * @param string $login Логин пользователя 
     * 
     * @return int id пользователя или 0
     */
    public function get_user_id_by_login($login)
    {
        // Суперобъект CodeIgniter
        $CI = &get_instance();
                                
        // Данные пользователя с логином $login
        $CI->load->model('Users_model');
        
        $res = $CI->Users_model
                      ->get_user_by_login($login);
        
        if (!empty($res))
            return (int) $res->user_id;
        else
            return 0;
    }
    
    /**
     * Обновление даты последнего посещения
     * 
     * @param string $login логин пользователя 
     */
    public function change_last_visit_date($login)
    {
        // Суперобъект CodeIgniter
        $CI = &get_instance();
        
        $id = $this->get_user_id_by_login($login);
        
        $CI->Auth_model->update_last_visit($id);
    }
    
    /**
     * Произведина ли аутентификация впервые?
     *
     * @param string $login логин пользователя 
     * 
     * @return boolean
     */
    public function is_first_time($login)
    {
        // Суперобъект CodeIgniter
        $CI = &get_instance();
        
        $id = $this->get_user_id_by_login($login);
        
        $date = $CI->Auth_model->get_last_visit($id);
        
        if ($date !== '0000-00-00 00:00:00')
            return FALSE;
        else
            return TRUE;
    }
}
