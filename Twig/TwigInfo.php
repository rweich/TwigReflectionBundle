<?php

namespace Alb\TwigReflectionBundle\Twig;

use TRex\Reflection\CallableReflection;

/**
 * Class TwigInfo
 * @package Alb\TwigReflectionBundle\Twig
 */
class TwigInfo
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var object
     */
    protected $function;

    /**
     * @var \Twig_ExtensionInterface|null
     */
    protected $extension;

    /**
     * TwigInfo constructor.
     *
     * @param string                        $name
     * @param object                        $function
     * @param \Twig_ExtensionInterface|null $extension
     */
    public function __construct($name, $function, \Twig_ExtensionInterface $extension = null)
    {
        $this->name      = $name;
        $this->function  = $function;
        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Twig_ExtensionInterface
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return $this->extension ? $this->extension->getName() : '';
    }

    /**
     * @return string
     */
    public function getParametersSignatureString()
    {
        if (!method_exists($this->function, 'getCallable')) {
            return '';
        }
        if (($method = $this->getReflection($this->function->getCallable()))) {
            $hasContext = method_exists($this->function, 'needsContext') && $this->function->needsContext();

            return $this->getParametersSignatureFromReflection(
                $method,
                $hasContext,
                $this->isFilterObject($this->function)
            );
        }

        return '';
    }

    /**
     * @param \ReflectionFunctionAbstract $function
     * @param bool                        $hasContext
     * @param bool                        $isFilter
     *
     * @return string
     */
    protected function getParametersSignatureFromReflection(
        \ReflectionFunctionAbstract $function,
        $hasContext,
        $isFilter
    )
    {
        $params = $function->getParameters();
        if ($isFilter) {
            $params = array_slice($params, 1);
        }
        $string = '';
        foreach ($params as $param) {
            if ($hasContext && $param->getName() === 'context') {
                continue;
            }
            if ($param->getClass() && $param->getClass()->getName() === 'Twig_Environment') {
                continue;
            }

            if ('' !== $string) {
                $string .= ', ';
            }
            if ($param->isPassedByReference()) {
                $string .= '&';
            }
            $string .= '$' . $param->getName();
            if ($param->isDefaultValueAvailable()) {
                $default       = $param->getDefaultValue();
                $defaultString = var_export($default, true);
                $defaultString = str_replace("\n", '', $defaultString);
                $string .= ' = ' . $defaultString;
            }
        }

        return $string;
    }

    /**
     * @param object $class
     *
     * @return bool
     */
    protected function isFilterObject($class)
    {
        return $class instanceof \Twig_SimpleFilter
        || $class instanceof \Twig_Filter_Function
        || $class instanceof \Twig_Filter_Method;
    }

    /**
     * @param callable $callable
     *
     * @return \ReflectionFunctionAbstract|null
     */
    protected function getReflection($callable)
    {
        if (!is_callable($callable)) {
            return null;
        }
        try {
            $reflection = new CallableReflection($callable);

            return $reflection->getReflector();
        } catch (\Exception $e) {
            return null;
        }
    }
}
