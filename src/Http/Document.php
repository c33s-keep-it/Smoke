<?php

namespace whm\Smoke\Http;

use Phly\Http\Uri;

/**
 * Document.
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class Document
{
    private $content;

    public function __construct($htmlContent)
    {
        $this->content = $htmlContent;
    }

    /**
     * @return Uri[]
     */
    public function getReferencedUris()
    {
        $pattern = '/(<img|<a|<link|script)(.*)(href|src)="(.*)"/iU';

        preg_match_all($pattern, $this->content, $matches);

        $uris = [];
        foreach ($matches[4] as $match) {
            if ($urlParts = parse_url($match)) {
                if (isset($urlParts['scheme']) && !in_array($urlParts['scheme'], ['http','https'], true)) {
                    continue;
                }

                $uris[] = new Uri($match);
            }
        }

        return $uris;
    }

    /**
     * @return Uri[]
     */
    public function getExternalDependencies($fileExtensions = ['css', 'js'])
    {
        if (!is_array($fileExtensions)) {
            return false;
        }
        $extensions = implode('|', $fileExtensions);
        $pattern = '/[^\'](?:<link|<script).*(?:href|src)=["\']([\S]+\.(?:' . $extensions . ')+[?\S]*)[\'"][^\']/iU';

        $matches = [];
        preg_match_all($pattern, $this->content, $matches);

        $files = [];

        foreach ($matches[1] as $file) {
            $files[] = new Uri($file);
        }

        return $files;
    }
}
