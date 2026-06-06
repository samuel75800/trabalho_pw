<?php
// C:\xampp\htdocs\puppy.co\gerar_hash.php
$hash = password_hash('12345678', PASSWORD_BCRYPT);
echo $hash;
echo '<br><br>';
echo 'Verificação: ';
var_dump(password_verify('12345678', $hash));
?>