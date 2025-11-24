@php
    /**
     * Workflow Progress Component - Simple horizontal progress with dots and connectors
     *
     * @param array $steps - Array of workflow steps with keys: 'key', 'label'
     * @param string $currentStatus - Current workflow status key
     * @param bool $rejected - Whether the workflow is rejected
     * @param array $stepDates - Optional array of dates for each step (key => date)
     * @param array $stepUsers - Optional array of users who approved each step (key => user name)
     */

    // Get data from widget or direct variables
    if (isset($widget) && isset($widget['content'])) {
        $steps = $widget['content']['steps'] ?? [];
        $currentStatus = $widget['content']['currentStatus'] ?? '';
        $isRejected = $widget['content']['rejected'] ?? false;
        $stepDates = $widget['content']['stepDates'] ?? [];
        $stepUsers = $widget['content']['stepUsers'] ?? [];
        $currentStepIndex = isset($widget['content']['currentStepIndex']) ? (int)$widget['content']['currentStepIndex'] : 0;
    } else {
        $steps = $steps ?? [];
        $currentStatus = $currentStatus ?? '';
        $isRejected = $rejected ?? false;
        $stepDates = $stepDates ?? [];
        $stepUsers = $stepUsers ?? [];
        $currentStepIndex = $currentStepIndex ?? 0;
    }
@endphp

<div class="workflow-progress-simple mb-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="la la-tasks"></i> Tiến trình phê duyệt
            </h5>
        </div>
            <div class="workflow-steps-container">
                @foreach ($steps as $index => $step)
                    @php
                        $hasDate = isset($stepDates[$step['key']]) && !empty($stepDates[$step['key']]);
                        $isCreatedStep = ($step['key'] === 'created');
                        $isCompletedStep = ($step['key'] === 'completed');

                        // Step is completed only if it's before the current step index
                        // Logic: A step is completed if its index is less than currentStepIndex
                        // The "created" step is always completed
                        // The "completed" step is completed if it has a date (workflow finished)
                        // Other steps are completed only if workflow has passed them
                        // If step has date/user data, it's completed (actual approval happened)
                        $hasStepData = $hasDate || isset($stepUsers[$step['key']]);
                        
                        if ($isCreatedStep) {
                            $isCompleted = true; // Created step is always completed
                        } elseif ($isCompletedStep && $hasDate) {
                            $isCompleted = true; // Completed step is done if it has a date
                        } elseif ($hasStepData && ($index <= $currentStepIndex)) {
                            $isCompleted = true; // Step with data is completed if it's at or before current step
                        } elseif ($index < $currentStepIndex) {
                            $isCompleted = true; // Steps before current are completed
                        } else {
                            $isCompleted = false; // Current and future steps are not completed yet
                        }

                        // Current step: only if it's the current index AND not completed AND not the completed step
                        $isCurrent = ($index === $currentStepIndex && !$isRejected && !$isCompleted) && !$isCompletedStep;
                        $isRejectedStep = $index === $currentStepIndex && $isRejected;
                        $isPending = !$isCompleted && !$isCurrent;

                        // Determine colors and class
                        if ($isRejectedStep) {
                            $stepClass = 'rejected';
                            $dotColor = '#dc3545';
                            $connectorColor = '#dc3545';
                            $iconClass = 'la-times';
                            $iconColor = '#fff';
                        } elseif ($isCompleted) {
                            $stepClass = 'completed';
                            $dotColor = '#007bff';
                            $connectorColor = '#007bff'; // Connector before completed step is blue
                            $iconClass = 'la-check';
                            $iconColor = '#fff';
                        } elseif ($isCurrent) {
                            $stepClass = 'current';
                            $dotColor = '#007bff';
                            // Connector after current step should be grey (pending)
                            $connectorColor = '#dee2e6';
                            // Current step should NOT have checkmark, just a circle or empty
                            $iconClass = 'la-circle';
                            $iconColor = '#fff';
                        } else {
                            $stepClass = 'pending';
                            $dotColor = '#6c757d';
                            $connectorColor = '#dee2e6';
                            $iconClass = 'la-circle';
                            $iconColor = '#6c757d';
                        }

                        $stepDate = $stepDates[$step['key']] ?? null;
                        $stepUser = $stepUsers[$step['key']] ?? null;
                        $isLast = $index === count($steps) - 1;

                        // Connector color: blue if connecting to a completed step, grey otherwise
                        // The connector goes from current step to next step
                        if (!$isLast) {
                            // Connector is blue if current step is completed (not just current)
                            if ($isCompleted) {
                                $connectorColor = '#007bff';
                            } else {
                                $connectorColor = '#dee2e6'; // Grey for pending segments
                            }
                        }
                    @endphp

                    <div class="workflow-step-item {{ $stepClass }}" data-step="{{ $step['key'] }}">
                        <div class="step-dot-wrapper">
                            @if (!$isLast)
                                <div class="step-connector" style="background-color: {{ $connectorColor }};"></div>
                            @endif
                            @if($stepClass === 'current')
                                 <div class="step-clock">
                                                    <img src="{{ asset('assets/icon/clock.svg') }}" alt="clock" />
                                                </div>
                            @else
                                <div class="step-dot {{ $stepClass }}" style="border-color: {{ $dotColor }};">
                                    <i class="la {{ $iconClass }}" style="color: {{ $iconColor }} !important;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="step-content">
                            <div class="step-label">{{ $step['label'] }}</div>
                            {{-- Only show date/user if step is completed (not current or pending) --}}
                            @if ($isCompleted && $stepDate)
                                <div class="step-date text-muted small">
                                    <i class="la la-calendar"></i> {{ $stepDate }}
                                </div>
                            @endif
                            @if ($isCompleted && $stepUser)
                                <div class="step-user text-muted small">
                                    <i class="la la-user"></i> {{ $stepUser }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
    </div>
</div>

@push('after_styles')
<style>
.workflow-progress-simple .workflow-steps-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    position: relative;
    padding: 20px 0;
    overflow: visible;
}

.workflow-step-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    overflow: visible;
}

.step-clock {
    position: relative;
    z-index: 2;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    background: #fff;
    border-radius: 50%;
}


.step-dot-wrapper {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    overflow: visible;
}

.step-connector {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    height: 3px;
    z-index: 0;
    transition: background-color 0.3s ease;
}

.step-dot {
    position: relative;
    z-index: 2;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 3px solid;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #fff !important;
    font-size: 18px;
    margin: 0 auto;
    transition: all 0.3s ease;
}

.step-dot i {
    color: inherit !important;
}


.workflow-step-item.completed .step-dot {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: #fff !important;
}

.workflow-step-item.current .step-dot {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: #fff !important;
}

.workflow-step-item.completed .step-dot i,
.workflow-step-item.current .step-dot i {
    color: #fff !important;
}

.workflow-step-item.rejected .step-dot {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff !important;
}

.workflow-step-item.rejected .step-dot i {
    color: #fff !important;
}

.workflow-step-item.pending .step-dot {
    background-color: #fff;
    border-color: #dee2e6;
    color: #6c757d;
}

.step-content {
    width: 100%;
}

.step-label {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 5px;
    color: #495057;
    word-break: break-word;
}

.workflow-step-item.completed .step-label {
    color: #007bff;
    font-weight: 700;
}

.workflow-step-item.current .step-label {
    color: #007bff;
    font-weight: 700;
    position: relative;
}

.workflow-step-item.current {
    position: relative;
}

.workflow-step-item.current .step-content {
    position: relative;
    z-index: 1;
}

.workflow-step-item.rejected .step-label {
    color: #dc3545;
}

.workflow-step-item.pending .step-label {
    color: #6c757d;
}

.step-date,
.step-user {
    margin-top: 3px;
    font-size: 11px;
}

@media (max-width: 768px) {
    .workflow-progress-simple .workflow-steps-container {
        flex-direction: column;
        align-items: flex-start;
    }

    .workflow-step-item {
        width: 100%;
        flex-direction: row;
        text-align: left;
        margin-bottom: 25px;
    }

    .step-dot-wrapper {
        width: auto;
        margin-right: 15px;
        margin-bottom: 0;
    }

    .step-connector {
        display: none;
    }

    .step-content {
        flex: 1;
    }
}
</style>
@endpush
