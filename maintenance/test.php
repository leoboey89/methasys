<?php
	$password = 'methasys2015';

                // A higher "cost" is more secure but consumes more processing power
                $securityLevel = 10;

                // Create a random salt
                $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

                // Prefix information about the hash so PHP knows how to verify it later.
                // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
                $salt = sprintf("$2a$%02d$", $securityLevel) . $salt;

                // Hash password with the salt
                echo $hash = crypt($password, $salt);
?>
