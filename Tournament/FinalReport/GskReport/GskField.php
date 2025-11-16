<?php
error_reporting(E_ALL);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

abstract class GskField
{
    private const fieldPrefix = "field_";
    private const module = "GSK-Report";

    abstract public function getParameterName(): string;
    abstract public function getDefaultValue(): string;

    public function getValue() {
        return htmlspecialchars(getModuleParameter(self::module, self::fieldPrefix . $this->getParameterName(), $this->getDefaultValue()));
    }

    public function setValue($newValue): void
    {
        setModuleParameter(self::module, self::fieldPrefix . $this->getParameterName(), $newValue);
    }
}