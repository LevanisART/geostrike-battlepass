<?php

class AuthMessageComponent {
    public static function render($Translate) {
        ob_start();
        ?>
        <div class="battlepass-auth-message">
            <div class="auth-message-content">
                <p><?= htmlspecialchars($Translate->get_translate_module_phrase('module_page_battlepass', '_AuthMsg')) ?></p>
                <a href="?auth=login" class="auth-login-btn">
                    <?= $Translate->get_translate_module_phrase('module_page_battlepass', '_Auth') ?>
                </a>
            </div>
        </div>
        
        <style>
        .battlepass-auth-message {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        
        .auth-message-content {
            background: var(--lk);
            border: 1px solid var(--navbar-first-color);
            border-radius: 6px;
            padding: 30px;
            text-align: center;
            max-width: 400px;
        }
        
        .auth-message-content p {
            color: var(--default-text-color);
            font-size: 14px;
            margin: 0 0 20px 0;
        }
        
        .auth-login-btn {
            background: var(--span-color);
            border: none;
            border-radius: 6px;
            color: var(--lk);
            display: inline-block;
            font-size: 14px;
            font-weight: var(--font-weight-medium);
            padding: 12px 24px;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }
        
        .auth-login-btn:hover {
            opacity: 0.8;
        }
        </style>
        <?php
        return ob_get_clean();
    }
}