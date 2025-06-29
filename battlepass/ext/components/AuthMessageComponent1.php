<?php

class AuthMessageComponent {
    public static function render($Translate) {
        ob_start();
        ?>
        <div class="error_contentauth">
            <div class="error_texts_block_bp">
                <div class="error_oops" style="display: flex;justify-content: center;align-items: center;gap: 12px;flex-wrap: wrap;text-align: center;">
				<style>
					a.button_steam_auth {
					display: flex;
					cursor: pointer;
					color: var(--fon);
					font-weight: 600;
					background-color: var(--span-color);
					justify-content: center;
					align-items: center;
					height: 40px;
					border-radius: 6px;
					padding: 10px;
				}
				</style>
                    <?= htmlspecialchars($Translate->get_translate_module_phrase('module_page_battlepass', '_AuthMsg')) ?><a class="button_steam_auth open-modal" data-modal="login"><?= 'ავტორიზაცია' ?></a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}