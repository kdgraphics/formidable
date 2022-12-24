<?php 

namespace Inspire\Formidable\Twig;

use Inspire\Formidable\Formidable;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class TwigForms extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        return array(
            'formidable' => Formidable::$plugin,
        );
    }
}