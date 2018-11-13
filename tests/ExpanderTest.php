<?php

use PHPUnit\Framework\TestCase;
use Yay\Engine;

class ExpanderTest extends TestCase
{
    private function expand($code) {
        // gc_disable();
        $expansion = (new Engine)->expand($code);
        // gc_enable();

        return $expansion;
    }

    public function test_visibility_modifiers()
    {
        // $expanded = $this->expand("
        //     $(macro) {
        //         $(visibilityModifiers())
        //     } >> {
        //         $$(visibilityModifiers($(visibilityModifiers)))
        //     }

        //     public protected private static
        // ");

        // print_r($expanded);
        // exit;
    }
}
