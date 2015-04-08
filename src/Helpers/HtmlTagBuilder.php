<?php
namespace SDS\Dytomate\Helpers;

class HtmlTagBuilder
{
    public function build($tag, $content, array $attributes)
    {
        $html = "<{$tag} {$this->buildAttributeString($attributes)}";

        if ($this->isSelfClosing($tag)) {
            $html .= " />";
        } else {
            $html .= ">{$content}</{$tag}>";
        }

        return $html;
    }

    protected function isSelfClosing($tag)
    {
        return in_array($tag, [
            "area",
            "base",
            "br",
            "col",
            "command",
            "embed",
            "hr",
            "img",
            "input",
            "keygen",
            "link",
            "meta",
            "param",
            "source",
            "track",
            "wbr"
        ]);
    }

    protected function buildAttributeString(array $attributes)
    {
        $html = [];

        foreach ($attributes as $name => $value) {
            $value = htmlspecialchars($value, ENT_COMPAT | ENT_HTML5, "UTF-8", false);
            $html[] = "{$name}=\"{$value}\"";
        }

        return implode(" ", $html);
    }
}
