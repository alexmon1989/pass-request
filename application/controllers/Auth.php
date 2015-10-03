<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Auth
*
* Контроллер CodeIgniter, который реализует автоизацию на сайте
*
* @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
* @version 1.0
*/
class Auth extends CI_Controller {

    /**
    *
    * Конструктор класса
    *
    */
    function __construct()
    {
        parent::__construct();

        // Библиотеки
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->library('Layout');
        $this->load->library('Auth_lib');

        // Стили
        $this->layout->add_styles('bootstrap.css');
        $this->layout->add_styles('body.css');
        $this->layout->add_styles('bootstrap-responsive.css');

        // JS
        $this->layout->add_scripts('jquery.js');
        $this->layout->add_scripts('bootstrap-alert.js');
        $this->layout->add_scripts('bootstrap-dropdown.js');
    }

    public function index()
    {
        redirect('auth/login');
    }

    /**
    * Метод, отвечающий за авторизацию.
    * Переадресовывает, если авторизация пройдена администратором
    */
    public function login()
    {
        // Правила валидации
        $this->form_validation->set_rules('username', 'Логин пользователя', 'required');
        $this->form_validation->set_rules('password', 'Пароль', 'required');

        if ($this->form_validation->run())
        { 
            // Если валидация прошла успешно

            // Получаем логин, пароль и приводим их к нижнему регистру
            $login = mb_strtolower($this->input->post('username'));
            $password = mb_strtolower($this->input->post('password'));
            
            // Залогинился ли пользователь
            if ($this->auth_lib->login($login, $password) === TRUE)
            {
                // Узнаём id роли пользователя
                $user_role_id = $this->auth_lib->get_user_role_id($login);
                                
                // В зависимости от типа пользователя грузим разные страницы
                if ($user_role_id === 4 or $user_role_id === 5)
                {
                    // Переадресация на стартовую страницу
                    redirect('requests');
                }
                else
                    // В случае авторизации охранника, администратора или начальника
                    redirect('admin');
            }            
            else
            {
                $this->layout->add_content(array('error' =>
                       '<div class="span alert alert-danger">
                            <a class="close" data-dismiss="alert">×</a>' .
                         'Неправильний логін або пароль!' .
                       '</div>'));
                $this->layout->set_page_title('Авторизація');
                $this->layout->add_styles('login_form.css');
                $this->layout->view_login();
            }
        }
        else
        {
            $err_data = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
                if ($err_data !== FALSE)
                    $this->layout->add_content(array('error' =>
                       '<div class="span alert alert-danger">
                               <a class="close" data-dismiss="alert">×</a>' .
                            $err_data .
                       '</div>'));
            $this->layout->set_page_title('Авторизація');
            $this->layout->add_styles('login_form.css');
            $this->layout->add_scripts('jquery.placeholder.min.js');            
            
            $this->layout->view_login();
        }
    }

    /**
    * Метод, отвечающий за логаут пользователя.
    */
    public function logout()
    {
        $this->auth_lib->logout();
        redirect('auth/login');
    }
    
    public function change_pass()
    {
        $login = $this->session->userdata('login');
        
        // Делаем страницу недоступной для тех, кто уже был в системе
        if (!$this->auth_lib->is_first_time($login))
            show_404();
        
        // Если POST-запрос
        if ($this->input->post() !== FALSE)
        {
            // Загрузка библиотеки валидации
            $this->load->library('form_validation');
            
            // Массив правил валидации
            $config = array(
                array(
                    'field'   => 'password',
                    'label'   => 'Новий пароль',
                    'rules'   => 'required|min_length[5]|trim'
                    ),                
                array(
                    'field'   => 'confirm_password',
                    'label'   => 'Підтвердження паролю',
                    'rules'   => 'required|matches[password]|trim'
                    ),
                );
            
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() === TRUE)
            {
                // Меняем пароль пользователя
                $user_id = $this->auth_lib->get_user_id_by_login($login);
                $this->load->model('auth_model');
                $this->auth_model->change_password($user_id, md5($this->input->post('password')));
                
                $this->session->set_flashdata(array('message' => 'Ваш пароль було успішно змінено!'));
                
                // Устанавливаем дату последнего посещения
                $this->auth_lib->change_last_visit_date($login);
                
                // Переадресовывем его
                redirect('requests');
            }
            else            
                $this->layout->add_content(array('errors' => validation_errors()));
        }
        $this->layout->set_page_title('Зміна паролю');
        $this->layout->view('change_pass');
    }
}
