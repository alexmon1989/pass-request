<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Document_types
 *
 * Класс (контроллер),
 * который отвечает за отображение страниц управления списком типов документов
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Document_types extends CI_Controller
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
        $this->load->model('documents_type_model');

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
        // Массив охранников
        $document_types = $this->documents_type_model->get_document_types();

        // Количество поверенных в БД
        $document_types_count = count($document_types);

        $this->layout->add_content(array('document_types' => $document_types,
                                     'document_types_count' => $document_types_count,
                                     'role_id' => $this->role_id,
                                  ));

        $this->layout->set_page_title('Список типів документів');

        $this->layout->add_scripts('bootstrap-modal.js');
        $this->layout->add_scripts('bootstrap-transition.js');

        $js = 'function SendAddData () {
                      var str = $("#add_form").serialize();
                      $.post("' . base_url('admin/document_types/add'). '", str, function(data) {
                            $("#add_form_info").html(data);
                          });
                   }
               function SendEditData () {
                      var str = $("#edit_form").serialize();
                      $.post("' . base_url('admin/document_types/edit'). '", str, function(data) {
                            $("#edit_form_info").html(data);
                          });
                   }
               function FillEditForm(id){
                      $("#errors").hide();
                      $("#login").text("");

                      id = parseInt(id.substring(5, id.length));
                      $("#edit_doc_type_id").val(id);
                     
                      var name = $("#doc_type_" + id).text();
                      $("#edit_doc_type").val(name);
               }
               function FillAddForm(){
                      $("#add_form").trigger("reset");
               }';
        $this->layout->add_js_code($js, FALSE);

        $this->layout->view_admin('Document_types/index');
    }
  
    /**
     * Обработчик AJAX-запроса на добавление нового охранника
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
                'field'   => 'add_doc_type',
                'label'   => 'Тип документа',
                'rules'   => 'required'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Данные пользователя с формы
            $document_type = trim($this->input->post('add_doc_type'));

            // Добавление в БД
            $last_id = $this->documents_type_model->add($document_type);

            // Выдача сообщения об успехе
            $message = 'Було успішно додано новий ти документа!';

            // Кудабудем переадресовывать после оповещения
            $location = base_url('admin/document_types');

            $this->session->set_flashdata(array('message' => $message));

            echo "<script>location = \"{$location}\";</script>";
        }
        else
            // Выдача ошибки
            echo $this->set_error(validation_errors());

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
                'field'   => 'edit_doc_type',
                'label'   => 'Змінити тип на',
                'rules'   => 'required'
            ),

            array(
                'field'   => 'edit_doc_type_id',
                'label'   => 'id типа документа',
                'rules'   => 'required|integer'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            // Данные с формы
            $document_type_id = (int)$this->input->post('edit_doc_type_id');
            $new_document_type = trim($this->input->post('edit_doc_type'));

            // Редактирование данных в БД
            $this->documents_type_model->edit($document_type_id, 
                                          $new_document_type);

            $message = 'Дані типа документа було змінено!';

            echo  "<script>
                        $('#edit_document_type_modal').modal('hide');
                        $('#information').show();
                        $('#message').text(\"$message\");
                        $('#message').show();

                        $('#doc_type_{$document_type_id}').html('<b>{$new_document_type}</b> <span class=\"caret\"></span>');

                        alert('$message');
                   </script>";
        }
        else
            // Выдача ошибки
            echo  $this->set_error(validation_errors());
    }

    public function delete($document_type_id = 0)
    {
         if ($this->role_id !== 1)
            show_error ('Недостатньо прав!');
         
        // Защита от дурака
        $document_type_id = (int) $document_type_id;
        // Данные типа документа
        $result = $this->documents_type_model
                     ->get_document_type($document_type_id);
        if ($document_type_id === 0 or empty($result) === TRUE)
            show_error('Такої сторінки не існує!');

        // Удаление
        if ($this->documents_type_model->delete($document_type_id) === FALSE)
            // Сообщение о неудаче
            show_error('Видалення неможливе! Деякі документи пов\'язані із цим типом!');
        else
        {
            // Сообщение об успехе
            $this->session->set_flashdata(array('message' => 'Видалення успішно здійснено!'));
            redirect('admin/document_types');
        }

        
    }
    
    /**
     * Возвращает оформленный текст ошибки
     *
     * @param type $error текст ошибки
     * 
     * @return string Оформленный текст ошибки
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