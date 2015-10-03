<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Update_long_requests_lib
{
    /*
     * 
     * Конструктор класса
     * 
     */
    public function __construct()
    {
        $this->ci =& get_instance();
        
        // Модель для работы с отложенными заявками
        $this->ci->load->model('long_requests_model');
        
        // Дата последнего обновления
        $update = $this->ci->long_requests_model->get_last_update();
        
        if (empty($update) === FALSE)
        {
            // Текущая дата
            $cur_date = date('Y-m-d h:m:s');
            
            $update = date_create($update->update_date); 
            $cur_date = date_create($cur_date);
            
            // Если текущая дата больше даті последнего обновления, 
            // то обновляем список отложенных заявок
            if ($this->date_diff($update, $cur_date) < 0)
                 $this->ci->long_requests_model->update_long_requests();
        }
        else // Если еще не обновляли
            $this->ci->long_requests_model->update_long_requests();
    }
    
    /**
     * Разница в днях между двумя датами (работает и в PHP 5.2)
     * 
     * @param DateTime $date1
     * @param DateTime $date2
     * 
     * @return int разница в днях между двумя датами 
     */
    private function date_diff($date1, $date2)
    {        
        $date1 = date_format($date1, 'U');
        $date2 = date_format($date2, 'U');
        
        $interval = round(($date1 - $date2) / (60*60*24)); 
        
        return (int)$interval;        
    }
}  