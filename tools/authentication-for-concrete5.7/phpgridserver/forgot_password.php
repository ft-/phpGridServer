<?php defined('C5_EXECUTE') or die('Access denied.');
$form = Loader::helper('form');
?>

<div class="forgotPassword">
    <form method="post"
          action="<?php echo View::url('/login', 'callback', $authType->getAuthenticationTypeHandle(), 'forgot_password') ?>">
        <div class="form-group">
            <h2><?php echo t('Forgot Your Password?') ?></h2>

            <div class="ccm-message"><?php echo $intro_msg ?></div>
            <div class='help-block'>
                <?php echo t('Enter your email address below. We will send you instructions to reset your password.') ?>
            </div>
        </div>
        <div class="form-group">
            <input name="uEmail" type="email" placeholder="<?php echo t('Email Address') ?>" class="form-control" />
        </div>
        <button name="resetPassword" class="btn btn-primary btn-block"><?php echo t('Reset and Email Password') ?></button>
    </form>
</div>
