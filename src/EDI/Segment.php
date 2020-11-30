<?php


namespace EDI;


class Segment
{
    /**
     * @var array
     */
    public $spliced;

    /** @var string */
    public $rawSegment;

    /**
     * @var \EDI\Definition
     */
    public $definition;

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * Segment constructor.
     *
     * @param \EDI\Definition $definition
     */
    public function __construct(Definition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * Segment constructor.
     *
     * @param array $spliced
     * @param string rawSegment
     */
    public function load(array $spliced, string $rawSegment): void
    {
        $this->spliced = $spliced;
        $this->rawSegment = trim($rawSegment);
        $attributes = $this->definition->getSegmentAttributes($spliced[0]);
        $this->id = $attributes['id']??'';
        $this->name = $attributes['name']??'';
        $this->description = $attributes['desc']??'';
    }

    public function getSegmentTag()
    {
        return $this->id;
    }

    public function getSegmentName(): string
    {
        return $this->name;
    }

    public function getSegmentDescription(): string
    {
        return $this->description;
    }

    public function getSegmentDocLink(): string
    {
        return $this->definition->getSegmentDocLink($this->getSegmentTag());
    }

    /**
     * @return SegmentElement[]
     */
    public function getElementList(): array
    {
        $outList = [];
        foreach($this->spliced as $n => $value){
            if($n === 0){
                continue;
            }
            $elementSqn = $n - 1;
            $element = $this->definition->getElementDetailDesc($this->id, $elementSqn);
            $outList[$n] = new SegmentElement($this, $elementSqn, $value, $element);
        }
        return $outList;
    }

    public function getTextLines(): array
    {
        $list = [];
        foreach($this->getElementList() as $element){
            if(!$element->getValueAsString()){
                continue;
            }
            $elementDetailList = $element->getElementList();
            if (!$elementDetailList) {
                $list[] =  self::createLine($element);
                continue;
            }
            foreach($element->getElementList() as $element2){
                if(!$element2->getValueAsString()){
                    continue;
                }
                $list[] =  self::createLine($element2);
            }
        }
        return $list;
    }

    /**
     * @param \EDI\SegmentElement $element
     *
     * @return array|string
     */
    private static function createLine(SegmentElement $element)
    {
        $line = $element->getValueAsString();
        if ($valueDescription = $element->getValueDescription()) {
            $line .= ' - ' . $valueDescription;
        } elseif ($description = $element->getDescription()) {
            $line .= ' - ' . $description;
        }

        return $line;
    }

}
