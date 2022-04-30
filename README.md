# Requirements:
- This setup requires an ubuntu 20.04
- This application is developed using PHP 8.1.5.
- Docker must be installed if MySQL is not installed.

# Setup the database:

Create a volume for persitance:
```bash
docker volume create shopy-science-submission
```

Run the container and set the mysql root password:
```bash
docker run -d -p 3306:3306 -v shopy-science-submission:/var/lib/mysql \
--name shopy-science-submission \
-e MYSQL_ROOT_PASSWORD=123 \
mysql
```

# Setup the application:

Clone the application:

```bash
git clone https://github.com/iheb-draouil/shopy-science-submission.git

```

Move into the application's root directory:
```bash
cd shopy-science-submission
```

Create the security sub-directory:
```bash
mkdir security
```
Move into the newly created directory and generate the private key / public key pair:
```bash
cd security

# generate the private key
openssl genpkey -algorithm RSA -out private.pem -pkeyopt rsa_keygen_bits:2048

# generate the public key based on the private key
openssl rsa -pubout -in private.pem -out public.pem
```

Move back into the application's root directory:
```bash
cd ..
```

Copy the .env file in the email in the application root.

Install the symfony CLI:

```bash
echo 'deb [trusted=yes] https://repo.symfony.com/apt/ /' | sudo tee /etc/apt/sources.list.d/symfony-cli.list
sudo apt update
sudo apt install symfony-cli
```

Move back into the application's root directory and start the development server:

```bash
cd ..
symfony server:start
```
# Usage

- Create a register a user at "http://localhost:8000/register" (you can use the pair "username" and "password").
- Login using the same pair of credentials. You will be redirected to the "flow/retreive-untreated-orders" path where you will be able to download the csv (unfortunately, the supplied api stopped working).
- You can logout at any time.