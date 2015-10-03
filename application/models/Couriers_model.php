<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     * Couriers_model
     *
     * Модель для работы со списком курьеров
     *
     * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
     * @version 1.0
     */
class Couriers_model extends CI_Model
{
    /**
     * Таблица БД
     * @var string
     */
    private $table = 'couriers';

    /**
     * Первичный ключ таблицы
     * @var string
     */
    private $table_id = 'courier_id';

    /**
     *  Конструктор класса
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Получить данные курьера
     *
     * @param $patent_agent_id id курьера
     * @return mixed Объект-результат запроса или пустой массив
     */
    public function get_courier($courier_id)
    {
        return $this->db->get_where($this->table, 
                                 array($this->table_id => $courier_id))
                      ->row();
    }

    /**
     * Извлекает всех курьеров из БД
     *
     * @return array массив объектов-результатов выполнения запроса
     */
    public function get_couriers()
    {
        return $this->db->order_by('name', 'ASC')
            ->get_where($this->table, array('deleted' => 0))
            ->result();
    }

    /**
     * Извлекает патентных поверненных, которые уже посещали Институт
     *
     * @return array массив объектов-результатов выполнения запроса
     */
    public function get_couriers_report()
    {
        $query = 'SELECT DISTINCT c.*
                  FROM couriers AS c
                  INNER JOIN requests AS r
                  ON c.courier_id = r.courier_id 
                  WHERE c.deleted = 0
                  ORDER BY c.name';
        return $this->db->query($query)
                        ->result();
    }

    /**
     * Получить число курьеров в БД
     *
     * @return int число патентных поверненных в БД
     */
    public function get_patent_agents_count()
    {
        return $this->db->select('COUNT(*) AS count')
                        ->get($this->table)
                        ->row()
                        ->count;
    }

    /**
     * Получить данные курьера
     *
     * @param $courier_id id курьера
     * @return mixed Объект-результат запроса или пустой массив
     */
    public function get_patent_agent($courier_id)
    {
        return $this->db->get_where($this->table, 
                                 array($this->table_id => $courier_id))
                      ->row();
    }

    /**
     * Редактирование данных курьера
     *
     * @param $courier_id id курьера
     * @param $data Массив данных
     */
    public function edit_courier($courier_id, $data)
    {
        $this->db->where($this->table_id, $courier_id);
        $this->db->update($this->table, $data);
    }

    /**
     * Проверить есть ли БД пат. курьер с таким ID
     *
     * @param $patent_agent_id id патентного поверенного
     * @return bool результат проверки наличия в БД курьера
     */
    public function is_courier_exist($courier_id)
    {
        // Условие в запросе (WHERE patent_agent_id = $courier_id AND deleted = 0)
        $where = array($this->table_id => $courier_id, 'deleted' => 0);

        // Запрос в БД
        $result = $this->db->where($where)
                         ->get($this->table)
                         ->row();

        // Если что-то найдено, то возвращаем TRUE
        if (empty($result) === FALSE)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Удаление курьера
     *
     * @param $patent_agent_id id курьера
     */
    public function delete($courier_id)
    {
        $this->db->where($this->table_id, $courier_id);
        $this->db->update($this->table, array('deleted' => 1));
    }

    /**
     * Добавление в БД нового курьера
     *
     * @param $data данные курьера
     * @return integer id добавленной записи
     */
    public function add($data)
    {
        // Сначала найдём старОго человека, если он был удален и отметим её как не удалённого
        $result = $this->db->where('deleted', 1)
            ->where('name', $data['name'])
            ->get($this->table)
            ->row();

        if (empty($result) === FALSE)
        {
            $old_courier_id = $result->courier_id;
            $this->db->where($this->table_id, $old_courier_id);
            $this->db->update($this->table, array('deleted' => 0));

            return $old_courier_id;
        }
        else
        {
            // Запрос в БД
            $this->db->insert($this->table, $data);

            // Только что добавленный ID
            return $this->db->insert_id();
        }
    }

    /**
     * ID последнего кабиета, на который оформляли заявку на курьера.
     *
     * @param $patent_agent_id id курьера
     */
    public function get_last_room_id($courier_id)
    {
        $query = 'SELECT r.room_id, c.`name`
                     FROM requests AS req
                  LEFT JOIN rooms as r
                     ON r.room_id = req.room_id
                  LEFT JOIN couriers AS c
                     ON c.courier_id = req.courier_id
                  WHERE c.courier_id = '. $this->db->escape($courier_id) .
                   ' ORDER BY req.request_date DESC
                     LIMIT 1';

        return $this->db->query($query)
                      ->row();
    }



    /**
     * Значение документа в последней заявке конкретного курьера
     *
     * @param $patent_agent_id id курьера
     */
    public function get_last_document($courier_id)
    {
        $query = 'SELECT d.*
                    FROM requests AS req
                  LEFT JOIN documents as d
                    ON d.document_id = req.document_id
                  LEFT JOIN couriers AS c
                    ON c.courier_id = req.courier_id
                  WHERE c.courier_id = '. $this->db->escape($courier_id) .
                 ' ORDER BY req.request_date DESC
                  LIMIT 1';

        return $this->db->query($query)
                      ->row();
    }



    /**
     * Значение id заявителя в последней заявке конкретного курьера
     *
     * @param $patent_agent_id id курьера
     */
    public function get_last_applicant_id($courier_id)
    {
        $query = 'SELECT u.user_id, u.`name`, c.`name`
                    FROM requests AS req
                  LEFT JOIN users as u
                    ON u.user_id = req.applicant_id
                  LEFT JOIN couriers AS c
                    ON c.courier_id = req.courier_id
                  WHERE c.courier_id = ' . $this->db->escape($courier_id) .
                 ' ORDER BY req.request_date DESC
                  LIMIT 1';

        return $this->db->query($query)
                        ->row();
    }
    
    /**
     * Значение id фото в последней заявке конкретного курьера
     *
     * @param $courier_id id курьера
     */
    public function get_last_photo_id($courier_id)
    {
        $result = $this->db->select('photo_id')
                         ->order_by('request_date', 'DESC')
                         ->get_where('requests', 
                                    array('courier_id' => $courier_id), 
                                    1)
                         ->row();
        
        return $result;
    }
}