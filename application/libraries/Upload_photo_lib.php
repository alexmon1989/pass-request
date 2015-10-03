<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Upload_photo_lib
{
    /**
     * Глобальный объект CI
     */
    private $CI;

    /**
     * Конструктор 
     */
    public function __construct() 
    {
        $this->CI = &get_instance();
    }

    /**
     * Добавление в БД изображения
     * 
     * @param string $file_name имя файла с фото посетителя
     *
     * @return mixed FALSE при неудаче и ID добавленного изображения при удаче 
     */    
    public function upload_photo($file_name = '')
    {
        // Полный путь к изображению
        $full_path = './uploads/' . $file_name;
        if (file_exists($full_path) === FALSE)
        {
            throw new Exception('Файл не существует');
            return FALSE;
        }
        
        // Загружаем в БД
        $this->CI->load->model('Photos_model');
        $uploaded_photo_id = $this->CI->Photos_model->add($full_path);
        
        // Удаляем из временного каталога
        unlink($full_path);
        
        // Возвращаем ID добавленной записи
        return $uploaded_photo_id;
    }
    
    /**
     * Добавление изображения во временную папку (обработчик Ajax-запроса)
     *
     * @return string JSON
     */    
    public function upload_photo_ajax()
    {
        $config = array();
        // Параметры для загрузки файла
        $config['upload_path'] = './uploads/';
		 $config['allowed_types'] = 'jpg|jpeg';
        $config['file_name'] = 'uploaded_image';
        
        $this->CI->load->library('upload', $config);
        
        if ( ! $this->CI->upload->do_upload('photo'))
        {
            // Вывод ошибки
            $json = array('error' => $this->CI->upload->display_errors());
            //$json = json_encode($json);
            return $json['error'];
        }
        else
        {
            // Добавление фото в БД
            $upload_data = $this->CI->upload->data();
            
            // Изменяем размер изображения
            $this->resize_image($upload_data['full_path']);
            
            // Возвращаем путь к изображению
            $json = array('error' => '',
                         'file_name' => $upload_data['file_name'],
                         'url' => base_url('uploads/' . $upload_data['file_name']));
            $json = json_encode($json);
            return $json;
        }
    }
    
    /**
     * Обновление в БД изображения
     *
     * @return boolean FALSE при неудаче 
     */    
    public function upload_and_update_photo($photo_id, $file_name = '')
    {
        // Полный путь к изображению
        $full_path = './uploads/' . $file_name;
        if (file_exists($full_path) === FALSE)
        {
            throw new Exception('Файл не существует');
            return FALSE;
        }
        
        // Загружаем в БД
        $this->CI->load->model('Photos_model');
        $this->CI->Photos_model->update($photo_id, $full_path);
        
        // Удаляем из временного каталога
        unlink($full_path);
    }
    
    /**
     *
     * @param string $path путь к файлу
     */
    public function resize_image($path)
    {
        if (file_exists($path) === FALSE)
        {
            throw new Exception('Файл для изменения размера не существует!');
            return FALSE;
        }
        $config['image_library'] = 'gd2';
        $config['source_image'] = $path;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = 400;
        $config['height'] = 300;
        $config['quality'] = 90;
        $this->CI->load->library('image_lib', $config);
        $this->CI->image_lib->resize();
        return TRUE;
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