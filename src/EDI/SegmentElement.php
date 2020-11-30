<?php


namespace EDI;


class SegmentElement
{
    /** @var int sequence number */
    public $n;

    /**
     * @var string|array
     */
    public $value;

    /**
     * @var \EDI\Segment
     */
    private $segment;

    private $attributes;

    public  $details;

    /** @var string */
    private $valueDescription;
    /**
     * @var string
     */
    private $valueComments;

    /**
     * Element constructor.
     *
     * @param \EDI\Segment $segment
     * @param int          $n
     * @param array|string $value
     * @param              $element
     */
    public function __construct(
        Segment $segment,
        int $n,
        $value,
        $element
    ){
        $this->segment = $segment;
        $this->n = $n;
        $this->value = $value;
        $this->details = $element['details'] ?? false;
        $this->attributes = $element['attributes'];
    }

    public function getId()
    {
        return $this->attributes['id']??false;
    }

    public function getName()
    {
        return $this->attributes['name']??false;
    }

    public function getType()
    {
        return $this->attributes['type']??false;
    }

    public function getMaxlength()
    {
        return $this->attributes['maxlength']??false;
    }

    public function getLength()
    {
        return $this->attributes['length']??false;
    }

    public function isRequired(): bool
    {
        return ($this->attributes['required']??false) === 'true';
    }

    public function getDescription()
    {
        return $this->attributes['desc']??false;
    }

    public function getValueDescription(): string
    {

        if($this->valueDescription === null){
            $this->loadValueData();
        }
        return $this->valueDescription;
    }

    public function getValueComments(): string
    {

        if($this->valueComments === null){
            $this->loadValueData();
        }
        return $this->valueComments;
    }

    /**
     * @return array|\EDI\SegmentElement[]
     */
    public function getElementList(): array
    {


        if(!$this->details){
            return [];
        }
        $value = $this->value;
        if(!is_array($value)){
            $value = [$value];
        }
        $list = [];
        foreach($this->details as $nD => $detail){

            if(!isset($value[$nD])){
                break;
            }
            $list[] = new SegmentElement($this->segment, $nD, $value[$nD], $detail);
//            $list[] = new SegmentElementDetail(
//                $this->segment->definition,
//                $value[$nD+1],
//                $detail
//
//            );
        }
        return $list;
    }

    public function getValueAsString()
    {
        if(is_array($this->value)){
            return implode(',',$this->value);
        }
        return $this->value;
    }

    public function loadValueData(): void
    {
        [
            $this->valueDescription,
            $this->valueComments
        ] = $this->segment->definition->getCodeValue($this->getId(), $this->value);

        if($this->valueDescription === null){
            $this->valueDescription = '';
        }

        if($this->valueComments === null){
            $this->valueComments = '';
        }

    }
}