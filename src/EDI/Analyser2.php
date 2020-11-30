<?php

declare(strict_types=1);

namespace EDI;

use function implode;
use function is_array;
use function json_encode;
use function wordwrap;
use const PHP_EOL;

/**
 * EDIFACT Messages Parser
 * (c)2016 Uldis Nelsons
 */
class Analyser2
{


    /**
     * @var string
     */
    public $directory;

    /**
     * @var array<mixed>
     */
    private $jsonedi;

    /**
     * @var \EDI\Definition
     */
    public $definiton;

    public function __construct(string $directory, string $docCacheDir)
    {
        $this->directory = $directory;
        $this->definiton = new Definition($directory,$docCacheDir);
    }

    /**
     * create readable EDI MESSAGE with comments
     *
     * @param array      $data        by EDI\Parser:parse() created array from plain EDI message
     * @param array|null $rawSegments (optional) List of raw segments from EDI\Parser::getRawSegments
     *
     * @return string file
     */
    public function process(array $data, array $rawSegments = null): string
    {
        $r = [];
        $sgmnt = new Segment($this->definiton);
        foreach ($data as $nrow => $segment) {
            $sgmnt->load($segment,$rawSegments[$nrow+1]);
            $id = $segment[0];


            $r[] = '';
            $jsonsegment = [];
            $r[] = $sgmnt->rawSegment;
            $r[] = '---------------';
            foreach ($sgmnt->getTextLines() as $tl){
                $r[] = $tl;
            }
            $r[] = '---------------';
            if($sgmnt->getSegmentName()){

                /**  SEGMENT HEADER */
                $idHeader = $sgmnt->getSegmentTag() . ' - ' . $sgmnt->getSegmentName();
                if($docLink = $sgmnt->getSegmentDocLink()) {
                    $idHeader .= PHP_EOL . '      ' . $docLink
                         . PHP_EOL . '      ' . wordwrap($sgmnt->getSegmentDescription(), 75, PHP_EOL . '      ');
                }
                $r[] = $idHeader;

                $jsonelements = ['segmentCode' => $id];
                /** SEGMENT ELEMENTS */
                foreach ($sgmnt->getElementList() as $element) {

                    /**
                     * creade element header
                     */
                    $l = '  [' . $element->n . '] ' . $element->getValueAsString();

                    if($codeDescription = $element->getValueDescription()) {
                        $l .= ' - ' . $codeDescription;
                    }
                    if($codeComments = $element->getValueComments()) {
                        $l .= PHP_EOL . '           ' . $codeComments;
                    }
                    $r[] = $l;
                    if(!$elementId = $element->getId()) {
                        $r[] = '   no found documentation';
                        continue;
                    }
                    $r[] = '      ' . $elementId . ' - ' . $element->getName();
                    $r[] = '      ' . wordwrap($element->getDescription(), 71, PHP_EOL . '      ');

                    $elementDetailList = $element->getElementList();

                    if (!is_array($elementDetailList)) {
                        $jsonelements[$element->getName()] = $element->getValueAsString();
                        continue;
                    }

                    $jsoncomposite = [];

                    foreach ($elementDetailList as $elementDetail) {
                        $line = '    [' . $element->n .'-'.$elementDetail->n . '] ' . $elementDetail->getValueAsString();

                        if($valueDescription = $elementDetail->getValueDescription()) {
                            $line .= ' - ' . $valueDescription;
                        }
                        if($valueComments = $elementDetail->getValueComments()) {
                            $line .= PHP_EOL . '           ' . $valueComments;
                        }

                        $r[] = $line;
                        $r[] = '        id: ' . $elementDetail->getId() . ' - ' . $elementDetail->getName();
                        $r[] = '        ' . wordwrap($elementDetail->getDescription(), 69, PHP_EOL . '        ');
                        if($type = $elementDetail->getType()) {
                            $r[] = '        type: ' . $type;
                        }

                        $jsoncomposite[$elementDetail->getName()] = $elementDetail->value;
                        if ($maxLength = $elementDetail->getMaxlength()) {
                            $r[] = '        maxlen: ' . $maxLength;
                        }
                        if ($elementDetail->isRequired()) {
                            $r[] = '        required';
                        }
                        if ($length = $elementDetail->getLength()) {
                            $r[] = '        length: ' . $length;
                        }

                    }

                    $jsonelements[$element->getName()] = $jsoncomposite;

                }
                $jsonsegment[$sgmnt->getSegmentName()] = $jsonelements;
            } else {
                $r[] = $id;
                $jsonsegment['UnrecognisedType'] = $segment;
            }
            $this->jsonedi[] = $jsonsegment;
        }

        return implode(PHP_EOL, $r);
    }

    /**
     * return the processed EDI in json format
     *
     * @return false|string
     */
    public function getJson()
    {
        return json_encode($this->jsonedi);
    }


}
