# Topcoder - Forums

## Requirements

- Docker (docker deployment)

## Docker Deployment
- Create `vanilla.env` and copy the contents of  `sample.vanilla.env` into `vanilla.env`. Configure parameters.
- Create `mysql.env` and copy the contents of  `sample.mysql.env` into `mysql.env`. Configure parameters.    
`MYSQL_DATABASE` and `MYSQL_ROOT_PASSWORD` might be changed. You need to use these values during Vanilla Forums
installation.These variables are mandatory. 
  `MYSQL_ROOT_PASSWORD` specifies the password that will be set for the MySQL root superuser account.
- Run `docker-compose build` to build the image
- Run `docker-compose up` to run Vanilla Forums
- The Vanilla Forums will be available at `http://<your_docker_machine_ip>` by default

## Setup Vanilla Forums 

Go to [Setup Vanilla Forums](./docs/SetupVanillaForums.md) to complete installation.
