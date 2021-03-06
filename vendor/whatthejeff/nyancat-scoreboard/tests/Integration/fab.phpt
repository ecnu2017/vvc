--TEST--
Integration test for a fab scoreboard
--FILE--
<?php

require_once 'vendor/autoload.php';

use NyanCat\Cat;
use NyanCat\Rainbow;
use NyanCat\Team;
use NyanCat\Scoreboard;

use Fab\Fab;

$scoreboard = new Scoreboard(
    new Cat(),
    new Rainbow(
        new Fab()
    ),
    array(
        new Team('pass', 'green', '^'),
        new Team('fail', 'red', 'o'),
        new Team('pending', 'cyan', '-'),
    )
);

$scoreboard->start();
for($i = 0; $i <= 2; $i++) {
    $scoreboard->score('pass');
    $scoreboard->score('fail');
    $scoreboard->score('pending');
}
$scoreboard->stop();

--EXPECT--
 [32m0[0m
 [31m0[0m
 [36m0[0m
 
[4A[5C[31m-[0m
[5C[31m-[0m
[5C[31m-[0m
[5C[31m-[0m
[4A[6C_,------,  
[6C_|  /\_/\  
[6C~|_( - .-) 
[6C ""  ""    
[4A [32m1[0m
 [31m0[0m
 [36m0[0m
 
[4A[5C[31m-[0m[32m_[0m
[5C[31m-[0m[32m_[0m
[5C[31m-[0m[32m_[0m
[5C[31m-[0m[32m_[0m
[4A[7C_,------,  
[7C_|   /\_/\ 
[7C^|__( ^ .^)
[7C  ""  ""   
[4A [32m1[0m
 [31m1[0m
 [36m0[0m
 
[4A[5C[31m-[0m[32m_[0m[33m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m
[4A[8C_,------,  
[8C_|  /\_/\  
[8C~|_( o .o) 
[8C ""  ""    
[4A [32m1[0m
 [31m1[0m
 [36m1[0m
 
[4A[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m
[4A[9C_,------,  
[9C_|   /\_/\ 
[9C^|__( - .-)
[9C  ""  ""   
[4A [32m2[0m
 [31m1[0m
 [36m1[0m
 
[4A[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m
[4A[10C_,------,  
[10C_|  /\_/\  
[10C~|_( ^ .^) 
[10C ""  ""    
[4A [32m2[0m
 [31m2[0m
 [36m1[0m
 
[4A[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m
[4A[11C_,------,  
[11C_|   /\_/\ 
[11C^|__( o .o)
[11C  ""  ""   
[4A [32m2[0m
 [31m2[0m
 [36m2[0m
 
[4A[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m
[4A[12C_,------,  
[12C_|  /\_/\  
[12C~|_( - .-) 
[12C ""  ""    
[4A [32m3[0m
 [31m2[0m
 [36m2[0m
 
[4A[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m
[4A[13C_,------,  
[13C_|   /\_/\ 
[13C^|__( ^ .^)
[13C  ""  ""   
[4A [32m3[0m
 [31m3[0m
 [36m2[0m
 
[4A[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m[33m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m[33m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m[33m-[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m[33m-[0m
[4A[14C_,------,  
[14C_|  /\_/\  
[14C~|_( o .o) 
[14C ""  ""    
[4A [32m3[0m
 [31m3[0m
 [36m3[0m
 
[4A[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m[33m-[0m[34m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m[33m-[0m[34m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m[33m-[0m[34m_[0m
[5C[31m-[0m[32m_[0m[33m-[0m[34m_[0m[35m-[0m[36m_[0m[31m-[0m[32m_[0m[33m-[0m[34m_[0m
[4A[15C_,------,  
[15C_|   /\_/\ 
[15C^|__( - .-)
[15C  ""  ""   
[4A 
 
 
 
