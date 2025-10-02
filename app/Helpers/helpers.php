<?php

if (!function_exists('getUserTitle')) {
    /**
     * Get user title based on their position
     * Returns position name if in leadership positions, otherwise returns "Bạn"
     * 
     * @param \App\Models\User|null $user
     * @return string
     */
    function getUserTitle($user = null)
    {
        if (!$user) {
            $user = backpack_user();
        }
        
        if (!$user) {
            return 'Bạn';
        }
        
        // Get employee info
        $employee = $user->getCorrectEmployee();
        
        if (!$employee || !$employee->position) {
            return 'Bạn';
        }
        
        $positionName = $employee->position->name;
        
        // Check if position is in leadership list
        $leadershipPositions = [
            'Giám đốc',
            'Phó Giám đốc', 
            'P. Giám đốc',
            'Trưởng Ban',
            'Trưởng ban',
            'Trưởng phòng',
            'Quản đốc'
        ];
        
        foreach ($leadershipPositions as $position) {
            if (stripos($positionName, $position) !== false || $positionName === $position) {
                return $positionName;
            }
        }
        
        return 'Bạn';
    }
}

if (!function_exists('getUserTitleLowercase')) {
    /**
     * Get user title in lowercase for use in middle of sentence
     * 
     * @param \App\Models\User|null $user
     * @return string
     */
    function getUserTitleLowercase($user = null)
    {
        $title = getUserTitle($user);
        
        // Keep "Bạn" as is, lowercase others
        if ($title === 'Bạn') {
            return 'bạn';
        }
        
        return strtolower($title);
    }
}

