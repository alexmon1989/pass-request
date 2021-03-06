<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Couriers
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления пкурьерами
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Couriers extends CI_Controller
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

        // Модель для работы с курьерами
        $this->load->model('couriers_model');

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
        $this->layout->add_scripts('jquery-ui-1.8.21.min.js');
        $this->layout->add_scripts('ui.datepicker.js');
        $this->layout->add_scripts('ui-datepicker-uk.js');
        $this->layout->add_scripts('couriers.js');

        //$this->output->enable_profiler(TRUE);
    }

    /**
     * Индексная страница
     */
    public function index()
    {
        $this->couriers();
    }

    /**
     * Страница показа списка курьеров
     */
    public function couriers()
    {
        // Массив курьеров
        $couriers = $this->couriers_model->get_couriers();

        // Количество курьеров в БД
        $couriers_count = count($couriers);

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

        $this->layout->add_content(array('couriers' => $couriers,
                                     'couriers_count' => $couriers_count,
                                     'rooms' => $rooms,
                                     'applicants' => $applicants,
                                     'document_types' => $document_types,
                                     'role_id' => $this->role_id,
                                  ));

        $this->layout->set_page_title('Список кур\'єрів');

        $this->layout->add_scripts('bootstrap-modal.js');
        $this->layout->add_scripts('bootstrap-transition.js');

        $this->layout->view_admin('Couriers/index');
    }

    /**
     * ID последнего кабиета, на который оформляли заявку курьеру
     *
     * @param $courier_id id курьера
     * 
     * @return int ID кабинета
     */
    private function get_last_room_id($courier_id)
    {
        // Запрос в БД
        $result = $this->couriers_model->get_last_room_id($courier_id);
        if (empty($result) === FALSE)
            return (int) $result->room_id;
        else
            return (int) $this->config->item('couriers_default_room_id');
    }

    /**
     * Последний документ, на который оформляли заявку курьеру
     *
     * @param $courier_id id курьера
     * 
     * @return array массив с данными документа
     */
    private function get_last_document($courier_id)
    {
         $data = array('document_id' => 0,
                      'document_number' => '',
                      'document_series' => '',
                      'document_type_id' => 0,
                     );
        
        // Запрос в БД
        $result = $this->couriers_model->get_last_document($courier_id);
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
     * ID последнего заявителя, на которого оформляли заявку курьеру
     *
     * @param $courier_id id курьера
     * 
     * @return int ID заявителя или ID Бондаренко
     */
    private function get_last_applicant_id($courier_id)
    {
        // Запрос в БД
        $result = $this->couriers_model->get_last_applicant_id($courier_id);
        if (empty($result) === FALSE)
            return (int) $result->user_id;
        else
            return (int) $this->config->item('patent_agents_default_applicant_id');
    }
    
    /**
     * ID последнего фото, которое использовалось при оформлении заявки
     * 
     * @param int $courier_id id курьера
     * 
     * @return int ID фото или null
     */
    private function get_last_photo_id($courier_id)
    {
        // Запрос в БД
        $result = $this->couriers_model->get_last_photo_id($courier_id);
        
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
     * Обработчик POST-запроса на добавление заявки на получение пропуска курьером
     */
    public function add_request_to_db($courier_id = 0)
    {
        // Данные курьера
        $courier = $this->couriers_model
                      ->get_courier($courier_id);
        if (empty($courier) === TRUE)
            show_404 ();
        
        // Массив с данными заявки
        $data = array();
        
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
            // Собираем данные формы
            $document_series = trim($this->input->post('document_series'));
            $document_number = trim($this->input->post('document_number'));
            $document_type_id = (int) $this->input->post('document_type');
            $room_id = (int) $this->input->post('room_number');
            $applicant_id = (int) $this->input->post('applicant_name');
            $pass_id = (int) $this->input->post('pass_number');

            // ID Охранника
            $security_id = $this->auth_lib
                                ->get_user_id_by_login($this->session
                                                        ->userdata('login'));

            // Документ
            $this->load->model('documents_model');

            // Данные для добавления
            $doc_data = array('document_type_id' => $document_type_id,
                            'series' => $document_series,
                            'number' => $document_number,
                            );
            // Добавляем новый
            $document_id = $this->documents_model->add_document($doc_data); 


            // Проверка корректности собранных данных
            // id комнаты
            $this->load->model('rooms_model');
            if ($this->rooms_model->is_room_exist($room_id) === FALSE)
                show_error('Невірні дані');
            
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
                // Было ли фото раньше у этого курьера
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
                    'courier_id' => (int)$courier_id,
                    'issue_date' => date('Y-m-d H:i:s'),
                    'service_employee_id' => null,
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
            $this->load->model('passes_model');
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
            $this->add_request($courier_id);
        }
    }
    
    /**
     * Страница добавления 
     * заявки на получение пропуска курьером
     */
    public function add_request($courier_id = 0)
    {        
        if ($this->role_id !== 1 and $this->role_id !== 2)
            show_error ('Доступ заборонено!');
        // Курьер
        $courier = $this->couriers_model
                      ->get_courier($courier_id);
        if (empty($courier) === TRUE)
            show_404();
        // Последний документ курьера
        $courier->document = $this->get_last_document($courier_id);
        // Последнее помещение курьера
        $courier->room_id = $this->get_last_room_id($courier_id);
        // Последний заявитель для этого курьера
        $courier->applicant_id = $this->get_last_applicant_id($courier_id);
        // Последнее фото этого курьера
        $courier->photo_id = $this->get_last_photo_id($courier_id);

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
        $this->layout->add_content(array('courier' => $courier,
                                     'applicants' => $applicants,   
                                     'rooms' => $rooms,
                                     'document_types' => $document_types,
                                     'passes' => $passes,
                                     'video' => $video));

        $this->layout->add_scripts('ajaxfileupload.js');
        $this->layout->add_scripts('jquery_cookie.js');
        $this->layout->add_scripts('jquery.combo.autocomplete.js');
        
        // Титул страницы
        $this->layout->set_page_title('Видача пропуска кур\'єру ' . $courier->name);

        // Отображаем страницу
        $this->layout->view_admin('Couriers/add_request');
    }

    /**
     * Обработчик отправки формы редактирования курьера
     */
    public function edit()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'edit_form_courier_name',
                'label'   => 'ПІБ особи',
                'rules'   => 'required'
            ),

            array(
                'field'   => 'edit_form_courier_id',
                'label'   => 'id особи',
                'rules'   => 'required|integer'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Данные с формы
            $courier_id = (int)$this->input->post('edit_form_courier_id');
            $data['name'] = $this->input->post('edit_form_courier_name');

            // Редактирование данных в БД
            $this->couriers_model->edit_courier($courier_id, $data);

            $message = 'Дані користувача було змінено!';

            echo  "<script>
                        $('#edit_courier_modal').modal('hide');
                        $('#courier_name_{$courier_id}').html(\"<b>{$data['name']} </b> <span class='caret'></span>\");
                        alert('$message');
                   </script>";
        }
        else
            // Выдача ошибки
            echo $this->set_error(validation_errors());

    }

    /**
     * Удаление курьера
     *
     * @param int $courier_id id курьера
     */
    public function delete($courier_id = 0)
    {
        // Защита от удаления обычным охранником
        if ($this->role_id !== 1)
            show_error ('Недостатньо прав!');
        
        // Защита от дурака
        $courier_id = (int) $courier_id;
        $is_courier_exist = $this->couriers_model
                           ->is_courier_exist($courier_id);
        if (($courier_id === 0) or ($is_courier_exist === FALSE))
            show_error('Такої сторінки не існує!');

        // Удаление
        $this->couriers_model->delete($courier_id);

        // Сообщение об успехе
        $this->session->set_flashdata(array('message' => 'Видалення особи успішно здійснено!'));

        // Отображение страницы с курьерами
        redirect('admin/couriers');
    }

    /**
     * Добавление курьера в БД (обработчик отправки формы)
     */
    public function add()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні!');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'add_form_courier_name',
                'label'   => 'ПІБ кур\'єра',
                'rules'   => 'required'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Собираем данные для добавления в БД
            $data['name'] = $this->input->post('add_form_courier_name');

            // Добавление в БД
            $last_added_courier_id = $this->couriers_model->add($data);

            // Выдача сообщения об успехе
            $message = 'Було успішно додано нову особу у список кур\'єрів!';

            // Куда будем переадресовывать после оповещения
            $location = base_url('admin/couriers');

            $this->session->set_flashdata(array('message' => $message));

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