<?php if (!defined('APPLICATION')) exit();

$isEmbedded = (bool)  c('Garden.Embed.Allow',false);
?>
<div class="Box GuestBox">
    <h4><?php echo t('Welcome to Topcoder!'); ?></h4>

    <p><?php
        $message = $isEmbedded?
            "Looks like you are new or haven't signed in. Please use the \"Login\" link in the upper right to log into your account.": "Looks like you are new or aren't currently signed in." ;
        echo $message;
        ?></p>

    <p><?php $this->fireEvent('BeforeSignInButton'); ?></p>

    <?php

    $signInUrl = signInUrl($this->_Sender->SelfUrl);

    if(!$isEmbedded) {
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
    }

    ?>
    <?php $this->fireEvent('AfterSignInButton'); ?>
</div>
