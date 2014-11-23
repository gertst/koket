<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gert
 * Date: 23/11/14
 * Time: 14:50
 * To change this template use File | Settings | File Templates.
 */
$command = "mysqldump -ukokettek_main -pVytOeHOWr6hH5Hu --default-character-set=utf8 kokettek_koket > backup.sql";
exec($command, $output, $return_var);