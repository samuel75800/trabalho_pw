#!/bin/bash
docker-compose up -d
sleep 15
docker exec -i puppyco_db mysql -uroot -proot_pass puppyco < database/schema.sql
HASH=$(docker exec puppyco_app php -r "echo password_hash('12345678', PASSWORD_BCRYPT);")
docker exec puppyco_db mysql -uroot -proot_pass puppyco -e "UPDATE users SET password='$HASH' WHERE username='admin';"
echo "✅ puppy.co pronto! Acesse /pages/login.php"