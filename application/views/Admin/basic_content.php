<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<?php if (isset($top_menu)) echo $top_menu; ?>

<div class="container">
    <div class="row">
            <?php if (isset($content)) echo $content; ?>
    </div>
</div>