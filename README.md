### Usage:

1. Build docker images and start mysql and pma:

    ```bash
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