<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Couriers
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления курьерами
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
        if ($this->role_id !== 5)
            show_error ('Доступ заборонено!');
        
        // Библиотека вывода
        $this->load->library('Layout');
        
        // Модель для работы с курьерами
        $this->load->model('couriers_model');

        // Стили
        $this->layout->add_styles('bootstrap.css');
        $this->layout->add_styles('body.css');
        $this->layout->add_styles('bootstrap-responsive.css');
        $this->layout->add_styles('jquery-ui-1.8.21.custom.css');

        // JS
        $this->layout->add_scripts('jquery.js');
        $this->layout->add_scripts('bootstrap-collapse.js');
        $this->layout->add_scripts('bootstrap-alert.js');
        $this->layout->add_scripts('bootstrap-dropdown.js');
        $this->layout->add_scripts('jquery-ui-1.8.21.min.js');
        $this->layout->add_scripts('couriers_chancellery.js');
        $this->layout->add_scripts('bootstrap-modal.js');
        $this->layout->add_scripts('bootstrap-transition.js');

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

        // Количество поверенных в БД
        $couriers_count = count($couriers);

        $this->layout->add_content(array('couriers' => $couriers,
                                     'couriers_count' => $couriers_count,
                                  ));

        $this->layout->set_page_title('Список кур\'єрів');

        $this->layout->view('couriers');
    }    

    /**
     * Обработчик отправки формы редактирования курьерa
     *
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
     * Удаление курьерa
     *
     * @param int $courier_id id курьерa
     */
    public function delete($courier_id = 0)
    {
        // Защита от удаления обычным охранником
        if ($this->role_id !== 5)
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
        redirect('requests/couriers');
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
                'label'   => 'ПІБ курьера',
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
            $last_added_patent_id = $this->couriers_model->add($data);

            // Выдача сообщения об успехе
            $message = 'Було успішно додано нову особу у список кур\'єрів!';

            // Куда будем переадресовывать после оповещения
            $location = base_url('requests/couriers');

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