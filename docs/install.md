# Installation

You can install Website-monitor traditional way or using docker compose

## Traditional installation

You will need ssh access, git and composer installed

```
git clone https://github.com/domrafacz/website-monitor.git

cd website-monitor/

composer install
```
After you need to rename `.env.example` to `.env` and setup database credentials in `DATABASE_URL` env variable

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

Below example cronjob running every minute
```
*/1 * * * * root        wget -O /dev/null       https://your-domain.com/cron/run-requests
```

## Building assets
```
yarn install
yarn build
```