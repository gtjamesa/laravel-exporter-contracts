<?php

namespace RenokiCo\LaravelExporter;

use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\RenderTextFormat;

class Exporter
{
    /**
     * The metrics to register.
     *
     * @var array
     */
    protected static array $metrics = [
        //
    ];

    /**
     * The registered metrics.
     *
     * @var array
     */
    protected static array $registeredMetrics = [
        //
    ];

    /**
     * The Collector Registry for this session.
     *
     * @var \Prometheus\CollectorRegistry
     */
    protected static CollectorRegistry $registry;

    /**
     * Set the registry.
     *
     * @param  \Prometheus\CollectorRegistry  $collectorRegistry
     * @return void
     */
    public static function setRegistry(CollectorRegistry $registry)
    {
        self::$registry = $registry;
    }

    /**
     * Add a metric to the registrable metrics.
     *
     * @param  string  $class
     * @return void
     */
    public static function register(string $class)
    {
        if (in_array($class, static::$metrics)) {
            return;
        }

        static::$metrics[] = $class;
    }

    /**
     * Set the metrics value.
     *
     * @param  array  $classes
     * @return void
     */
    public static function metrics(array $classes)
    {
        static::$metrics = [];

        foreach ($classes as $class) {
            /** @var string $class */
            static::register($class);
        }
    }

    /**
     * Add the registered metrics to the Prometheus registry.
     *
     * @return \Prometheus\CollectorRegistry
     */
    public static function run()
    {
        foreach (static::$metrics as $metricClass) {
            /** @var \RenokiCo\LaravelExporter\Metric $metric */
            $metric = new $metricClass(static::$registry);

            try {
                $metric->registerCollector();
            } catch (MetricsRegistrationException $e) {
                $metric = static::$registeredMetrics[$metricClass];
            }

            /** @var \RenokiCo\LaravelExporter\Metric $metric */
            $metric->update();

            static::$registeredMetrics[$metricClass] = $metric;
        }

        return static::$registry;
    }

    /**
     * Export the metrics as plaintext.
     *
     * @return string
     */
    public static function exportAsPlainText(): string
    {
        return (new RenderTextFormat)->render(
            static::run()->getMetricFamilySamples()
        );
    }
}