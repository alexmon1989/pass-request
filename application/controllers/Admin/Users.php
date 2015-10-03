<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Users
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления списком начальства
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Users extends CI_Controller
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
        $this->load->model('users_model');

        // Стили
        $this->layout->add_styles('bootstrap.css');
        $this->layout->add_styles('body.css');
        $this->layout->add_styles('bootstrap-responsive.css');

        // JS
        $this->layout->add_scripts('jquery.js');
        $this->layout->add_scripts('bootstrap-collapse.js');
        $this->layout->add_scripts('bootstrap-alert.js');
        $this->layout->add_scripts('bootstrap-dropdown.js');

        //$this->output->enable_profiler(TRUE);
    }

    public function index()
    {
        // Массив патентных поверенных
        $users = $this->users_model->get_users();

        // Количество поверенных в БД
        $users_count = count($users);

        $this->layout->add_content(array('users' => $users,
                                         'users_count' => $users_count,
        ));

        $this->layout->set_page_title('Список начальників');

        $this->layout->add_scripts('bootstrap-modal.js');
        $this->layout->add_scripts('bootstrap-transition.js');

        $js = 'function SendAddData () {
                      var str = $("#add_form").serialize();
                      $.post("' . base_url('admin/users/add'). '", str, function(data) {
                            $("#add_form_info").html(data);
                          });
                   }
               function SendEditData () {
                      var str = $("#edit_form").serialize();
                      $.post("' . base_url('admin/users/edit'). '", str, function(data) {
                            $("#edit_form_info").html(data);
                          });
                   }
               function FillEditForm(id){
                      $("#errors").hide();
                      $("#edit_form_user_password").val("");
                      $("#login").val("");

                      id = parseInt(id.substring(5, id.length));
                      $("#edit_form_user_id").val(id);

                      $.post("' . base_url('admin/users/get_login_by_id'). '/" + id, function(data) {
                            $("#login").text(data);
                          });

                      var name = $("#user_name_" + id).text();
                      $("#edit_form_user_name").val($.trim(name));
               }
               function FillAddForm(){
                      $("#add_form").trigger("reset");
               }';
        $this->layout->add_js_code($js, FALSE);

        $this->layout->view_admin('Users/index');
    }

    public function get_login_by_id($user_id = 0)
    {
        $login = $this->users_model->get_login_by_id($user_id);
        if (empty($login) === FALSE)
            echo $login->login;
    }

    /**
     * Обработчик AJAX-запроса на добавление нового пользователя
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
                'field'   => 'add_form_user_name',
                'label'   => 'ПІБ Особи',
                'rules'   => 'required'
            ),
            array(
                'field'   => 'add_form_user_login',
                'label'   => 'Логін особи',
                'rules'   => 'required|is_unique[administrators.login]|is_unique[security.login]|is_unique[users.login]'
            ),
            array(
                'field'   => 'add_form_user_password',
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
            $data['name'] = trim($this->input->post('add_form_user_name'));
            $data['login'] = mb_strtolower($this->input->post('add_form_user_login'));
            $data['password'] = md5(mb_strtolower($this->input->post('add_form_user_password')));

            // Добавление в БД
            $last_added_user_id = $this->users_model->add_user($data);

            // Выдача сообщения об успехе
            $message = 'Було успішно додано нову особу!';

            // Кудабудем переадресовывать после оповещения
            $location = base_url('admin/users');

            $this->session->set_flashdata(array('message' => $message));

            echo "<script>location = \"{$location}\";</script>";
        }
        else
            // Выдача ошибки
            echo  '<div class="control-group" id="errors">
                        <div class="span alert alert-danger" style="margin-left: 0">
                            <a class="close" data-dismiss="alert" onclick="$(\'#errors\').hide();">×</a>' .
                            validation_errors() .
                        '</div>
                   </div>';

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
                'field'   => 'edit_form_user_name',
                'label'   => 'ПІБ Особи',
                'rules'   => 'required'
            ),

            array(
                'field'   => 'edit_form_user_id',
                'label'   => 'id патентного повіренного',
                'rules'   => 'required|integer'
            ),

            array(
                'field'   => 'edit_form_user_password',
                'label'   => 'Новий пароль',
                'rules'   => 'valid_password[5]'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Данные с формы
            $user_id = (int)$this->input->post('edit_form_user_id');
            $data['name'] = trim($this->input->post('edit_form_user_name'));
            $password = $this->input->post('edit_form_user_password');
            if ($password !== '')
                $data['password'] = md5(mb_strtolower($password));

            // Редактирование данных в БД
            $this->users_model->edit_user($user_id, $data);

            $message = 'Дані користувача було змінено!';

            echo  "<script>
                        $('#edit_user_modal').modal('hide');
                        $('#information').show();
                        $('#message').text(\"$message\");
                        $('#message').show();

                        $('#user_name_{$user_id}').html('<b>{$data['name']} </b> <span class=\"caret\"></span>');

                        alert('$message');
                   </script>";
        }
        else
            // Выдача ошибки
            echo  '<div class="control-group" id="errors">
                        <div class="span alert alert-danger" style="margin-left: 0">
                            <a class="close" data-dismiss="alert" onclick="$(\'#errors\').hide();">×</a>' .
                            validation_errors() .
                        '</div>
                   </div>';
    }

    public function delete($user_id = 0)
    {
        if ($this->role_id !== 1)
            show_error ('Недостатньо прав!');
        
        // Защита от дурака
        $user_id = (int) $user_id;
        if (($user_id === 0) or ($this->users_model->is_user_exist($user_id) === TRUE))
            show_error('Такої сторінки не існує!');

        // Удаление
        $this->users_model->delete_user($user_id);

        // Сообщение об успехе
        $this->session->set_flashdata(array('message' => 'Видалення особи успішно здійснено!'));

        // Отображение страницы с пат. поверенными
        redirect('admin/users');
    }
}
