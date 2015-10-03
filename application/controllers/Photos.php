<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Requests
*
* Класс (контроллер),
* который отвечает за отображение картинок из БД
*
* @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
* @version 1.0
*/
class Photos extends CI_Controller
{
    public function index()
    {        
        show_404();
    }
    
    /**
     * Вывод изображения из БД на экран
     * @param int $image_id 
     */
    public function get_image($image_id = 0)
    {
        // Защита от дурака
        if ($image_id === 0)
            show_404 ();
        
        $this->load->model('Photos_model');
        $photo = $this->Photos_model->get($image_id);
        if (empty($photo) === FALSE)
        {
            header('Content-type: image/jpeg');
            echo $photo;
        }
    }
}