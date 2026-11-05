<?php
namespace App\Services;

class Totp
{
    private const ALPHABET='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function secret(int $length=32): string
    {
        $bytes=random_bytes($length);$bits='';foreach(str_split($bytes) as $char)$bits.=str_pad(decbin(ord($char)),8,'0',STR_PAD_LEFT);$secret='';foreach(str_split($bits,5) as $chunk){if(strlen($chunk)<5)$chunk=str_pad($chunk,5,'0');$secret.=self::ALPHABET[bindec($chunk)];}return $secret;
    }

    public function verify(string $secret,string $code,?int $timestamp=null): bool
    {
        if(!preg_match('/^\d{6}$/',$code))return false;$counter=(int)floor(($timestamp??time())/30);
        for($offset=-1;$offset<=1;$offset++)if(hash_equals($this->code($secret,$counter+$offset),$code))return true;
        return false;
    }

    public function code(string $secret,int $counter): string
    {
        $key=$this->decode($secret);$binary=pack('N2',0,$counter);$hash=hash_hmac('sha1',$binary,$key,true);$offset=ord($hash[19])&0x0f;$value=((ord($hash[$offset])&0x7f)<<24)|((ord($hash[$offset+1])&0xff)<<16)|((ord($hash[$offset+2])&0xff)<<8)|(ord($hash[$offset+3])&0xff);return str_pad((string)($value%1000000),6,'0',STR_PAD_LEFT);
    }

    public function uri(string $secret,string $email): string
    {
        return 'otpauth://totp/'.rawurlencode(config('app.name').':'.$email).'?secret='.$secret.'&issuer='.rawurlencode(config('app.name')).'&algorithm=SHA1&digits=6&period=30';
    }

    private function decode(string $secret): string
    {
        $bits='';foreach(str_split(strtoupper($secret)) as $character){$index=strpos(self::ALPHABET,$character);if($index===false)continue;$bits.=str_pad(decbin($index),5,'0',STR_PAD_LEFT);}$bytes='';foreach(str_split($bits,8) as $byte){if(strlen($byte)===8)$bytes.=chr(bindec($byte));}return $bytes;
    }
}
