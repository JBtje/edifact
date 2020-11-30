<?php


namespace EDI;


class SegmentElementDetail
{
    public $attribute;
    public $id;
    public $value;

    /**
     * @var \EDI\Definition
     */
    public $definition;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $maxlength;
    /**
     * @var bool
     */
    public $required;
    /**
     * @var string
     */
    public $length;

    public $desc;

    /**
     * SegmentElementDetail constructor.
     *
     * @param                 $value

     * @param \EDI\Definition $definiton
     */
    public function __construct(
        $value,
        array $detail,
        Definition $definiton
    )
    {
        $this->value = $value;
        $this->type = $detail['type']??false;
//        $this->attribute = $detail['attributes'];
        $this->id = $detail['attributes']['id'];
        $this->required = ($detail['attributes']['required']??'')==='true';
        $this->name = $detail['attributes']['name']??null;
        $this->maxlength = $detail['attributes']['maxlength']??false;
        $this->required = ($detail['attributes']['required']??false) === 'true;';
        $this->length = $detail['attributes']['length']??false;
        $this->desc = $detail['attributes']['desc']??false;
        $this->definition = $definiton;
    }

    public function getValueName()
    {
        [$value,$description] =  $this->definition->getCodeValue($this->id, $this->value);
        return $value;
    }

    public function getValueDescription()
    {
        [$value,$description] =  $this->definition->getCodeValue($this->id, $this->value);
        return $description;
    }

}