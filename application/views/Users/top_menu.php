<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <!--<a class="brand" href="<?php echo base_url(); ?>">АС "Бюро перепусток"</a>-->

            <!--[if IE 7]> 
            <div class="nav-collapse" style="position: absolute; right: 50%; width: 600px;">
              <div style="position: relative; left: 70%;">  
            <![endif]--> 
            <![if !IE 7]>
                <div class="nav-collapse">
                        <div style="display: table; margin: 0 auto;">  
            <![endif]>
                    <ul class="nav">
                        <?php foreach ($top_menu_list as $top_menu_item): ?>
                            <li <?php if ('requests/' . $top_menu_item['uri'] === uri_string()) echo 'class="active"';?>><a href="<?php echo base_url('requests/' . $top_menu_item['uri']); ?>"><?php echo $top_menu_item['title']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
              </div>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>