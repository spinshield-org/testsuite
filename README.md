# TESTSUITE

## Install
- Copy `.env.example` to `.env`
- Set `.env` variables, mainly API_* variables and the Telegram bot token
- Run `php artisan key:generate`
- Test if testsuite runs correctly by running `php artisan app:test-session-creation`
- Set `crontab -e` so this automatically runs every 15 minutes:
```bash
    * * * * * cd /var/www/LOCATION_TO_TESTSUITE && php artisan schedule:run >> /dev/null 2>&1
```
- Enjoy!

## Helpers
### Postgres Setup
```bash
sudo -u postgres psql

CREATE DATABASE db_2;
CREATE USER db_2_user WITH ENCRYPTED PASSWORD 'wqrro0@!1254012k_rrk2mLd@@e';
GRANT ALL PRIVILEGES ON DATABASE db_2 TO db_2_user;

GRANT USAGE ON SCHEMA public TO db_2_user;
ALTER DATABASE db_2 OWNER TO db_2_user;
```

### Permissions Helper
Replace `$USER` to `www-data` if you are running this on server with "root" user.
```bash
sudo chown -R $USER:www-data .
sudo find . -type f -exec chmod 664 {} \;
sudo find . -type d -exec chmod 775 {} \;
sudo find . -type d -exec chmod g+s {} \;
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```
