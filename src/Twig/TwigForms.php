<?php 

namespace Ryssbowh\Formidable\Twig;

use Ryssbowh\Formidable\Formidable;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class TwigForms extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals()
    {
        return array(
            'formidable' => Forms::$plugin,
        );
    }
}