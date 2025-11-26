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

if (!function_exists('getStatusBadgeColor')) {
    /**
     * Get Tabler badge color for a given status
     * Returns Tabler color class (blue, azure, indigo, green, red, etc.)
     * 
     * @param string $status The status value
     * @param string $type Type of entity: 'leave', 'vehicle', 'general'
     * @return string Tabler badge color class
     */
    function getStatusBadgeColor($status, $type = 'general')
    {
        // Leave Request workflow statuses
        if ($type === 'leave') {
            $badges = [
                'pending' => 'orange',                    // Chờ chỉ huy xác nhận - Orange (cảnh báo chờ)
                'approved_by_department_head' => 'azure', // Chờ thẩm định - Azure (đang xử lý)
                'approved_by_reviewer' => 'indigo',      // Chờ BGD ký - Indigo (chờ quyết định cuối)
                'approved_by_director' => 'green',        // Đã hoàn tất - Green (thành công)
                'rejected' => 'red',                      // Đã từ chối - Red (thất bại)
                'cancelled' => 'secondary',              // Đã hủy - Gray
            ];
            
            return $badges[$status] ?? 'secondary';
        }
        
        // Vehicle Registration workflow statuses
        if ($type === 'vehicle') {
            $badges = [
                'submitted' => 'cyan',           // Đã gửi - Cyan (mới tạo)
                'dept_review' => 'azure',        // Đang được xét duyệt - Azure (đang xử lý)
                'director_review' => 'indigo',   // Chờ BGD phê duyệt - Indigo (chờ quyết định)
                'approved' => 'green',           // Đã phê duyệt - Green (thành công)
                'rejected' => 'red',             // Đã từ chối - Red (thất bại)
                'pending' => 'orange',           // Chờ xử lý - Orange
            ];
            
            return $badges[$status] ?? 'secondary';
        }
        
        // General statuses (for backward compatibility)
        $badges = [
            'pending' => 'orange',
            'approved' => 'green',
            'rejected' => 'red',
            'cancelled' => 'secondary',
            'active' => 'green',
            'inactive' => 'secondary',
            'draft' => 'yellow',
            'published' => 'green',
        ];
        
        return $badges[$status] ?? 'secondary';
    }
}

if (!function_exists('renderStatusBadge')) {
    /**
     * Render a status badge with consistent styling
     * Uses Tabler badge classes: bg-{color} text-white
     * NO icon - text only for consistency
     * 
     * @param string $status The status value
     * @param string $label The display label (optional, will use status if not provided)
     * @param string $type Type of entity: 'leave', 'vehicle', 'general'
     * @param string|null $icon DEPRECATED - not used anymore, kept for backward compatibility
     * @param bool $pill Whether to use pill style (rounded)
     * @return string HTML badge string
     */
    function renderStatusBadge($status, $label = null, $type = 'general', $icon = null, $pill = true)
    {
        $color = getStatusBadgeColor($status, $type);
        $displayLabel = $label ?? ucfirst(str_replace('_', ' ', $status));
        
        // Always use white text for consistency
        $badgeClass = "badge bg-{$color} text-white";
        if ($pill) {
            $badgeClass .= ' badge-pill';
        }
        
        // NO icon - text only for consistency
        return '<span class="' . $badgeClass . '" style="color: #ffffff !important;">' . htmlspecialchars($displayLabel) . '</span>';
    }
}

if (!function_exists('getStatusIcon')) {
    /**
     * Get icon class for a given status
     * 
     * @param string $status The status value
     * @param string $type Type of entity: 'leave', 'vehicle', 'general'
     * @return string Icon class
     */
    function getStatusIcon($status, $type = 'general')
    {
        // Leave Request icons
        if ($type === 'leave') {
            $icons = [
                'pending' => 'la-clock',
                'approved_by_department_head' => 'la-check-circle',
                'approved_by_reviewer' => 'la-check-circle',
                'approved_by_director' => 'la-check-double',
                'rejected' => 'la-times-circle',
                'cancelled' => 'la-ban',
            ];
            
            return $icons[$status] ?? 'la-circle';
        }
        
        // Vehicle Registration icons
        if ($type === 'vehicle') {
            $icons = [
                'submitted' => 'la-paper-plane',
                'dept_review' => 'la-clock',
                'director_review' => 'la-hourglass-half',
                'approved' => 'la-check-double',
                'rejected' => 'la-times-circle',
            ];
            
            return $icons[$status] ?? 'la-circle';
        }
        
        // General icons
        $icons = [
            'pending' => 'la-clock',
            'approved' => 'la-check',
            'rejected' => 'la-times',
            'cancelled' => 'la-ban',
            'active' => 'la-check-circle',
            'inactive' => 'la-circle',
        ];
        
        return $icons[$status] ?? 'la-circle';
    }
}

