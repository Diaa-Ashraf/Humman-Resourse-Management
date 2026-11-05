<?php
namespace App\Rules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
class StrongPassword implements ValidationRule
{
    public function validate(string $attribute,mixed $value,Closure $fail):void
    {
        $minimum=(int)config('security.password_min_length',12);
        if(!is_string($value)||strlen($value)<$minimum||!preg_match('/[a-z]/',$value)||!preg_match('/[A-Z]/',$value)||!preg_match('/\d/',$value)||!preg_match('/[^A-Za-z0-9]/',$value))$fail("يجب أن تتكون كلمة المرور من {$minimum} أحرف على الأقل، وبها حرف كبير وصغير ورقم ورمز.");
    }
}
