<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Applicants
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления списком заявителей
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Applicants extends CI_Controller
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
        if (!in_array($this->role_id, array(1,3)))
            show_error ('Доступ заборонено!');

        // Библиотека вывода
        $this->load->library('Layout');

        // Модель для работы с пользователями
        $this->load->model('applicants_model');

        // Стили
        $this->layout->add_styles('bootstrap.css');
        $this->layout->add_styles('body.css');
        $this->layout->add_styles('bootstrap-responsive.css');

        // JS
        $this->layout->add_scripts('jquery.js');
        $this->layout->add_scripts('bootstrap-collapse.js');
        $this->layout->add_scripts('bootstrap-alert.js');
        $this->layout->add_scripts('bootstrap-transition.js');
        $this->layout->add_scripts('bootstrap-dropdown.js');

        //$this->output->enable_profiler(TRUE);
    }

    public function index()
    {
        // Массив охранников
        $applicants = $this->applicants_model->get_applicants();

        // Количество поверенных в БД
        $applicants_count = count($applicants);

        $this->layout->add_content(array('applicants' => $applicants,
                                     'applicants_count' => $applicants_count,
                                     'role_id' => $this->role_id,   
                                  ));

        $this->layout->set_page_title('Список заявників');

        $this->layout->add_scripts('bootstrap-modal.js');
        $this->layout->add_scripts('bootstrap-transition.js');
        $this->layout->add_scripts('applicants.js');

        $this->layout->view_admin('Applicants/index');
    }

    public function get_login_by_id($applicant_id = 0)
    {
        $login = $this->applicants_model->get_login_by_id($applicant_id);
        if (empty($login) === FALSE)
            echo $login->login;
    }
    
    public function is_chancelerry($applicant_id = 0)
    {
        $is_chancelerry = $this->applicants_model->get($applicant_id);
        $is_chancelerry = (int)$is_chancelerry->role_id;
        if ($is_chancelerry === 5)
            echo 'yes';
        else
            echo 'no';
    }

    /**
     * Обработчик AJAX-запроса на добавление нового заявителя
     */
    public function add()
    { 
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'add_form_applicant_name',
                'label'   => 'ПІБ особи',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'add_form_applicant_login',
                'label'   => 'Логін особи',
                'rules'   => 'required|is_unique[users.login]'
            ),
            array(
                'field'   => 'add_form_applicant_password',
                'label'   => 'Пароль особи',
                'rules'   => 'required|min_length[5]'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Данные пользователя с формы
            $data['name'] = trim($this->input->post('add_form_applicant_name'));
            $data['login'] = mb_strtolower($this->input->post('add_form_applicant_login'));
            $data['password'] = md5(mb_strtolower($this->input->post('add_form_applicant_password')));
            if (FALSE !== $this->input->post('add_form_applicant_chancellery'))
                 $data['role_id'] = 5;
            else
                 $data['role_id'] = 4;
            // Добавление в БД
            $last_added_applicant_id = $this->applicants_model
                                            ->add_applicant($data);

            // Выдача сообщения об успехе
            $message = 'Було успішно додано нову особу!';

            // Кудабудем переадресовывать после оповещения
            $location = base_url('admin/applicants');

            $this->session->set_flashdata(array('message' => $message));

            echo "<script>location = \"{$location}\";</script>";
        }
        else
            // Выдача ошибки
            echo $this->get_error_html(validation_errors());

    }

    /**
     * Обработчик AJAX-запроса на редактирование данных пользователя
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
                'field'   => 'edit_form_applicant_name',
                'label'   => 'ПІБ Особи',
                'rules'   => 'required'
            ),

            array(
                'field'   => 'edit_form_applicant_id',
                'label'   => 'id administrator',
                'rules'   => 'required|integer'
            ),

            array(
                'field'   => 'edit_form_applicant_password',
                'label'   => 'Новий пароль',
                'rules'   => 'valid_password[4]'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Данные с формы
            $applicant_id = (int)$this->input->post('edit_form_applicant_id');
            $data['name'] = trim($this->input->post('edit_form_applicant_name'));
            $password = $this->input->post('edit_form_applicant_password');
            if ($password !== '')
                $data['password'] = md5(mb_strtolower($password));
            if (FALSE !== $this->input->post('edit_form_applicant_chancellery'))
                $data['role_id'] = 5;
            else
                $data['role_id'] = 4;

            // Редактирование данных в БД
            $this->applicants_model->edit_applicant($applicant_id, $data);

            $message = 'Дані користувача було змінено!';

            // Куда будем переадресовывать после оповещения
            $location = base_url('admin/applicants');

            $this->session->set_flashdata(array('message' => $message));

            echo "<script>location = \"{$location}\";</script>";
        }
        else
            // Выдача ошибки
            echo $this->get_error_html(validation_errors());
    }

    public function delete($applicant_id = 0)
    {
        if ($this->role_id !== 1)
            show_error ('Недостатньо прав!');
        
        // Защита от дурака
        $applicant_id = (int) $applicant_id;
        $login = $this->applicants_model->get_login_by_id($applicant_id);
        if (($applicant_id === 0) or empty($login) === TRUE)
            show_error('Такої сторінки не існує!');

        // Удаление
        $this->applicants_model->delete($applicant_id);

        // Сообщение об успехе
        $this->session->set_flashdata(array('message' => 'Видалення особи успішно здійснено!'));

        // Отображение страницы с пат. поверенными
        redirect('admin/applicants');
    }
    
    /**
     * Html-код с ошибкой
     *
     * @param string $error
     * @return string 
     */
    private function get_error_html($error)
    {
        return '<div class="control-group" id="errors">
                        <div class="span alert alert-danger" style="margin-left: 0">
                            <a class="close" data-dismiss="alert" onclick="$(\'#errors\').hide();">×</a>' .
                            $error .
                        '</div>
                   </div>';
    }
}