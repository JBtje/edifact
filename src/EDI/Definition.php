<?php


namespace EDI;


use EDI\Mapping\MappingProvider;

class Definition
{
    /**
     * @var array
     */
    private $codes;
    /**
     * @var array|false
     */
    private $segments;
    /**
     * @var \EDI\UnseceOrg
     */
    private $useceOrg;

    /**
     * Definition constructor.
     *
     * @param string $directory
     * @param string $cacheDir
     */
    public function __construct(string $directory, string $cacheDir = '')
    {
        if($cacheDir) {
            $this->useceOrg = new UnseceOrg($directory, $cacheDir);
        }
        $mapping = new MappingProvider($directory);
        $this->codes = $mapping->loadCodesXml();
        $this->segments = $mapping->loadSegmentsXml();
    }

    public function existSegment(string $segmentCode): bool
    {
        return isset($this->segments[$segmentCode]);
    }

    public function getSegmentDocLink(string $segmentCode): string
    {
        return $this->useceOrg->getMessageLink($segmentCode);
    }

    public function getSegmentAttributes(string $segmentCode)
    {
        return $this->segments[$segmentCode]['attributes'];
    }

    public function getSegmentDetails(string $segmentCode)
    {
        return $this->segments[$segmentCode]['details'];
    }

    public function getElementId(int $n)
    {
        return $this->segments[$n]['attributes']['id']??false;
    }

    public function getElementDetailDesc(string $segmentCode, int $n)
    {
        return $this->segments[$segmentCode]['details'][$n]??false;
    }

    public function getCodeValue($elementId, $code): array
    {
        if(is_array($code)){
            return ['',''];
        }
        if($this->useceOrg && $value = $this->useceOrg->getElementValueData($elementId,$code)){
            return [$value->description,$value->comments];
        }

        if(preg_match('#^\d+$#',$code)){
            if($value =  $this->codes[(int)$elementId][(int)$code]??''){
                return [$value,''];
            }

        }

        if($value =  $this->codes[(int)$elementId][$code]??''){
            return [$value,''];
        }

        return ['',''];
    }


}