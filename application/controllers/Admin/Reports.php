<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Report
 *
 * Класс (контроллер),
 * который отвечает за формирование отчётов
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Reports extends CI_Controller
{
    // Конструктор класса
    public function __construct()
    {
        parent::__construct();

        // Библиотека авторизации
        $this->load->library('Auth_lib');

        // Авторизирован ли пользователь
        if ($this->auth_lib->is_user_logged() === FALSE)
            redirect('auth/login');
        
        // Проверка роли авторизированного пользователя для ограничения доступа
        $role_id = $this->auth_lib->get_user_role_id_from_sess();
        if (!in_array($role_id, array(1,3)))
            show_error ('Доступ заборонено!');

        // Библиотека вывода
        $this->load->library('Layout');

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
        $this->layout->add_scripts('ui.datepicker.js');
        $this->layout->add_scripts('ui-datepicker-uk.js');
        $this->layout->add_scripts('reports.js');

        //$this->output->enable_profiler(TRUE);
    }

    public function index()
    {
        // Список заявителей
        $this->load->model('users_model');
        $applicants = $this->users_model->get_applicants();

        // Список кабинетов
        $this->load->model('rooms_model');
        $rooms = $this->rooms_model->get_rooms_list();

        // Список пропусков
        $this->load->model('passes_model');
        $passes = $this->passes_model->get_passes();

        // Список посетителей
        $this->load->model('visitors_model');
        $visitors = $this->visitors_model->get_visitors_report();

        $this->load->model('service_model');
        $services_employees = $this->service_model->get_services_employees_report();

        $this->load->model('patent_agents_model');
        $patent_agents = $this->patent_agents_model->get_patent_agents_report();
        
        $this->load->model('couriers_model');
        $couriers = $this->couriers_model->get_couriers_report();

        // Массив для передачи в вид
        $data = array('applicants' => $applicants,
                      'rooms' => $rooms,
                      'passes' => $passes,
                      'visitors' => $visitors,
                      'patent_agents' => $patent_agents,
                      'couriers' => $couriers,
                      'services_employees' => $services_employees
                      ,);
        $this->layout->add_content($data);

        // Название страницы
        $this->layout->set_page_title('Генерація звітів');

        $this->layout->view_admin('Reports/index');
        //var_dump($_COOKIE);
    }
    
    /**
     * Обработчик AJAX-запроса на экспорт отчёта в ексель 
     */
    public function generate_report_excel()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'datepicker_from',
                'label'   => 'Дата з',
                'rules'   => 'required|valid_date'
            ),

            array(
                'field'   => 'datepicker_to',
                'label'   => 'Дата по',
                'rules'   => 'required|valid_date'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            $report_data = $this->get_report_array();

            if (empty($report_data) === FALSE)
            {
                // Библиотека для формирования Excel-файла
                $this->load->library('excel_lib', array('report.xls'));

                if($this->excel_lib==false)
                    show_error($this->excel_lib->error);

                $arr = array('<b>Номер заявки</b>', '<b>Дата видачі</b>', '<b>Дата здачі</b>',
                            '<b>ПІБ заявника</b>', '<b>ПІБ відвідувача</b>', 
                            '<b>Номер документа</b>', '<b>Тип документа</b>', 
                            '<b>Номер кабінета', '<b>Номер пропуска</b>', '<b>Підстава</b>');
                $this->excel_lib->writeLine($arr);
                foreach ($report_data as $value)
                {
                    if (($value->visitorname) === '')
                        if (isset($value->patent_agent))
                            $value->visitorname = $value->patent_agent;
                        elseif (isset($value->service_employee))
                            $value->visitorname = $value->service_employee;
                        elseif (isset($value->courier))
                            $value->visitorname = $value->courier;
                        else
                            $value->visitorname = 'Не вказано!';
                        
                    if ($value->reason !== NULL)    
                    {
                        $value->reason = json_decode($value->reason);
                        $value->reason = $value->reason->reason;
                    }
                    else
                        $value->reason = '';

                    $arr = array($value->request_number, $value->issue_date, 
                                $value->pass_date, $value->username,
                                $value->visitorname, $value->document_series . $value->document_number,
                                $value->document_type, $value->room_number,
                                $value->pass_number, $value->reason);
                    $this->excel_lib->writeLine($arr);
                }
                $this->excel_lib->close();

                $file = base_url('report.xls');

                echo "<script>location = \"{$file}\";</script>";
            }
            else
                echo $this->set_error('Цим умовам пошуку не задовольняє жодна заявка! Звіт не сформовано!');
        }
        else
            echo $this->set_error(validation_errors());
    }
    
    /**
     * Получение данных отчёта из БД
     * 
     * @return array Массив с данными отчёта 
     */
    private function get_report_array()
    {
        $result = array();
        
        $date_from = $this->input->post('datepicker_from') . ' 00:00:00';
        $date_to = $this->input->post('datepicker_to') . ' 23:59:59';
        $applicant_id = (int) $this->input->post('applicant_name');
        $room_id = (int) $this->input->post('room_number');
        $visitor_id = (int) $this->input->post('visitor_name');
        $patent_agent_id = (int) $this->input->post('patent_agent_name');
        $courier_id = (int) $this->input->post('courier_name');
        $service_employee_id = (int) $this->input->post('service_employee_name');
        $pass_id = (int) $this->input->post('pass_number');

        // Строим условие для запроса в БД

        // Форматируем дату для запроса в БД
        $date_from = date_create($date_from);
        $date_from = date_format($date_from, 'Y-m-d H:i:s');

        $date_to = date_create($date_to);
        $date_to = date_format($date_to, 'Y-m-d H:i:s');

        //$date_from = DateTime::createFromFormat('d.m.Y H:i:s', $date_from);
        //$date_to = DateTime::createFromFormat('d.m.Y H:i:s', $date_to);
        $where = ' WHERE (r.issue_date BETWEEN ' . $this->db->escape($date_from) .
                                        ' AND ' . $this->db->escape($date_to) . ')';

        if ($applicant_id !== 0)
            $where .= 'AND u.user_id = '. $this->db->escape($applicant_id) . ' ';

        if ($room_id !== 0)
            $where .= 'AND rm.room_id = ' . $this->db->escape($room_id) . ' ';

        if ($visitor_id !== 0)
        {            
            $this->load->model('visitors_model');
            $visitor = $this->visitors_model->get_visitor_by_id($visitor_id);
            
            $where .= ' AND v.last_name = ' . $this->db->escape($visitor->last_name) . ' ';
            $where .= 'AND v.first_name = ' . $this->db->escape($visitor->first_name) . ' ';
            $where .= 'AND v.middle_name = ' . $this->db->escape($visitor->middle_name) . ' ';
        }

        if ($pass_id !== 0)
            $where .= 'AND p.pass_id = ' . $this->db->escape($pass_id) . ' ';

        if ($patent_agent_id !== 0)
            $where .= 'AND p_a.patent_agent_id = ' . $this->db->escape($patent_agent_id) . ' ';
        
        if ($courier_id !== 0)
            $where .= 'AND c.courier_id = ' . $this->db->escape($courier_id) . ' ';

        if ($service_employee_id !== 0)
            $where .= 'AND s_e.service_employee_id = ' . $this->db->escape($service_employee_id) . ' ';

        // Модель для работы с заявками
        $this->load->model('requests_model');

        // Делаем запрос в БД
        $result = $this->requests_model->get_requests_report(0, 9999999999, $where);

        return $result;
    }
    
    public function generate_report()
    {
        // Защита от дурака
        if (empty($_POST) === TRUE)
            show_error('Дані для додання в БД відсутні');

        // Загрузка библиотеки валидации
        $this->load->library('form_validation');

        // Массив правил валидации
        $config = array(
            array(
                'field'   => 'datepicker_from',
                'label'   => 'Дата з',
                'rules'   => 'required|valid_date'
            ),

            array(
                'field'   => 'datepicker_to',
                'label'   => 'Дата по',
                'rules'   => 'required|valid_date'
            ),
        );

        // Применяем правила валидации
        $this->form_validation->set_rules($config);

        // Проверка корректности заполнения формы
        if ($this->form_validation->run() === TRUE)
        {
            $report_data = $this->get_report_array();

            if (empty($report_data) === FALSE)
            {
                //var_dump($report_data);
                // Генерируем HTML-код
                $html = '<table id="table_report" class="table table-striped" width="100%" style="font-size: 16px">';
                $html .= '  <thead>';
                $html .= '      <tr>';                
                $html .= '          <th>#</th>';                       
                $html .= '          <th>Номер заявки</th>';       
                $html .= '          <th>Дата видачі</th>';       
                $html .= '          <th>Дата здачі</th>';       
                $html .= '          <th>ПІБ заявника</th>'; 
                $html .= '          <th>ПІБ відвідувача</th>'; 
                $html .= '          <th>Тип документа</th>'; 
                $html .= '          <th>Номер документа</th>'; 
                $html .= '          <th>Номер кабінета</th>'; 
                $html .= '          <th>Номер пропуска</th>'; 
                $html .= '          <th>Підстава</th>';
                $html .= '      </tr>';
                $html .= '  </thead>';
                
                $html .= '  <tbody>';
                
                $count = 1;
                foreach ($report_data as $value)
                {
                    $html .= '<tr>';
                    $html .= '  <td>';
                    $html .=        $count;
                    $html .= '  </td>';
                    $html .= '  <td>';
                    $html .=        $value->request_number;
                    $html .= '  </td>';
                    $html .= '  <td>';
                    $html .=        $value->issue_date;
                    $html .= '  </td>';
                    $html .= '  <td>';
                    $html .=        $value->pass_date;
                    $html .= '  </td>';
                    $html .= '  <td>';
                    $html .=        $value->username;
                    $html .= '  </td>';
                    $html .= '  <td>';
                        if (trim($value->visitorname) <> '')
                            $html .=        $value->visitorname;                        
                        elseif (isset($value->patent_agent))
                            $html .=        $value->patent_agent;
                        elseif (isset($value->courier))
                            $html .=        $value->courier;
                        elseif (isset($value->service_employee))
                            $html .=        $value->service_employee;
                    $html .= '  </td>';
                    $html .= '  <td>';
                    $html .=        $value->document_type;
                    $html .= '  </td>';
                    $html .= '  <td>';
                    $html .=        $value->document_series . $value->document_number;
                    $html .= '  </td>';
                    $html .= '  <td>';
                    $html .=        $value->room_number;
                    $html .= '  </td>';
                    $html .= '  <td>';
                    $html .=        $value->pass_number;
                    $html .= '  </td>';
                    $html .= '  <td>';
                        if ($value->reason !== NULL)    
                        {
                            $value->reason = json_decode($value->reason);
                            $value->reason = $value->reason->reason;
                        }
                    $html .=        $value->reason;
                    $html .= '  </td>';
                    $html .= '</tr>';
                    
                    $count++;
                }
                
                $html .= '  </tbody>';
                
                $html .= '</table>';
                
                
                /**$this->excel_lib->writeLine($arr);
                foreach ($result as $value)
                {
                    if (($value->visitorname) === '')
                        if (isset($value->patent_agent))
                            $value->visitorname = $value->patent_agent;
                        elseif (isset($value->service_employee))
                            $value->visitorname = $value->service_employee;
                        else
                            $value->visitorname = 'Не вказано!';
                        
                    if ($value->reason !== NULL)    
                    {
                        $value->reason = json_decode($value->reason);
                        $value->reason = $value->reason->reason;
                    }
                    else
                        $value->reason = '';

                    $arr = array($value->request_number, $value->issue_date, 
                                $value->pass_date, $value->username,
                                $value->visitorname, $value->document,
                                $value->document_type, $value->room_number,
                                $value->pass_number, $value->reason);
                    $this->excel_lib->writeLine($arr);
                }
                $this->excel_lib->close();

                $file = base_url('report.xls');

                echo "<script>location = \"{$file}\";</script>";*/
                
                echo $html;
                
            }
            else
                echo $this->set_error('Цим умовам пошуку не задовольняє жодна заявка! Звіт не сформовано!');
        }
        else
            echo $this->set_error(validation_errors());
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