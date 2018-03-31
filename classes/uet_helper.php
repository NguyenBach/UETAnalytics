<?php
/**
 * Created by PhpStorm.
 * User: bachnguyen
 * Date: 3/9/18
 * Time: 8:38 AM
 */

class uet_helper
{
    public static function getUserRoles($context,$userid){
        $roles = get_user_roles($context,$userid);
        $role = [];
        foreach ($roles as $r){
            $role[] = $r->shortname;
        }
        return $role;
    }
}