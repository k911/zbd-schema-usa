# Source database for Data Warehouse
Streaming Services - Schema USA

## Creating fixtures

1. Build docker images and start mysql and pma:

    ```bash
    docker login
    docker-compose build --pull
    docker-compose up -d mysql pma
    ```

2. Initialize and migrate database:

    ```bash
    docker-compose run console doctrine:database:create
    docker-compose run console doctrine:migrations:migrate
    ```

3. Run fixtures

    ```bash
    docker-compose run console doctrine:fixtures:load -n
    ```

4. Go to http://localhost:8080

## Exporting data to staging database of Date Warehouse

0. Login to Docker Hub and get access to Oracle EE from Docker Store: https://store.docker.com/images/oracle-database-enterprise-edition
1. Build docker images and start oracle database:

    ```bash
    docker login
    docker-compose build --pull
    docker-compose up -d oradb
    ```
2. Use `docker ps` to check whether database is healthy
3. Make sure `staging` user exists and its schema

    ```bash
    docker-compose exec oradb bash
    sqlplus staging/staging@ORCLCDB

    # you should be connected to database now
    ```

4. Make sure you have compatible schema in project and database

    ```bash
    docker-compose run console doctrine:schema:validate --em=warehouse_stage
    ```

5. Run migrations script

    ```bash
    docker-compose run console app:staging:migrate
    ```

## Fast usage

```bash
docker-compose run console doctrine:database:drop --force
docker-compose run console doctrine:database:create
docker-compose run console doctrine:migrations:migrate -n
docker-compose run console doctrine:fixtures:load -n
docker-compose run console app:staging:migrate
```