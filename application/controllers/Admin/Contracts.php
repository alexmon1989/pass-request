<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Temporary_requests
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления списком временных пропусков
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Contracts extends CI_Controller
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

        // Модель для работы с пользователями
        $this->load->model('contracts_model');
        
        // Модель для работы с логами
        $this->load->model('contracts_histories_model');
        
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
                        'contracts.js');
        $this->layout->add_scripts($js_array);

        //$this->output->enable_profiler(TRUE);
    }

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
        
        $order_by = $this->session->userdata('contract_order_by'); 
        $method = $this->session->userdata('contract_method'); 
        //$where = 'WHERE c.date_to > CURRENT_TIMESTAMP';
        $where = '';

        // Массив заявок
        if ($order_by !== FALSE and $method !== FALSE)
            $contracts = $this->contracts_model->get_contracts($offset, 15, 
                                                 '', $order_by, $method);
        else
            $contracts = $this->contracts_model->get_contracts($offset, 15, $where);

        // Изменение формата даты
        if (empty($contracts) === FALSE)
        {
            foreach ($contracts as $key => $contract)
            {
                // Формат даты
                $date_from = date_create($contract->date_from);
                $contracts[$key]->date_from = date_format($date_from, 'd.m.Y');
                $date_to = date_create($contract->date_to);
                $contracts[$key]->date_to = date_format($date_to, 'd.m.Y');
                
                $cur_date = date_create(); 
                if ($cur_date > $date_to)
                    $contracts[$key]->actual = -1;
                else
                    $contracts[$key]->actual = 1; 
                
                // Разница между датами
                $diff = $this->date_diff($date_to, $cur_date);
                
                // Состояние контракта
                if ($diff >= 0 and $diff <= 4)
                    $contracts[$key]->actual = 0; // Заканчивается
                elseif ($diff > 3)
                    $contracts[$key]->actual = 1; // В процессе
                else
                {
                    $contracts[$key]->actual = -1; // Закончились  
                    // Если пропуск не сдан
                    if ((int)$contract->status_id === 2)
                        $contracts[$key]->actual = 0; // Заканчивается
                }
                 if ((int)$contract->status_id === 3)
                        $contracts[$key]->actual = -1; // Заканчивается
            }
        }

        $this->load->library('pagination');

        // Всего заявок
        $contracts_count = $this->contracts_model->get_count();

        if (($page_num * 15 > $contracts_count + 15) and ($page_num !== 1))
            show_error('Такої сторінки не існує!');

        // Разбивание на страницы
        $config_pagination['base_url'] = base_url('admin/contracts/');
        $config_pagination['uri_segment'] = 3;
        $config_pagination['total_rows'] = $contracts_count;
        $config_pagination['per_page'] = 15;
        $config_pagination['use_page_numbers'] = TRUE;
        $config_pagination['first_link'] = '<< Перша';
        $config_pagination['last_link'] = 'Остання >>';
        $this->pagination->initialize($config_pagination);
        $pages = $this->pagination->create_links();

        // Модели для работы со списками кабинетов и начальников
        $this->load->model('rooms_model');
        $this->load->model('users_model');
        
        // Список начальства (заявителей)
        $applicants = $this->users_model->get_users(4);
        
        // Список типов документов
        $this->load->model('documents_type_model');
        $document_types = $this->documents_type_model->get_document_types();
        
        // Заканчивающиеся контракты
        if ($page_num === 1) // Показываем только на 1-ой странице
        {
            $contracts_old = $this->contracts_model->get_old_contracts();
            // Изменение формата даты
            if (empty($contracts) === FALSE)
            {
                foreach ($contracts_old as $key => $request)
                {
                    // Формат даты
                    $date_from = date_create($request->date_from);
                    $contracts_old[$key]->date_from = date_format($date_from, 'd.m.Y');
                    $date_to = date_create($request->date_to);
                    $contracts_old[$key]->date_to = date_format($date_to, 'd.m.Y');                
                }
            }
        }
        else
        {
            $contracts_old = array();
            $contracts_old_count = 0;
        }
        
        
        $contracts_old_count = count($contracts_old);

        $this->layout->add_content(array('contracts' => $contracts,
                                     'page_num' => $page_num - 1,
                                     'pages' => $pages,
                                     'contracts_count' => $contracts_count,
                                     'applicants' => $applicants,
                                     'document_types' => $document_types,
                                     'contracts_old' => $contracts_old,
                                     'contracts_old_count' => $contracts_old_count,
                                     'role_id' => $this->role_id,
                                    ));

        $this->layout->add_scripts('bootstrap-modal.js');
        $this->layout->add_scripts('bootstrap-transition.js');

        $this->layout->set_page_title('Список заявок на перепустки');

        $this->layout->view_admin('Contracts/index');
    }
    
    /**
     * Обработчик запроса на добавление нового контракта 
     */
    public function add_contract_to_db()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'applicant_name',
                'label'   => 'ПІБ начальника',
                'rules'   => 'required|is_natural_no_zero'
            ),
            array(
                'field'   => 'visitor_last_name',
                'label'   => 'Прізвище відвідувача',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'visitor_first_name',
                'label'   => 'Ім\'я відвідувача',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'visitor_middle_name',
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
            $applicant_id = (int)$this->input->post('applicant_name');
            $visitor_last_name = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_last_name')));
            $visitor_first_name = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_first_name')));
            $visitor_middle_name = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_middle_name')));
            $pass_id = (int)$this->input->post('pass_number');
            $date_from = $this->input->post('date_from') . ' 00:00:00';
            $date_to = $this->input->post('date_to') . ' 23:59:59';
            
            // Форматируем дату для запроса в БД
            $date_from = date_create($date_from);
            $date_from = date_format($date_from, 'Y-m-d H:i:s');

            $date_to = date_create($date_to);
            $date_to = date_format($date_to, 'Y-m-d H:i:s');
                       
            // Массив для добавления в БД посетителя
            $data = array('name' => $visitor_last_name . ' ' . 
                                  $visitor_first_name . ' ' . 
                                  $visitor_middle_name);
            
            // Проверяем есть ли такой посетитель (если нету - добавляем его)
            $this->load->model('contract_visitors_model');
            $contract_visitor = $this->contract_visitors_model
                                  ->get_contract_visitor($data['name']);
            if (empty($contract_visitor) === FALSE)
                $contract_visitor_id = (int)$contract_visitor->contract_visitor_id;
            else
                $contract_visitor_id = $this->contract_visitors_model->add($data);
            unset($data);
            
            // Число заявок за месяц
            $count_contracts_month = $this->contracts_model
                                       ->get_count_contracts_month();            
            
            // Создаём запись в таблице истории
            $contract_history_id = $this->contracts_histories_model->create();
            
            // Данные контракта для добавления в БД
            $data = array('contract_visitor_id' => $contract_visitor_id,
                         'contract_number' => date('my') . '-' . ($count_contracts_month + 1),
                         'applicant_id' => $applicant_id,
                         'date_from' => $date_from,
                         'date_to' => $date_to,
                         'pass_id' => $pass_id,
                         'issue_date' => date('Y-m-d H:i:s'),
                         'contract_history_id' => $contract_history_id,
                         'administrator_id' => (int) $this->auth_lib
                                                       ->get_user_id_by_login($this->session
                                                                                ->userdata('login')));
            
            // Загрузка фото в БД
            if ('' !== $this->input->post('photo_filename'))
            {
                // Библиотека для загрузки изображений
                $this->load->library('upload_photo_lib');
                
                // Грузим фото в БД
                $uploaded_photo_id = $this->upload_photo_lib
                                       ->upload_photo($this->input
                                                         ->post('photo_filename'));
                
                // Помещаем ID фото в массив 
                // для дальнейшей загрузки данных о заявки в БД
                $data['photo_id'] = $uploaded_photo_id;
            }
            
            // Добавляем в БД контракт
            $new_contract_id = $this->contracts_model->add($data);
            
            // Помечаем пропуск как выданный
            $this->passes_model->change_status($pass_id, 2);
            
            // Запись в лог
            $log_msg = "{$data['issue_date']} : Створено угоду (<b>{$this->admin_name}</b>)";
            $this->contracts_histories_model
                ->add_to_end($contract_history_id, $log_msg);
            
            // Выдача сообщения об успехе
            $message = 'Було створено нову трудову угоду!';

            $this->session->set_flashdata(array('message' => $message));

            redirect('admin/contracts/show/' . $new_contract_id);         
        }
        else
        {
            // Выдача ошибки
            $this->layout->add_content(array('error' => $this->set_error(validation_errors())));
            $this->add();
        }  
    }
    
    /**
     * Действие, отображающее страницу добавления контракта
     */
    public function add()
    {
        // Список заявителей
        $this->load->model('applicants_model');
        $applicants = $this->applicants_model->get_applicants();
        
        // Список пропусков
        $this->load->model('passes_model');
        $passes = $this->passes_model->get_free_passes();
        
        // Получаем IP камеры
        $this->load->model('settings_model');
        $camera_url = $this->settings_model->get();
        $camera_url = $camera_url->ip_cam;
        
        // Загружаем ХТМЛ-код страницы с видео с камеры        
        if ($camera_url !== '')
            $video = $this->load->view('Admin/Requests/video', 
                            array('camera_url' => $camera_url), TRUE);
        else
            $video = 'IP камери не було задано в налаштуваннях!';
        
        // Передаём переменные в представление
        $this->layout->add_content(array('applicants' => $applicants,   
                                     'passes' => $passes,
                                     'video' => $video));

        $this->layout->add_scripts('ajaxfileupload.js');
        $this->layout->add_scripts('jquery_cookie.js');
        $this->layout->add_scripts('jquery.combo.autocomplete.js');
        
        // Титул страницы
        $this->layout->set_page_title('Додання нової трудової угоди');

        // Отображаем страницу
        $this->layout->view_admin('Contracts/add_contract');
    }
    
    /**
     * Сдать пропуск
     * 
     * @param int $contract_id ID контракта
     */
    public function take_pass($contract_id = 0)
    {
        if (empty($_POST) === FALSE and $this->role_id === 1)
        {
            $contract_id = (int) $this->input->post('contract_id');            
            $password = trim($this->input->post('password'));
            
            if ($contract_id === 0)
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
            
            // Проверка, существует ли данный забытый пропуск
            $contract = $this->contracts_model->get_contract_data($contract_id);
            if (empty($contract) === TRUE)
            {
                echo $this->set_error('Дана трудова угода не існує!');
                exit();
            }
            
            // Проверка, сдан ли уже пропуск
            if ($contract->status_id === 3) // Признак того, что пропуск был сдан
            {
                echo $this->set_error('Цей пропуск вже було здано! Можливо це було зроблено іншою особою.');
                exit();
            }
            
            // Сдать пропуск
            $data = array('status_id' => 3,
                         'pass_administrator_id' => (int) $this->auth_lib
                                                           ->get_user_id_by_login($this->session
                                                                                    ->userdata('login')),
                         'pass_date' => date('Y-m-d H:i:s'));
            
            $this->contracts_model->edit($contract_id, $data);
            
            $this->load->model('passes_model');
            // Отметить пропуск как не выданный
            $this->passes_model->change_status($contract->pass_id, 1);
            
            // Запись в лог о сдаче пропуска
            $contract_history_id = (int)$contract->contract_history_id;
            $log_msg = "{$data['pass_date']} : Здано перепустку (<b>{$this->admin_name}</b>)";
            $this->contracts_histories_model->add_to_end($contract_history_id, 
                                                    $log_msg);
            
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
            $this->session->set_userdata(array('contract_order_by' => $this->input->post('order_by')));
            $this->session->set_userdata(array('contract_method' => $this->input->post('method')));  
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
            $contract_id = (int)$this->input->post('contract_id');
            $new_pass_id = (int)$this->input->post('new_pass_id');
            $password = trim($this->input->post('password'));
            
            if ($contract_id === 0)
            {
                echo 'Невірний ID трудової угоди';
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
            $contract = $this->contracts_model->get_contract_data($contract_id);
            if (empty($contract) === TRUE)
            {
                echo 'Данної трудової угоди не існує!';
                exit();
            }
            
            // Поменять значение пропуска
            $this->contracts_model->edit($contract_id,
                                         array('pass_id' => $new_pass_id));
            
            $this->load->model('passes_model');
            // Отметить новый пропуск как выданный
            $this->passes_model->change_status($new_pass_id, 2);
            
            // Отметить старый пропуск как утерянный
            $this->passes_model->change_status($contract->pass_id, 3);
            
            // Запись в лог о смене пропуска
            $contract_history_id = $contract->contract_history_id;
            $old_pass = $contract->pass_number;
            $new_pass = $this->passes_model->get_pass($new_pass_id);
            $new_pass = $new_pass['number'];
            $log_msg = date('Y-m-d H:i:s') . " : Зміна перепустки {$old_pass} на {$new_pass} (<b>{$this->admin_name}</b>)";
            $this->contracts_histories_model->add_to_end($contract_history_id, 
                                                    $log_msg);
            
            $message = 'Перепустку було відмічено як загублену та видану нову!';            
            $this->session->set_flashdata(array('message' => $message));
        }
        else
            show_404 ();
    }
    
    /**
     * Действие для отображения страницы контракта
     * 
     * @param int $contract_id id контракта
     */
    public function show($contract_id)
    {
        // Защита от дурака
        $contract_id = (int)$contract_id;
        if ($contract_id === 0)
            show_404 ();
        
        // Извлекаем из БД контракт
        $contract = $this->contracts_model->get_contract_data($contract_id);
        if (empty($contract) === TRUE)
            show_404 ();
        
        // Форматируем даты в контракте
        $contract = $this->format_contract_dates($contract);
        
        $contract->history = str_replace(PHP_EOL, '<br/>', $contract->history);
                
        $this->layout->set_page_title('Дані трудової угоди №' . $contract->contract_number);
        
        if ((int)$contract->status_id !== 3)
        {
            // Получаем IP камеры
            $this->load->model('settings_model');
            $camera_url = $this->settings_model->get();
            $camera_url = $camera_url->ip_cam;
            // Загружаем ХТМЛ-код страницы с видео с камеры
            if ($camera_url !== '')
                $video = $this->load->view('Admin/Requests/video', 
                                array('camera_url' => $camera_url), TRUE);
            else
                $video = 'IP камери не було задано в налаштуваннях!';

            $this->layout->add_content(array('video' => $video));
        }
        
        $this->load->model('applicants_model');
        $this->layout->add_content(array('contract' => $contract, 
                                     'role_id' => $this->role_id,
                                     'applicants' => $this->applicants_model->get_applicants(),
                                ));
        
        $this->layout->add_scripts('ajaxfileupload.js');
        $this->layout->add_scripts('jquery_cookie.js');
        $this->layout->add_scripts('jquery.combo.autocomplete.js');
        
        $this->layout->view_admin('Contracts/show');
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
    
    /**
     * Обработчик запроса на редактирование данных контракта
     */
    public function edit_contract()
    {
        // Защита от дурака
        if (FALSE === $this->input->post())
            show_404 ();
        
        // ID контракта
        $contract_id = (int) $this->input->post('contract_id');
        
        if ($contract_id === 0)
            show_error ('Невірний ID трудової угоди');
        
        $contract = $this->contracts_model->get_contract_data($contract_id);
        if (empty($contract) === TRUE)
            show_error ('Невірний ID трудової угоди');
        
        // Массив правил валидации
        $config = array(
                array(
                    'field'   => 'contract_id',
                    'label'   => 'ID contract',
                    'rules'   => 'required|integer|greater_than[0]'
                ),
                array(
                    'field'   => 'date_to',
                    'label'   => 'Дата по',
                    'rules'   => 'required|valid_date|today_or_future_date'
                ),
                array(
                    'field'   => 'visitor',
                    'label'   => 'ПІБ відвідувача',
                    'rules'   => 'required|alpha2'
                ),
                array(
                    'field'   => 'applicant_id',
                    'label'   => 'ID',
                    'rules'   => 'required|integer|existing_applicant'
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
                'date_to' => date_format(date_create($this->input
                                                     ->post('date_to')), 
                                                 'Y-m-d 23:59:59'),
                'applicant_id' => (int) $this->input->post('applicant_id'),
            );
            
            // Редактирование посетителя
            // Узнаём его ID
            $contract_visitor_id = $contract->contract_visitor_id;
            $this->load->model('contract_visitors_model');
            // Редактируем его имя
            $this->contract_visitors_model->edit($contract_visitor_id, 
                                             array('name' => $this->input
                                                                ->post('visitor')));
            
            // Запись в лог
            $contract_history_id = $contract->contract_history_id;
            $log_head = date('Y-m-d H:i:s') . " : Зміна даних (<b>{$this->admin_name}</b>):" . PHP_EOL;
            $log_msg = '';
            if ($contract->contract_visitor !== $this->input->post('visitor'))
                $log_msg =  'ПІБ відвідувача: ' . $contract->contract_visitor . ' на ' . $this->input->post('visitor') . PHP_EOL;
            if ($contract->date_to != $data['date_to'])
                $log_msg .= 'Дата по: ' . $contract->date_to . ' на ' . $data['date_to'] . PHP_EOL;
            $this->load->model('applicants_model');
            $new_applicant = $this->applicants_model->get($data['applicant_id'])->name;
            if ($contract->applicant !== $new_applicant)
                $log_msg .= 'ПІБ начальника: ' . $contract->applicant . ' на ' . $new_applicant . PHP_EOL;
                        
            // Обновление фото
            if ('' !== $this->input->post('photo_filename'))
            {
                // Библиотека для загрузки изображений
                $this->load->library('upload_photo_lib');

                // Проверяем, было ли у данной заявки фото?
                if ($contract->photo_id !== NULL)
                {
                    // Если было - обновляем
                    $this->upload_photo_lib
                        ->upload_and_update_photo($contract->photo_id, 
                                                $this->input
                                                    ->post('photo_filename'));
                }
                else
                {            
                    // Если не было - добавляем
                    // Грузим фото в БД
                    $uploaded_photo_id = $this->upload_photo_lib
                                            ->upload_photo($this->input
                                                              ->post('photo_filename'));

                    // Помещаем ID фото в массив 
                    // для дальнейшей загрузки данных о заявке в БД
                    $data['photo_id'] = $uploaded_photo_id;
                }
                $log_msg .= 'Була здійснена зміна фото відвідувача' . PHP_EOL;
            }
            
            // Редактирование данных в таблице contracts
            $this->contracts_model->edit($contract_id, $data);
            
            if ($log_msg !== '')
                $this->contracts_histories_model->add_to_end($contract_history_id, 
                                                        trim($log_head . $log_msg));
                                                    
            $this->session->set_flashdata(array(
                        'message' => 'Дані було відредаговано!')
                    );
            redirect('admin/contracts/show/' . $contract_id);
        }
        else
        {
            $this->layout->add_content(array('error' => $this->set_error(validation_errors())));
            $this->show($contract_id); 
        }
    }
    
    /**
     * Обработчик AJAX-запроса на получение данных о ID изображения заявки
     * 
     * @param int $request_id
     * @return boolean 
     */
    public function get_photo_id($request_id = 0)
    {
        if ((int)$request_id === 0)
        {
            echo 0;
            return FALSE;
        }
        
        $image_id = $this->contracts_model->get_photo_id($request_id);
        echo $image_id;
        return TRUE;
    }
}