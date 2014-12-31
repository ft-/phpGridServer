<?php defined('C5_EXECUTE') or die('Access denied.');
$form = Loader::helper('form');
?>
<div class="alert alert-sucess">
    <?php echo t('Successfully changed password'); ?>
</div>
<div>
    <a href="<?php echo URL::to('login', 'callback', 'phpgridserver') ?>" class="btn btn-block btn-success">
        <?php echo t('Click here to log in'); ?>
    </a>
</div>
