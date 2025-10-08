<?php

namespace App\Helpers;

class RankHelper
{
    /**
     * Map rank code to star icons
     */
    const RANK_ICONS = [
        '3//CN' => ['stars' => 3, 'slashes' => 2, 'suffix' => 'CN', 'title' => 'Đại tá CN'],
        '3/CN' => ['stars' => 3, 'slashes' => 1, 'suffix' => 'CN', 'title' => 'Đại tá CN'],
        '3//' => ['stars' => 3, 'slashes' => 2, 'suffix' => '', 'title' => 'Đại tá'],
        '3/' => ['stars' => 3, 'slashes' => 1, 'suffix' => '', 'title' => 'Đại tá'],
        '2//' => ['stars' => 2, 'slashes' => 2, 'suffix' => '', 'title' => 'Trung tá'],
        '2/' => ['stars' => 2, 'slashes' => 1, 'suffix' => '', 'title' => 'Trung tá'],
        '1//' => ['stars' => 1, 'slashes' => 2, 'suffix' => '', 'title' => 'Thiếu tá'],
        '1/' => ['stars' => 1, 'slashes' => 1, 'suffix' => '', 'title' => 'Thiếu tá'],
        '4/' => ['stars' => 4, 'slashes' => 1, 'suffix' => '', 'title' => 'Đại tá'],
        '4//' => ['stars' => 4, 'slashes' => 2, 'suffix' => '', 'title' => 'Đại tá'],
        'Đại úy' => ['stars' => 1, 'slashes' => 0, 'suffix' => '', 'title' => 'Đại úy'],
        'Thượng úy' => ['stars' => 1, 'slashes' => 0, 'suffix' => '', 'title' => 'Thượng úy'],
        'Trung úy' => ['stars' => 1, 'slashes' => 0, 'suffix' => '', 'title' => 'Trung úy'],
        'Thiếu úy' => ['stars' => 1, 'slashes' => 0, 'suffix' => '', 'title' => 'Thiếu úy'],
    ];

    /**
     * Get rank icon HTML
     */
    public static function getRankIcon($rankCode)
    {
        $rankCode = trim($rankCode);
        
        // Check exact match first
        if (isset(self::RANK_ICONS[$rankCode])) {
            $rank = self::RANK_ICONS[$rankCode];
        } else {
            // Check partial match
            $rank = self::getRankByPartialMatch($rankCode);
        }

        if (!$rank) {
            return self::getDefaultRankIcon();
        }

        return self::generateStarIcon($rank['stars'], $rank['slashes'], $rank['suffix'], $rank['title']);
    }

    /**
     * Get rank by partial match
     */
    private static function getRankByPartialMatch($rankCode)
    {
        // Remove spaces for better matching
        $cleanRankCode = str_replace(' ', '', $rankCode);
        
        foreach (self::RANK_ICONS as $key => $rank) {
            $cleanKey = str_replace(' ', '', $key);
            if (strpos($cleanRankCode, $cleanKey) !== false || strpos($cleanKey, $cleanRankCode) !== false) {
                return $rank;
            }
        }
        
        // Try to parse pattern like "4/" or "3//CN"
        if (preg_match('/^(\d+)(\/+)(.*)$/', $cleanRankCode, $matches)) {
            $stars = (int)$matches[1];
            $slashes = strlen($matches[2]);
            $suffix = $matches[3];
            
            return [
                'stars' => $stars,
                'slashes' => $slashes,
                'suffix' => $suffix,
                'title' => 'Cấp bậc ' . $stars . ' sao'
            ];
        }
        
        return null;
    }

    /**
     * Generate star icon HTML
     */
    private static function generateStarIcon($stars, $slashes, $suffix, $title)
    {
        // Just display the title (rank name) instead of stars and slashes
        return '<span title="' . htmlspecialchars($title) . '">' . htmlspecialchars($title) . '</span>';
    }

    /**
     * Get default rank icon
     */
    private static function getDefaultRankIcon()
    {
        return '<span title="Không xác định">-</span>';
    }

    /**
     * Get rank display text with icon
     */
    public static function getRankDisplay($rankCode)
    {
        $rankCode = trim($rankCode);
        
        if (isset(self::RANK_ICONS[$rankCode])) {
            $rank = self::RANK_ICONS[$rankCode];
            return self::generateStarIcon($rank['stars'], $rank['slashes'], $rank['suffix'], $rank['title']) . ' ' . $rank['title'];
        }

        $rank = self::getRankByPartialMatch($rankCode);
        if ($rank) {
            return self::generateStarIcon($rank['stars'], $rank['slashes'], $rank['suffix'], $rank['title']) . ' ' . $rank['title'];
        }

        return self::getDefaultRankIcon() . ' ' . $rankCode;
    }

    /**
     * Get all available ranks
     */
    public static function getAllRanks()
    {
        return self::RANK_ICONS;
    }
}
