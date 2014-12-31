<?php defined('C5_EXECUTE') or die('Access denied.');
$form = Loader::helper('form');
?>
<div class='forgotPassword'>
    <h2><?php echo t('Unable to validate email') ?></h2>

    <div class='help-block'>
        <?php echo t(
            'The token you provided doesn\'t appear to be valid, please paste the url exactly as it appears in the email.') ?>
    </div>
    <a href="<?php echo \URL::to('/login/callback/phpgridserver') ?>" class="btn btn-block btn-primary">
        <?php echo t('Continue') ?>
    </a>
</div>
