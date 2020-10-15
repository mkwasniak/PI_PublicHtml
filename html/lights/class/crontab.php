<?php

/*
# at 5 a.m every week with:
# 0 5 * * 1 tar -zcf /var/backups/home.tgz /home/
# 
# m h  dom mon dow   command
*/

class Crontab 
{
    private $minute         = '*';
    private $hour           = '*';
    private $day_of_month   = '*';
    private $month          = '*';
    private $day_of_week    = '*';


}

?>