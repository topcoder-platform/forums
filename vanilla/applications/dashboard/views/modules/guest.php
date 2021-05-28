<?php if (!defined('APPLICATION')) exit(); ?>
<div class="Box GuestBox">
    <h4><?php echo t('Welcome to Topcoder!'); ?></h4>

    <p><?php echo t($this->MessageCode, $this->MessageDefault); ?></p>

    <p><?php $this->fireEvent('BeforeSignInButton'); ?></p>

    <?php
    $signInUrl = signInUrl($this->_Sender->SelfUrl);

    if ($signInUrl) {
        echo '<div class="P">';
        echo anchor(t('Login'), signInUrl($this->_Sender->SelfUrl), 'Button Primary SignIn BigButton'.(signInPopup() ? ' SignInPopup' : ''), ['rel' => 'nofollow']);
        echo '</div>';
    }

    $Url = registerUrl($this->_Sender->SelfUrl);
    if (!empty($Url)) {
        ?>
        <p class="SignUpBlock">
            <span>Don't have an account?</span>
        <?php
            echo anchor(t('SIGN UP').sprite('SpArrowRight'), $Url, 'Button SignUp', ['rel' => 'nofollow']);
        ?>
        </p>
      <?php
    }

    ?>
    <?php $this->fireEvent('AfterSignInButton'); ?>
</div>
