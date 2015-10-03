<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Passes
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления пропусками
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Passes extends CI_Controller
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
        if (!in_array($this->role_id, array(1,3)) and $this->uri->segment(3) !== 'add')
            show_error ('Доступ заборонено!');
        
        // Библиотека вывода
        $this->load->library('Layout');        
        $this->layout->add_content(array('role_id' => $this->role_id));

        // Модель для работы с патентными поверенными
        $this->load->model('passes_model');

        // Стили
        $styles = array('bootstrap.css', 
                      'body.css', 
                      'bootstrap-responsive.css');        
        $this->layout->add_styles($styles);

        // JS
        $scripts = array('jquery.js',
                        'bootstrap-modal.js',
                        'jquery_cookie.js',
                        'bootstrap-collapse.js',
                        'bootstrap-alert.js',
                        'bootstrap-dropdown.js',
                        'bootstrap-tab.js',
                        'passes.js');
        $this->layout->add_scripts($scripts);

        //$this->output->enable_profiler(TRUE);
    }
    
    /**
     * Отображение списков 
     */
    public function index()
    {
        // Массив пропусков
        $passes = $this->passes_model->get_passes();
        
        // Массивы выданных, не выданных и утерянных пропусков
        $issued_passes = array();
        $not_issued_passes = array();
        $lost_passes = array();
        
        // Формирование массивов выданных, не выданных и утерянных пропусков
        foreach ($passes as $pass)
        {
            if ((int) $pass->pass_status_id === 1)
                $not_issued_passes[] = $pass;
            elseif ((int) $pass->pass_status_id === 2)
                $issued_passes[] = $pass;
            elseif ((int) $pass->pass_status_id === 3)
                $lost_passes[] = $pass;
        }
        
        $this->load->model('rooms_model');
        
        $this->layout
            ->add_content(
                    array(
                      'not_issued_passes' => $not_issued_passes,
                      'not_issued_passes_count' => count($not_issued_passes),   
                      'issued_passes' => $issued_passes,
                      'issued_passes_count' => count($issued_passes),   
                      'lost_passes' => $lost_passes,
                      'lost_passes_count' => count($lost_passes),
                      'rooms' => $this->rooms_model->get_rooms_list()  
                        )
                    );
        
        $this->layout->set_page_title('Списки перепусток');
        $this->layout->view_admin('Passes/index');
    }
    
    /**
     * Обработчик AJAX-запроса на отправление пропуска в список утерянных 
     */
    public function send_to_lost()
    {
        if (FALSE !== $this->input->post())
        {
            // Получение id пропуска
            $pass_id = (int) $this->input->post('pass_id');
            
            $message = 'Пропуск успішно відмічено як загублений!';
            $this->session->set_flashdata(array('message' => $message));
            
            $this->passes_model->change_status($pass_id, 3);
            
            return TRUE;
        }
    }
    
    /**
     * Обработчик AJAX-запроса на отправление пропуска в список не выданных 
     */
    public function send_to_not_issued()
    {
        if (FALSE !== $this->input->post())
        {
            // Получение id пропуска
            $pass_id = (int) $this->input->post('pass_id');
            
            $message = 'Пропуск успішно відмічено як не виданний!';
            $this->session->set_flashdata(array('message' => $message));
            
            $this->passes_model->change_status($pass_id, 1);
            
            return TRUE;            
        }        
        else
            show_404();
    }
    
    /**
     * Обработчик AJAX-запроса на удаление пропуска 
     */
    public function delete()
    {
        if (FALSE !== $this->input->post())
        {
            // Получение id пропуска
            $pass_id = (int) $this->input->post('pass_id');
            
            // Удаление пропуска
            $this->passes_model->delete($pass_id);
                        
            $message = 'Видалення успішно здійснено!';
            $this->session->set_flashdata(array('message' => $message));

            return TRUE;
        }        
        else
            show_404();
    }
    
    /**
     * Обработчик AJAX-запроса на добавление пропуска 
     */
    public function add()
    {
        if (FALSE !== $this->input->post())
        {   
            // Получение номера пропуска
            $number = trim($this->input->post('number'));
            
            if ($number === '')
            {
                echo $this->set_error('Поле <b>"Номер перепустки"</b> не може бути пустим');
                return FALSE;
            }
            
            // Получение ID комнаты (этажа)
            $room_id = (int) $this->input->post('room_id');
            $this->load->model('rooms_model');
            $room = $this->rooms_model->get_number_by_id($room_id);            
            if ($room_id === 0 or empty($room) === TRUE)
            {
                echo $this->set_error('Невірний номер кімнати');
                return FALSE;
            }            
            
            // Добавление пропуска
            $id = $this->passes_model->add_pass($number, $room_id);
            
            $message = 'Додання нової перепустки успішно здійснено!';
            $this->session->set_flashdata(array('message' => $message));

            echo $id;
            return TRUE;            
        }       
        else
            show_404();
    }
    
    /**
     * Обработчик AJAX-запроса на изменение данных пропуска (его номера)
     */
    public function edit()
    {
        if (FALSE !== $this->input->post())
        {
            // Получение id пропуска            
            $pass_id = $this->input->post('pass_id');
            $pass = $this->passes_model->get_pass($pass_id);
            
            if ($pass_id === 0 or empty($pass) === TRUE)
            {
                echo $this->set_error('Невірний ID перепустки');
                return FALSE;
            }
            
            // Получение номера пропуска
            $number = trim($this->input->post('number'));
            
            if ($number === '')
            {
                echo $this->set_error('Поле <b>"Номер перепустки"</b> не може бути пустим');
                return FALSE;
            }
            
            // Получение ID комнаты (этажа)
            $room_id = $this->input->post('room_id');
            $this->load->model('rooms_model');
            $room = $this->rooms_model->get_number_by_id($room_id);
            if ($room_id === 0 or empty($room) === TRUE)
            {
                echo $this->set_error('Невірний номер кімнати');
                return FALSE;
            }            
            
            // Добавление пропуска
            $this->passes_model->edit($pass_id, array('number' => $number,
                                                 'room_id' => $room_id)
                                  );
            
            $message = 'Редагування перепустки успішно здійснено!';
            $this->session->set_flashdata(array('message' => $message));

            return TRUE;    
        }  
        else
            show_404();
    }
    
    /**
     * Обработчик AJAX-запроса на получение данных пропуска
     */
    public function get_pass_data()
    {
        if (FALSE !== $this->input->post())
        {
            // Получение id пропуска
            $pass_id = (int) $this->input->post('pass_id');
            
            // Получение данных пропуска
            $pass_data = $this->passes_model->get_pass($pass_id);
            
            // Переводим в формат JSON
            $pass_data = json_encode($pass_data);
            
            echo $pass_data;
        }      
        else
            show_404();
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
}