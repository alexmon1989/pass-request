<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Forget_passes
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления выдачей 
 * пропусков сотрудникам, которые свои забыли дома
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Forget_passes extends CI_Controller
{
    /**
     * ID роли
     * @var int 
     */
    private $role_id;
    
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

        // Модель для работы с заявками
        $this->load->model('forget_passes_model');

        // Стили
       $css_array = array('bootstrap.css', 
                         'body.css', 
                         'bootstrap-responsive.css', 
                         'jquery-ui-1.8.21.custom.css'
                        );
        $this->layout->add_styles($css_array);

        // JS
        $js_array = array('jquery.js', 
                        'jquery_cookie.js',
                        'jquery-ui-1.8.21.min.js',
                        'bootstrap-collapse.js',
                        'bootstrap-dropdown.js',
                        'bootstrap-alert.js',
                        'bootstrap-modal.js',
                        'bootstrap-transition.js',
                        'passes.js',
                        'forget_passes.js');
        $this->layout->add_scripts($js_array);    

        //$this->output->enable_profiler(TRUE);
    }
    
    /**
     * Отображение страницы с заявками
     *
     * @param int $page_num номер страницы
     */
    public function index($page_num = 1)
    {
        // Защита от дурака
        $page_num = (int) $page_num;
        if ($page_num === 0)
            show_error('Такої сторінки не існує!');

        // С какой строки в таблице БД брать данные
        if ($page_num === 1)
            $offset = 0;
        else
            $offset = ($page_num)*15 - 15;
        
        // Фильтр
        $filter = $this->session->userdata('filter_forget_passes');
        if ($filter !== FALSE)
            $forget_passes = $this->forget_passes_model->get_passes($offset, 15, $filter);  
        else        
            // Массив пропусков
            $forget_passes = $this->forget_passes_model->get_passes($offset, 15);
        
        // Изменение формата даты
        if (empty($forget_passes) === FALSE)
        {
            foreach ($forget_passes as $key => $forget_pass)
            {
                // Формат даты
                $issue_date = date_create($forget_pass->issue_date);
                $forget_passes[$key]->issue_date = date_format($issue_date, 'd.m.Y H:i:s');
                
                if ($forget_pass->pass_date !== NULL)
                {
                    $pass_date = date_create($forget_pass->pass_date);
                    $forget_passes[$key]->pass_date = date_format($pass_date, 'd.m.Y H:i:s');
                }
            }
        }
        
        // Всего пропусков
        $forget_passes_count = $this->forget_passes_model->get_passes_count($filter);
        
         if (($page_num * 15 > $forget_passes_count + 15) and ($page_num !== 1))
            show_error('Такої сторінки не існує!');

        // Разбивание на страницы
        $this->load->library('pagination');
        $config_pagination['base_url'] = base_url('admin/forget_passes/');
        $config_pagination['uri_segment'] = 3;
        $config_pagination['total_rows'] = $forget_passes_count;
        $config_pagination['per_page'] = 15;
        $config_pagination['use_page_numbers'] = TRUE;
        $config_pagination['first_link'] = '<< Перша';
        $config_pagination['last_link'] = 'Остання >>';
        $this->pagination->initialize($config_pagination);
        $pages = $this->pagination->create_links();

        // Список сотрудников
        $this->load->model('employees_model');
        $employees = $this->employees_model->get_all();
        
        $this->layout->add_content(array('forget_passes' => $forget_passes,
                                     'page_num' => $page_num - 1,
                                     'pages' => $pages,
                                     'forget_passes_count' => $forget_passes_count,
                                     'employees' => $employees, 
                                     'filter' => (int)$filter,
                                     'role_id' => $this->role_id,   
                                    ));        
        
        // Название страницы
        $this->layout->set_page_title('Забуті пропуска');
         
        $this->layout->add_scripts('jquery.combo.autocomplete.js');
        
        $this->layout->view_admin('Forget_passes/index');
    }
    
    public function set_filter()
    {
        $filter = $this->input->get('filter');
        
        $this->session->set_userdata(array('filter_forget_passes' => $filter));
    }
    
    /**
     * Обработчик АЯКС-запроса на сдачу пропуска
     */
    public function take_pass()
    {
        if (empty($_POST) === FALSE)
        {
            $forget_pass_id = (int) $this->input->post('forget_pass_id');            
            $password = trim($this->input->post('password'));
            
            if ($forget_pass_id === 0)
            {
                echo 'Невірний ID забутого пропуска';
                exit();
            }
            
            if ($password === '')
            {
                echo 'Пароль не може бути пустим!';
                exit();
            }
            
            if ($this->auth_lib->check_pass($password) === FALSE)
            {
                echo 'Невірний пароль!';
                exit();
            }
            
            // Проверка, существует ли данный забытый пропуск
            $forget_pass = $this->forget_passes_model->get_forget_pass($forget_pass_id);
            if (empty($forget_pass) === TRUE)
            {
                echo 'Данного забутого пропуску не існує!';
                exit();
            }
            
            // Проверка, сдан ли уже пропуск
            if ($forget_pass->pass_date !== NULL) // Признак того, что пропуск был сдан
            {
                echo 'Цей пропуск вже було здано! Можливо це було зроблено іншою особою.';
                exit();
            }
            
            // Сдать пропуск
            $data = array('pass_administrator_id' => (int) $this->auth_lib
                                                           ->get_user_id_by_login($this->session
                                                                                    ->userdata('login')),
                         'pass_date' => date('Y-m-d H:i:s'));
            
            $this->forget_passes_model->edit($forget_pass_id, $data);
            
            $this->load->model('passes_model');
            // Отметить пропуск как не выданный
            $this->passes_model->change_status($forget_pass->pass_id, 1);
                
            $this->session->set_flashdata(array('message' => 'Пропуск успішно здано.'));
        }
    }
    
    /**
     * Добавление пропуска 
     */
    public function add_pass()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні!');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'employee_name',
                'label'   => 'ПІБ співробітника',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'pass_number',
                'label'   => 'Номер пропуска',
                'rules'   => 'required|integer'
            ),
            array(
                'field'   => 'form_pass_password',
                'label'   => 'Ваш пароль',
                'rules'   => 'required|valid_admin_password'
            ),
            array(
                'field'   => 'pass_number',
                'label'   => '№ пропуска',
                'rules'   => 'required|integer|free_pass'
                ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Имя сотрудника
            $employee_name = $this->input->post('employee_name');
            
            // Номер пропуска
            $pass_id = $this->input->post('pass_number');
            
            // ID сотрудника
            $this->load->model('employees_model');
            $employee_id = $this->employees_model->get_id_by_name($employee_name);
                if (empty($employee_id) === TRUE)
                    $employee_id = $this->employees_model->add($employee_name);
                else
                    $employee_id = (int)$employee_id->employee_id;
                         
            // Число заявок за месяц
            $count_forget_passes_month = $this->forget_passes_model
                                          ->get_count_forget_passes_month();
            
            // Добавляем забытый пропуск в БД
            $data = array('employee_id' => $employee_id,
                         'forget_pass_number' => date('my') . '-' . ($count_forget_passes_month + 1),
                         'issue_date' => date('Y-m-d H:i:s'),
                         'administrator_id' => (int) $this->auth_lib
                                                       ->get_user_id_by_login($this->session
                                                                                ->userdata('login')),
                         'pass_id' => $pass_id);
            $this->forget_passes_model->add($data);
            
            // Помечаем пропуск как выданный
            $this->load->model('passes_model');
            $this->passes_model->change_status($pass_id, 2);
            
            // Выдача сообщения об успехе
            $message = 'Було створено забутий пропуск!';

            // Кудабудем переадресовывать после оповещения
            $location = base_url('admin/forget_passes');

            $this->session->set_flashdata(array('message' => $message));

            echo "<script>location = \"{$location}\";</script>";
        }
        else
            // Выдача ошибки
            echo $this->set_error(validation_errors());
    }
    
    /**
     * Обработчик АЯКС-запроса, которій поступает при утере пропуска 
     */
    public function lost_pass()
    {
        if (empty($_POST) === FALSE)
        {
            $forget_pass_id = (int)$this->input->post('forget_pass_id');
            $password = trim($this->input->post('password'));
            
            if ($forget_pass_id === 0)
            {
                echo 'Невірний ID забутого пропуска';
                exit();
            }
            
            if ($password === '')
            {
                echo 'Пароль не може бути пустим!';
                exit();
            }
            
            if ($this->auth_lib->check_pass($password) === FALSE)
            {
                echo 'Невірний пароль!';
                exit();
            }
            
            // Проверка, существует ли данный забытый пропуск
            $forget_pass = $this->forget_passes_model->get_forget_pass($forget_pass_id);
            if (empty($forget_pass) === TRUE)
            {
                echo 'Данного забутого пропуску не існує!';
                exit();
            }
            
            // Закрыть заявку
            $this->forget_passes_model->take_pass($forget_pass_id);
            
            $this->load->model('passes_model');
            // Отметить новый пропуск как выданный
            
            // Отметить старый пропуск как утерянный
            $this->passes_model->change_status($forget_pass->pass_id, 3);
            
            $message = 'Заявку було закрито, перепустку було відмічено як загублену!';            
            $this->session->set_flashdata(array('message' => $message));
        }
    }
    
    /**
     * Действие для поиска сотрудников по части имени
     * Обработчик AJAX-запроса 
     */
    public function search_visitor()
    {
        // Часть фамилии
        $term = trim(strip_tags($this->input->get('term')));
        
        $this->load->model('employees_model');
        $visitors = $this->employees_model
                       ->get_name_by_term($term);
        
        $row_set = array();
        
        foreach ($visitors as $visitor)
        {
            $row['value']=$visitor->name; 
            $row_set[] = $row;
        }
       
        echo json_encode($row_set);//format the array into json data
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