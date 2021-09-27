<?php

namespace RenokiCo\LaravelExporter;

abstract class GaugeMetric extends CounterMetric
{
    /**
     * The collector to store the metric.
     *
     * @var \Prometheus\Gauge
     */
    protected $collector;

    /**
     * Set the value for the gauge.
     *
     * @param  float  $value
     * @return mixed[]
     */
    public function set($value)
    {
        return $this->collector->set($value, $this->getLabels());
    }

    /**
     * Decrement by a specific count.
     *
     * @param  int|float  $count
     * @return mixed[]
     */
    public function decBy($count)
    {
        return $this->collector->decBy($count, $this->getLabels());
    }

    /**
     * Register the collector to the registry.
     *
     * @return \Prometheus\Collector
     */
    public function registerCollector()
    {
        return $this->registry->registerGauge(
            namespace: $this->namespace(),
            name: $this->name(),
            help: $this->help(),
            labels: $this->allowedLabels(),
        );
    }
}
