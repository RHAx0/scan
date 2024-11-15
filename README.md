# UDP MULTITHREADING PAYLOAD SCANNER

```
 ____  _   _    _      ____  _______     __
|  _ \| | | |  / \    |  _ \| ____\ \   / /
| |_) | |_| | / _ \   | | | |  _|  \ \ / / 
|  _ <|  _  |/ ___ \ _| |_| | |___  \ V /  
|_| \_\_| |_/_/   \_(_)____/|_____|  \_/ 
        by rhadev@protonmail.com              	
```

## UDP SCANNER v1.0.1 BY RHA.DEV - https://github.com/RHAx0

- **Parameters**:
  - `TARGET` (string): IP or CIDR (10.0.0.1 or 10.0.0.0/24)
  - `PORT` (int): Port to scan or 0 for all ports
  - `THREADS` (int): Use more threads to speed up your scan
  - `PAYLOAD FILE` (string): Payloads used for scan (PORT<space>PAYLOAD_SIZE<space>PAYLOAD_IN_HEX<space> Ignore....)
  - `OUTPUT FILE` (string): Here you can see saved results
  - `AMP VECTOR` (float): Calculated based on the sent and received packet size
  - `INTERFACE` (string): You can use eth0 or any vpn interface
  

## EXAMPLE USSAGE
```php udp_scanner.php <target> <port> <threads> <timeout> <payloads.txt> <result.txt> <min_amp[1.23]> <interface>```

```php udp_scanner.php 8.8.8.8 53 100 3 udp_payloads.txt result.txt 20 eth0```
```php udp_scanner.php 8.8.8.0/24 53 500 1 udp_payloads.txt result.txt 20 vpn0```


## Contact
For any questions, you can reach me at [rhadev@protonmail.com](mailto:rhadev@protonmail.com).
