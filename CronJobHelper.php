<?php
/**
 * Description: Help to create a WP cronjob easily.
 * Version: 1.0.0
 * Author: Noah Bosman
 * Author URI: https://njbosman.com
 * Text Domain: CronJobHelper
 */


class CronJobHelper {

    /** @var string $hook A name for this cron. */
    public string $hook;

    /** @var int $interval How often to run this cron in seconds. */
    public int $interval;

    /** @var Closure|string|null $callback Optional. Anonymous function, function name or null to override with your own handle() method. */
    public mixed $callback;

    /** @var array $args Optional. An array of arguments to pass into the callback. */
    public mixed $args;

    /** @var string $recurrence How often the event should subsequently recur. See wp_get_schedules(). */
    public string $recurrence;

    private function __construct($hook, $interval, $callback = null, $args = []) {

        $this->hook = trim($hook);
        $this->interval = absint($interval);
        $this->callback = $callback;
        $this->args = $args;
        $this->setRecurrence();

        add_action('wp', [$this, 'scheduleEvent']);
        add_filter('cron_schedules', [$this, 'addSchedule']);
        add_action($this->hook, [$this, 'handle']);
    }

    public static function init($hook, $interval, $callback = null, $args = []): static {
        return new static($hook, $interval, $callback, $args);
    }

    public function handle(): void {
        if (is_callable($this->callback)) {
            call_user_func_array($this->callback, $this->args);
        }
    }

    public function scheduleEvent(): void {
        if (!wp_next_scheduled($this->hook, $this->args)) {
            wp_schedule_event(time(), $this->recurrence, $this->hook, $this->args);
        }
    }

    public function addSchedule($schedules) {

        if (in_array($this->recurrence, $this->defaultWpRecurrence())) {
            return $schedules;
        }

        $schedules[$this->recurrence] = [
            'interval' => $this->interval,
            'display' => __('Every ' . $this->interval . ' seconds'),
        ];

        return $schedules;
    }

    private function setRecurrence(): void {
        foreach ($this->defaultWpSchedules() as $recurrence => $schedule) {
            if ($this->interval == absint($schedule['interval'])) {
                $this->recurrence = $recurrence;

                return;
            }
        }

        $this->recurrence = 'every_' . absint($this->interval) . '_seconds';
    }

    private function defaultWpSchedules(): array {
        return array_filter(wp_get_schedules(), function ($schedule) {
            return in_array($schedule, $this->defaultWpRecurrence());
        }, ARRAY_FILTER_USE_KEY);
    }

    private function defaultWpRecurrence(): array {
        return ['hourly', 'twicedaily', 'daily'];
    }
}