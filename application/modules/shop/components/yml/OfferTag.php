<?php
namespace app\modules\shop\components\yml;

class OfferTag
{
    const TAG_OFFER = 'offer';

    private $tag = '';
    private $attributes = [];
    private $value = '';

    /**
     * Offer constructor.
     * @param string $tag
     * @param array $attributes
     * @param string $value
     */
    public function __construct($tag, $value, array $attributes = [])
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return string
     */
    function __toString()
    {
        $attr = true === empty($this->attributes)
            ? ''
            : ' ' . implode(' ', array_map(function($k,$v) {
                return false === empty($v) ? "{$k}=\"{$v}\"" : null;
            }, array_keys($this->attributes), $this->attributes));

        $result = "<{$this->tag}{$attr}>";

        if (true === is_array($this->value)) {
            $result .= PHP_EOL;
            foreach ($this->value as $sub) {
                $result .= $sub . PHP_EOL;
            }
        } else {
            $result .= $this->value;
        }

        $result .= "</{$this->tag}>";
        if (self::TAG_OFFER === $this->tag) {
            $result .= PHP_EOL;
        } else {
            $result = "\t" . $result;
        }

        return $result;
    }
}
