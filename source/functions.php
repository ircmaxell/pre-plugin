<?php

namespace Pre\Plugin;

function find($file, $iterations = 10, $prefix = __DIR__)
{
    $folder = "../";

    if ($prefix) {
        $folder = "{$prefix}/{$folder}";
    }

    for ($i = 0; $i < $iterations; $i++) {
        $try = "{$folder}{$file}";

        if (file_exists($try)) {
            return realpath($try);
        }

        $folder .= "../";
    }
}

function base()
{
    $vendor = find("vendor");
    return realpath("{$vendor}/../");
}

function instance()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Parser();
    }

    return $instance;
}

function process($from)
{
    $instance = instance();
    return $instance->process($from);
}

function compile($from, $to, $format = true, $comment = true)
{
    $instance = instance();
    return $instance->compile($from, $to, $format, $comment);
}

function parse($code)
{
    $instance = instance();
    return $instance->parse($code);
}

function format($code)
{
    $instance = instance();
    return $instance->format($code);
}

function addMacro($macro)
{
    $instance = instance();
    return $instance->addMacro($macro);
}

function removeMacro($macro)
{
    $instance = instance();
    return $instance->removeMacro($macro);
}

function addCompiler($compiler, $callable)
{
    $instance = instance();
    return $instance->addCompiler($compiler, $callable);
}

function removeCompiler($compiler)
{
    $instance = instance();
    return $instance->removeCompiler($compiler);
}
