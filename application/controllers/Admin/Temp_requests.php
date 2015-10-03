<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Temporary_requests
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления списком временных заявок
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Temp_requests extends CI_Controller
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
        if (!in_array($this->role_id, array(1,3)))
            show_error ('Доступ заборонено!');

        // Библиотека вывода
        $this->load->library('Layout');

        // Модель для работы с временными заявками
        $this->load->model('temp_requests_model');
        
        // Модель для работы с админами
        $this->load->model('administrators_model');
        $this->admin_name = $this->administrators_model
                              ->get_name_by_login($this->session
                                                    ->userdata('login'));
        
        // Стили
        $css_array = array('bootstrap.css', 
                         'body.css', 
                         'bootstrap-responsive.css', 
                         'jquery-ui-1.8.21.custom.css', 
                         'my_style.css');
        $this->layout->add_styles($css_array);
        
        // JS
        $js_array = array('jquery.js', 
                        'jquery_cookie.js',
                        'jquery-ui-1.8.21.min.js',
                        'bootstrap-collapse.js', 
                        'bootstrap-alert.js',
                        'bootstrap-dropdown.js', 
                        'jquery-ui-1.8.21.min.js', 
                        'ui.datepicker.js', 
                        'ui-datepicker-uk.js',
                        'passes.js',
                        'temp_requests.js');
        $this->layout->add_scripts($js_array);

        //$this->output->enable_profiler(TRUE);
    }

    /**
     * Действие для отображения списка временных заявок
     *
     * @param int $page_num
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
        
        $order_by = $this->session->userdata('temp_requests_order_by'); 
        $method = $this->session->userdata('temp_requests_method'); 
        $where = '';

        // Массив заявок
        if ($order_by !== FALSE and $method !== FALSE)
            $temp_requests = $this->temp_requests_model->get_all($offset, 15, 
                                                            $order_by, $method);
        else
            $temp_requests = $this->temp_requests_model->get_all($offset, 15);

        // Изменение формата даты
        if (empty($temp_requests) === FALSE)
        {
            foreach ($temp_requests as $key => $contract)
            {
                // Формат даты
                $date_from = date_create(date('Y-m-d H:i:s', $contract->date_from));
                $temp_requests[$key]->date_from = date_format($date_from, 'd.m.Y');
                $date_to = date_create(date('Y-m-d H:i:s', $contract->date_to));
                $temp_requests[$key]->date_to = date_format($date_to, 'd.m.Y');
                $created_at = date_create(date('Y-m-d H:i:s', $contract->created_at));
                $temp_requests[$key]->created_at = date_format($created_at, 'd.m.Y');
                
                $cur_date = date_create(); 
                if ($cur_date > $date_to)
                    $temp_requests[$key]->actual = -1;
                else
                    $temp_requests[$key]->actual = 1;
                
                // Разница между датами
                $diff = $this->date_diff($date_to, $cur_date);
                
                // Состояние контракта
                if ($diff >= 0 and $diff <= 4)
                    $temp_requests[$key]->actual = 0; // Заканчивается
                elseif ($diff > 3)
                    $temp_requests[$key]->actual = 1; // В процессе
                else
                {
                    $temp_requests[$key]->actual = -1; // Закончились  
                    // Если пропуск не сдан
                    if ((int)$contract->status_id === 2)
                        $temp_requests[$key]->actual = 0; // Заканчивается
                }
                 if ((int)$contract->status_id === 3)
                        $temp_requests[$key]->actual = -1; // Заканчивается
            }
        }

        $this->load->library('pagination');

        // Всего заявок
        $temp_requests_count = $this->temp_requests_model->get_count();

        if (($page_num * 15 > $temp_requests_count + 15) and ($page_num !== 1))
            show_error('Такої сторінки не існує!');

        // Разбивание на страницы
        $config_pagination['base_url'] = base_url('admin/temp_requests/');
        $config_pagination['uri_segment'] = 3;
        $config_pagination['total_rows'] = $temp_requests_count;
        $config_pagination['per_page'] = 15;
        $config_pagination['use_page_numbers'] = TRUE;
        $config_pagination['first_link'] = '<< Перша';
        $config_pagination['last_link'] = 'Остання >>';
        $this->pagination->initialize($config_pagination);
        $pages = $this->pagination->create_links();
                        
        $this->layout->add_content(array('temp_requests' => $temp_requests,
                                     'page_num' => $page_num - 1,
                                     'pages' => $pages,
                                     'role_id' => $this->role_id,
                                     'temp_requests_count' => $temp_requests_count
                                    ));

        $this->layout->set_page_title('Список тимчасових заявок');

        $this->layout->view_admin('Temp_requests/index');
    }    
       
    /**
     * Действие, отображающее страницу добавления контракта
     */
    public function add()
    {   
        if (!empty($_POST))
        {
            // Загрузка библиотеки валидации
            $this->load->library('form_validation');

            // Массив правил валидации
            $config = array(
                array(
                    'field'   => 'last_name',
                    'label'   => 'Прізвище відвідувача',
                    'rules'   => 'required'
                ),
                array(
                    'field'   => 'first_name',
                    'label'   => 'Ім\'я відвідувача',
                    'rules'   => 'required'
                ),
                array(
                    'field'   => 'middle_name',
                    'label'   => 'По-батькові відвідувача',
                    'rules'   => 'required'
                ),
                array(
                    'field'   => 'date_from',
                    'label'   => 'Дата з',
                    'rules'   => 'required|valid_date'
                ),

                array(
                    'field'   => 'date_to',
                    'label'   => 'Дата по',
                    'rules'   => 'required|valid_date'
                ),
                array(
                    'field'   => 'pass_number',
                    'label'   => '№ пропуска',
                    'rules'   => 'required|integer|free_pass'
                    ),
                array(
                    'field'   => 'password',
                    'label'   => 'Ваш пароль',
                    'rules'   => 'required|valid_admin_password'
                ),
            );

            // Применяем правила валидации
            $this->form_validation->set_rules($config);

            // Проверка корректности заполнения формы
            if ($this->form_validation->run() === TRUE)
            {
                $last_name = $this->mb_ucwords(mb_strtolower($this->input->post('last_name')));
                $first_name = $this->mb_ucwords(mb_strtolower($this->input->post('first_name')));
                $middle_name = $this->mb_ucwords(mb_strtolower($this->input->post('middle_name')));
                $pass_id = (int)$this->input->post('pass_number');
                
                // Форматируем дату для запроса в БД
                $date_from = strtotime($this->input->post('date_from') . '00:00:00');
                $date_to = strtotime($this->input->post('date_to') . '23:59:59');
                $created_at = time();
                
                // Число заявок за месяц
                $count_temp_requests_month = $this->temp_requests_model
                                           ->get_count_month();            

                // Данные заявки для добавления в БД
                $data = array(
                             'visitor_name' => $last_name . ' ' . 
                                               $first_name . ' ' . 
                                               $middle_name,
                             'number' => date('my') . '-' . ($count_temp_requests_month + 1),
                             'date_from' => $date_from,
                             'date_to' => $date_to,
                             'pass_id' => $pass_id,
                             'created_at' => $created_at,
                             'status_id' => 2,
                             'history' => date('d.m.Y H:i:s', $created_at) . " - Створено угоду (<b>{$this->admin_name}</b>)".PHP_EOL);

                
                // Добавляем в БД заявку
                $new_temp_request_id = $this->temp_requests_model->add($data);

                // Помечаем пропуск как выданный
                $this->passes_model->change_status($pass_id, 2);
                
                // Выдача сообщения об успехе
                $message = 'Було створено нову тимчасову заявку!';

                $this->session->set_flashdata(array('message' => $message));

                redirect('admin/temp_requests/edit/' . $new_temp_request_id);         
            }
            else
            {
                // Выдача ошибки
                $this->layout->add_content(array('error' => $this->set_error(validation_errors())));
            }  
        }
        
        // Список пропусков
        $this->load->model('passes_model');
        $passes = $this->passes_model->get_free_passes();
                        
        // Передаём переменные в представление
        $this->layout->add_content(array('passes' => $passes));

        $this->layout->add_scripts('jquery.combo.autocomplete.js');
        
        // Титул страницы
        $this->layout->set_page_title('Додання нової тимчасової заявки');

        // Отображаем страницу
        $this->layout->view_admin('Temp_requests/add');
    }
    
    /**
     * Сдать пропуск
     * 
     * @param int $id ID контракта
     */
    public function take_pass($id = 0)
    {
        if (empty($_POST) === FALSE and $this->role_id === 1)
        {
            $id = (int) $this->input->post('id');            
            $password = trim($this->input->post('password'));
            
            if ($id === 0)
            {
                echo $this->set_error('Невірний ID забутого пропуска');
                exit();
            }
            
            if ($password === '')
            {
                echo $this->set_error('Пароль не може бути пустим!');
                exit();
            }
            
            if ($this->auth_lib->check_pass($password) === FALSE)
            {
                echo $this->set_error('Невірний пароль!');
                exit();
            }
            
            // Проверка, существует ли данная заявка
            $temp_request = $this->temp_requests_model->get_data($id);
            if (empty($temp_request) === TRUE)
            {
                echo $this->set_error('Дана заявка не існує!');
                exit();
            }
            
            // Проверка, сдан ли уже пропуск
            if ($temp_request->status_id === 3) // Признак того, что пропуск был сдан
            {
                echo $this->set_error('Цей пропуск вже було здано! Можливо це було зроблено іншою особою.');
                exit();
            }
            
            // Сдать пропуск
            $data = array('status_id' => 3);          
            
            $this->load->model('passes_model');
            // Отметить пропуск как не выданный            
            $this->passes_model->change_status($temp_request->pass_id, 1);
            
            // Запись в лог о сдаче пропуска
            $log_msg = date('d.m.Y H:i:s') . " : Здано перепустку (<b>{$this->admin_name}</b>)";
            $data['history'] = $temp_request->history . PHP_EOL . $log_msg;
            $data['pass_date'] = time();            
            
            $this->temp_requests_model->edit($id, $data);
            
            $this->session->set_flashdata(array('message' => 'Пропуск успішно здано.'));
        }
        else
            show_404 ();
    }
    
     /**
     * Обработчик AJAX-запроса на сортровку 
     */
    public function sort()
    {   
        if (empty($_POST) === FALSE)
        {            
            $this->session->set_userdata(array('temp_requests_order_by' => $this->input->post('order_by')));
            $this->session->set_userdata(array('temp_requests_method' => $this->input->post('method')));  
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
        return '<div class="control-group" id="errors">
                        <div class="span alert alert-danger" style="margin-left: 0">
                            <a class="close" data-dismiss="alert" onclick="$(\'#errors\').hide();">×</a>' .
                            $error .
                        '</div>
                </div>';
    }
    
    /**
     * Сделать первую букву заглавной
     *
     * @param $str
     * @return string
     */
    private function mb_ucwords($str)
    {
        $str = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
        return ($str);
    }
    
    /**
     * Разница в днях между двумя датами (работает и в PHP 5.2)
     * 
     * @param DateTime $date1
     * @param DateTime $date2
     * 
     * @return int разница в днях между двумя датами 
     */
    private function date_diff($date1, $date2)
    {        
        $date1 = date_format($date1, 'U');
        $date2 = date_format($date2, 'U');
        
        $interval = round(($date1 - $date2) / (60*60*24)); 
        
        return $interval;        
    }
    
    /**
     * Обработчик АЯКС-запроса, которій поступает при утере пропуска 
     */
    public function lost_pass()
    {
        if (empty($_POST) === FALSE)
        {
            $id = (int)$this->input->post('id');
            $new_pass_id = (int)$this->input->post('new_pass_id');
            $password = trim($this->input->post('password'));
            
            if ($id === 0)
            {
                echo 'Невірний ID заяви';
                exit();
            }
            
            if ($new_pass_id === 0)
            {
                echo 'Невірний номер нового пропуска';
                exit();
            }
            
            $this->load->library('form_validation');
            if (FALSE === $this->form_validation->free_pass($new_pass_id))
            {
                echo 'Цей пропуск вже видано';
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
            $temp_request = $this->temp_requests_model->get_data($id);
            if (empty($temp_request) === TRUE)
            {
                echo 'Данної заявки не існує!';
                exit();
            }
                       
            
            $this->load->model('passes_model');
            // Отметить новый пропуск как выданный
            $this->passes_model->change_status($new_pass_id, 2);
            
            // Отметить старый пропуск как утерянный
            $this->passes_model->change_status($temp_request->pass_id, 3);
            
            // Запись в лог о смене пропуска
            $old_pass = $this->passes_model->get_pass($temp_request->pass_id);
            $old_pass = $old_pass['number'];
            $new_pass = $this->passes_model->get_pass($new_pass_id);
            $new_pass = $new_pass['number'];
            $log_msg = date('d.m.Y H:i:s') . " : Зміна перепустки {$old_pass} на {$new_pass} (<b>{$this->admin_name}</b>)";
            
            // Правим данные заявки
            $data = array(
                'history' => $temp_request->history . PHP_EOL . $log_msg,
                'pass_id' => $new_pass_id
            );            
            $this->temp_requests_model->edit($id, $data);
            
            $message = 'Перепустку було відмічено як загублену та видану нову!';            
            $this->session->set_flashdata(array('message' => $message));
        }
        else
            show_404 ();
    }
    
    /**
     * Действие для отображения страницы контракта
     * 
     * @param int $id id контракта
     */
    public function edit($id)
    {
        // Защита от дурака
        $id = (int)$id;
        if ($id === 0)
            show_404 ();
        
        // Извлекаем из БД контракт
        $temp_request = $this->temp_requests_model->get_data($id);
        if (empty($temp_request) === TRUE)
            show_404 ();
        
        // Если поступил запрос на редактирование
        if (!empty($_POST))
        {
            // Массив правил валидации
            $config = array(
                    array(
                        'field'   => 'date_to',
                        'label'   => 'Дата по',
                        'rules'   => 'required|valid_date|today_or_future_date'
                    ),
                    array(
                        'field'   => 'visitor_name',
                        'label'   => 'ПІБ відвідувача',
                        'rules'   => 'required|alpha2'
                    ),
                    array(
                        'field'   => 'password',
                        'label'   => 'Ваш пароль',
                        'rules'   => 'required|valid_admin_password'
                    ),
                );

            // Проверка корректности заполнения формы
            $this->load->library('form_validation');
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() === TRUE)
            {            
                // Массив для редактирования данных в таблице contracts
                $data = array(
                    'date_to' => strtotime($this->input->post('date_to') . '23:59:59'),
                    'visitor_name' => $this->input->post('visitor_name')
                );

                                
                $log_head = date('d.m.Y H:i:s') . " : Зміна даних (<b>{$this->admin_name}</b>):" . PHP_EOL;
                $log_msg = '';
                if ($temp_request->visitor_name !== $this->input->post('visitor_name'))
                    $log_msg =  'ПІБ відвідувача: ' . $temp_request->visitor_name . ' на ' . $this->input->post('visitor_name') . PHP_EOL;
                if ($temp_request->date_to != $data['date_to'])
                    $log_msg .= 'Дата по: ' . date('d.m.Y', $temp_request->date_to)  . ' на ' . date('d.m.Y', $data['date_to']) . PHP_EOL;
                $data['history'] = $temp_request->history . $log_head . $log_msg;
                
                // Редактирование данных в таблице
                $this->temp_requests_model->edit($temp_request->id, $data);

                $this->session->set_flashdata(array(
                            'message' => 'Дані було відредаговано!')
                        );
                redirect('admin/temp_requests/edit/' . $temp_request->id);
            }
            else
            {
                $this->layout->add_content(array('error' => $this->set_error(validation_errors())));
            }
        }
                
        $temp_request->history = str_replace(PHP_EOL, '<br/>', $temp_request->history);
                
        $this->layout->set_page_title('Дані тимчасової заявки №' . $temp_request->number);
                        
        $this->layout->add_content(array('temp_request' => $temp_request, 
                                        'role_id' => $this->role_id
                                ));
        
        $this->layout->add_scripts('jquery.combo.autocomplete.js');
        
        $this->layout->view_admin('Temp_requests/edit');
    }
    
    /**
     * Форматирование дат в объекте $contract
     * 
     * @param object $contract
     * @return object  
     */
    private function format_contract_dates($contract)
    {
        // Формат даты создания
        if ($contract->issue_date !== null)
        {
            $date = date_create($contract->issue_date);
            $contract->issue_date = date_format($date, 'd.m.Y в H:i:s');
        }
        
        // Формат даты начала действия
        if ($contract->date_from !== null)
        {
            $date = date_create($contract->date_from);
            $contract->date_from = date_format($date, 'd.m.Y');
        }

        // Формат даты завершения действия
        if ($contract->date_to !== null)
        {
            $date = date_create($contract->date_to);
            $contract->date_to = date_format($date, 'd.m.Y');
        }
        
        // Формат даты завершения действия
        if ($contract->pass_date !== null)
        {
            $date = date_create($contract->pass_date);
            $contract->pass_date = date_format($date, 'd.m.Y в H:i:s');
        }
        return $contract;
    }
}