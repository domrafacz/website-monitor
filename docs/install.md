# Installation

You can install Website-monitor traditional way or using docker compose

## Traditional installation

You will need ssh access, git and composer installed

```
git clone https://github.com/domrafacz/website-monitor.git

cd website-monitor/

composer install
```
After this, you have to rename `.env.example` to `.env` and setup database credentials in `DATABASE_URL` env variable

If you are using postgresql, you can use the following command
```
php bin/console doctrine:migrations:migrate
```
For mysql, mariadb etc use

```
php bin/console doctrine:schema:update --force
```
## Docker installation

Build Docker images

```
git clone https://github.com/domrafacz/website-monitor.git

cd website-monitor/

docker compose build --no-cache --pull
```
Starting containers
```
SERVER_NAME=domain-name.com \
APP_SECRET=!ChangeMe! \
CADDY_MERCURE_JWT_SECRET=ChangeMe \
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

## Running cron
Website-monitor is using cron to check websites uptime, you need to add cron server ip to `TRUSTED_IPS` env variable in order to run checks

Below is an example cronjob running every minute
```
*/1 * * * * root        wget -O /dev/null       https://your-domain.com/cron/run-requests
```

## Building assets (only for development purposes)
```
yarn install
yarn build
```