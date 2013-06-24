<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: rst.inc.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

/* Needed by admuser.php, so that password resets can be sent in the appropriate languge. */

$GLOBALS['register_conf_subject']       = 'Подтверждение регистрации';
$GLOBALS['reset_newpass_title']         = 'Сброс пароля';
$GLOBALS['reset_confirmation']          = 'Спасибо за регистрацию,\n\nДля активации вашей регистрации пожалуйста следуйте приведенной ниже ссылке:\n\n'.$url.'\n\nПосле активации вы будете зарегистрированы на форуме и\nперенаправлены на его главную страницу.\n\n\n\nЕсли Вы получили это сообщение по ошибке, просто не обращайте н него внимания. Если вы не запрашивали, но получили несколько таких сообщений, пожалуйста, свяжитесь с администратором форума по e-mail: '.$GLOBALS['ADMIN_EMAIL'].'\n\nЭтот запрос был инициирован с компьютера с адресом: '.$_SERVER['REMOTE_ADDR'].'.\n\n';
$GLOBALS['reset_reset']                 = 'Привет,\n\nВы затребовали сброс вашего пароля, для завершения этой операции\nследуйте этой ссылке\n\n'.$url.'\n\nПримечание: Пароли в форуме хранятся в односторонне зашифрованном виде,\nчто означает невозможность получения пароля в исходном виде после того,\nкак он был введен. После ввода пароля он шифруется и результат сравнивается\nс полученной при регистрации версией. (Если вас интересуют детали,\nвы можете почитать литературу о алгоритме MD5 HASH)\n\nЕсли Вы получили это сообщение по ошибке, просто не обращайте н него внимания. Если вы не запрашивали, но получили несколько таких сообщений, пожалуйста, свяжитесь с администратором форума по e-mail: '.$GLOBALS['ADMIN_EMAIL'].'\n\nЭтот запрос был инициирован с компьютера с адресом: '.$_SERVER['REMOTE_ADDR'].'.\n\n';
?>