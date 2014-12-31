<?php defined('C5_EXECUTE') or die('Access denied.');
$form = Loader::helper('form');
?>
<div class='forgotPassword'>
    <h2><?php echo t('Forgot Your Password?') ?></h2>

    <div class="ccm-message"><?php echo $intro_msg ?></div>
    <div class='help-block'>
        <?php echo t(
            'If there is an account associated with this email, instructions for resetting your password have been sent.') ?>
    </div>
    <a href="<?php echo \URL::to('/login') ?>" class="btn btn-block btn-primary">
        <?php echo t('Go Back') ?>
    </a>
</div>
