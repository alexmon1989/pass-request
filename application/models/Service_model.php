<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Service_model
 *
 * Модель для работы со списком сторонних фирм и их сотрудников
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */
class Service_model extends CI_Model
{
    /**
     * Таблица БД со списком сторонних фирм
     * @var string
     */
    private $table_services = 'services';

    /**
     * Первичный ключ таблицы со списком сторонних фирм
     * @var string
     */
    private $table_services_id = 'service_id';

    /**
     * Таблица БД со списком сотрудников сторонних фирм
     * @var string
     */
    private $table_services_employees = 'services_employees';

    /**
     * Первичный ключ таблицы со списком сотрудников сторонних фирм
     * @var string
     */
    private $table_services_employees_id = 'service_employee_id';

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Получение списка сотрудников сторонних фирм, которые уже посещали Институт
     *
     * @return array массив объектов-результатов запроса
     */
    public function get_services_employees_report()
    {
        $query = 'SELECT DISTINCT s_e.*
                  FROM services_employees AS s_e
                  INNER JOIN requests AS r
                  ON r.service_employee_id = s_e.service_employee_id
                  WHERE s_e.deleted = 0
                  ORDER BY s_e.name';
        return $this->db->query($query)
                        ->result();
    }

    /**
     * Получение списка фирм из БД
     *
     * @return array массив объектов-результатов запроса
     */
    public function get_services()
    {
        return $this->db->select($this->table_services_id . ', name AS service_name')
            ->where('deleted', 0)
            ->get($this->table_services)
            ->result();
    }

    public function get_services_employees()
    {
        return $this->db->order_by('name', 'ASC')
                        ->get_where($this->table_services_employees, array('deleted' => 0))
                        ->result();
    }

    /**
     * Получение списка сотрудников конкретной фирмы
     *
     * @param $service_id id фирмы
     *
     * @return array массив объектов-результатов запроса
     */
    public function get_service_employees($service_id)
    {
        // Запрос
       $query = 'SELECT s_e.*, p.number AS pass_number,
                     (SELECT room_id
                       FROM requests
                       WHERE service_employee_id = s_e.service_employee_id
                       ORDER BY request_id DESC
                       LIMIT 1) AS last_room_id,
                     (SELECT document_id
                       FROM requests
                       WHERE service_employee_id = s_e.service_employee_id
                       ORDER BY request_id DESC
                       LIMIT 1) AS document_id,
                     (SELECT applicant_id
                       FROM requests
                       WHERE service_employee_id = s_e.service_employee_id
                       ORDER BY request_id DESC
                       LIMIT 1) AS applicant_id
                 FROM services_employees AS s_e
                 LEFT JOIN passes AS p
                   ON p.pass_id = s_e.pass_id
                  WHERE s_e.deleted = 0 AND s_e.service_id = ' . $this->db->escape($service_id) .
               ' ORDER BY s_e.name ASC';

        // Выполняем запрос
        $result = $this->db->query($query);

        return $result->result();
    }

    /**
     * Редактрование сотрудника сторонней организации
     *
     * @param $service_employee_id id сотрудника сторонней организации
     * @param $data массив с данными для обновления
     */
    public function edit_employee($service_employee_id, $data)
    {
        $this->db->where($this->table_services_employees_id, $service_employee_id)
                 ->update($this->table_services_employees, $data);
    }

    /**
     * Проверка существует ли сотрудник в БД
     *
     * @param $service_employee_id id сотрудника в БД
     * @return bool
     */
    public function is_employee_exist($service_employee_id)
    {
        // Запрос в БД
        $result = $this->db->where('deleted', 0)
                           ->where($this->table_services_employees_id, $service_employee_id)
                           ->get($this->table_services_employees)
                           ->result();
        
        // Если что-то вернулось - то значит такой сотрудник существует в БД
        if (empty($result) === TRUE)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * Удаление сотрудника
     * (на самом деле в поле deleted заносится 1 - это и есть показатель удаления)
     *
     * @param $employee_id id сотрудника
     */
    public function delete_employee($employee_id)
    {
        $this->db->where($this->table_services_employees_id, $employee_id);
        $this->db->update($this->table_services_employees, array('deleted' => 1));
    }

    /**
     * Удаление сторонней фирмы
     * (на самом деле в поле deleted заносится 1 - это и есть показатель удаления)
     *
     * @param $service_id id сторонней фирмы
     */
    public function delete_service($service_id)
    {
        // Удаление из таблицы 'services'
        $this->db->where($this->table_services_id, $service_id);
        $this->db->update($this->table_services, array('deleted' => 1));

        // Удаление из таблицы 'services_employees' всех сотрудников даной фирмы
        $this->db->where($this->table_services_id, $service_id);
        $this->db->update($this->table_services_employees, array('deleted' => 1));

    }

    /**
     * Проверка существует ли такая сторонняя фирма
     *
     * @param $service_id id сторонней фирмы
     * @return bool результат выборки из БД
     */
    public function is_service_exist($service_id)
    {
        // Запрос в БД
        $result = $this->db->where('deleted', 0)
                           ->where($this->table_services_id, $service_id)
                           ->get($this->table_services)
                           ->result();

        // Если что-то вернулось - то значит такой сотрудник существует в БД
        if (empty($result) === TRUE)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * Редактрование сторонней организации
     *
     * @param $service_id id сторонней организации
     * @param $data массив с данными для обновления
     */
    public function edit_service($service_id, $data)
    {
        $this->db->where($this->table_services_id, $service_id)
                 ->update($this->table_services, $data);
    }

    /**
     * Добавление новой организации в БД
     *
     * @param $name название организации
     *
     * @return int значение id добавленной организации
     */
    public function add_service($name)
    {
        // Сначала найдём старую организацию, если она была удалена и отметим её как не удалённую
        $result = $this->db->where('deleted', 1)
                           ->where('name', $name)
                           ->get($this->table_services)
                           ->row();

        if (empty($result) === FALSE)
        {
            $old_service_id = $result->service_id;
            $this->db->where($this->table_services_id, $old_service_id);
            $this->db->update($this->table_services, array('deleted' => 0));

            // Также отмечаем сотрудников этой фирмы как не удалённых
            $this->db->where($this->table_services_id, $old_service_id);
            $this->db->update($this->table_services_employees, array('deleted' => 0));

            return $old_service_id;
        }
        else
        {
            // Вставляем в БД новую организацию
            $this->db->insert($this->table_services, array('name' => $name));

            return $this->db->insert_id();
        }
    }

    /**
     * Добавление сотрудника стороннней организации в БД
     *
     * @param $data массив с данніми для добавления
     *
     * @return int значение id добавленной особы
     */
    public function add_employee($data)
    {
        // Вставляем в БД новую организацию
        $this->db->insert($this->table_services_employees, $data);

        return $this->db->insert_id();
    }
    
    /**
     * ID последнего кабиета, на который оформляли заявку
     *
     * @param $patent_agent_id id сотрудника
     */
    public function get_last_room_id($service_employee_id)
    {
        $query = 'SELECT r.room_id, s_a.`name`
                     FROM requests AS req
                  LEFT JOIN rooms as r
                     ON r.room_id = req.room_id
                  LEFT JOIN services_employees AS s_a
                     ON s_a.service_employee_id = req.service_employee_id
                  WHERE s_a.service_employee_id = '. $this->db->escape($service_employee_id) .
                   ' ORDER BY req.request_date DESC
                     LIMIT 1';

        return $this->db->query($query)
                        ->row();
    }



    /**
     * Значение документа в последней заявке конкретного сотрудника стор. формы
     *
     * @param $patent_agent_id id сотрудника
     */
    public function get_last_document($service_employee_id)
    {
        $query = 'SELECT d.*
                    FROM requests AS req
                  LEFT JOIN documents as d
                    ON d.document_id = req.document_id
                  LEFT JOIN services_employees AS s_a
                     ON s_a.service_employee_id = req.service_employee_id
                  WHERE s_a.service_employee_id = '. $this->db->escape($service_employee_id) .
                 ' ORDER BY req.request_date DESC
                  LIMIT 1';

        return $this->db->query($query)
                        ->row();
    }



    /**
     * Значение id заявителя в последней заявке конкретного сотрудника
     *
     * @param $patent_agent_id id сотрудника
     */
    public function get_last_applicant_id($service_employee_id)
    {
        $query = 'SELECT u.user_id, u.`name`, s_a.`name`
                    FROM requests AS req
                  LEFT JOIN users as u
                    ON u.user_id = req.applicant_id
                  LEFT JOIN services_employees AS s_a
                     ON s_a.service_employee_id = req.service_employee_id
                  WHERE s_a.service_employee_id = ' . $this->db->escape($service_employee_id) .
                 ' ORDER BY req.request_date DESC
                  LIMIT 1';

        return $this->db->query($query)
                        ->row();
    }
    
    /**
     * Получение данных сотрудника сторонней организации
     *
     * @param int $service_employee_id ID сотрудника сторонней организации
     * 
     * @return object Объект-сотрудник сторонней орг-ции 
     */
    public function get_service_employee($service_employee_id)
    {
        $result = $this->db
                      ->get_where($this->table_services_employees, 
                                 array($this->table_services_employees_id => $service_employee_id))
                      ->row();
        return $result;
    }
    
    /**
     * Значение id фото в последней заявке
     *
     * @param $service_employee_id id сотрудника сторонней организации
     */
    public function get_last_photo_id($service_employee_id)
    {
        $result = $this->db->select('photo_id')
                         ->order_by('request_date', 'DESC')
                         ->get_where('requests', 
                                    array('service_employee_id' => $service_employee_id), 
                                    1)
                         ->row();
        
        return $result;
    }
}