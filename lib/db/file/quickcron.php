<?php

class db_quickcron {
    function executeflag() {
        $time = rtrim(file_string('.../storage/tmp/cron/alarm'));
        if ($time >= date('U')) return false;
        return true;
    }
}
?>