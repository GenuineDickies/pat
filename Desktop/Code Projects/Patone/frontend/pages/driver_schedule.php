<!-- Driver Availability Schedule -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Availability Schedule - <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></h4>
                    <p class="text-muted mb-0">Manage driver weekly availability schedule</p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>drivers/dashboard/<?php echo $driver['id']; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Weekly Schedule</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo SITE_URL; ?>drivers/saveSchedule/<?php echo $driver['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <?php
                        $daysOfWeek = [
                            0 => 'Sunday',
                            1 => 'Monday',
                            2 => 'Tuesday',
                            3 => 'Wednesday',
                            4 => 'Thursday',
                            5 => 'Friday',
                            6 => 'Saturday'
                        ];
                        
                        // Organize schedule by day
                        $scheduleByDay = [];
                        foreach ($schedule as $s) {
                            $scheduleByDay[$s['day_of_week']][] = $s;
                        }
                        
                        foreach ($daysOfWeek as $dayNum => $dayName):
                        ?>
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="day_enabled_<?php echo $dayNum; ?>"
                                           onchange="toggleDay(<?php echo $dayNum; ?>)"
                                           <?php echo isset($scheduleByDay[$dayNum]) ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="day_enabled_<?php echo $dayNum; ?>">
                                        <?php echo $dayName; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="card-body" id="day_content_<?php echo $dayNum; ?>" 
                                 style="<?php echo isset($scheduleByDay[$dayNum]) ? '' : 'display: none;'; ?>">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Start Time</label>
                                        <input type="time" class="form-control" 
                                               name="schedules[<?php echo $dayNum; ?>][start_time]"
                                               value="<?php echo isset($scheduleByDay[$dayNum][0]) ? $scheduleByDay[$dayNum][0]['start_time'] : '09:00'; ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">End Time</label>
                                        <input type="time" class="form-control" 
                                               name="schedules[<?php echo $dayNum; ?>][end_time]"
                                               value="<?php echo isset($scheduleByDay[$dayNum][0]) ? $scheduleByDay[$dayNum][0]['end_time'] : '17:00'; ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="schedules[<?php echo $dayNum; ?>][is_available]">
                                            <option value="1" <?php echo (isset($scheduleByDay[$dayNum][0]) && $scheduleByDay[$dayNum][0]['is_available']) ? 'selected' : ''; ?>>
                                                Available
                                            </option>
                                            <option value="0" <?php echo (isset($scheduleByDay[$dayNum][0]) && !$scheduleByDay[$dayNum][0]['is_available']) ? 'selected' : ''; ?>>
                                                Unavailable
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Notes (optional)</label>
                                        <input type="text" class="form-control" 
                                               name="schedules[<?php echo $dayNum; ?>][notes]"
                                               placeholder="e.g., Training, Personal appointment"
                                               value="<?php echo isset($scheduleByDay[$dayNum][0]) ? htmlspecialchars($scheduleByDay[$dayNum][0]['notes'] ?? '') : ''; ?>">
                                    </div>
                                    <input type="hidden" name="schedules[<?php echo $dayNum; ?>][day_of_week]" value="<?php echo $dayNum; ?>">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="setStandardHours()">
                                <i class="bi bi-clock"></i> Apply Standard Hours (9AM-5PM)
                            </button>
                            <div>
                                <a href="<?php echo SITE_URL; ?>drivers/dashboard/<?php echo $driver['id']; ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Schedule
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Summary -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Current Schedule Summary</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($schedule)): ?>
                    <div class="row">
                        <?php
                        $totalHours = 0;
                        foreach ($daysOfWeek as $dayNum => $dayName):
                            if (isset($scheduleByDay[$dayNum])):
                                $slot = $scheduleByDay[$dayNum][0];
                                $start = new DateTime($slot['start_time']);
                                $end = new DateTime($slot['end_time']);
                                $hours = $start->diff($end)->h + ($start->diff($end)->i / 60);
                                if ($slot['is_available']) {
                                    $totalHours += $hours;
                                }
                        ?>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="p-3 border rounded <?php echo $slot['is_available'] ? 'bg-success bg-opacity-10' : 'bg-secondary bg-opacity-10'; ?>">
                                <strong><?php echo $dayName; ?></strong><br>
                                <small>
                                    <?php echo $start->format('g:i A'); ?> - <?php echo $end->format('g:i A'); ?><br>
                                    <?php if (!$slot['is_available']): ?>
                                        <span class="badge bg-secondary">Unavailable</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo number_format($hours, 1); ?> hrs</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    <div class="alert alert-info mt-3">
                        <strong>Total Available Hours per Week:</strong> <?php echo number_format($totalHours, 1); ?> hours
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> No schedule configured. Set up the driver's availability schedule above.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDay(dayNum) {
    const checkbox = document.getElementById('day_enabled_' + dayNum);
    const content = document.getElementById('day_content_' + dayNum);
    
    if (checkbox.checked) {
        content.style.display = 'block';
    } else {
        content.style.display = 'none';
    }
}

function setStandardHours() {
    // Set 9 AM to 5 PM for weekdays (Monday-Friday)
    for (let day = 1; day <= 5; day++) {
        const checkbox = document.getElementById('day_enabled_' + day);
        const content = document.getElementById('day_content_' + day);
        const startTime = document.querySelector(`input[name="schedules[${day}][start_time]"]`);
        const endTime = document.querySelector(`input[name="schedules[${day}][end_time]"]`);
        const available = document.querySelector(`select[name="schedules[${day}][is_available]"]`);
        
        if (checkbox && content && startTime && endTime) {
            checkbox.checked = true;
            content.style.display = 'block';
            startTime.value = '09:00';
            endTime.value = '17:00';
            if (available) available.value = '1';
        }
    }
    
    // Disable weekends
    for (let day = 0; day <= 6; day += 6) {
        const checkbox = document.getElementById('day_enabled_' + day);
        const content = document.getElementById('day_content_' + day);
        
        if (checkbox && content) {
            checkbox.checked = false;
            content.style.display = 'none';
        }
    }
}
</script>
