<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Requests
*
* Класс (контроллер),
* который отвечает за подачу заявки на выдачу пропуска
*
* @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
* @version 1.0
*/
class Requests extends CI_Controller
{
     private $applicant_name;
    
    /**
    *  Конструктор класса
    */
    public function  __construct()
    {
        parent::__construct();

        // Проверка роли авторизированного пользователя для ограничения доступа
        $this->load->library('auth_lib');
        
        // Авторизирован ли пользователь
        if ($this->auth_lib->is_user_logged() === FALSE)
            redirect('auth/login');
        
        $role_id = $this->auth_lib->get_user_role_id_from_sess();
        if (in_array($role_id, array(1,2,3)))
            redirect('admin');
        
        // Проверяем, зашел ли человек впервые? 
        // (и переадресовываем его на страницу смены пароля)
        if ($this->auth_lib->is_first_time($this->session->userdata('login')))
        {
            redirect('auth/change_pass');
        }

        // Устанавливаем дату последнего посещения
        $this->auth_lib->change_last_visit_date($this->session->userdata('login'));
        
        // Библиотека вывода
        $this->load->library('Layout');
        
        // Модели
        $this->load->model('users_model');
        $this->load->model('rooms_model');
        $this->load->model('requests_model');
        
        $this->applicant_name = $this->users_model
                                  ->get_name_by_login($this->session
                                                        ->userdata('login'))
                                                        ->name;

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
                        'bootstrap-alert.js', 
                        'jquery-ui-1.8.21.min.js',
                        'ui.datepicker.js', 
                        'ui-datepicker-uk.js',
                        'requests.js',
                        'ajaxfileupload.js');
        $this->layout->add_scripts($js_array);    
        
        //$this->output->enable_profiler(TRUE);
    }

    /**
     * Метод отображения страниц
     *
     * @param string $page Загружаемая страница
     */
    public function index($page = 'add_request')
    {
        // Загружаемая страница
        switch ($page)
        {
            case 'add_request':
            {        
                // Имя пользователя                
                $name = $this->users_model->get_name_by_login($this->session->userdata('login'));
                
                // Прередаём данные о пользователе в вид
                $this->layout->add_content(array('username' => $name->name, 
                                             'user_id' => $name->user_id));

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
                
                // Данные для шаблона заполения полей
                if (isset($_COOKIE['visitor_last_name']) and isset($_COOKIE['visitor_first_name'])
                    and isset($_COOKIE['visitor_middle_name']) and isset($_COOKIE['room_id'])
                    and isset($_COOKIE['document_number']) and isset($_COOKIE['document_type_id']))
                {
                    $template_data = array('visitor_last_name' => $_COOKIE['visitor_last_name'],
                                         'visitor_first_name' => $_COOKIE['visitor_first_name'],
                                         'visitor_middle_name' => $_COOKIE['visitor_middle_name'],
                                         'room_id' => $_COOKIE['room_id'],
                                         'document_number' => $_COOKIE['document_number'],
                                         'document_type_id' => $_COOKIE['document_type_id'],
                          );
                    
                    if (isset($_COOKIE['document_series']))
                         $template_data['document_series'] = $_COOKIE['document_series'];
                    
                    if (isset($_COOKIE['document_date']))
                    {
                         $template_data['document_date'] = $_COOKIE['document_date'];
                         $template_data['document_date'] = date_create($template_data['document_date']);
                         $template_data['document_date'] = date_format($template_data['document_date'], 'd.m.Y');
                    }
                    
                    if (isset($_COOKIE['photo_id']))
                        $template_data['photo_id'] = $_COOKIE['photo_id'];
                    
                    // добавляем эти данные в шаблон
                    $this->layout->add_content(array('template_data' => $template_data));
                }
                
                // Уничтожение куков шаблона заявки (уничтожаем в любом случае!)
                setcookie('visitor_last_name','', time() - 3600, '/');
                setcookie('visitor_first_name','', time() - 3600, '/');
                setcookie('visitor_middle_name','', time() - 3600, '/');
                setcookie('room_id','', time() - 3600, '/');
                setcookie('document_series','', time() - 3600, '/');
                setcookie('document_number','', time() - 3600, '/');
                setcookie('document_date','', time() - 3600, '/');
                setcookie('document_type_id','', time() - 3600, '/');
                setcookie('photo_id','', time() - 3600, '/');
                
                $this->layout->set_page_title('Додати заявку на перепустку');

                break;
            }

            case 'patent_agents':
            {
                redirect('patent_agents');
                break;
            }

            case 'couriers':
            {
                redirect('couriers');
                break;
            }

            case 'settings':
            {
                $this->layout->set_page_title('Налаштування');
                $page = 'change_pass';
                break;
        }

            default:
                break;
        }
        $this->layout->view($page);
    }

    function add_request()
    {
        $this->index();
    }

    function settings()
    {
        $this->index('settings');
    }

    function patent_agents()
    {
        $this->index('patent_agents');
    }

    function couriers()
    {
        $this->index('couriers');
    }

    /**
     * Метод добавления запроса в БД
     */
    public function add_request_to_db()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні!');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');
        
        // Библиотека для загрузки изображений
        $this->load->library('upload_photo_lib');

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
            /*array(
                'field'   => 'visitor_firstname',
                'label'   => 'Ім\'я відвідувача',
                'rules'   => 'required|alpha'
            ),
            array(
                'field'   => 'visitor_middlename',
                'label'   => 'По-батькові відвідувача',
                'rules'   => 'required|alpha'
            ),*/
            array(
                'field'   => 'document_type',
                'label'   => 'Тип документа',
                'rules'   => 'is_natural_no_zero'
            ),
            array(
                'field'   => 'document_number',
                'label'   => 'Номер',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'room_number',
                'label'   => 'Поверх',
                'rules'   => 'required|int'
            ),
            array(
                'field'   => 'date_from',
                'label'   => 'Дата відвідування',
                'rules'   => 'required|valid_date|today_or_future_date'
            ),
            array(
                'field'   => 'photo_id',
                'label'   => 'photo_id',
                'rules'   => ''
            ),
            array(
                'field'   => 'show_date',
                'label'   => 'Заявка на декілька днів',
                'rules'   => ''
            ),            
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
                if ('0' !== $this->input->post('photo_id'))
                     $data['photo_id'] = (int)$this->input->post('photo_id');
            }
            
            // Получаем значения полей
            // ID пользователя
            $applicant_id = $this->auth_lib
                              ->get_user_id_by_login($this->session
                                                       ->userdata('login'));

            // ID комнаты
            $room_id = (int)$this->input->post('room_number');
            // Проверка корректности введенного номера кабинета
            $room_by_id = $this->rooms_model->get_number_by_id($room_id); // Получение номера комнаты по её ID
            if (empty($room_by_id) === TRUE)
                show_error('Спроба вводу неправдивих данних!');

            // Данные посетителя
            $visitor_lastname = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_lastname')));
            $visitor_firstname = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_firstname')));
            $visitor_middlename = $this->mb_ucwords(mb_strtolower($this->input->post('visitor_middlename')));
            
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
            
            $this->load->model('documents_model');
            // Данные для добавления
            $doc_data = array('document_type_id' => $document_type_id,
                            'series' => $document_series,
                            'number' => $document_number);
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
                // Создаём лог
                $this->load->model('requests_histories_model');
                $data['request_history_id'] = $this->requests_histories_model->create();
                
                // Добавляем в БД
                $last_request_id = $this->requests_model->add_request($data);
                
                // Пишем лог
                $log_msg = "{$data['request_date']} : Створено заявником (<b>{$this->applicant_name}</b>)";
                $this->requests_histories_model->add_to_end($data['request_history_id'], $log_msg);
                
                // А также делаем пометку, что сегодняшняя заявка создана 
                // в таблице long_requests
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
                
                $this->load->model('long_requests_model');
                $data['date_from'] = date_format($date_from, 'Y-m-d H:i:s');
                $data['date_to'] = date_format($date_to, 'Y-m-d 23:59:59');
                unset($data['request_history_id']);
                $this->long_requests_model->add($data);
            }            
            
            // Куда переадресовываем?
            // Если заявка на сегодня создана - переадресовываем на нее
            if ($this->date_diff($cur_date, $date_from) === 0)
            {
                $this->session->set_flashdata(array('message' => 'Було створено нову заявку!'));
                // Если была нажата кнопка "Зберегти"
                if ('1' === $this->input->post('send_request'))
                    redirect('requests/show/' . $last_request_id);
                else // Если была нажата кнопка "Зберегти та вийти"
                    redirect('requests/past_requests');
                    
            }
            else
            {
                // Если сегодняшняя не создана
                $this->session->set_flashdata(array('message' => 'Було створено відкладену заявку. Вона з\'явиться у списку заявок в день початку її дії!'));
                // Если была нажата кнопка "Зберегти"
                if ('1' === $this->input->post('send_request'))
                    redirect('requests/add_request');
                else // Если была нажата кнопка "Зберегти та вийти"
                    redirect('requests/past_requests');
            }
        }
        else
        {
            // Ошибки валидации формы
            $error = validation_errors();
            $this->layout->add_content(array('error' => $this->set_error($error)));
            $this->add_request();
            return FALSE;
        }
    }

    /**
     * Страница просмотра прошлых заявок пользователя
     *
     * @param int $page_num номер страницы
     */
    function past_requests($page_num = 1)
    {
        // Защита от дурака
        $page_num = (int) $page_num;
        if ($page_num === 0)
            show_error('Такої сторінки не існує!');
        
        $applicant_id = $this->auth_lib
                           ->get_user_id_by_login($this->session
                                                    ->userdata('login'));

        // С какой строки в таблице БД брать данные
        if ($page_num === 1)
            $offset = 0;
        else
            $offset = ($page_num)*10 - 10;
        
        $where = ' AND r.visitor_id IS NOT NULL ';
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
                            s_e.`name` LIKE '{$filter_name}%')"; 

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
        
        // Запрос в БД
        $requests = $this->requests_model->get_past_requests($offset, 10, 
                                                   $applicant_id, $where);

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
        $requests_count = $this->requests_model->get_requests_user_count($applicant_id);

        if (($page_num * 10 > $requests_count + 10) and ($page_num !== 1))
            show_error('Такої сторінки не існує!');

        // Разбивание на страницы
        $config_pagination['base_url'] = base_url('requests/past_requests');
        $config_pagination['uri_segment'] = 3;
        $config_pagination['total_rows'] = $requests_count;
        $config_pagination['per_page'] = 10;
        $config_pagination['use_page_numbers'] = TRUE;
        $config_pagination['first_link'] = '<< Перша';
        $config_pagination['last_link'] = 'Остання >>';
        $this->pagination->initialize($config_pagination);
        $pages = $this->pagination->create_links();

        $this->layout->add_content(array('requests' => $requests,
            'page_num' => $page_num - 1,
            'pages' => $pages,
            'requests_count' => $requests_count,
            ));

        $this->layout->set_page_title('Список ваших минулих заявок');

        $this->layout->view('past_requests');
    }
    
    /**
     * Отображение страницы с данными одной заявки
     * @param int $id id заявки
     */
    public function show($request_id = 0)
    {
        // Защита от дурака
        $request_id = (int) $request_id;
        if ($request_id === 0)
            show_error ('Невірний ID заявки');
        
        $request = $this->requests_model->get_request($request_id);
        if (empty($request) === TRUE)
            show_error ('Невірний ID заявки');
        
        // Формат даты
        $date = date_create($request->request_date);
        $request->request_date = date_format($date, 'd.m.Y в H:i:s');

        // Формат даты
        if ($request->issue_date !== null)
        {
            $date = date_create($request->issue_date);
            $request->issue_date = date_format($date, 'd.m.Y в H:i:s');
        }

        // Модель для работы со списком статусов
        $this->load->model('statuses_model');

        // Модель для работы со списком охранников
        $this->load->model('users_model');

        // Список типов документов
        $this->load->model('documents_type_model');
        $document_types = $this->documents_type_model->get_document_types();

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
        
        $this->layout->add_content(array('request' => $request,
                                     'document_types' => $document_types,
                                     'rooms' => $this->rooms_model->get_rooms_list()));

        
        $this->layout->view('show_request');
        //var_dump($request);
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
        
        return (int)$interval;        
    }
    
    /** 
     * Обраблтчик АЯКС-запроса на изменение данных заявки 
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
        $document_series = $this->input->post('document_series');
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
            if ('1' === $this->input->post('send_request'))
                redirect('requests/show/' . $request->request_id);
            else // Если была нажата кнопка "Зберегти та вийти"
                redirect('requests/past_requests');
        }
    }
    
    /**
     * Обработчик AJAX-запроса на получение данных заявки 
     */
    public function get_request_json()
    {
        // Получаем ID запроса
        $request_id = (int) $this->input->post('request_id');
        if ($request_id === 0)
            show_404 ();
        
        $request = $this->requests_model->get_request($request_id);
        if (empty($request) === TRUE)
            show_404 ();
        
        $request = json_encode($request);
        echo $request;
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