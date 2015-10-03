<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Long_requests
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления отложенными заявками
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Long_requests extends CI_Controller
{
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
        $this->load->model('long_requests_model');

        // Стили
        $css_array = array('bootstrap.css', 
                         'body.css', 
                         'bootstrap-responsive.css', 
                        );
        $this->layout->add_styles($css_array);
        
        // JS
        $js_array = array('jquery.js', 
                        'bootstrap-collapse.js', 
                        'bootstrap-alert.js',
                        'bootstrap-dropdown.js', 
                        );
        $this->layout->add_scripts($js_array);

        //$this->output->enable_profiler(TRUE);
    }

    public function index()
    {
        $long_requests = $this->long_requests_model->get_requests();
     
         // Изменение формата даты
        if (empty($long_requests) === FALSE)
        {
            foreach ($long_requests as $key => $request)
            {
                // Формат даты
                $date_from = date_create($request->date_from);
                $long_requests[$key]->date_from = date_format($date_from, 'd.m.Y');
                $date_to = date_create($request->date_to);
                $long_requests[$key]->date_to = date_format($date_to, 'd.m.Y');
            }
        }
        
        $this->layout->add_content(array('long_requests' => $long_requests,
                                     'long_requests_count' => count($long_requests),
                                     'role_id' => $this->role_id,   
                                    ));
        
        $this->layout->set_page_title('Список відкладених заявок на перепустки');
        
        $this->layout->view_admin('Long_requests/index');        
    }
    
     /**
     * Удаление заявки на пропуск
     *
     * @param int $request_id id заявки
     */
    public function delete($long_request_id = 0)
    {
        if ($this->role_id !== 1)
            show_error ('Недостатньо прав!');
        
        // Защита от дурака
        $long_request_id = (int) $long_request_id;
        if ($long_request_id === 0)
            show_error('Такої сторінки не існує!');

        // Удаление заявки
        $this->long_requests_model->delete($long_request_id);

        /**$this->layout->add_content(array('message' => 'Заявку було видалено!'));
        $this->index();*/

        $this->session->set_flashdata(array('message' => 'Заявку було видалено!'));
        redirect('admin/long_requests');
    }
}