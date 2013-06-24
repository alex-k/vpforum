<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: avatar_msg.inc.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

/* Needed by admapprove_avatar.php, so that avatar approval/decline can be sent in the appropriate languge. */

$GLOBALS['approved_avatar_title'] = 'Ваша картинка утверждена.';
$GLOBALS['approved_avatar_msg'] = 'Модератор утвердил вашу картинку и теперь она должна быть видимой в вашей персональной информации и во всех ваших сообщениях. Если же картинка по-прежнему не видна, то возможно ваш браузер просто показывает устаревшую страницу из кэша. Попробуйте перезагрузить ее раз или два.';
$GLOBALS['unapproved_avatar_title'] = 'Ваша картинка отвергнута.';
$GLOBALS['unapproved_avatar_msg'] = 'Администратор не утвердил вашу картинку. Наиболее вероятной причиной этого могли стать слишком большой ее размер или недопустимое/неприличное содержимое. Для получения дополнительной информации по поводу неутверждения вы можете связаться с администратором.';
?>