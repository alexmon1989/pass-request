<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Patent_agents
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления патентными поверенными
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Patent_agents extends CI_Controller
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
        
        // Модель для работы с патентными поверенными
        $this->load->model('patent_agents_model');

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
        $this->layout->add_scripts('patent_agents_chancellery.js');
        $this->layout->add_scripts('bootstrap-modal.js');
        $this->layout->add_scripts('bootstrap-transition.js');

        //$this->output->enable_profiler(TRUE);
    }

    /**
     * Индексная страница
     */
    public function index()
    {
        $this->patent_agents();
    }

    /**
     * Страница показа списка патентных поверенных
     */
    public function patent_agents()
    {
        // Массив патентных поверенных
        $patent_agents = $this->patent_agents_model->get_patent_agents();

        // Количество поверенных в БД
        $patent_agents_count = count($patent_agents);

        $this->layout->add_content(array('patent_agents' => $patent_agents,
                                     'patent_agents_count' => $patent_agents_count,
                                  ));

        $this->layout->set_page_title('Список патентних повірених');

        $this->layout->view('patent_agents');
    }    

    /**
     * Обработчик отправки формы редактирования патентного поверенного
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
                'field'   => 'edit_form_patent_agent_name',
                'label'   => 'ПІБ особи',
                'rules'   => 'required'
            ),

            array(
                'field'   => 'edit_form_patent_agent_id',
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
            $patent_agent_id = (int)$this->input->post('edit_form_patent_agent_id');
            $data['name'] = $this->input->post('edit_form_patent_agent_name');

            // Редактирование данных в БД
            $this->patent_agents_model->edit_patent_agent($patent_agent_id, $data);

            $message = 'Дані користувача було змінено!';

            echo  "<script>
                        $('#edit_patent_agent_modal').modal('hide');
                        $('#patent_agent_name_{$patent_agent_id}').html(\"<b>{$data['name']} </b> <span class='caret'></span>\");
                        alert('$message');
                   </script>";
        }
        else
            // Выдача ошибки
            echo $this->set_error(validation_errors());

    }

    /**
     * Удаление патентного поверенного
     *
     * @param int $patent_agent_id id пат. поверенного
     */
    public function delete($patent_agent_id = 0)
    {
        // Защита от удаления обычным охранником
        if ($this->role_id !== 5)
            show_error ('Недостатньо прав!');
        
        // Защита от дурака
        $patent_agent_id = (int) $patent_agent_id;
        $is_patent_agent_exist = $this->patent_agents_model
                           ->is_patent_agent_exist($patent_agent_id);
        if (($patent_agent_id === 0) or ($is_patent_agent_exist === FALSE))
            show_error('Такої сторінки не існує!');

        // Удаление
        $this->patent_agents_model->delete($patent_agent_id);

        // Сообщение об успехе
        $this->session->set_flashdata(array('message' => 'Видалення особи успішно здійснено!'));

        // Отображение страницы с пат. поверенными
        redirect('requests/patent_agents');
    }

    /**
     * Добавление патентного поверенного в БД (обработчик отправки формы)
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
                'field'   => 'add_form_patent_agent_name',
                'label'   => 'ПІБ патентного повіренного',
                'rules'   => 'required'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Собираем данные для добавления в БД
            $data['name'] = $this->input->post('add_form_patent_agent_name');

            // Добавление в БД
            $last_added_patent_id = $this->patent_agents_model->add($data);

            // Выдача сообщения об успехе
            $message = 'Було успішно додано нову особу у список патентних повіренних!';

            // Куда будем переадресовывать после оповещения
            $location = base_url('requests/patent_agents');

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