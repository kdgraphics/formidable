<?php 

namespace Inspire\Forms\Twig;

use Inspire\Forms\Forms;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class TwigForms extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals()
    {
        return array(
            'inspireforms' => Forms::$plugin,
        );
    }
}