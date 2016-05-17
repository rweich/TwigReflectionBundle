<?php

/*
 * This file is part of AlbTwigReflectionBundle
 *
 * (c) 2012 Arnaud Le Blanc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alb\TwigReflectionBundle\Twig;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
abstract class FunctionListAbstract
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * FunctionListAbstract constructor.
     *
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param OutputInterface $output
     */
    public function dumpList(OutputInterface $output)
    {
        $functions = $this->getFunctions();
        ksort($functions);

        $table = new Table($output);
        $table->setHeaders(['Function', 'Extension', 'Class']);

        foreach ($functions as $function) {
            $params = $function->getParametersSignatureString();
            $table->addRow(
                [
                    '<comment>' . $function->getName() . '</comment>' . (strlen($params) ? " ( {$params} ) " : ''),
                    $function->getExtensionName(),
                    get_class($function->getExtension()),
                ]
            );
        }

        $table->render();
    }

    /**
     * @return TwigInfo[]
     */
    abstract protected function getFunctions();
}
