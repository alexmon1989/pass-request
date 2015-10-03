<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="container">
    <div class="content">
        <div class="row">
            <div class="login-form">
                <h2>Авторизація</h2>
                    <fieldset>
                        <?php echo form_open('auth/login'); ?>
                            <div class="clearfix">
                                <input type="text" placeholder="Логін" name="username" value="<?php echo set_value('username'); ?>">
                            </div>
                            <div class="clearfix">
                                <input type="password" placeholder="Пароль" name="password">
                            </div>
                            <button class="btn btn-primary btn-large" type="submit">Увійти</button>
                        <?php echo form_close(); ?>
                    </fieldset>

            </div>
                <?php if (isset($error)) echo $error; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $('input[placeholder]').placeholder();
    });
</script>