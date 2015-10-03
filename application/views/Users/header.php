<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <base href="<?php echo site_url('') ?>">
    <meta charset="utf-8">
    <title><?php if (isset($page_title)) echo $page_title; ?></title>
    <noscript>
       <meta http-equiv="refresh" content="0;url=<?php echo base_url('errors/js_cookie_off'); ?>">
    </noscript>
    <script type="text/javascript">
        if (!navigator.cookieEnabled)
            window.location = "<?php echo base_url('errors/js_cookie_off'); ?>";
    </script>


    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <?php if (isset($head_tags)) echo $head_tags; ?>


    <!--[if IE 6]>
        <link type="text/css" href="<?php echo base_url('css/ie6.min.css') ?>" rel="stylesheet">
        <script src="<?php echo base_url('js/ie6.min.js') ?>"></script>
      <![endif]-->
  </head>

  <body>