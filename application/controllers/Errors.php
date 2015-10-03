<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Errors
 *
 * Контроллер для вывода страниц ошибок
 *
 * @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
 * @version 1.0
 */

class Errors extends CI_Controller
{
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Вывод страницы с просьбой включить JS
     */
    public function js_cookie_off()
    {
        echo 'Будь-ласка, включіть JavaScript та підтримку Cookies у Вашому браузері та спробуйте ще раз. ' . anchor('', 'АС "Бюро перепусток"');
    }
}