<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Requests
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления запросами на выдачу пропусков
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Requests extends CI_Controller
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
        
        // Модель для работы с админами
        $this->load->model('administrators_model');
        $this->admin_name = $this->administrators_model
                              ->get_name_by_login($this->session
                                                    ->userdata('login'));

        // Библиотека вывода
        $this->load->library('Layout');
        $this->layout->add_content(array('role_id' => $this->role_id));

        // Модель для работы с заявками
        $this->load->model('requests_model');
        
        // Модель для работы с логами
        $this->load->model('requests_histories_model');

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
                        'bootstrap-collapse.js',
                        'bootstrap-dropdown.js',
                        'bootstrap-alert.js', 
                        'jquery-ui-1.8.21.min.js',
                        'ui.datepicker.js', 
                        'ui-datepicker-uk.js',
                        'passes.js',);
        $this->layout->add_scripts($js_array);    
        
        //$this->output->enable_profiler(TRUE);
    }
    
    /**
     * Действие, отображаеющее страницу добавления заявки на пропуск 
     */
    public function add()
    {
        if ($this->role_id === 1 or $this->role_id === 2)
            $this->add_from_admin();
        elseif ($this->role_id === 3)
            $this->add_from_chief();
        else
            show_404();
    }

    /**
     * Отображение страницы с заявками
     *
     * @param int $page_num номер страницы
     */
    public function requests($page_num = 1)
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
        
        $order_by = $this->session->userdata('order_by'); 
        $method = $this->session->userdata('method');
        $applicant_id_sess = $this->session->userdata('applicant_id');
        
        $where = 'r.deleted = 0';
        if ($this->role_id === 2)
            $where .= ' AND r.status_id <> 3'; 
        if ($applicant_id_sess !== FALSE)
            $where .= ' AND r.applicant_id = ' . $this->db->escape($applicant_id_sess); 
        
        // Фильтр по имени
        if (FALSE !== $this->input->post('filter_name'))
        {
            $filter_name = $this->input->post('filter_name');
            // Добавление фильтра в сессию
            $this->session->set_userdata('filter_name', $filter_name);          
        }
        else
            // Считывание данных фильтра из сессии
            $filter_name =  $this->db
                               ->escape_like_str($this->session
                                                   ->userdata('filter_name'));
        if ($filter_name !== FALSE)
            $where .= " AND (CONCAT_WS(' ', v.last_name, v.first_name, v.middle_name) LIKE '{$filter_name}%' OR
                            p_a.`name` LIKE '{$filter_name}%' OR
                            s_e.`name` LIKE '{$filter_name}%' OR
                            c.`name` LIKE '{$filter_name}%')"; 

        // Фильтр по номеру заявки
        if (FALSE !== $this->input->post('request_num_filter'))
        {
            $request_num_filter = $this->input->post('request_num_filter');  
            // Добавление фильтра в сессию
            $this->session->set_userdata('request_num_filter', $request_num_filter);
        }
        else
            // Считывание данных фильтра из сессии
            $request_num_filter =  $this->db
                                     ->escape_like_str($this->session
                                                         ->userdata('request_num_filter'));  
        
        if ($request_num_filter !== FALSE)
            $where .= " AND r.request_number LIKE '{$request_num_filter}%'";

        // Массив заявок
        if ($order_by !== FALSE and $method !== FALSE)
            $requests = $this->requests_model->get_requests($offset, 15, $where, $order_by, $method);
        else
            $requests = $this->requests_model->get_requests($offset, 15, $where);

        // Изменение формата даты
        if (empty($requests) === FALSE)
        {
            foreach ($requests as $key => $request)
            {
                // Формат даты
                $date = date_create($request->request_date);
                $requests[$key]->request_date = date_format($date, 'd.m.Y H:i:s');
            }
        }

        $this->load->library('pagination');

        // Всего заявок
        $requests_count = $this->requests_model->get_requests_count($where);

        if (($page_num * 15 > $requests_count + 15) and ($page_num !== 1))
            show_error('Такої сторінки не існує!');

        // Разбивание на страницы
        $config_pagination['base_url'] = base_url('admin/requests/');
        $config_pagination['uri_segment'] = 3;
        $config_pagination['total_rows'] = $requests_count;
        $config_pagination['per_page'] = 15;
        $config_pagination['use_page_numbers'] = TRUE;
        $config_pagination['first_link'] = '<< Перша';
        $config_pagination['last_link'] = 'Остання >>';
        $this->pagination->initialize($config_pagination);
        $pages = $this->pagination->create_links();
        
        $security_id = (int)$this->administrators_model->get_id_by_name($this->admin_name)->administrator_id;
        
        $this->layout->add_content(array('requests' => $requests,
                                     'page_num' => $page_num - 1,
                                     'pages' => $pages,
                                     'requests_count' => $requests_count,
                                     'security_id' =>  $security_id,  
                                    ));

        $this->layout->add_scripts('bootstrap-modal.js');
        $this->layout->add_scripts('bootstrap-transition.js');
        $this->layout->add_scripts('requests_admin.js');

        $this->layout->set_page_title('Список заявок на перепустки');

        $js = 'function Sort(order_by, method) {
                        $.post("' . base_url('admin/requests/sort') . '", 
                            {
                                order_by: order_by,
                                method: method,
                                ' . $this->security->get_csrf_token_name() . ': "' . $this->security->get_csrf_hash() . '"
                            },
                            function(data) {
                                location.href = "' . current_url() . '";
                            });
                      };';
        
        $this->layout->add_js_code($js, FALSE);

        $this->layout->view_admin('Requests/index');
    }
    
    /**
     * Обработчик AJAX-запроса на сортровку 
     */
    public function sort()
    {       
        if (empty($_POST) === FALSE)
        {            
            $this->session->set_userdata(array('order_by' => $this->input->post('order_by')));
            $this->session->set_userdata(array('method' => $this->input->post('method')));   
        }
    }

    /**
     * Добавление заявки на пропуск в БД
     */
    public function add_request()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні!');
                
        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'applicant_name',
                'label'   => 'ПІБ заявника',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'room_number',
                'label'   => '№ кабінета',
                'rules'   => 'required|integer|is_natural_no_zero'
            ),
            array(
                'field'   => 'visitor_last_name',
                'label'   => 'Прізвище відвідувача',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'date_from',
                'label'   => 'Дата відвідування',
                'rules'   => 'required|valid_date|today_or_future_date'
            ),
            array(
                'field'   => 'date_to',
                'label'   => 'Дата по (включно)',
                'rules'   => 'valid_date|today_or_future_date|bigger_date_than[date_from]'
            ),
            array(
                'field'   => 'password',
                'label'   => 'Пароль',
                'rules'   => 'required|valid_admin_password'
            ),
            array(
                'field'   => 'document_number',
                'label'   => 'Номер',
                'rules'   => 'required'
            ),
        );
        
        // Интервал действия заявки
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');
        
        // Текущая дата
        $cur_date = date("d.m.Y");
        $cur_date = date_create($cur_date);
            
        
        // Производится ли валидация поля Підстава
        if ($date_from !== FALSE and $date_to !== '')
        {
            if (($this->date_diff(date_create($date_to), date_create($date_from)) + 1) > 1)
            {
                $config[] = array(
                              'field'   => 'reason',
                              'label'   => 'Підстава',
                              'rules'   => 'required'
                    );                                
            }
        }
        
        // Валидация номера пропуска, если выбран статус "Выдано"
        if ((int)$this->input->post('status') === 2)
            $config[] = array(
                              'field'   => 'pass_number',
                              'label'   => '№ пропуска',
                              'rules'   => 'required|greater_than[0]|free_pass'
                    );  
            
        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            $data = array();
            
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
            else
            {
                $photo_id = $this->input->post('photo_id');
                if ($photo_id !== FALSE and $photo_id !== '')
                    $data['photo_id'] = (int) $this->input->post('photo_id');
                else
                    $data['photo_id'] = NULL;
            }
            
            // Собираем данные формы
            $applicant_id = (int) $this->input->post('applicant_name');
            // проверяем правильность ввода id заявителя
            $this->load->model('users_model');
            $applicant = $this->users_model->get_name_by_id($applicant_id);
                if (empty($applicant) === TRUE)
                {
                    echo $this->set_error('Неправильне значення ID заявника!');
                    exit();
                }

            // ID комнаты
            $this->load->model('rooms_model');
            $room_id = (int)$this->input->post('room_number');
            // Проверка корректности введенного номера кабинета
            $room_by_id = $this->rooms_model->get_number_by_id($room_id); // Получение номера комнаты по её ID
                if (empty($room_by_id) === TRUE)
                {
                    echo $this->set_error('Неправильне значення ID кабінета!');
                    exit();
                }

            // Данные посетителя
            $this->load->model('visitors_model');
            $visitor_lastname = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_last_name')));
            $visitor_firstname = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_first_name')));
            $visitor_middlename = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_middle_name')));
            // ID посетителя для добавления в БД
            $visitor_id = $this->visitors_model->add_visitor($visitor_lastname,
                                                       $visitor_firstname,
                                                       $visitor_middlename); 
            
            // Номер пропуска
            $pass_id = (int) $this->input->post('pass_number');            

            // Проверка значения статуса (TODO: сделать нормально с запросом из БД)
            $status_id = (int) $this->input->post('status');
            if ($status_id === FALSE or $status_id > 2)
                $status_id = 1;
            
            // Тип документа
            $document_type_id = (int) $this->input->post('document_type');             
            if ($document_type_id !== 0)
            {
                // Модель для работы с типами документов
                $this->load->model('documents_type_model');
                
                // Проверка существует ли такой тип документа
                $result = $this->documents_type_model
                              ->get_document_type($document_type_id);
                
                if (empty($result) === TRUE)
                {
                    // Выдача ошибки
                    echo $this->set_error('Неправильный тип документа');
                    exit();
                }
            }
            else
                $document_type_id = NULL;

            // Аттрибуты документа
            $document_series = trim($this->input->post('document_series'));
            if ($document_series === '') 
                $document_series = NULL;
            $document_number = trim($this->input->post('document_number'));
            $this->load->model('documents_model');
            // Данные для добавления
            $doc_data = array('document_type_id' => $document_type_id,
                            'series' => $document_series,
                            'number' => $document_number,
                            );
            // Добавляем документ
            $document_id = $this->documents_model->add_document($doc_data); 

            $request_date = date('Y-m-d H:i:s');
            $security_id = (int)$this->administrators_model->get_id_by_name($this->admin_name)->administrator_id;
            
            // Если статус "Выдано" необхожимо знать ID выдавшего охранника или админа,
            // а также дату выдачи
            if ($status_id === 2)
                // дата выдачи и номер пропуска
                $issue_date = $request_date;
            else
            {
                $issue_date = NULL;
                $pass_id = NULL;
                $security_id = NULL;
            }    
            
            // Интервал действия заявки
            $date_from = date_create($this->input->post('date_from'));
            if ($date_to !== '')
                $date_to = date_create($date_to);
            
            // Число заявок за месяц
            $count_req_month = $this->requests_model
                                  ->get_count_requests_month();
           
            $data = array_merge($data, array(
                         'request_number' => date('my') . '-' . ($count_req_month + 1),
                         'applicant_id' => $applicant_id,
                         'room_id' => $room_id,
                         'document_id' => $document_id,
                         'pass_id' => $pass_id,
                         'visitor_id' => $visitor_id,
                         'request_date' => $request_date,
                         'status_id' => $status_id,
                         'issue_security_id' => $security_id,   
                         'issue_date' => $issue_date
                    )); 
            
            // Если поле "Дата з" датировано сегодняшним числом, 
            // то добавляем заявку в таблицу requests
            if ($this->date_diff($cur_date, $date_from) === 0)
            {
                // Если срок заявки больше 1 дня, 
                // то значит и Підстава заполнена
                if ($date_to !== '') 
                    if (($this->date_diff($date_to, $date_from) + 1) > 1)
                    {
                        // Получаем значение поля Підстава 
                        // и заносим его в отдельную таблицу
                        $reason = trim($this->input->post('reason'));
                        if ($reason !== FALSE)
                        {
                            $reason = json_encode(array(
                                        'date_from' => $this->input->post('date_from'), 
                                        'date_to' => $this->input->post('date_to'), 
                                        'reason' => $reason,
                                        ));

                            $this->load->model('requests_reasons_model');
                            $reason_id = $this->requests_reasons_model->add($reason);
                            $data['request_reason_id'] = $reason_id;    
                        }
                    }
                
                // Создаём запись в таблице requests_histories, содержащей логи
                $data['request_history_id'] = $this->requests_histories_model
                                               ->create();
                
                // Добавляем запись в основную таблицу с заявками
                $request_id = $this->requests_model->add_request($data);
                                
                // Запись в лог сообщения о создании
                $log_msg = "{$data['request_date']} : Створено (<b>{$this->admin_name}</b>)";
                $this->requests_histories_model->add_to_end($data['request_history_id'], $log_msg);
                // Если статус "Выдано", то пишем в историю
                if ($status_id === 2)
                {
                    $log_msg = "{$data['issue_date']} : Видано перепустку (<b>{$this->admin_name}</b>)";
                    $this->requests_histories_model->add_to_end($data['request_history_id'], $log_msg);
                }
                unset($data['request_history_id']);  
                
                // Помечаем пропуск как выданный
                if ($status_id === 2)
                {                    
                    $this->load->model('passes_model');
                    $this->passes_model->change_status($pass_id, 2);
                }
                
                // А также делаем пометку, что сегодняшняя заявка создана 
                // в таблице requests
                $data['was_created_at_once'] = 1;
            }
            
            // Если $date_to не указывали, предполагаем, что это заявка на 1 день
            if ($date_to === '')
                $date_to = $date_from;
            
            // Если срок заявки - больше одного дня или $date_from больше $cur_date, 
            // то добавляем её в таблицу long_requests
            if (($this->date_diff($date_to, $date_from) + 1) > 1 or 
                    $this->date_diff($cur_date, $date_from) < 0)
            {
                // В таблице long_requests не предусмотрено поле status_id
                unset($data['status_id']);
                // и issue_date
                unset($data['issue_date']);
                // и request_number
                unset($data['request_number']);
                $this->load->model('long_requests_model');
                $data['date_from'] = date_format($date_from, 'Y-m-d H:i:s');
                $data['date_to'] = date_format($date_to, 'Y-m-d 23:59:59');

                // ID охранника, создавшего запись в long_requests
                $data['issue_security_id'] = $this->admin_name;

                // Добавление записи в таблицу long_requests
                $this->long_requests_model->add($data);
            }            

            // Выдача сообщения об успехе
            $message = 'Було створено нову заявку на видачу пропуска!';

            $this->session->set_flashdata(array('message' => $message));
            
            if (isset($request_id) === TRUE)
                redirect('admin/requests/show/' . $request_id);
            else
                redirect ('admin/requests/');
        }
        else
        {
            // Выдача ошибки
            $this->layout->add_content(array('error' => $this->set_error(validation_errors())));
            $this->add_from_admin();            
        }
    }

    /**
     * Отображение страницы редактирования заявки
     *
     * @param int $request_id id заявки
     */
    public function show($request_id = 0)
    {
        // Защита от дурака
        $request_id = (int) $request_id;
        if ($request_id === 0)
            show_error('Такої сторінки не існує!');

        // Получаем из БД данные о заявке
        $request = $this->requests_model->get_request($request_id);

        if (empty($request) === TRUE or (int) $request->deleted === 1)
            show_error('Такої заявки не існує');
        else
        {
            // Формат даты
            $date = date_create($request->request_date);
            $request->request_date = date_format($date, 'd.m.Y в H:i:s');
            unset($date);

            // Формат даты выдачи
            if ($request->issue_date !== null)
            {
                $date = date_create($request->issue_date);
                $request->issue_date = date_format($date, 'd.m.Y в H:i:s');
            }
            
            // Формат даты сдачи
            if ($request->pass_date !== null)
            {
                $date = date_create($request->pass_date);
                $request->pass_date = date_format($date, 'd.m.Y в H:i:s');
            }
            
            $request->history = str_replace(PHP_EOL, '<br/>', $request->history);

            // Модель для работы со списком статусов
            $this->load->model('statuses_model');
            
            // Модель для работы со списком охранников
            $this->load->model('users_model');
            
            // Модель для работы со списком gjvtotybq
            $this->load->model('rooms_model');
            
            // Список типов документов
            $this->load->model('documents_type_model');
            $document_types = $this->documents_type_model->get_document_types();
            
            // Список пропусков
            $this->load->model('passes_model');
            $passes = $this->passes_model->get_free_passes();

            // Список статусов
            $statuses = $this->statuses_model->get_statuses();
            if (!empty($statuses))
                $this->layout->add_content(array('statuses' => $statuses));
            else
                show_error('Помилка! Немає даних в БД (таблиця статусів не заповнена).');
            
            // Поле Примітка
            if ((int) $request->request_reason_id > 0)
            {
                $request->reason = json_decode($request->reason);
                $request->reason = 'Заявка на дату: ' . 
                                 $request->reason->date_from . 
                                 ' - ' . $request->reason->date_to . 
                                 '. Підстава: ' .
                                 $request->reason->reason . '.';
            }            
            
            // Титул страницы
            $this->layout->set_page_title('Заявка № ' . $request->request_number);
            
            $request->status_id = (int) $request->status_id; 
            
            $this->layout->add_scripts('requests_admin.js');
            $this->layout->add_scripts('ajaxfileupload.js');
            $this->layout->add_scripts('jquery.combo.autocomplete.js');

            // Если роль - начальник, то проверим свою ли заявку он смотрит
            $is_chiefs_req = FALSE;
            if ($this->role_id === 3)
            {
                 $chief_id = $this->auth_lib
                                ->get_user_id_by_login($this->session
                                                         ->userdata('login'));
                 if ((int)$request->applicant_id === $chief_id)
                     $is_chiefs_req = TRUE;
            }
            else
            {
                if ((int) $request->status_id === 1)
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
            }
            
            $this->layout->add_content(array('request' => $request,
                                         'document_types' => $document_types,
                                         'rooms' => $this->rooms_model->get_rooms_list(),
                                         'is_chiefs_req' => $is_chiefs_req,
                                         'passes' => $passes,
                                    ));

            $this->layout->view_admin('Requests/show');
        }
        //var_dump($request);
    }
    
    /**
     * ПРоверка корректности пароля
     * Это обработчик AJAX-запроса 
     */
    public function check_password()
    {        
        $this->load->library('auth_lib');
        if ($this->auth_lib->check_pass($this->input->get('password')) === TRUE)
            echo 'valid';
        else
            echo 'not valid';
    }

    /**
     * Редактирование заявки (обработчик формы)
     *
     * @param int $request_id id заявки
     */
    public function edit($request_id = 0)
    {
        // Защита от дурака
        $request_id = (int) $request_id;
        if ($request_id === 0 or empty($_POST))
            show_error('Такої сторінки не існує!');
        
        // Строка с текстом логов
        $log_msg = '';

        // Статус
        if (isset($_POST['give_pass_subm']) === TRUE) // выдать пропуск
        {            
            $request = $this->requests_model
                          ->get_request($request_id);
            // Проверяем был ли уже выдан пропуск на такую заявку 
            // (полезно, если данная заявка была открыта на двух компах: с одного выдали пропуск, 
            //  с другого - нет, но пытаются)
            $request_status = $request->status_id;
            if ($request_status === 2 or $request_status === 3)
            {
                $error = '<b>Увага!</b> На дану заявку вже було видано пропуск! Можливо це було зроблено з іншого комп\'ютера або вікна браузера!';
                
                $this->session->set_flashdata(array('error' => $error));
                    redirect('admin/requests/show/' . $request_id);
                    exit();
            }
            
            // Загрузка библиотеки валидации
            $this->load->library('form_validation');

            // Массив правил валидации
            $config = array(
                array(
                    'field'   => 'pass_number',
                    'label'   => '№ пропуска',
                    'rules'   => 'required|integer|free_pass'
                ),
                array(
                    'field'   => 'visitor_last_name',
                    'label'   => 'ПІБ відвідувача',
                    'rules'   => 'required'
                ),
                array(
                    'field'   => 'document_number',
                    'label'   => 'Номер',
                    'rules'   => 'required'
                ),
                array(
                    'field'   => 'document_type',
                    'label'   => 'Тип документа',
                    'rules'   => 'is_natural_no_zero'
                ),
                array(
                    'field'   => 'form_password',
                    'label'   => 'Пароль',
                    'rules'   => 'required|valid_admin_password'
                ),
            );
            
            // ID выдавшего
            $security_id = $this->auth_lib
                              ->get_user_id_by_login($this->session
                                                       ->userdata('login'));

            // Применяем правила валидации
            $this->form_validation->set_rules($config);

            // Проверка корректности заполнения формы
            if ($this->form_validation->run() === TRUE)
            {
                $status_id = 2;

                // Номер пропуска
                $pass_id = $this->input->post('pass_number');

                // Номер документа
                $document_id = (int) $this->input->post('document_id');
                                
                // ФИО посетителя
                $visitor_first_name = trim($this->input->post('visitor_first_name'));
                $visitor_last_name = trim($this->input->post('visitor_last_name'));
                $visitor_middle_name = trim($this->input->post('visitor_middle_name'));
                $visitor_id = $this->input->post('visitor_id');
                // Проверяем, изменил ли охранник имя посетителя (если да - то правим его в БД)
                $this->load->model('visitors_model');
                $visitor_from_bd = $this->visitors_model->get_visitor_by_id($visitor_id);
                if (empty($visitor_from_bd) === TRUE)
                    show_error('Невірний ID відвідувача');
                else
                {
                    $visitor_name_from_bd = $visitor_from_bd->last_name . ' '
                                    . $visitor_from_bd->first_name . ' '
                                    . $visitor_from_bd->middle_name;
                    
                    $visitor_name = $visitor_last_name . ' '
                                            . $visitor_first_name . ' '
                                            . $visitor_middle_name;

                    // Если имя на форме и в базе не совпадают
                    if ($visitor_name_from_bd !== $visitor_name)
                    {                
                        // Собираем массив для добавления в БД
                        $visitor_name = array('last_name' => $visitor_last_name,
                                            'first_name' => $visitor_first_name,
                                            'middle_name' => $visitor_middle_name);

                        $this->visitors_model->edit_visitor_name($visitor_id, $visitor_name);
                        
                        // Сообщение в лог
                        $log_msg .= 'ПІБ відвідувача: <b>' . 
                                    $visitor_name_from_bd .                    
                                    '</b> на <b>' . 
                                    implode(' ', $visitor_name) .  '</b>' . PHP_EOL;
                        
                    }
                }
                
                // Обновление фото
                if ('' !== $this->input->post('photo_filename'))
                {
                    // Библиотека для загрузки изображений
                    $this->load->library('upload_photo_lib');

                    // Проверяем, было ли у данной заявки фото?
                    if ($request->photo_id !== NULL)
                    {
                        // Если было - обновляем
                        $this->upload_photo_lib
                            ->upload_and_update_photo($request->photo_id, 
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
                
                // Обновление документа
                $document_series = $this->input->post('document_series');
                if ($document_series === '') 
                    $document_series = NULL;
                $document_number = trim($this->input->post('document_number'));
                // Данные для обновления
                $doc_data = array('document_type_id' => (int)$this->input->post('document_type'),
                                'series' => $document_series,
                                'number' => $document_number,
                                'document_id' => (int)$this->input->post('document_id'));
                $this->load->model('documents_model');
                // Извлекаем из БД данные об этом документе
                $doc_data_from_db = $this->documents_model
                                    ->get_document_by_id($doc_data['document_id']);

                // Сообщения в лог
                if ((int)$doc_data_from_db->document_type_id !== $doc_data['document_type_id'])
                {
                    $this->load->model('documents_type_model');
                    $old_type = $this->documents_type_model
                                   ->get_document_type($doc_data_from_db->document_type_id)
                                   ->type;
                    $new_type = $this->documents_type_model
                                   ->get_document_type($doc_data['document_type_id'])
                                   ->type;
                    $log_msg .= 'Тип документа <b>' . 
                                $old_type . 
                                '</b> на ' . '<b>' . 
                                $new_type . '</b>' . PHP_EOL;
                }

                if ($doc_data_from_db->series !== $doc_data['series'] and 
                    ($doc_data['series'] !== '' and $doc_data_from_db->series !== NULL)) 
                    $log_msg .= 'Серія документа <b>' . 
                                $doc_data_from_db->series . 
                                '</b> на ' . '<b>' . 
                                $doc_data['series'] . '</b>' . PHP_EOL;

                if ($doc_data_from_db->number !== $doc_data['number'])
                    $log_msg .= 'Номер документа <b>' . 
                                $doc_data_from_db->number . 
                                '</b> на ' . '<b>' . 
                                $doc_data['number'] . '</b>' . PHP_EOL;
                
                $document_id = $this->documents_model->edit($document_id, $doc_data);
                
                // Обновление помещения
                $data['room_id'] = (int)$this->input->post('room_number');
                if ((int)$request->room_id !== $data['room_id'])
                {
                    $this->load->model('rooms_model');
                    $old_room = $this->rooms_model
                                    ->get_number_by_id($request->room_id)
                                    ->number;
                    $new_room = $this->rooms_model
                                ->get_number_by_id($data['room_id'])
                                ->number;
                    $log_msg .= 'Приміщення <b>' . 
                                $old_room . 
                                '</b> на ' . '<b>' . 
                                $new_room . '</b>' . PHP_EOL;
                }
                
                // Массив данных для обновления
                $data = array_merge($data, array('issue_security_id' => $security_id,
                                              'pass_id' => $pass_id,
                                              'status_id' => $status_id,
                                              'issue_date' => date('Y-m-d H:i:s')));
                
                // Редактирование заявки в БД
                $this->requests_model->edit_request($request_id, $data);
                
                // Модель для работы с пропусками
                $this->load->model('passes_model');
                
                // Помечаем пропуск как выданный
                $this->passes_model->change_status($pass_id, 2);
                
                if ($log_msg !== '')
                {
                    $log_head = date('Y-m-d H:i:s') . " : Зміна даних (<b>{$this->admin_name}</b>):" . PHP_EOL;
                    $log_msg = $log_head . $log_msg;
                }
                
                $log_msg .= "{$data['issue_date']} : Видано перепустку (<b>{$this->admin_name}</b>)";
                $this->requests_histories_model->add_to_end($this->requests_model
                                                            ->get_history_id($request_id), $log_msg);

                $this->session->set_flashdata(array('message' => 'Заявку було відредаговано!'));

                redirect('admin/requests/show/' . $request_id);
            }
            else
            {
                $this->layout->add_content(array('error' => $this->set_error(validation_errors())));
                $this->show($request_id); 
                return FALSE;
            }
        }
        elseif (isset($_POST['take_pass_subm']) === TRUE) // сдать пропуск
        {
            // Отметить заявку как закрытую (сдали пропуск)
            $this->take_pass($request_id);
            
            // Получение id пропуска
            $pass_id = $this->requests_model->get_pass_id($request_id);
            
            // Помечаем пропуск как не выданный
            $this->load->model('passes_model');
            $this->passes_model->change_status($pass_id, 1);

            $this->session->set_flashdata(array('message' => 'Заявку було відредаговано!'));
            redirect('admin/requests/show/' . $request_id);
        }
        else
            show_error('Збій у роботі програми!');
    }

    /**
     * Метод отображения страниц
     * @param int $page номер страницы
     */
    public function index($page = 1)
    {
        $this->requests($page);
    }
    
    /**
     * Отметить заявку как закрытую (сдали пропуск)
     *
     * @param int $request_id id заявки 
     * @param boolean $lost_pass Был ли утерян пропуск
     */
    private function take_pass($request_id, $lost_pass = FALSE)
    {
        // Проверяем был ли уже сдан пропуск на такую заявку 
        // (полезно, если данная заявка была открыта на двух компах: с одного выдали пропуск, 
        //  с другого - нет, но пытаются)
        $request_status = $this->requests_model
                             ->get_request_status($request_id);
        if ($request_status === 3)
        {
            $error = '<b>Увага!</b> Даний пропуск вже був зданий. Можливо це було зроблено з іншого комп\'ютера або вікна браузера!';

            $this->session->set_flashdata(array('error' => $error));
                redirect('admin/requests/show/' . $request_id);
                exit();
        }

        // Статус сданного пропуска
        $status_id = 3;

        if ($lost_pass === FALSE)
            $lost_pass = 0;
        else
            $lost_pass = 1;
        
        // Данные для обновления
        $data = array(
            'status_id' => $status_id,
            'pass_date' => date('Y-m-d H:i:s'),
            'pass_security_id' => $this->auth_lib
                                    ->get_user_id_by_login($this->session
                                                             ->userdata('login')),
            'lost_pass' => $lost_pass
        );

        // Редактирование заявки в БД
        $this->requests_model->edit_request($request_id, $data);
        
        $log_msg = "{$data['pass_date']} : Здано перепустку (<b>{$this->admin_name}</b>)";
        $this->requests_histories_model->add_to_end($this->requests_model
                                                   ->get_history_id($request_id), $log_msg);
    }
    
    /** 
     * Обработчик AJAX-запроса, 
     * который высылается при нажатии на кнопку "Пропуск загублено" 
     */
    public function lost_pass()
    {
        // Проверка роли
        if ($this->role_id !== 1)
            show_error ('Недостатньо прав!');
        
        if (empty($_POST) === FALSE )
        {   
            $request_id = (int) $this->input->post('request_id');
            if ($request_id === 0)
                show_error('Такої сторінки не існує!');
            
            // Отметка в логе об утере пропуска
            $log_msg = date('Y-m-d H:i:s') . " : Перепустку відмічено як загублену (<b>{$this->admin_name}</b>)";
            $this->requests_histories_model->add_to_end($this->requests_model
                                                       ->get_history_id($request_id), $log_msg);
            
            // Сдать пропуск
            $this->take_pass($request_id, TRUE);
            
            // Получение id пропуска
            $pass_id = $this->requests_model->get_pass_id($request_id);
            
            // Помечаем пропуск как утерянный
            $this->load->model('passes_model');
            $this->passes_model->change_status($pass_id, 3);

            $this->session->set_flashdata(array('message' => 'Заявку було зачинено. Пропуск було відмічено як загублений!'));
        }
    }


    /**
     * Удаление заявки на пропуск
     */
    public function delete()
    {
        // Проверка роли
        if ($this->role_id !== 1)
            show_error ('Недостатньо прав!');
         
        // Проверка на правильность способа удаления
        if (empty($_POST) === FALSE )
        {   
            // Защита от дурака
            $request_id = (int) $this->input->post('request_id');
            if ($request_id === 0)
                show_error('Такої сторінки не існує!');
            
            // Удаление заявки
            $this->requests_model->delete_request($request_id);

            $this->session->set_flashdata(array('message' => 'Заявку було видалено!'));
        }
        else
            show_error ('Спроба некорретного видалення заявки!');
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
        
        return (int)$interval;        
    }
    
    /**
     * Обработчик AJAX-запроса на получение не выданных пропусков 
     */
    public function get_free_passes()
    {
        if (empty($_POST) !== FALSE)
        {
            // Извлекаем не выданные пропуска
            $this->load->model('passes_model');
            $free_passes = $this->passes_model->get_free_passes();
            
            $result = json_encode($free_passes);
            echo $result;            
        }
    }
    
    /**
     * Отображение страницы добавления заявки от начальника 
     */
    private function add_from_chief()
    {
        // Имя пользователя           
        $this->load->model('users_model');
        $this->load->model('rooms_model');
        $name = $this->users_model->get_name_by_login($this->session->userdata('login'));
        
        // Выборка из БД списка кабинетов
        $rooms = $this->rooms_model->get_rooms_list();

        // Передаём список в вид
        $this->layout->add_content(array('rooms' => $rooms));
        
        // Выборка из БД ID кабинета пользователя
        $room_id = $this->users_model->get_user_room_id($name->user_id);

        // Передаём ID кабинета пользователя в вид
        if (!empty($room_id))
            $this->layout->add_content(array('user_room_id' => $room_id->room_id));
        
        // Список типов документов
        $this->load->model('documents_type_model');
        $document_types = $this->documents_type_model->get_document_types();

        $this->layout->add_content(array('document_types' => $document_types));
        
        $this->layout->set_page_title('Додати заявку на перепустку');
        
        // Прередаём данные о пользователе в вид
        $this->layout->add_content(array('username' => $name->name, 'user_id' => $name->user_id));
        
        $this->layout->add_scripts('requests_admin.js');
        $this->layout->add_scripts('ajaxfileupload.js');
        $this->layout->view_admin('Requests/add_form_chief');
    }
    
    /**
     * Отображение страницы добавления заявки от админа или охранника
     */
    private function add_from_admin()
    {
        // Имя пользователя           
        $this->load->model('users_model');
        $this->load->model('rooms_model');
        $name = $this->users_model->get_name_by_login($this->session->userdata('login'));
        
        // Выборка из БД списка кабинетов
        $rooms = $this->rooms_model->get_rooms_list();

        // Передаём список в вид
        $this->layout->add_content(array('rooms' => $rooms));
        
        // Выборка из БД ID кабинета пользователя
        $room_id = $this->users_model->get_user_room_id($name->user_id);

        // Передаём ID кабинета пользователя в вид
        if (!empty($room_id))
            $this->layout->add_content(array('user_room_id' => $room_id->room_id));
        
        // Список типов документов
        $this->load->model('documents_type_model');
        $document_types = $this->documents_type_model->get_document_types();
        
        // Список начальства (заявителей)
        $applicants =$this->users_model->get_applicants(); 
        
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
        
        $this->layout->add_content(array('document_types' => $document_types,
                                     'applicants' => $applicants,
                                     'video' => $video,
                                     'passes' => $passes));
        
        $this->layout->set_page_title('Додати заявку на перепустку');
        
        $this->layout->add_scripts('requests_admin.js');
        $this->layout->add_scripts('ajaxfileupload.js');
        $this->layout->add_scripts('jquery.combo.autocomplete.js');
        $this->layout->view_admin('Requests/add_form_admin');
    }
    
    /**
     * Метод добавления запроса в БД с данными, полученными от формы начальника
     */
    public function add_request_to_db_from_chief()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні!');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Модели
        $this->load->model('visitors_model');
        $this->load->model('documents_model');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'visitor_lastname',
                'label'   => 'Прізвище відвідувача',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'visitor_firstname',
                'label'   => 'Ім\'я відвідувача',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'visitor_middlename',
                'label'   => 'По-батькові відвідувача',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'document_number',
                'label'   => 'Номер',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'document_type',
                'label'   => 'Тип документа',
                'rules'   => 'is_natural_no_zero'
            ),
            array(
                'field'   => 'date_from',
                'label'   => 'Дата відвідування',
                'rules'   => 'required|valid_date|today_or_future_date'
            ),
            
            array(
                'field'   => 'show_date',
                'label'   => 'Заявка на декілька днів',
                'rules'   => ''
            ),            
        );
        
        if ('' !== $this->input->post('document_date'))
                $config[] = array(
                    'field'   => 'document_date',
                    'label'   => 'Дата видачі',
                    'rules'   => 'valid_date|today_or_past_date'
                );
        
        // Если оформляется заявка на несколько дней, то проводим валидацию поля date_to
        if (FALSE !== $this->input->post('show_date'))
            $config[] = array(
                'field'   => 'date_to',
                'label'   => 'Дата по (включно)',
                'rules'   => 'required|valid_date|today_or_future_date|bigger_date_than[date_from]'
            );
        
        // Интервал действия заявки
        $date_from = date_create(trim($this->input->post('date_from')));
        $date_to = trim($this->input->post('date_to'));
        // Если дата окончания заявки неизвестна, то считаем, что это заявка на один день
        if ($date_to === '')
            $date_to = $date_from;
        else
            $date_to = date_create($date_to);
        
        // Производится ли валидация поля Підстава
        if ($date_from !== FALSE and $date_to !== FALSE)
        {
            if (($this->date_diff($date_to, $date_from) + 1) > 1)
            {
                $config[] = array(
                              'field'   => 'reason',
                              'label'   => 'Підстава',
                              'rules'   => 'required'
                    );                                
            }
        }

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            $data = array();
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
            else
            {
                $photo_id = $this->input->post('photo_id');
                if ($photo_id !== FALSE and $photo_id !== '')
                    $data['photo_id'] = (int) $this->input->post('photo_id');
                else
                    $data['photo_id'] = NULL;
            }
            
            // Получаем значения полей
            // ID пользователя
            $applicant_id = $this->auth_lib
                              ->get_user_id_by_login($this->session
                                                       ->userdata('login'));

            // ID комнаты
            $room_id = (int)$this->input->post('room_number');
            // Проверка корректности введенного номера кабинета
            $this->load->model('rooms_model');
            $room_by_id = $this->rooms_model->get_number_by_id($room_id); // Получение номера комнаты по её ID
            if (empty($room_by_id) === TRUE)
                show_error('Спроба вводу неправдивих данних!');

            // Данные посетителя
            $visitor_lastname = $this->mb_ucwords(mb_strtolower($this->input
                                                               ->post('visitor_lastname')));
            $visitor_firstname = $this->mb_ucwords(mb_strtolower($this->input
                                                                ->post('visitor_firstname')));
            $visitor_middlename = $this->mb_ucwords(mb_strtolower($this->input
                                                                 ->post('visitor_middlename')));
            
            // ID посетителя для добавления в БД
            $visitor_id = $this->visitors_model->add_visitor($visitor_lastname,
                                                        $visitor_firstname,
                                                        $visitor_middlename); // Добавляем новый
                                                                             
            // Тип документа
            $document_type_id = (int)$this->input->post('document_type');
            if ($document_type_id !== 0)
            {
                // Модель для работы с типами документов
                $this->load->model('documents_type_model');
                
                // Проверка существует ли такой тип документа
                $result = $this->documents_type_model
                              ->get_document_type($document_type_id);
                
                if (empty($result) === TRUE)
                    show_error('Неправильный тип документа');
            }
            else
                show_error('Неправильный тип документа');

            // Аттрибуты документа
            $document_series = trim($this->input->post('document_series'));
            if ($document_series === '') 
                $document_series = NULL;
            $document_number = trim($this->input->post('document_number'));
            $document_date = trim($this->input->post('document_date'));
            if ($document_date !== '')
            {
                $document_date = date_create($document_date);
                $document_date = date_format ($document_date, 'Y-m-d');
            }
            else
                $document_date = NULL;
            $this->load->model('documents_model');
            // Данные для добавления
            $doc_data = array('document_type_id' => $document_type_id,
                            'series' => $document_series,
                            'number' => $document_number,
                            'issue_date' => $document_date);
            // Добавляем документ
            $document_id = $this->documents_model->add_document($doc_data); 
            
            // Текущая дата
            $cur_date = date("d.m.Y");
            $cur_date = date_create($cur_date);
            
            // Число заявок за месяц
            $count_req_month = $this->requests_model
                                  ->get_count_requests_month();
           
            $data = array_merge($data, array(
                            'request_number' => date('my') . '-' . ($count_req_month + 1),
                            'applicant_id' => $applicant_id,
                            'room_id' => $room_id,
                            'document_id' => $document_id,
                            'visitor_id' => $visitor_id,
                            'status_id' => 1,
                            'request_date' => date('Y-m-d H:i:s'),
                        ));
            
            // Если поле "Дата з" датировано сегодняшним числом, 
            // то добавляем заявку в таблицу requests
            if ($this->date_diff($cur_date, $date_from) === 0)
            {
                // Если срок заявки больше 1 дня, 
                // то значит и Підстава заполнена
                if (($this->date_diff($date_to, $date_from) + 1) > 1)
                {
                    // Получаем значение поля Підстава 
                    // и заносим его в отдельную таблицу
                    $reason = trim($this->input->post('reason'));
                    if ($reason !== FALSE)
                    {
                        $reason = json_encode(array(
                                    'date_from' => $this->input->post('date_from'), 
                                    'date_to' => $this->input->post('date_to'), 
                                    'reason' => $reason,
                                    ));

                        $this->load->model('requests_reasons_model');
                        $reason_id = $this->requests_reasons_model->add($reason);
                        $data['request_reason_id'] = $reason_id;    
                    }
                }
                
                // Пишем лог
                $data['request_history_id'] = $this->requests_histories_model->create();
                $log_msg = "{$data['request_date']} : Створено заявником (<b>{$this->admin_name}</b>)";
                $this->requests_histories_model->add_to_end($data['request_history_id'], $log_msg);
                
                // Добавляем в БД
                $last_request_id = $this->requests_model->add_request($data);
                
                // А также делаем пометку, что сегодняшняя заявка создана 
                // в таблице requests
                $data['was_created_at_once'] = 1;
            }
            
            // Если срок заявки - больше одного дня или $date_from больше $cur_date, 
            // то добавляем её в таблицу long_requests
            if (($this->date_diff($date_to, $date_from) + 1) > 1 or 
                    $this->date_diff($cur_date, $date_from) < 0)
            {
                // В таблице long_requests не предусмотрено поле status_id
                unset($data['status_id']);
                // и request_number
                unset($data['request_number']);
                
                $this->load->model('long_requests_model');
                $data['date_from'] = date_format($date_from, 'Y-m-d H:i:s');
                $data['date_to'] = date_format($date_to, 'Y-m-d 23:59:59');
                
                $this->long_requests_model->add($data);
            }            

            // Куда переадресовываем?
            // Если заявка на сегодня создана - переадресовываем на нее
            if ($this->date_diff($cur_date, $date_from) === 0)
            {
                $this->session->set_flashdata(array('message' => 'Було створено нову заявку!'));
                if (FALSE !== $this->input->post('submit_save_exit'))
                    redirect('admin/requests');
                else
                    redirect('admin/requests/show/' . $last_request_id);
            }
            else
            {
                // Если сегодняшняя не создана
                $this->session->set_flashdata(array('message' => 'Було створено відкладену заявку. Вона з\'явиться у списку заявок в день початку її дії!'));
                redirect('admin/requests');
            }
        }
        else
        {
            // Выдача ошибки
            $this->layout->add_content(array('error' => $this->set_error(validation_errors())));
            $this->add_from_chief();        
        }
    }
    
    /** 
     * Обраблтчик запроса на изменение данных заявки от начальника
     */
    public function save_request()
    {        
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_404 ();
        
        $error = '';
        
        $request_id = (int) $this->input->post('request_id');
        $room_id = (int) $this->input->post('room_number');
        $visitor_first_name = trim($this->input->post('visitor_first_name'));
        $visitor_last_name = trim($this->input->post('visitor_last_name'));
        $visitor_middle_name = trim($this->input->post('visitor_middle_name'));
        $visitor_id = (int) $this->input->post('visitor_id');
        $document_type_id = (int) $this->input->post('document_type');
        $first_document_type_id = (int) $this->input->post('first_document_type');
        $document_series = trim($this->input->post('document_series'));
        if ($document_series === '') 
            $document_series = NULL;
        $document_number = trim($this->input->post('document_number'));
        $document_id = (int) $this->input->post('document_id');
                
        $this->load->library('form_validation');
        if ($visitor_first_name === '' or FALSE === $this->form_validation->alpha($visitor_first_name))
            $error .= 'Поле <b>"Ім\'я відвідувача"</b> заповнено не вірно!' . PHP_EOL;
        
        if ($visitor_last_name === '' or FALSE === $this->form_validation->alpha($visitor_last_name))
            $error .= 'Поле <b>"Прізвище відвідувача"</b> заповнено не вірно!' . PHP_EOL;
        
        if ($visitor_middle_name === '' or FALSE === $this->form_validation->alpha($visitor_middle_name))
            $error .= 'Поле <b>"По-батькові відвідувача"</b> заповнено не вірно!' . PHP_EOL;
        
        if ($document_number === '')
            $error .= 'Поле <b>"Номер"</b> не заповнено!' . PHP_EOL;
                
        // Извлекаем данные пропуска
        $request = $this->requests_model->get_request($request_id);
        
        if (empty($request) === TRUE)
            $error .= 'Заявка з цим ID не існує' . PHP_EOL;
        
        // Проверяем, действительно ли заявка этого пользователя
        $applicant_id = $this->auth_lib
                              ->get_user_id_by_login($this->session
                                                       ->userdata('login'));
        if ($applicant_id !== (int) $request->applicant_id)
             $error .= 'Це не Ваша заявка' . PHP_EOL;
        
        // Проверяем, выдано ли пропуск                 
        if ((int) $request->status_id === 2 or (int) $request->status_id === 3)
            $error .= 'Пропуск вже було видано або здано' . PHP_EOL;
        
        // Проверяем ID помещения
        $this->load->model('rooms_model');
        $rooms = $this->rooms_model->get_rooms_list();
        $is_room_in_list = FALSE;
        foreach ($rooms as $room)
            if ((int) $room->room_id === $room_id)
                $is_room_in_list = TRUE;
        if ($is_room_in_list === FALSE)
            $error .= 'Невірний обрано поверх' . PHP_EOL;
        
        // Проверяем ID типа документа
        if ($document_type_id === 0)
            $error .= 'Тип документу не обрано!' . PHP_EOL;   
        else
        {
            $this->load->model('documents_type_model');
            $document_types = $this->documents_type_model->get_document_types();
            $is_doc_type_in_list = FALSE;
            foreach ($document_types as $document_type)
                if ((int) $document_type->document_type_id === $document_type_id)
                    $is_doc_type_in_list = TRUE;
            if ($is_doc_type_in_list === FALSE)
                $error .= 'Невірний обрано тип документу' . PHP_EOL;        
        }
        
        // Редактируем заявку
        // Проверяем, изменил ли пользователь имя посетителя (если да - то правим его в БД)
        $this->load->model('visitors_model');
        $visitor_from_bd = $this->visitors_model->get_visitor_by_id($visitor_id);
        if (empty($visitor_from_bd) === TRUE)
            $error .= 'Невірний ID відвідувача' . PHP_EOL;            
        
        // Проверяем, изменил ли пользователь номер документа (если да - то правим его в БД)
        $this->load->model('documents_model');
        $doc_from_bd = $this->documents_model->get_document_by_id($document_id);
        if (empty($doc_from_bd) === TRUE)
            $error .= 'Невірний ID документа';
        
        if ($error !== '')
        {
            $error = str_replace(PHP_EOL, '<br/>', $error);
            $error = $this->set_error($error);
            $this->layout->add_content(array('error' => $error));
            $this->show($request->request_id);
            return FALSE;
        }
        else
        {   
            // Проверяем загружается ли фото (если да - то загружаем его)
            if (isset($_FILES['photo']['name']) and $_FILES['photo']['name'] !== '')
            {
                // Библиотека для загрузки изображений
                $this->load->library('upload_photo_lib');
                
                if ($request->photo_id !== null)
                {
                    $uploaded_photo = $this->upload_photo_lib
                                         ->upload_and_update_photo($request->photo_id);
                    if ($uploaded_photo === FALSE)
                    {
                        $this->show($request->request_id);
                        return FALSE;
                    }
                }
                else
                {
                    $uploaded_photo_id = $this->upload_photo_lib
                                           ->upload_photo();
                    if ($uploaded_photo_id === FALSE)
                    {
                        $this->show($request->request_id);
                        return FALSE;
                    }
                    else
                        $data['photo_id'] = $uploaded_photo_id;
                } 
            }            
            
            // Обновление данных посетителя
            $visitor_name_from_bd = $visitor_from_bd->last_name . ' '
                                    . $visitor_from_bd->first_name . ' '
                                    . $visitor_from_bd->middle_name;
            $visitor_name = $visitor_last_name . ' '
                                    . $visitor_first_name . ' '
                                    . $visitor_middle_name;

            // Если имя на форме и в базе не совпадают
            if ($visitor_name_from_bd !== $visitor_name)
            {                
                // Собираем массив для добавления в БД
                $visitor_name = array('last_name' => $visitor_last_name,
                                    'first_name' => $visitor_first_name,
                                    'middle_name' => $visitor_middle_name);
                
                $this->visitors_model->edit_visitor_name($visitor_id, $visitor_name);
            }
            
            // Данные для добавления
            $doc_data = array('document_type_id' => $document_type_id,
                            'series' => $document_series,
                            'number' => $document_number);
            $document_id = $this->documents_model->edit($document_id, $doc_data); 
            
            // Редактирование заявки в БД
            $data['room_id'] = $room_id;
            $this->requests_model->edit_request($request_id, $data);

            $this->session->set_flashdata(array('message' => 'Заявку було відредаговано!'));  
            // Если была нажата кнопка "Зберегти"
            if (FALSE !== $this->input->post('submit_save_exit'))
                redirect('admin/requests/');
            else // Если была нажата кнопка "Зберегти та вийти"
                redirect('admin/requests/show/' . $request->request_id);
        }
    }
    
    /**
     * Добавление в сессию данных о applicant_id
     * для того, чтоб начальник мог видеть только свои заявки
     * @return boolean 
     */
    public function set_applicant_id()
    {
        if (FALSE === $this->input->post())
            show_404 ();
        
        $applicant_id = $this->input->post('applicant_id');
        if ($applicant_id === FALSE)
            return FALSE;
        else
            $this->session->set_userdata(array('applicant_id' => $applicant_id));
        return TRUE;
    }
    
    /**
     * Удаление из сессии данных об applicant_id
     */
    public function unset_applicant_id()
    {
        $this->session->unset_userdata('applicant_id');
    }
    
    
    /**
     * Ajax-загрузка изображения на сервер 
     */
    public function doAjaxFileUpload()
    {
        // Библиотека для загрузки изображений
        $this->load->library('upload_photo_lib');
        
        // Загружаем изображение во временную папку
        $result = $this->upload_photo_lib->upload_photo_ajax();
        echo $result;
    }
    
    /**
     * Обработчик запроса на изменение данных заявки (от охранника, админа) 
     */
    public function save_request_data()
    {
        if (FALSE === $this->input->post())
            show_404 ();
        
        $request = $this->requests_model->get_request($this->input->post('request_id'));
        
        $log_msg = '';    
                
        // Собираем данные ФИО посетителя и обновляем их
        $visitor_data = array();
        $visitor_data['visitor_id'] = (int)$this->input->post('visitor_id');
        $visitor_data['last_name'] = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_last_name')));
        $visitor_data['first_name'] = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_first_name')));
        $visitor_data['middle_name'] = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_middle_name')));
        
        // Извлекаем из БД данные о посетителе
        $this->load->model('visitors_model');
        $visitor_data_from_db = $this->visitors_model->get_visitor_by_id($visitor_data['visitor_id']);
        if ($visitor_data_from_db->last_name !== $visitor_data['last_name'] or
            $visitor_data_from_db->first_name !== $visitor_data['first_name'] or
            $visitor_data_from_db->middle_name !== $visitor_data['middle_name'])
        {        
            // Обновляем данные
            $this->visitors_model->edit_visitor_name($visitor_data['visitor_id'], $visitor_data);
            
            // Сообщение в лог
            $log_msg = 'ПІБ відвідувача: <b>' . 
                        $visitor_data_from_db->last_name . 
                        ' ' . $visitor_data_from_db->first_name .
                        ' ' . $visitor_data_from_db->middle_name .                    
                        '</b> на <b>' . $visitor_data['last_name'] . 
                        ' ' . $visitor_data['first_name'] .
                        ' ' . $visitor_data['middle_name']                    
                        .  '</b>' . PHP_EOL;
        }
        
        // Собираем данные документа посетителя и обновляем их
        $doc_data = array();
        $doc_data['document_id'] = (int)$this->input->post('document_id');
        $doc_data['document_type_id'] = (int)$this->input->post('document_type');
        $doc_data['series'] = trim($this->input->post('document_series'));
        $doc_data['number'] = trim($this->input->post('document_number'));    
        $doc_data['document_type_id'] = (int)$this->input->post('document_type');
        
        // Извлекаем из БД данные об этом документе
        $this->load->model('documents_model');
        $doc_data_from_db = $this->documents_model
                              ->get_document_by_id($doc_data['document_id']);
        
        // Сообщения в лог
        if ((int)$doc_data_from_db->document_type_id !== $doc_data['document_type_id'])
        {
            $this->load->model('documents_type_model');
            $old_type = $this->documents_type_model->get_document_type($doc_data_from_db->document_type_id)->type;
            $new_type = $this->documents_type_model->get_document_type($doc_data['document_type_id'])->type;
            $log_msg .= 'Тип документа <b>' . 
                        $old_type . 
                        '</b> на ' . '<b>' . 
                        $new_type . '</b>' . PHP_EOL;
        }
        
        if ($doc_data_from_db->series !== $doc_data['series'] and 
                    ($doc_data['series'] !== '' and $doc_data_from_db->series !== NULL)) 
            $log_msg .= 'Серія документа <b>' . 
                        $doc_data_from_db->series . 
                        '</b> на ' . '<b>' . 
                        $doc_data['series'] . '</b>' . PHP_EOL;
        
        if ($doc_data_from_db->number !== $doc_data['number'])
            $log_msg .= 'Номер документа <b>' . 
                        $doc_data_from_db->number . 
                        '</b> на ' . '<b>' . 
                        $doc_data['number'] . '</b>' . PHP_EOL;
                
        $this->documents_model->edit($doc_data['document_id'], $doc_data);
        
        // Обновление помещения
        $data['room_id'] = (int)$this->input->post('room_number');
        if ((int)$request->room_id !== $data['room_id'])
        {
            $this->load->model('rooms_model');
            $old_room = $this->rooms_model
                            ->get_number_by_id($request->room_id)
                            ->number;
            $new_room = $this->rooms_model
                           ->get_number_by_id($data['room_id'])
                           ->number;
            $log_msg .= 'Приміщення <b>' . 
                        $old_room . 
                        '</b> на ' . '<b>' . 
                        $new_room . '</b>' . PHP_EOL;
        }
        
        // Обновление фото
        if ('' !== $this->input->post('photo_filename'))
        {
            // Библиотека для загрузки изображений
            $this->load->library('upload_photo_lib');
                
            // Проверяем, было ли у данной заявки фото?
            if ($request->photo_id !== NULL)
            {
                // Если было - обновляем
                $this->upload_photo_lib
                    ->upload_and_update_photo($request->photo_id, 
                                           $this->input->post('photo_filename'));
            }
            else
            {            
                // Если не было - добавляем

                // Грузим фото в БД
                $uploaded_photo_id = $this->upload_photo_lib
                                        ->upload_photo($this->input
                                                            ->post('photo_filename'));

                // Помещаем ID фото в массив 
                // для дальнейшей загрузки данных о заявки в БД
                $data['photo_id'] = $uploaded_photo_id;
            }
            $log_msg .= 'Була здійснена зміна фото відвідувача' . PHP_EOL;
        }
        
        // Обновляем данные в таблице requests
        $this->requests_model->edit_request($this->input->post('request_id'), $data);
        
        // Пишем лог
        if ($log_msg !== '')
        {
            $this->load->model('requests_histories_model');
            $log_head = date('Y-m-d H:i:s') . " : Зміна даних (<b>{$this->admin_name}</b>):" . PHP_EOL;
            $this->requests_histories_model->add_to_end($request->request_history_id, $log_head . $log_msg);
        }
        
        $message = 'Дані заявки було збережено';
        $this->session->set_flashdata(array('message' => $message));
        
        $location = base_url('admin/requests/show/' . $this->input->post('request_id'));
        
        echo "<script type='text/javascript'>window.location = \"$location\"</script>";
    }
    
    /**
     * Поиск данных посетителя 
     */
    public function search_user()
    {
        // Часть фамилии
        $term = trim(strip_tags($this->input->get('term')));
        
        // Грузим из БД данные посетителей
        $this->load->model('visitors_model');
        $visitors = $this->visitors_model
                      ->get_last_visitor_by_user($term);
        
        $row_set = array();
        
        foreach ($visitors as $key => $visitor)
        {
            $row['id']=(int)$visitor->visitor_id;
            $row['value']=$visitor->last_name;            
            $row['first_name']=$visitor->first_name;         
            $row['middle_name']=$visitor->middle_name;            
            $row['doc_num']=$visitor->doc_num;         
            $row['doc_ser']=$visitor->doc_ser;    
            $row['document_type_id']=$visitor->document_type_id;            
            $row['room_id']=$visitor->room_id;
            $row['applicant_id']=$visitor->applicant_id;
            $row['photo_id']=$visitor->photo_id;
            $row_set[] = $row;
        }
       
        echo json_encode($row_set);//format the array into json data
    }
    
    /**
     * Обработчик AJAX-запроса на сохранение фото с камеры 
     */
    public function make_photo()
    {
        // Считываем с БД настройки
        $this->load->model('settings_model');
        $settings = $this->settings_model->get();
        
        // Настройки прокси
        /*$aContext = array(
            'http' => array(
            'proxy' => 'tcp://' . $settings->ip_proxy,
            'request_fulluri' => TRUE,
            'header'  => "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1"
            ),
        );
        $cxContext = stream_context_create($aContext);*/
        $url = "http://{$settings->ip_cam}/axis-cgi/jpg/image.cgi";
        $file_name = sprintf('p-%s.jpg', uniqid(md5(time()), TRUE));
        $path = './uploads/' . $file_name;
        file_put_contents($path, file_get_contents($url));
        
        // Изменяем на всякий случай размер изображения (кросскамерность:))
        $this->load->library('upload_photo_lib');
        $this->upload_photo_lib->resize_image($path);
        
        $data = array('url' => base_url('uploads/' . $file_name),
                     'file_name' => $file_name);
        $data = json_encode($data);
        echo $data;
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
        
        $image_id = $this->requests_model->get_photo_id($request_id);
        echo $image_id;
        return TRUE;
    }
    
   /* 
    public function update_users()
    {
        $users = array();
        $file_handle = fopen("users.txt", "r");
        while (!feof($file_handle)) 
        {
            $line = fgets($file_handle);
            $users[]['name'] = trim($line);
        }
        fclose($file_handle);
                
        foreach ($users as $key => $value)
        {
            $login = $this->db->select('login')
                            ->get_where('users', array('name' => $value['name']))
                            ->row();
            
            if (!empty($login))
                $users[$key]['login'] = $login->login;
            else
                $users[$key]['login'] = NULL;
        }
        
        foreach ($users as $value)
        {
            if ($value['login'] === NULL)
                $value['login'] = '';
            $value['role_id'] = 4;
            $this->db->insert('users_copy', $value);            
        }
        //var_dump($users);
    }
    
    public function update_users1()
    {
        $login = $this->db->get_where('users_copy', array('deleted' => 0))
                        ->result();
        
        foreach ($login as $value)
        {
            $this->db->update('users_copy', 
                            array('password' => md5($value->login)), 
                            array('user_id' => $value->user_id));
        }
    }*/
}