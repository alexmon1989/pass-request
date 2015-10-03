<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Layout
*
* Библиотека CodeIgniter, которая выводит страницы сайта
*
* @author Монастырецкий Александр Николаевич <monastyretsky@mail.ru>
* @version 1.0
*/
class Layout
{
    /**
    * Объект CodeIgniter
    * @var object
    */
    private $ci	= null;

    /**
    * Список JS-скриптов
    *
    * @var array
    */
    private $scripts = array();

    /**
    * Информация, которая содержится в мета-теге description
    *
    * @var string
    */
    private $description = '';

    /**
    * Информация, которая содержится в мета-теге keywords
    *
    * @var string
    */
    private $keywords = '';

    /**
    * Список CSS-стилей
    *
    * @var array
    */
    private $styles = array();

    /**
    * JS - код
    *
    * @var array
    */
    private $js_code = array();

    /**
    * Код JS, выполняющийся сразу после загрузки страницы
    *
    * @var array
    */
    private $js_code_onload = array();

    /**
    * CSS - код
    *
    * @var array
    */
    private $css_code = array();


    /**
    * Содержание странички
    *
    * @var array
    */
    private $content = array();

    /**
    * Название страницы
    *
    * @var string
    */
    private $page_title = '';


    /**
    *
    * Конструктор класса
    *
    */
    function __construct()
    {
        // Получение Handle объекта CodeIgniter
        $this->ci =& get_instance();
        $this->ci->load->helper('url');
        $this->ci->load->helper('form');
    }

    /**
    * Метод, генерирующий html-код в теге <head>.
    * Добавляет скрипты, стили на страницу
    *
    * @return string html-код с объявлением скрипов, стилей
    */
    private function head_tags()
    {
        $tags = '';

        if ($this->scripts)
        {
            foreach ($this->scripts as $filename) {
                    $tags .= sprintf('<script type="text/javascript" src="' . base_url() . 'js/%s" ></script>'."\n", $filename);
            }
        }

        if ($this->styles)
        {
            foreach ($this->styles as $filename) {
                    $tags .= sprintf('<link type="text/css" rel="stylesheet" href="' . base_url() . 'css/%s" />'."\n", $filename);
            }
        }

        //var_dump($this->js_code_onload);
        if ($this->js_code_onload)
        {
            $this->js_code[] = sprintf("$(function() { %s });", implode("; ", $this->js_code_onload));
        }

        if ($this->js_code)
        {
            $tags .= '<script type="text/javascript">';
            $tags .= implode(" ", $this->js_code);
            $tags .= '</script>';
        }

        if ($this->css_code)
        {
            $tags .= '<style type="text/css">';
            $tags .= implode(" ", $this->css_code);
            $tags .= '</style>'."\n";
        }

        if ($this->css_code)
        {
            $tags .= '<style type="text/css">';
            $tags .= implode(" ", $this->css_code);
            $tags .= '</style>'."\n";
        }

        if ($this->description !== '')
            $tags .= '<meta name="Description" content="' . quotes_to_entities($this->description) . '">' . "\n";

        if ($this->keywords !== '')
            $tags .= '<meta name="Keywords" content="' . $this->keywords . '">' . "\n";

        return $tags;
    }

    /**
    * Метод добавляет названия JS-скриптов в свойство класса <b>scripts</b>
    *
    * @param array|string $scripts массив(строка) названий(я) скриптов(а)
    * @return object
    */
    function add_scripts($scripts)
    {
        if ( ! is_array($scripts))
        {
            $scripts = array($scripts);
        }

        $this->scripts = array_merge($this->scripts, $scripts);

        return $this;
    }

    /**
    * Метод добавляет названия CSS-стилей в свойство класса <b>styles</b>
    *
    * @param array|string $styles массив(строка) названий(я) стилей(я)
    * @return object
    */
    function add_styles($styles)
    {
        if ( ! is_array($styles))
        {
            $styles = array($styles);
        }

        $this->styles = array_merge($this->styles, $styles);

        return $this;
    }

    /**
    * Метод добавляет JS-код в свойство класса <b>js_code_onload</b>,
    * если он должен выполнится сразу после загрузки страницы
    * или в свойство класса <b>js_code</b>,
    * если он НЕ должен выполнится сразу после загрузки страницы
    *
    * @param string $js строка, содержащая JS-код
    * @param boolean $onload переменная определяет должен ли выполняться
    * код сразу после загрузки страницы
    * @return object
    */
    function add_js_code($js, $onload = FALSE)
    {        
        if ($onload)
        {
            $this->js_code_onload[] = $js;
        }
        else
        {
            $this->js_code[] = $js;
        }

        return $this;
    }

    /**
    * Метод добавляет CSS-код в свойство класса <b>styles</b>
    *
    * @param string $css строка, содержащая код стилей
    * @return object
    */
    function add_css_code($css)
    {
        $this->css_code[] = $css;

        return $this;
    }

    /**
    * Метод добавляет JS-переменную в свойство класса <b>js_code_onload</b>,
    *
    * @param string $name название JS-переменной
    * @param string $value значение JS-переменной
    * @param boolean $onload переменная определяет должен ли выполняться
    * код сразу после загрузки страницы
    * @return object
    */
    function add_js_var($name, $value, $onload = FALSE)
    {
        $js_code = sprintf("var %s = %s;\n", $name, $value);

        if ($onload === TRUE)
        {
            $this->js_code_onload[] = $js_code;
        }
        else
        {
            $this->js_code[] = $js_code;
        }

        return $this;
    }

    /**
    * Заполнение переменной контента страницы
    *
    * @param array $data контент
    * @return object
    */
    function add_content($data)
    {
        $this->content = array_merge($this->content, $data);
        return $this;
    }

    /**
    * Название странцы
    *
    * @param string $page_title название страницы
    * @return object
    */
    function set_page_title($page_title)
    {
        // Название страницы состоит из названия сайта и названия страницы
        $this->page_title = $this->ci->config->item('site_name') . ' :: '
                                                                  . $page_title;
        return $this;
    }

    /**
     * Функция добавления описания страницы
     *
     * @param string $description описание страницы
     */
    function set_description($description)
    {
        $this->description = $description;
    }

    /**
     * Функция добавления ключевых слов
     *
     * @param string $keywords
     */
    function set_keywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
    * Отображение страницы
    *
    * @param string $page_type какая страница загружается
    */
    function view($page_type)
    {
        // Загрузка хедера
        $header_vars = array(
                    'head_tags' => $this->head_tags(),
                    'page_title' => $this->page_title,
        );
        $this->ci->load->view('Users/header', $header_vars);
        
        // Роль пользователя
        $role_id = $this->ci->auth_lib->get_user_role_id_from_sess();
        
        // Загружаем пункты ТОП-меню
        if ($role_id === 4)
            $top_menu_list = $this->get_user_menu();
        else
            $top_menu_list = $this->get_chancellery_menu();
        
        // Списки меню (загружаем их HTML-код)
        $top_menu_html = $this->ci->load->view('Users/top_menu',
                        array('top_menu_list' => $top_menu_list,
                             'uri' => uri_string(),
                        ),
                        TRUE);

        // HTML-код содержимого при выводе одного рецепта
        $content_html = array('content' => $this->ci->load->view('Users/' . $page_type, $this->content, TRUE));

        // Добавление кодов разных элементов страницы
        // в переменную контента
        $this->add_content(array('top_menu' => $top_menu_html));
        $this->add_content($content_html);

        // Базовый шаблон контента на странице
        $this->ci->load->view('Users/basic_content', $this->content);
        
        // Загрузка футера
        $this->ci->load->view('footer');
    }

    /**
    * Отображение страницы модуля администрирования
    *
    * @param string $page какая страница загружается
    */
    function view_admin($page = '')
    {
        // Загрузка хедера
        $header_vars = array(
            'head_tags' => $this->head_tags(),
            'page_title' => $this->page_title,
        );
        $this->ci->load->view('Admin/header', $header_vars);

        // Роль пользователя
        $role_id = $this->ci->auth_lib->get_user_role_id_from_sess();
        
        // Загружаем пункты ТОП-меню
        if ($role_id === 1 or $role_id === 3) // Роли Начальника и Администратора
            $top_menu_list = $this->get_admin_chief_menu();
        else // роль Охранника
            $top_menu_list = $this->get_security_menu();
                
        // Получение логина и имени пользователя
        $user_login = $this->ci->session->userdata('login');
        
        // Списки меню (загружаем их HTML-код)
        $top_menu_html = $this->ci->load->view('Admin/top_menu',
                        array('top_menu_list' => $top_menu_list,
                             'uri' => uri_string(),   
                             'login' => $user_login,
                             ),
                        TRUE);

        // HTML-код содержимого
        $content_html = array('content' =>
                            $this->ci->load->view('Admin/' . $page, 
                                                $this->content, TRUE)
                           );

        // Добавление кодов разных элементов страницы
        // в переменную контента
        $this->add_content(array('top_menu' => $top_menu_html));
        $this->add_content($content_html);

        // Базовый шаблон контента на странице
        $this->ci->load->view('Admin/basic_content', $this->content);

        // Загрузка футера
        $this->ci->load->view('footer');
    }

    /**
     * Отображение страницы модуля авторизации
     */
    function view_login()
    {
        // Загрузка хедера
        $header_vars = array(
            'head_tags' => $this->head_tags(),
            'page_title' => $this->page_title,
        );
        $this->ci->load->view('Admin/header', $header_vars);

        // HTML-код содержимого при выводе одного рецепта
        $login_html = array('content' =>
                $this->ci->load->view('login', $this->content, TRUE));

        $this->add_content($login_html);

        // Базовый шаблон контента на странице
        //$this->ci->load->view('Security/basic_content', $this->content);
        $this->ci->load->view('login', $this->content);

        // Загрузка футера
        $this->ci->load->view('Admin/footer');
    }
    
    /**
     * Меню администратора
     * 
     * @return array 
     */
    private function get_admin_chief_menu()
    {
        $menu = array(
            0 => array(
                'uri' => 'requests',
                'title' => 'Заявки',                
            ),
            1 => array(
                'uri' => 'contracts',
                'title' => 'Трудові угоди',                
            ),
            2 => array(
                'uri' => 'temp_requests',
                'title' => 'Тимчасові заявки',                
            ),
            3 => array(
                'uri' => 'lists',
                'title' => 'Списки',    
                'children' => array(
                    0 => array(
                        'uri' => 'patent_agents',
                        'title' => 'Патентні повірені',                
                    ),
                    1 => array(
                        'uri' => 'couriers',
                        'title' => 'Кур\'єри',                
                    ),
                    2 => array(
                        'uri' => 'service',
                        'title' => 'Сервіс',                
                    ),
                    3 => array(
                        'uri' => 'forget_passes',
                        'title' => 'Забуті пропуска',                
                    ),
                    4 => array(
                        'uri' => 'administrators',
                        'title' => 'Адміністратори',                
                    ),
                    5 => array(
                        'uri' => 'security',
                        'title' => 'Охорона',                
                    ),                    
                    6 => array(
                        'uri' => 'chiefs',
                        'title' => 'Начальники',                
                    ),
                    7 => array(
                        'uri' => 'applicants',
                        'title' => 'Заявники',                
                    ),
                    8 => array(
                        'uri' => 'document_types',
                        'title' => 'Типи документів',                
                    ),
                    9 => array(
                        'uri' => 'long_requests',
                        'title' => 'Відкладені заявки',                
                    ),                    
                    10 => array(
                        'uri' => 'passes',
                        'title' => 'Перепустки',                
                    ),
                )
            ),            
            4 => array(
                'uri' => 'reports',
                'title' => 'Звіт',  
                'children' => array(
                    0 => array(
                        'uri' => 'reports',
                        'title' => 'Звіт тип 1',
                    ),
                )
            ),
            5 => array(
                'uri' => 'settings',
                'title' => 'Налаштування'
            ),
            6 => array(
                'uri' => 'logout',
                'title' => 'Вихід',                
            ),
        );
        
        return $menu;
    }
    
    /**
     * Меню охранника
     * 
     * @return array 
     */
    private function get_security_menu()
    {
        $menu = array(
            0 => array(
                'uri' => 'requests',
                'title' => 'Заявки',                
            ),
            1 => array(
                'uri' => 'lists',
                'title' => 'Списки',    
                'children' => array(
                    0 => array(
                        'uri' => 'patent_agents',
                        'title' => 'Патентні повірені',                
                    ),
                    1 => array(
                        'uri' => 'couriers',
                        'title' => 'Кур\'єри',                
                    ),
                    2 => array(
                        'uri' => 'service',
                        'title' => 'Сервіс',                
                    ),
                )
            ),
            3 => array(
                'uri' => 'forget_passes',
                'title' => 'Забуті перепустки',    
            ),
            2 => array(
                'uri' => 'logout',
                'title' => 'Вихід',                
            ),
        );
        
        return $menu;
    }
    
    private function get_user_menu()
    {
        $menu = array(
            0 => array(
                'uri' => 'add_request',
                'title' => 'Додати заявку',                
            ),
            1 => array(
                'uri' => 'past_requests',
                'title' => 'Мої заявки', 
            ),
            2 => array(
                'uri' => 'logout',
                'title' => 'Вихід',                
            ),
        );
        
        return $menu;
    }
    
    private function get_chancellery_menu()
    {
        $menu = array(
            0 => array(
                'uri' => 'add_request',
                'title' => 'Додати заявку',                
            ),
            1 => array(
                'uri' => 'past_requests',
                'title' => 'Мої заявки', 
            ),
            2 => array(
                'uri' => 'patent_agents',
                'title' => 'Патентні повірені', 
            ),
            3 => array(
                'uri' => 'couriers',
                'title' => 'Кур\'єри', 
            ),
            4 => array(
                'uri' => 'logout',
                'title' => 'Вихід',                
            ),
        );
        
        return $menu;
    }
}