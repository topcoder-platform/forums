# Topcoder - Forums

## Requirements

- Docker (docker deployment)

## Docker Deployment with all services
- Create `vanilla.env` and copy the contents of  `sample.vanilla.env` into `vanilla.env`. Configure parameters.
- Create `mysql.env` and copy the contents of  `sample.mysql.env` into `mysql.env`. Configure parameters.    
`MYSQL_DATABASE` and `MYSQL_ROOT_PASSWORD` might be changed. You need to use these values during Vanilla Forums
installation.These variables are mandatory. 
  `MYSQL_ROOT_PASSWORD` specifies the password that will be set for the MySQL root superuser account.
- Run `docker-compose -f docker-compose.yml -f docker-compose.dev.yml build` to build the image
- Run `docker-compose -f docker-compose.yml -f docker-compose.dev.yml up` to run Vanilla Forums
- The Vanilla Forums will be available at `http://<your_docker_machine_ip>` by default

## Docker Deployment with existing MySql
- You need to know how connect to MySQL Database:
     - **Database Host**  
     - **Database Name** 
     - **Database User** 
     - **Database Password**
  These parameters will be used in Vanilla Forums Installation Wizard.   
- Create `vanilla.env` and copy the contents of  `sample.vanilla.env` into `vanilla.env`. Configure parameters.
- Run `docker-compose build` to build the image
- Run `docker-compose up` to run Vanilla Forums
- The Vanilla Forums will be available at `http://<your_docker_machine_ip>` by default

## Setup Vanilla Forums 

Go to [Setup Vanilla Forums](./docs/SetupVanillaForums.md) to complete installation.
