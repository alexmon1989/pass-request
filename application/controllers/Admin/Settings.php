<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Settings
 *
 * Класс (контроллер),
 * который отвечает за отображение страницы управления настройками
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Settings extends CI_Controller
{
    /**
     * ID роли
     * @var int 
     */
    private $role_id;
    
    private $admin_name;


    /**
     * Конструктор класса
     */
    public function __construct()
    {
        parent::__construct();
        // Библиотека авторизации
        $this->load->library('Auth_lib');

       // Авторизирован ли пользователь
        if ($this->auth_lib->is_user_logged() === FALSE)
            redirect('auth/login');
        
        // Проверка роли авторизированного пользователя для ограничения доступа
        $this->role_id = $this->auth_lib->get_user_role_id_from_sess();
        if (!in_array($this->role_id, array(1,2,3)))
            show_error ('Доступ заборонено!');
        
        // Библиотека вывода
        $this->load->library('Layout');
        
        // Модель для работы с админами
        $this->load->model('administrators_model');
        $this->admin_name = $this->administrators_model
                              ->get_name_by_login($this->session
                                                    ->userdata('login'));
        
        $this->load->model('settings_model');

        // Стили
        $this->layout->add_styles('bootstrap.css');
        $this->layout->add_styles('body.css');
        $this->layout->add_styles('bootstrap-responsive.css');
        $this->layout->add_styles('jquery-ui-1.8.21.custom.css');
        $this->layout->add_styles('my_style.css');

        // JS
        $this->layout->add_scripts('jquery.js');
        $this->layout->add_scripts('bootstrap-collapse.js');
        $this->layout->add_scripts('bootstrap-alert.js');
        $this->layout->add_scripts('bootstrap-dropdown.js');
    }
    
    public function index()
    {
        // Получаем текущие настройки программы
        $data = $this->settings_model->get();
        $this->layout->add_content(array('settings' => $data));
        
        $this->layout->set_page_title('Налаштування');
        $this->layout->view_admin('Settings/index');
    }
    
    public function save()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні!');
                
        // Загрузка библиотеки валидации
        $this->load->library('form_validation');
        
        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'ip_cam',
                'label'   => 'IP камери',
                'rules'   => 'required|valid_ip'
            ),
            array(
                'field'   => 'ip_proxy',
                'label'   => 'IP проксі-сервера',
                'rules'   => 'required'
            ),
        );
        
        // Применяем правила валидации
        $this->form_validation->set_rules($config);
        
         // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Сохранение настроек
            $data['ip_cam'] = $this->input->post('ip_cam');
            $data['ip_proxy'] = $this->input->post('ip_proxy');
            $this->settings_model->save($data);
            $this->session->set_flashdata(array('message' => 'Дані успішно збережено!'));
            
            redirect('admin/settings');
        }
        else
        {
            // Выдача ошибки
            $this->layout->add_content(array('error' => $this->set_error(validation_errors())));
            $this->index();            
        }
    }
    
    /**
     * Отформатированный текст ошибки
     *
     * @param $error текст ошибки
     * @return string HTML-код ошибки
     */
    private function set_error($error)
    {
        return  '<div class="control-group" id="errors">
                        <div class="span alert alert-danger" style="margin-left: 0">
                            <a class="close" data-dismiss="alert" onclick="$(\'#errors\').hide();">×</a>' .
                             $error .
                    '</div>
                   </div>';
    }
}