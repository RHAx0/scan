<?php
/*
  ____  _   _    _      ____  _______     __
 |  _ \| | | |  / \    |  _ \| ____\ \   / /
 | |_) | |_| | / _ \   | | | |  _|  \ \ / / 
 |  _ <|  _  |/ ___ \ _| |_| | |___  \ V /  
 |_| \_\_| |_/_/   \_(_)____/|_____|  \_/ 
         by rhadev@protonmail.com              
			
MIT License
Copyright (c) 2024 RhaDev

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

Additional Terms:
This software is intended for testing and educational purposes only. Any commercial use is strictly prohibited without the prior written consent of the author.
*/

//INIT
error_reporting(E_ALL);
set_time_limit(-1);


/* START FUNCTIONS */
function str2hex($string)
{
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}


function hex2str($hex)
{
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

function color(string $text, string $color = 'default', string $background = 'default'): string {
    $colors = [
        'default' => '39',
        'black' => '30',
		'gray' => '37',
        'dark_gray' => '90',
        'red' => '91',
        'green' => '92',
        'yellow' => '93',
        'blue' => '94',
        'magenta' => '95',
        'cyan' => '96',
        'white' => '97',
        'dark_red' => '31',
        'dark_green' => '32',
        'dark_yellow' => '33',
        'dark_blue' => '34',
        'dark_magenta' => '35',
        'dark_cyan' => '36'
    ];
    $backgrounds = [
        'default' => '49',
        'black' => '40',
        'gray' => '47',
        'dark_gray' => '100',
        'red' => '101',
        'green' => '102',
        'yellow' => '103',
        'blue' => '104',
        'magenta' => '105',
        'cyan' => '106',
        'white' => '107',
		'dark_red' => '41',
        'dark_green' => '42',
        'dark_yellow' => '43',
        'dark_blue' => '44',
        'dark_magenta' => '45',
        'dark_cyan' => '46',
    ];
    $colorCode = $colors[$color] ?? $colors['default'];
    $backgroundCode = $backgrounds[$background] ?? $backgrounds['default'];
    return "\033[{$colorCode};{$backgroundCode}m{$text}\033[0m";
}

function send_udp($ip, $port, $payload, $timeout, $interface = NULL)
{
		global $target;
		$recived = NULL;
		$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if($interface)
		{
			if(!socket_set_option($socket, SOL_SOCKET, SO_BINDTODEVICE, $interface))
			{
				die(color("Can't set interface ans $interface\n", "red"));
			}
		}
		socket_set_option($socket, SOL_SOCKET,SO_RCVTIMEO, array("sec"=>$timeout,"usec"=>0));
		socket_connect($socket, $ip, $port);
		socket_write($socket, hex2str($payload));
		$response = socket_read($socket, 65535);
		socket_close($socket);
		
		if($response)
		{
			$recived['payload'] = str2hex($response);
			$recived['psize'] = strlen($response);
		}
		
		return($recived);
}

function get_interfaces($ignoreList)
{
	$output = [];
	exec('ip link show', $output);
	$interfaces = [];
	foreach($output as $line) {
		if (preg_match('/^\d+: (\w+):/', $line, $matches)) {
			$interface = $matches[1];
			if (!in_array($interface, $ignoreList)) 
			{
				$interfaces[] = $interface;
			}
		}
	}
	return($interfaces);
}

function filter_fy_first_column(array $array, int $value): array
{
    return array_filter($array, function ($row) use ($value) {
        return isset($row[0]) && $row[0] == $value;
    });
}

function cidr2array($cidr) 
{
    list($subnet, $mask) = explode('/', $cidr);
    $start = ip2long($subnet);
    $mask = (1 << (32 - $mask)) - 1;
    $end = ($start | $mask);
    $ipArray = [];
    for ($ip = $start; $ip <= $end; $ip++) {
        $ipArray[] = long2ip($ip);
    }
    return $ipArray;
}

function is_cidr_or_ip($input)
{
    if (filter_var($input, FILTER_VALIDATE_IP))
	{
        return 'ip';
    } 
	elseif (strpos($input, '/') !== false) 
	{
        list($subnet, $mask) = explode('/', $input);
        if (filter_var($subnet, FILTER_VALIDATE_IP) && is_numeric($mask) && $mask >= 0 && $mask <= 32)
		{
            return 'cidr';
        }
    }
    return 0;
}
/* END FUNCTIONS */


/* START MAIN */
if(php_sapi_name() !== "cli")
{
	die(color("Run the program from the console!\n", "red"));
} 
$interfaces = implode(' ', get_interfaces(array('lo')));
echo color("
  ____  _   _    _      ____  _______     __
 |  _ \| | | |  / \    |  _ \| ____\ \   / /
 | |_) | |_| | / _ \   | | | |  _|  \ \ / / 
 |  _ <|  _  |/ ___ \ _| |_| | |___  \ V /  
 |_| \_\_| |_/_/   \_(_)____/|_____|  \_/   
                                            
         v1.0.1 by rhadev@protonmail.com\n
", "dark_cyan");
echo color("UDP SCANNER v1.0.1 BY RHA.DEV - https://github.com/RHAx0\n", "cyan");
echo color("AVAILABLE INTERFACES: {$interfaces}\n", "blue");
if($argc < 9)
{
    die(color("Usage: php {$argv[0]} <target_ip_or_cidr> <target_port[0_for_all]> <threads> <timeout> <payloads.txt> <result.txt> <min_amp[1.23]> <interface>\n", "default"));
}

// Get parameters from the command line
$target['ip'] = $argv[1];
$target['port'] = (int)$argv[2];
$target_port = intval($argv[2]);
$target['threads'] = (int)$argv[3];
$target['timeout'] = (int)$argv[4];
$target['input'] = $argv[5];
$target['output'] = $argv[6];
file_put_contents($target['output'], "IP PORT AMP SEND_SIZE RECIVED_SIZE SEND_PAYLOAD RECIVED_PAYLOAD\n");
$target['amp'] = floatval($argv[7]);
$target['interface'] = $argv[8];


if(is_cidr_or_ip($target['ip']) == "ip")
{
	$target['ips'][] = $target['ip'];
}
elseif(is_cidr_or_ip($target['ip']) == "cidr")
{
	$target['ips'] = cidr2array($target['ip']);
}
else
{
	die(color("ERROR: IP's is incorrect!\n", "red"));
}
$target['number_of_ips'] = count($target['ips']);

if(!file_exists($target['input'])) 
{
	echo(color("ERROR: Payloads file not found!\n", "red"));
	die(color("EXAMPLE: PORT<space>PAYLOAD_SIZE<space>PAYLOAD_HEX<space>IGNORE....\n", "blue"));
}

echo color("TARGET: \n IP: {$target['ip']}\n IPS: {$target['number_of_ips']}\n PORT: {$target['port']} \n THREADS: {$target['threads']} \n TIMEOUT: {$target['timeout']}\n RESULT FILE: {$target['output']}\n REQUIRED AMP VECTOR: {$target['amp']}x\n INTERFACE: {$target['interface']} \n PAYLOAD FILE: {$target['input']}\n", "blue");
sleep(3);

$input = file($target['input'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$payloads = array_map(function ($input) { return preg_split('/\s+/', trim($input)); }, $input);
if($target['port'] !== 0)
{
	$payloads = filter_fy_first_column($payloads, $target['port']);
}
if(!count($payloads ?? []))
{
	die(color("ERROR: Payloads not found!\n", "red"));
}
$payloads_count = count($payloads);
echo color("FOUND: {$payloads_count} payloads!\n", "dark_cyan");
sleep(2);


if(!$payloads_count)
{
	die(color("ERROR: Payloads not found!\n", "red"));
}
if($target['threads'] > $payloads_count)
{
	echo color("THREADS: Reduced to {$payloads_count}\n", "magenta");
}

$payloads_chunks = array_chunk($payloads, ceil(count($payloads) / $target['threads']));
if (!function_exists('pcntl_fork')) //MAKE `pcntl_fork`
{
	die(color("THREADS: Reduced to {$payloads_count}\n", "red"));;
}
$childPids = [];
foreach($payloads_chunks as $chunk) 
{
    $pid = pcntl_fork();
    if ($pid == -1) 
	{
		die(color("Failed to create child process!\n", "red"));
    } 
	elseif($pid) 
	{
        $childPids[] = $pid; // The parent process adds the PID to the list
    } 
	else
	{ 
		foreach($chunk as $chunk_row) 
		{	
			foreach($target['ips'] as $target['ip']) 
			{
				list($chunk_row['port'], $chunk_row['psize'], $chunk_row['payload'], $chunk_row['count']) = $chunk_row;
				$startTime = microtime(true); // Start czasu operacji
				$recived = send_udp($target['ip'], $chunk_row['port'], $chunk_row['payload'], $target['timeout'], $target['interface']);
				if($recived) 
				{	
					
					$recived['amp'] = round(((20 + $recived['psize']) / (20 + $chunk_row['psize'])), 2);
					if($recived['amp'] >= $target['amp'])
					{
						echo color("SUCCESS: {$target['ip']} {$chunk_row['port']} {$recived['amp']} {$chunk_row['psize']} {$recived['psize']} {$chunk_row['payload']} {$recived['payload']}\n", "green");
						file_put_contents($target['output'], "{$target['ip']} {$chunk_row['port']} {$recived['amp']} {$chunk_row['psize']} {$recived['psize']} {$chunk_row['payload']} {$recived['payload']}"."\n", FILE_APPEND);
					}
					else
					{
						echo color("SKIPPED: {$target['ip']} {$chunk_row['port']} {$recived['amp']} {$chunk_row['psize']} {$recived['psize']} {$chunk_row['payload']} {$recived['payload']}\n", "yellow");
					}
				}
				else
				{
					echo color("TIMEOUT: {$target['ip']} {$chunk_row['port']} 0 {$chunk_row['psize']} 0 {$chunk_row['payload']} NULL\n", "red");
					
				}
					//usleep(100000); // small gap to not overload the network
			}
		}
		/*  END SCANNING */
		exit(0); // Termination of child process
    }
}

// We are waiting for all child processes to finish
foreach ($childPids as $pid)
{
    pcntl_waitpid($pid, $status);
}

/* END MAIN */

echo (color("DONE...\n", "magenta"));
?>


