<?php


namespace EDI;

//$idHeader .= ' http://www.unece.org/trade/untdid/' . strtolower($this->directory) . '/trsd/trsd' . strtolower($id) . '.htm';

class UnseceOrg
{
    private const REG_EXPR_SEGMENT_DETAILS = '#([X\*\#\|\-\+ ]) {2,7}([A-Z0-9]{1,3}) {1,4}(.+)[\s^]((?: {13}(?:.+)[\s^])+)#';

    private const BASE_URL = 'http://www.unece.org/fileadmin/DAM/trade/untdid/';
    /** @var string */
    private $directory;

    private $dirForPageCache;

    /**
     * UnseceOrg constructor.
     *
     * @param string $directory
     * @param string $dirForPageCache
     */
    public function __construct(string $directory, string $dirForPageCache = '')
    {
        $this->directory = strtolower($directory);
        $this->dirForPageCache = $dirForPageCache;
    }

    public function getMessageLink(string $messageId): string
    {
        return self::BASE_URL . $this->directory . '/trsd/trsd' . strtolower($messageId) . '.htm';
    }

    public function getElementLink(string $elementCode): string
    {
        if(preg_match('#^[0-9]+$#',$elementCode)) {
            return self::BASE_URL . $this->directory . '/uncl/uncl' . strtolower($elementCode) . '.htm';
        }
        return self::BASE_URL . $this->directory . '/trcd/trcd' . strtolower($elementCode) . '.htm';
    }

    public function loadElementValueList(string $elementCode): array
    {
        $url = $this->getElementLink($elementCode);
        if(!$content = $this->loadPage($url, 'element', $elementCode)){
            return [];
        }

        if(!preg_match_all(self::REG_EXPR_SEGMENT_DETAILS,$content,$match)){
            return [];
        }

        $list = [];
        foreach($match[0] as $k => $value)
        {
            $code = $match[2][$k];
            $data = new SegmentData();
            $data->indicator = $match[1][$k];
            $data->code = $code;
            $data->description = trim($match[3][$k]);
            if($comments = trim($match[4][$k])) {
                $data->comments = preg_replace('#\s+#',' ', $comments);
            }
            $list[$code] = $data;
        }
        return $list;

    }

    /**
     * @param string $elementCode
     *
     * @param string $elementValue
     *
     * @return \EDI\SegmentData|null
     */
    public function getElementValueData(string $elementCode, string $elementValue): ?SegmentData
    {
        $list = $this->loadElementValueList($elementCode);
        if(preg_match('#^\d+$#',$elementValue)){
            return $list[(int)$elementValue] ?? null;
        }

        return $list[$elementValue] ?? null;
    }

    private function loadPage(string $url, string $type, string $code)
    {
        $cachePath = $this->dirForPageCache . '/' . $type . '_' . $code. '.html';
        if(file_exists($cachePath)){
            return file_get_contents($cachePath);
        }
        $context = stream_context_create(
            array(
                "http" => array(
                    "follow_location" => false,
                ),
            )
        );
        $content = file_get_contents($url, false, $context);
        file_put_contents($cachePath,$content);
        return $content;
    }

}