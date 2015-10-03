<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Third_firms
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления сторонними фирмами и их сотрудниками
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Service extends CI_Controller
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
        
        // Модель для работы с админами
        $this->load->model('administrators_model');
        $this->admin_name = $this->administrators_model
                              ->get_name_by_login($this->session
                                                    ->userdata('login'));

        // Модель для работы с патентными поверенными
        $this->load->model('service_model');

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
        $this->layout->add_scripts('passes.js');
        $this->layout->add_scripts('service.js');
        $this->layout->add_scripts('jquery-ui-1.8.21.min.js');
        $this->layout->add_scripts('ui.datepicker.js');
        $this->layout->add_scripts('ui-datepicker-uk.js');
        $this->layout->add_scripts('jquery.combo.autocomplete.js');

        //$this->output->enable_profiler(TRUE);
    }

    /**
     * Индексная страница
     */
    public function index()
    {
        $this->services();
    }

    public function services()
    {
        // Извлекаем список фирм из БД
        $services = $this->service_model->get_services();

        // Количество сотрудников сторонних фирм
        $services_employees_count = 0;

        if (empty($services) === FALSE)
        {
            // Проходим в цикле по фирмам и извлекаем список их сотрудников
            foreach($services as $key => $service)
            {
                $services[$key]->employees = $this->service_model
                                               ->get_service_employees($service->service_id);
                $services_employees_count += count($services[$key]->employees);
            }
        }
        $services_count = count($services);                   // var_dump($services);

        // Модели для работы со списками кабинетов и начальников
        $this->load->model('rooms_model');
        $this->load->model('users_model');

        // Список кабинетов
        $rooms = $this->rooms_model->get_rooms_list();

        // Список начальства (заявителей)
        $applicants = $this->users_model->get_users(4);
                
        // Список типов документов
        $this->load->model('documents_type_model');
        $document_types = $this->documents_type_model->get_document_types();

        // Переменные в вид
        $this->layout->add_content(array('services' => $services,
                                         'services_count' => $services_count,
                                         'services_employees_count' => $services_employees_count,
                                         'rooms' => $rooms,
                                         'applicants' => $applicants,
                                         'document_types' => $document_types,
                                         'role_id' => $this->role_id,
                                        ));

        // Название страницы
        $this->layout->set_page_title('Список співробітників сторонніх організацій');

        // JS для модальной формы
        $this->layout->add_scripts('bootstrap-modal.js');
        $this->layout->add_scripts('bootstrap-transition.js');
        
        $this->layout->view_admin('Services/index');

        //var_dump($services);
    }
    
    /**
     * ID последнего кабиета, на который оформляли заявку
     *
     * @param $service_employee_id id сотрудника сторонней фирмы
     * 
     * @return int ID кабинета или 0
     */
    public function get_last_room_id($patent_agent_id)
    {
        // Запрос в БД
        $result = $this->service_model->get_last_room_id($patent_agent_id);
        if (empty($result) === FALSE)
            return (int) $result->room_id;
        else
            return 0;
    }

    /**
     * Последний документ, на который оформляли заявку 
     *
     * @param $service_employee_id id сотрудника сторонней фирмы
     * 
     * @return array массив с данными документа
     */
    public function get_last_document($service_employee_id)
    {        
        $data = array('document_id' => 0,
                      'document_number' => '',
                      'document_series' => '',
                      'document_type_id' => 0,
                     );
        
        // Запрос в БД
        $result = $this->service_model->get_last_document($service_employee_id);
        if (empty($result) === FALSE)
        {
            $data = array('document_id' =>$result->document_id,
                         'document_number' => $result->number,
                         'document_series' => $result->series,
                         'document_type_id' => $result->document_type_id,
                        );
        }
        
        return $data;
    }

    /**
     * ID последнего заявителя, на которого оформляли заявку
     *
     * @param $service_employee_id id сотрудника сторонней фирмы
     * 
     * @return int ID заявителя или 0
     */
    public function get_last_applicant_id($service_employee_id)
    {
        // Запрос в БД
        $result = $this->service_model
                      ->get_last_applicant_id($service_employee_id);
        if (empty($result) === FALSE)
            return (int) $result->user_id;
        else
            return 0;
    }
    
    /**
     * ID последнего фото, которое использовалось при оформлении заявки
     * 
     * @param int $service_employee_id id патентного поверенного
     * 
     * @return int ID фото или null
     */
    private function get_last_photo_id($service_employee_id)
    {
        // Запрос в БД
        $result = $this->service_model->get_last_photo_id($service_employee_id);
        
        if (empty($result) === FALSE)
        {
            $photo_id = (int) $result->photo_id;
            if ($photo_id === 0)
                return NULL;
            else
                return $photo_id;
        }
        else
            return NULL;
    }

    /**
     * Обработчик AJAX-запроса на редактирование данных сотрудника сторонней фирмы
     */
    public function edit_employee()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'service_employee_name',
                'label'   => 'ПІБ особи',
                'rules'   => 'required'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Собираем данные для редактирования
            $employee_id = (int) $this->input->post('service_employee_id');
            $employee_name = $this->input->post('service_employee_name');
            $pass_id = $this->input->post('service_employee_pass_number');
            if (trim($pass_id) === '') // Если не введен номер пропуска
                $pass_id = NULL;

            // Массив данных для обновления
            $data = array(
                'service_employee_id' => $employee_id,
                'name' => $employee_name,
                'pass_id' => $pass_id,
            );

            // Обновляем данные
            $this->service_model->edit_employee($employee_id, $data);

            // Выдача сообщения об успехе
            $message = 'Дані користувача було змінено!';
            $this->session->set_flashdata(array('message' => $message));

            // Куда будем переадресовывать после оповещения
            $location = base_url('admin/service');

            echo  "<script>
                        location.href = '{$location}';
                     </script>";
        }
        else
            // Выдача ошибки
            echo $this->set_error(validation_errors());
    }

    /**
     * Обработчик AJAX-запроса на выдачу пропуска работникку сторонней организации
     */
    public function add_request_to_db($service_employee_id)
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні!');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'document_number',
                'label'   => '№ документа',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'room_number',
                'label'   => '№ кабінета',
                'rules'   => 'required|integer|is_natural_no_zero'
            ),
            array(
                'field'   => 'applicant_name',
                'label'   => 'ПІБ заявника',
                'rules'   => 'required|is_natural_no_zero'
            ),
            array(
                'field'   => 'pass_number',
                'label'   => '№ пропуска',
                'rules'   => 'required|integer|free_pass'
            ),
            array(
                'field'   => 'document_type',
                'label'   => 'Тип документа',
                'rules'   => 'is_natural_no_zero'
            ),
            array(
                'field'   => 'password',
                'label'   => 'Пароль',
                'rules'   => 'required|valid_admin_password'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            $data = array();
            
            // Собираем данные формы
            $document_series = trim($this->input->post('document_series'));
            $document_number = trim($this->input->post('document_number'));
            $document_type_id = (int) $this->input->post('document_type');
            $room_id = (int) $this->input->post('room_number');
            $applicant_id = (int) $this->input->post('applicant_name');
            $pass_id = (int) $this->input->post('pass_number');
            $security_id = $this->auth_lib
                              ->get_user_id_by_login($this->session
                                                       ->userdata('login'));

            // Проверка корректности собранных данных
            // сотрудник сторонней фирмы
            if ($this->service_model->is_employee_exist($service_employee_id) === FALSE)
                // Выдача ошибки
                show_error('Невірні дані');
            
            // id комнаты
            $this->load->model('rooms_model');
            if ($this->rooms_model->is_room_exist($room_id) === FALSE)
                show_error('Невірні дані');

            // Модель для работы с пропусками
            $this->load->model('passes_model');
            
            // Данные для добавления документа
            $doc_data = array('document_type_id' => $document_type_id,
                            'series' => $document_series,
                            'number' => $document_number,
                            );
            // Добавляем новый
            $this->load->model('documents_model');
            $document_id = $this->documents_model->add_document($doc_data); 
            
            // Обновление фото
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
                // Было ли фото раньше у этого патентного поверенного
                if (0 !== (int) $this->input->post('photo_id'))
                    $data['photo_id'] = $this->input->post('photo_id');
            }

            // Модель для работы с запросами на пропуски
            $this->load->model('requests_model');
            
            // Число заявок за месяц
            $count_req_month = $this->requests_model
                                  ->get_count_requests_month();

            $data = array_merge($data, array(
                    'request_number' => date('my') . '-' . ($count_req_month + 1),
                    'applicant_id' => $applicant_id,
                    'room_id' => $room_id,
                    'document_id' => $document_id,
                    'pass_id' => $pass_id,
                    'visitor_id' => null,
                    'request_date' => date('Y-m-d H:i:s'),
                    'status_id' => 2,
                    'issue_security_id' => $security_id,
                    'patent_agent_id' => null,
                    'issue_date' => date('Y-m-d H:i:s'),
                    'service_employee_id' => $service_employee_id,
                  ));
            
            // Создаём запись в таблице requests_histories, содержащей логи
            $this->load->model('requests_histories_model');
            $data['request_history_id'] = $this->requests_histories_model
                                           ->create();

            // Добавление заявки в БД
            $new_request_id = $this->requests_model->add_request($data);
            
            // Запись в лог сообщения о создании
            $log_msg = "{$data['request_date']} : Створено (<b>{$this->admin_name}</b>)";
            $this->requests_histories_model->add_to_end($data['request_history_id'], $log_msg);
            
            // Помечаем пропуск как выданный
            $this->passes_model->change_status($pass_id, 2);

            // Выдача сообщения об успехе
            $message = 'Було створено заявку про видачу пропуска та присвоїно їй статус "Видано"!';

            $this->session->set_flashdata(array('message' => $message));

            redirect('admin/requests/show/' . $new_request_id);
        }
        else
        {
            // Выдача ошибки
            $this->layout->add_content(array('error' => $this->set_error(validation_errors())));
            $this->add_request($service_employee_id);
        }
    }
    
    /**
     * Действие, которое отображает страницу оформления заявки на пропуск
     * 
     * @param int $service_employee_id id сотрудника сторонней фирмы
     */
    public function add_request($service_employee_id = 0)
    {
        if ($this->role_id !== 1 and $this->role_id !== 2)
            show_error ('Доступ заборонено!');
        // Данные сотрудника сторонней организации
        $service_employee = $this->service_model
                              ->get_service_employee($service_employee_id);
        if (empty($service_employee) === TRUE)
            show_404 ();
        
        if ((int)$service_employee->deleted === 1)
            show_error ('Дану особу було видалено');
        
        // Последний документ сотрудника сторонней организации
        $service_employee->document = $this->get_last_document($service_employee_id);
        // Последнее помещение сотрудника сторонней организации
        $service_employee->room_id = $this->get_last_room_id($service_employee_id);
        // Последний заявитель для этого сотрудника сторонней организации
        $service_employee->applicant_id = $this->get_last_applicant_id($service_employee_id);
        // Последнее фото этого сотрудника сторонней организации
        $service_employee->photo_id = $this->get_last_photo_id($service_employee_id);
        
        // Список заявителей
        $this->load->model('applicants_model');
        $applicants = $this->applicants_model->get_applicants();

        // Список помещений
        $this->load->model('rooms_model');
        $rooms = $this->rooms_model->get_rooms_list();

        // Список типов документов
        $this->load->model('documents_type_model');
        $document_types = $this->documents_type_model->get_document_types();

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
        $this->layout->add_content(array('service_employee' => $service_employee,
                                     'applicants' => $applicants,   
                                     'rooms' => $rooms,
                                     'document_types' => $document_types,
                                     'passes' => $passes,
                                     'video' => $video));
        
        $this->layout->add_scripts('ajaxfileupload.js');
        $this->layout->add_scripts('jquery_cookie.js');
        $this->layout->add_scripts('jquery.combo.autocomplete.js');
        
        // Титул страницы
        $this->layout->set_page_title('Видача пропуска особі ' . $service_employee->name);

        // Отображаем страницу
        $this->layout->view_admin('Services/add_request');
    }

    /**
     * Удаление сотрудника сторонней фирмы
     *
     * @param $employee_id id сотрудника сторонней фирмы
     */
    public function delete_employee($employee_id)
    {
        if ($this->role_id !== 1)
            show_error ('Недостатньо прав!');
        
        // Защита от дурака
        $employee_id = (int) $employee_id;
        if (($employee_id === 0) or ($this->service_model->is_employee_exist($employee_id) === FALSE))
            show_error('Такої сторінки не існує!');

        // Удаление
        $this->service_model->delete_employee($employee_id);

        // Сообщение об успехе
        $this->session->set_flashdata(array('message' => 'Видалення особи успішно здійснено!'));

        // Отображение страницы с пат. поверенными
        redirect('admin/service');
    }

    /**
     * Удаление сторонней фирмы
     *
     * @param $service_id id сторонней фирмы
     */
    public function delete_service($service_id)
    {
        if ($this->role_id !== 1)
            show_error ('Недостатньо прав!');
        
        // Защита от дурака
        $service_id = (int) $service_id;
        if (($service_id === 0) or ($this->service_model->is_service_exist($service_id) === FALSE))
            show_error('Такої сторінки не існує!');

        // Удаление
        $this->service_model->delete_service($service_id);

        // Сообщение об успехе
        $this->session->set_flashdata(array('message' => 'Видалення організації успішно здійснено!'));

        // Отображение страницы с пат. поверенными
        redirect('admin/service');
    }

    public function edit_service()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'service_name',
                'label'   => 'Назва організації',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'service_id',
                'label'   => 'ID сторонньої організації',
                'rules'   => 'required|integer'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Собираем данные для редактирования
            $service_id = (int) $this->input->post('service_id');
            $service_name = $this->input->post('service_name');

            // Проверка существования фирмы
            if (($service_id === 0) or ($this->service_model->is_service_exist($service_id) === FALSE))
                show_error('Такої сторінки не існує!');

            // Массив данных для обновления
            $data = array(
                'service_id' => $service_id,
                'name' => $service_name,
            );

            $this->service_model->edit_service($service_id, $data);

            // Выдача сообщения об успехе
            $message = 'Дані організації було змінено!';

            // Куда будем переадресовывать после оповещения
            //$location = base_url('admin/service');

            echo  "<script>
                        $('#modal_edit_service').modal('hide');
                        $('#message').html('$message');
                        $('#information').show();
                        alert('$message');

                        $('#organization_{$service_id}').text('Співробітники організації \"{$service_name}\":');
                   </script>";

        }
        else
            // Выдача ошибки
            echo $this->set_error(validation_errors());

    }

    /**
     * Добавление сторонней фирмы в систему
     */
    public function add_service()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні!');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'form_service_name',
                'label'   => 'Назва організації',
                'rules'   => 'required'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Собираем данные формы
            $service_name = $this->input->post('form_service_name');

            // Добавляем в БД организацию
            $this->service_model->add_service($service_name);

            // Выдача сообщения об успехе
            $message = 'Було додано нову організацію!';

            $this->session->set_flashdata(array('message' => $message));

            $location = base_url('admin/service');

            echo "<script>location = \"{$location}\";</script>";
        }
        else
            // Выдача ошибки
            echo $this->set_error(validation_errors());
    }

    public function add_employee()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні!');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'form_add_employee_name',
                'label'   => 'Ім\'я співробітника організації',
                'rules'   => 'required'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Собираем данные формы
            $employee_name = $this->input->post('form_add_employee_name');
            $pass_id = $this->input->post('form_add_pass_number');
                if (trim($pass_id) === '') // Если не введен номер пропуска
                    $pass_id = NULL;
            $service_id = $this->input->post('form_add_employee_org_id');

            $data = array('name' => $employee_name,
                         'service_id' => $service_id,
                         'pass_id' => $pass_id);

            // Добавление в БД
            $this->service_model->add_employee($data);

            // Выдача сообщения об успехе
            $message = 'Було додано нового співробітника організації!';

            $this->session->set_flashdata(array('message' => $message));

            $location = base_url('admin/service');

            echo "<script>location = \"{$location}\";</script>";
        }
        else
            // Выдача ошибки
            echo $this->set_error(validation_errors());
    }
    
    /**
     * Возвращает отформатированное сообщение об ошибке
     *
     * @param type $error Сообщение ошибки
     * @return string отформатированнное сообщение об ошибке
     */
    private function set_error($error = '')
    {
        return '<div class="control-group" id="errors">
                        <div class="span alert alert-danger" style="margin-left: 0">
                            <a class="close" data-dismiss="alert" onclick="$(\'#errors\').hide();">×</a>' .
                            $error .
                        '</div>
                   </div>';
    }
}