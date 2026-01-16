<?php

namespace App\Services;

use App\Models\CaseCargoItem;

class RiskAnalysisService
{
    // Risk score thresholds
    const LOW_RISK_THRESHOLD = 30;
    const MEDIUM_RISK_THRESHOLD = 60;
    const HIGH_RISK_THRESHOLD = 100;

    // Scoring weights
    const HS_CODE_WEIGHT = 30;
    const ROUTE_WEIGHT = 20;
    const VALUE_WEIGHT = 25;
    const ORIGIN_WEIGHT = 15;
    const VIOLATION_WEIGHT = 10;

    // High-risk HS codes
    const HIGH_RISK_HS_CODES = [
        '2710',  // Mineral oils
        '8703',  // Motor vehicles
        '2709',  // Crude oil
        '2401',  // Tobacco
        '6204',  // Women's clothing
        '6203',  // Men's clothing
    ];

    // High-risk countries
    const HIGH_RISK_COUNTRIES = [
        'IR', 'SY', 'KP', 'CU'
    ];

    public static function analyzeCase($caseModel)
    {
        $score = 0;
        $reasons = [];

        // 1. HS Code Analysis (30 points max)
        $hsCodeScore = self::analyzeHSCodes($caseModel, $reasons);
        $score += $hsCodeScore;

        // 2. Route Analysis (20 points max)
        $routeScore = self::analyzeRoute($caseModel, $reasons);
        $score += $routeScore;

        // 3. Value Analysis (25 points max)
        $valueScore = self::analyzeValue($caseModel, $reasons);
        $score += $valueScore;

        // 4. Origin Country Analysis (15 points max)
        $originScore = self::analyzeOrigin($caseModel, $reasons);
        $score += $originScore;

        // 5. Previous Violations (10 points max)
        $violationScore = self::analyzeViolations($caseModel, $reasons);
        $score += $violationScore;

        // Update case with risk score
        $caseModel->update([
            'risk_score' => $score,
            'risk_reason' => implode('; ', $reasons),
        ]);

        return [
            'risk_score' => $score,
            'risk_level' => self::getRiskLevel($score),
            'should_inspect' => $score >= self::MEDIUM_RISK_THRESHOLD,
            'reasons' => $reasons,
        ];
    }

    private static function analyzeHSCodes($caseModel, &$reasons)
    {
        $score = 0;
        $cargoItems = $caseModel->cargoItems;

        if ($cargoItems->isEmpty()) {
            return 0;
        }

        foreach ($cargoItems as $item) {
            $hsCode = substr($item->hs_code, 0, 4);

            if (in_array($hsCode, self::HIGH_RISK_HS_CODES)) {
                $score += 15;
                $reasons[] = "High-risk commodity (HS: {$item->hs_code})";
            } elseif ($item->hs_code < '2000' || $item->hs_code > '9000') {
                $score += 5;
            }
        }

        return min($score, self::HS_CODE_WEIGHT);
    }

    private static function analyzeRoute($caseModel, &$reasons)
    {
        $score = 0;

        // Check for unusual routes
        if ($caseModel->route && strlen($caseModel->route) > 20) {
            $score += 5;
            $reasons[] = "Complex or unusual route detected";
        }

        return $score;
    }

    private static function analyzeValue($caseModel, &$reasons)
    {
        $score = 0;

        $declaredValue = $caseModel->declared_value ?? 0;
        $actualValue = $caseModel->actual_value ?? 0;

        // High value shipments
        if ($declaredValue > 100000) {
            $score += 10;
            $reasons[] = "High-value shipment (€" . number_format($declaredValue) . ")";
        }

        // Value discrepancy
        if ($declaredValue > 0 && $actualValue > 0) {
            $discrepancy = abs($actualValue - $declaredValue) / $declaredValue;
            if ($discrepancy > 0.2) {
                $score += 15;
                $reasons[] = "Significant value discrepancy detected";
            }
        }

        return min($score, self::VALUE_WEIGHT);
    }

    private static function analyzeOrigin($caseModel, &$reasons)
    {
        $score = 0;

        if ($caseModel->origin_country && in_array(strtoupper($caseModel->origin_country), self::HIGH_RISK_COUNTRIES)) {
            $score += 15;
            $reasons[] = "Shipment from high-risk country ({$caseModel->origin_country})";
        }

        return min($score, self::ORIGIN_WEIGHT);
    }

    private static function analyzeViolations($caseModel, &$reasons)
    {
        $score = 0;

        if ($caseModel->previous_violations > 0) {
            $score = min($caseModel->previous_violations * 3, self::VIOLATION_WEIGHT);
            $reasons[] = "Previous violations found ({$caseModel->previous_violations})";
        }

        return $score;
    }

    private static function getRiskLevel($score)
    {
        if ($score >= self::HIGH_RISK_THRESHOLD) {
            return 'high';
        } elseif ($score >= self::MEDIUM_RISK_THRESHOLD) {
            return 'medium';
        }
        return 'low';
    }
}
