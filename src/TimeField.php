<?php

namespace Laraning\NovaTimeField;

use Carbon\Carbon;
use DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class TimeField extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-time-field';

    /**
     * @var string
     */
    protected $inputFormat;

    /**
     * @var string
     */
    protected $outputFormat;

    /**
     * Create a new field.
     *
     * @param string      $name
     * @param string|null $attribute
     * @param mixed|null  $resolveCallback
     *
     * @return void
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        $this->getFormatsFromConfig();

        parent::__construct($name, $attribute, $resolveCallback ?? function ($value) {
            return Carbon::createFromFormat($this->outputFormat, $value)->format($this->inputFormat);
        });

        $this->withMeta(['format' => $this->inputFormat]);
    }

    /**
     * Indicate that the date field is nullable.
     *
     * @return $this
     */
    public function nullable($nullable = true, $value = null)
    {
        return $this->withMeta(['nullable' => true]);
    }

    public function withTwelveHourTime()
    {
        return $this->withMeta(['twelveHourTime' => true]);
    }

    protected function getFormatsFromConfig()
    {
        $this->inputFormat = config('nova-time-field.input-format');
        $this->outputFormat = config('nova-time-field.output-format');
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param string                                  $requestAttribute
     * @param object                                  $model
     * @param string                                  $attribute
     *
     * @return void
     */
    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ) {
        if ($request->exists($requestAttribute) && $request[$requestAttribute]) {
            $sentData = $request[$requestAttribute];

            if (DateTime::createFromFormat($this->inputFormat, $sentData) === false) {
                throw new Exception('The field must contain a valid time.');
            }

            $newDate = Carbon::createFromFormat($this->inputFormat, $request[$requestAttribute])->format($this->inputFormat);
            $model->{$attribute} = $newDate;
        }
    }
}
