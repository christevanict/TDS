<?php

if (!function_exists('has_access')) {
    function has_access($menu, $action = null) {
        $user = auth()->user();

        if (!$user || !$user->role) return false;

        $privileges = $user->roles->privileges;

        if (!isset($privileges[$menu])) return false;

        return $action ? in_array($action, $privileges[$menu]) : true;
    }
}
