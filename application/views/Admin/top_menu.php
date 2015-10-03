<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <!--<a class="brand" href="<?php echo base_url('admin'); ?>">АС "Бюро перепусток"</a>-->

          <div class="nav-collapse">
              <div style="margin-left: 35%;">  
                    <ul class="nav">
                    <?php foreach ($top_menu_list as $key => $top_menu_item): ?>
                        <li id="<?php echo 'menu_item_' . $key; ?>" class="<?php echo ($this->uri->segment(2) == $top_menu_item['uri']) ? 'active' : ''; ?>">
                            <?php if (isset($top_menu_item['children'])): ?>
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="<?php echo 'anchor_item_' . $key; ?>"><?php echo $top_menu_item['title']; ?> <b class="caret"></b></a>
                                <script>
                                    $(document).ready(function(){
                                        $("#menu_item_<?php echo $key; ?>").addClass("dropdown");
                                    });
                                </script>
                                <ul class="dropdown-menu">
                                    <?php foreach ($top_menu_item['children'] as $menu): ?>
                                        <li><a href="<?php echo base_url('admin/' . $menu['uri']); ?>"><i class="icon-list"></i> <?php echo $menu['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <a href="<?php echo base_url('admin/' . $top_menu_item['uri']); ?>" id="<?php echo 'anchor_item_' . $key; ?>">
                                    <?php echo $top_menu_item['title']; ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
              <p class="pull-right" style="margin-top: 10px">Ви увійшли як <b><?php echo $login; ?></b></p>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>