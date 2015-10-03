<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     * Patent_agents_model
     *
     * Модель для работы со списком патентных поверенных
     *
     * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
     * @version 1.0
     */
class Patent_agents_model extends CI_Model
{
    /**
     * Таблица БД со списком патентных поверенных
     * @var string
     */
    private $table_patent_agents = 'patent_agents';

    /**
     * Первичный ключ таблицы со списком патентных поверенных
     * @var string
     */
    private $table_id = 'patent_agent_id';

    /**
     *  Конструктор класса
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Извлекает всех патентных поверненных из БД
     *
     * @return array массив объектов-результатов выполнения запроса
     */
    public function get_patent_agents()
    {
        return $this->db->order_by('name', 'ASC')
            ->get_where($this->table_patent_agents, array('deleted' => 0))
            ->result();
    }

    /**
     * Извлекает патентных поверненных, которые уже посещали Институт
     *
     * @return array массив объектов-результатов выполнения запроса
     */
    public function get_patent_agents_report()
    {
        $query = 'SELECT DISTINCT p_a.*
                  FROM patent_agents AS p_a
                  INNER JOIN requests AS r
                  ON r.patent_agent_id = p_a.patent_agent_id
                  WHERE p_a.deleted = 0
                  ORDER BY p_a.name';
        return $this->db->query($query)
                        ->result();
    }

    /**
     * Получить число патентных поверненных в БД
     *
     * @return int число патентных поверненных в БД
     */
    public function get_patent_agents_count()
    {
        return $this->db->select('COUNT(*) AS count')
                        ->get($this->table_patent_agents)
                        ->row()
                        ->count;
    }

    /**
     * Получить данные патентного поверенного
     *
     * @param $patent_agent_id id патентного поверенного
     * @return mixed Объект-результат запроса или пустой массив
     */
    public function get_patent_agent($patent_agent_id)
    {
        return $this->db->get_where($this->table_patent_agents, 
                                 array($this->table_id => $patent_agent_id))
                      ->row();
    }

    /**
     * Редактирование данных пат. поверенного
     *
     * @param $patent_agent_id id патентного поверенного
     * @param $data Массив данных
     */
    public function edit_patent_agent($patent_agent_id, $data)
    {
        $this->db->where($this->table_id, $patent_agent_id);
        $this->db->update($this->table_patent_agents, $data);
    }

    /**
     * Проверить есть ли БД пат. поверенный с таким ID
     *
     * @param $patent_agent_id id патентного поверенного
     * @return bool результат проверки наличия в БД патентного поверенного
     */
    public function is_patent_agent_exist($patent_agent_id)
    {
        // Условие в запросе (WHERE patent_agent_id = $patent_agent_id AND deleted = 0)
        $where = array($this->table_id => $patent_agent_id, 'deleted' => 0);

        // Запрос в БД
        $result = $this->db->where($where)
                         ->get($this->table_patent_agents)
                         ->row();

        // Если что-то найдено, то возвращаем TRUE
        if (empty($result) === FALSE)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Удаление патентного поверенного
     *
     * @param $patent_agent_id id патентного поверенного
     */
    public function delete($patent_agent_id)
    {
        $this->db->where($this->table_id, $patent_agent_id);
        $this->db->update($this->table_patent_agents, array('deleted' => 1));
    }

    /**
     * Добавление в БД нового патентного поверенного
     *
     * @param $data данные патентного поверенного
     * @return integer id добавленной записи
     */
    public function add($data)
    {
        // Сначала найдём старОго человека, если он был удален и отметим её как не удалённого
        $result = $this->db->where('deleted', 1)
            ->where('name', $data['name'])
            ->get($this->table_patent_agents)
            ->row();

        if (empty($result) === FALSE)
        {
            $old_patent_agent_id = $result->patent_agent_id;
            $this->db->where($this->table_id, $old_patent_agent_id);
            $this->db->update($this->table_patent_agents, array('deleted' => 0));

            return $old_patent_agent_id;
        }
        else
        {
            // Запрос в БД
            $this->db->insert($this->table_patent_agents, $data);

            // Только что добавленный ID
            return $this->db->insert_id();
        }
    }

    /**
     * ID последнего кабиета, на который оформляли заявку пат. пов.
     *
     * @param $patent_agent_id id патентного поверенного
     */
    public function get_last_room_id($patent_agent_id)
    {
        $query = 'SELECT r.room_id, p_a.`name`
                     FROM requests AS req
                  LEFT JOIN rooms as r
                     ON r.room_id = req.room_id
                  LEFT JOIN patent_agents AS p_a
                     ON p_a.patent_agent_id = req.patent_agent_id
                  WHERE p_a.patent_agent_id = '. $this->db->escape($patent_agent_id) .
                   ' ORDER BY req.request_date DESC
                     LIMIT 1';

        return $this->db->query($query)
                      ->row();
    }



    /**
     * Значение документа в последней заявке конкретного пат. пов.
     *
     * @param $patent_agent_id id патентного поверенного
     */
    public function get_last_document($patent_agent_id)
    {
        $query = 'SELECT d.*
                    FROM requests AS req
                  LEFT JOIN documents as d
                    ON d.document_id = req.document_id
                  LEFT JOIN patent_agents AS p_a
                    ON p_a.patent_agent_id = req.patent_agent_id
                  WHERE p_a.patent_agent_id = '. $this->db->escape($patent_agent_id) .
                 ' ORDER BY req.request_date DESC
                  LIMIT 1';

        return $this->db->query($query)
                      ->row();
    }



    /**
     * Значение id заявителя в последней заявке конкретного пат. пов.
     *
     * @param $patent_agent_id id патентного поверенного
     */
    public function get_last_applicant_id($patent_agent_id)
    {
        $query = 'SELECT u.user_id, u.`name`, p_a.`name`
                    FROM requests AS req
                  LEFT JOIN users as u
                    ON u.user_id = req.applicant_id
                  LEFT JOIN patent_agents AS p_a
                    ON p_a.patent_agent_id = req.patent_agent_id
                  WHERE p_a.patent_agent_id = ' . $this->db->escape($patent_agent_id) .
                 ' ORDER BY req.request_date DESC
                  LIMIT 1';

        return $this->db->query($query)
                        ->row();
    }
    
    /**
     * Значение id фото в последней заявке конкретного пат. пов.
     *
     * @param $patent_agent_id id патентного поверенного
     */
    public function get_last_photo_id($patent_agent_id)
    {
        $result = $this->db->select('photo_id')
                         ->order_by('request_date', 'DESC')
                         ->get_where('requests', 
                                    array('patent_agent_id' => $patent_agent_id), 
                                    1)
                         ->row();
        
        return $result;
    }
}