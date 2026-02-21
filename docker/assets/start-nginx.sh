#!/bin/sh
# Wait for PHP-FPM to be listening so nginx does not 502 on first requests
php -r "for(\$i=0;\$i<20;\$i++){if(@fsockopen('127.0.0.1',9000)){exit(0);}usleep(500000);}exit(1);"
exec nginx -c /etc/nginx.conf
