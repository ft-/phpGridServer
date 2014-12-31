<?php defined('C5_EXECUTE') or die('Access denied.');
$form = Loader::helper('form');
?>

<form method='post'
      action='<?php echo View::url('/login', 'authenticate', $this->getAuthenticationTypeHandle()) ?>'>
    <div class="form-group concrete-login">
        <span><?php echo t('Sign in with a phpGridServer account.') ?> </span>
        <hr>
    </div>
    <div class="form-group">
        <input name="uFirstName" class="form-control col-sm-12"
               placeholder="<?php echo t('First Name')?>" />
    </div>

    <div class="form-group">
        <input name="uLastName" class="form-control col-sm-12"
               placeholder="<?php echo t('Last Name')?>" />
    </div>

    <div class="form-group">
        <label>&nbsp;</label>
        <input name="uPassword" class="form-control" type="password"
               placeholder="<?php echo t('Password')?>" />
    </div>

    <div class="checkbox">
        <label style="font-weight:normal">
            <input type="checkbox" name="uMaintainLogin" value="1">
            <?php echo t('Stay signed in for two weeks') ?>
        </label>
    </div>

    <?php
    if (isset($locales) && is_array($locales) && count($locales) > 0) {
        ?>
        <div class="form-group">
            <label for="USER_LOCALE" class="control-label"><?php echo t('Language') ?></label>
            <?php echo $form->select('USER_LOCALE', $locales) ?>
        </div>
    <?php
    }
    ?>

    <div class="form-group">
        <button class="btn btn-primary"><?php echo t('Log in') ?></button>
        <a href="<?php echo View::url('/login', 'phpgridserver', 'forgot_password')?>" class="btn pull-right"><?php echo t('Forgot Password') ?></a>
    </div>

    <script type="text/javascript">
        document.querySelector('input[name=uName]').focus();
    </script>
    <?php Loader::helper('validation/token')->output('login_' . $this->getAuthenticationTypeHandle()); ?>

    <?php if (Config::get('concrete.user.registration.enabled')) { ?>
        <br/>
        <hr/>
        <a href="<?php echo URL::to('/register')?>" class="btn btn-block btn-success"><?php echo t('Not a member? Register')?></a>
    <?php } ?>
</form>
