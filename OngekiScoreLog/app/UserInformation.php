<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInformation extends Model{
    protected $table = "user_informations";
    protected $guarded = ['id'];

    public static function ResetAllUserPaymentState(){
        $all = self::all();
        foreach ($all as $value) {
            $value->update(['is_standard_plan' => 0, 'is_premium_plan' => 0]);
        }
    }

    public static function IsStandardPlan($userId){
        $u = self::where('user_id', $userId)->first();
        return ($u !== null && $u->is_standard_plan !== 0);
    }

    public static function IsPremiumPlan($userId){
        $u = self::where('user_id', $userId)->first();
        return ($u !== null && $u->is_premium_plan !== 0);
    }
}
