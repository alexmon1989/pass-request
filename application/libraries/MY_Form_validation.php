<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Расширенная библиотека валидации
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 */
class MY_Form_validation extends CI_Form_validation {


    /**
     * Проверка корректности даты
     *
     * @param $str строка с даитой
     * @return bool правильная ли дата
     */
    function valid_date($str)
    {
        $this->CI->form_validation->set_message('valid_date', 'Поле "<b>%s</b>" містить невірний формат дати');
        if(preg_match ("/^(0?[1-9]|[12][0-9]|3[01])[\/\.-](0?[1-9]|1[0-2])[\/\.-](19|20)\d{2}$/", $str)){
            $arr = explode(".", $str);    // разносим строку в массив
            $yyyy = $arr[2];            // год
            $mm = $arr[1];              // месяц
            $dd = $arr[0];              // день
            if(is_numeric($yyyy) && is_numeric($mm) && is_numeric($dd))
            {
                if(checkdate($mm, $dd, $yyyy))
                    return true;
                else
                    return FALSE;
            }
            else
                return FALSE;
        }
        else
            return false;
    }
    
    function today_or_future_date($str)
    {
        $this->CI->form_validation->set_message('today_or_future_date', 'Дата <b>"%s"</b> не може бути минулою');
                
        $my_date = date_create($str);
        
        $cur_date = date("d.m.Y");
        $cur_date = date_create($cur_date);
        if ($this->date_diff($my_date, $cur_date) < 0)
            return FALSE;
        else
            return TRUE;                
    }
    
    function today_or_past_date($str)
    {
        $this->CI->form_validation->set_message('today_or_past_date', 'Дата <b>"%s"</b> не може бути майбутньою');
                
        $my_date = date_create($str);
        
        $cur_date = date("d.m.Y");
        $cur_date = date_create($cur_date);
        if ($this->date_diff($my_date, $cur_date) > 0)
            return FALSE;
        else
            return TRUE;                
    }
    
    function bigger_date_than($str, $field)
    {
         $this->CI->form_validation->set_message('bigger_date_than', 'Дата <b>"%s"</b> має бути пізнішою або рівною <b>"%s"</b>');
        
        if ( ! isset($_POST[$field]))
		 {
            return FALSE;
		 }

		 $field = $_POST[$field];
       
        $my_date = date_create($str);
        $my_date2 = date_create($field);
        
        if ($this->date_diff($my_date, $my_date2) < 0)
            return FALSE;
        else
            return TRUE;           
    }

    /**
     * Валидация пароля
     *
     * @param $str строка на входе
     * @param $password_length желаемая длинна пароля
     *
     * @return bool результат валидации
     */
    function valid_password($str, $password_length)
    {
        $this->CI->form_validation->set_message('valid_password', 'Поле <b>%s</b> має містити або 0 символів, або більше ' . $password_length . '!');

        $length = strlen($str);
        if ($length > $password_length or $length === 0)
            return TRUE;
        else
            return FALSE;
    }
    
    /**
     * Проверка правильности введенного пароля
     * 
     * @param type $str введенный пароль
     * @return boolean результат
     */
    function valid_admin_password($str)
    {
        $this->CI->form_validation->set_message('valid_admin_password', 
                              'Введено невірний пароль в поле "<b>%s</b>"');
        
        $this->CI->load->library('auth_lib');
        if ($this->CI->auth_lib->check_pass(mb_strtolower($str)) === TRUE)
            return TRUE;
        else
            return FALSE;
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
        
        return $interval;        
    }
    
    /**
     * Проверяет свободен ли пропуск
     * 
     * @param int $pass_id ID пропуска
     * 
     * @return boolean 
     */
    public function free_pass($pass_id)
    {
        $this->CI->form_validation->set_message('free_pass', 
                              'Пропуск "<b>%s</b>" вже видано');
        
        $this->CI->load->model('passes_model');
        
        if ($this->CI->passes_model->is_pass_free($pass_id) === TRUE)
            return TRUE;
        else
            return FALSE;
    }
    
    /**
	 * Alpha
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha($str)
	{
		return ( ! preg_match("/^([АаБбВвГгҐґДдЕеЄєЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЬьЮюЯяЫыA-Za-z-])+$/i", $str)) ? FALSE : TRUE;
	}
    
    /**
	 * Alpha с пробелом
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha2($str)
	{
		return ( ! preg_match("/^([АаБбВвГгҐґДдЕеЄєЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЬьЮюЯяЫыA-Za-z ])+$/i", $str)) ? FALSE : TRUE;
	}
    
    /**
     * Проверяет, существует ли заявитель
     *
     * @param int $str ID заявителя
     * @return boolean 
     */
    public function existing_applicant($str)
    {
        $this->CI->form_validation->set_message('existing_applicant', 
                                               'Такий заявник не існує!');
        $applicant_id = (int) $str;        
        $this->CI->load->model('applicants_model');
        $applicants = $this->CI->applicants_model->get_applicants();
        foreach ($applicants as $applicant)
            if ((int)$applicant->applicant_id === $applicant_id)
                return TRUE;
        return FALSE;
    }
}