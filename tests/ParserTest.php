<?php

use PHPUnit\Framework\TestCase;

use function Pre\Plugin\format;
use function Pre\Plugin\parse;

class ParserTest extends TestCase
{
    private function fixture($name)
    {
        return file_get_contents(__DIR__ . "/fixtures/{$name}.yay");
    }

    private function parse($code)
    {
        return trim(format(parse($code)));
    }

    private function eval($code)
    {
        return eval(preg_replace("/^\<\?php/", "", $code));
    }

    public function test_class_constant()
    {
        $actual = $this->eval($this->parse($this->fixture("parser/class-constant")));
        
        $expected = [
            ["bar", "baz"],
            ["private", "foo", "bar"]
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_class_trait()
    {
        $actual = $this->eval($this->parse($this->fixture("parser/class-trait")));
        
        $expected = [
            ["\Foo", "no body"],
            ["Foo", "Bar", "Foo\Bar\Baz", "no body"],
            ["Foo", "bar", "as", "baz"],
            ["Foo", "bar", "as", "baz"],
            ["Foo", "bar", "as", "protected", "baz"],
            ["Foo", "Bar", "Bar::baz", "insteadof", "Foo::baz"],
            ["Foo", "Bar", "Bar::baz", "insteadof", "Foo::baz", "Foo::bar", "as", "public", "boo"]
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_class_property()
    {
        $actual = $this->eval($this->parse($this->fixture("parser/class-property")));
        
        $expected = [
            ["private", "\$foo"],
            ["private", "\$foo", "\"bar\""],
            ["private", "string", "\$foo"],
            ["private", "\$foo", "bar()"],
            ["private", "\$foo", "new Bar()"]
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_class_function()
    {
        $actual = $this->eval($this->parse($this->fixture("parser/class-function")));
        
        $expected = [
            ["no modifiers", "function", "greet", "no arguments", "print \"hello\"; "],
            ["public", "static", "function", "greet", "string\$name=get()", ":?string", "print \"hello {\$name}\"; "]
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_visibility_modifiers()
    {
        $actual = $this->eval($this->parse($this->fixture("parser/visibility-modifiers")));
        
        $expected = [
            ["public"],
            ["protected"],
            ["private"],
            ["static"],
            ["public", "static"]
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_type()
    {
        $actual = $this->eval($this->parse($this->fixture("parser/type")));
        
        $expected = [
            ["string"],
            ["int"],
            ["integer"],
            ["bool"],
            ["boolean"],
            ["float"],
            ["mixed"],
            ["array"],
            ["callable"],
            ["resource"],
            ["stdClass"],
            ["\stdClass"],
            ["\\Foo\\Bar\\Baz"]
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_argument()
    {
        $actual = $this->eval($this->parse($this->fixture("parser/argument")));
        
        $expected = [
            ["\$foo"],
            ["nullable", "string", "\$foo"],
            ["nullable", "string", "\$foo", "equals", "\"bar\""],
            ["nullable", "stdClass", "\$foo", "equals", "makeObject()"],
            ["nullable", "stdClass", "\$foo", "equals", "new", "stdClass()"],
            ["nullable", "stdClass", "\$foo", "equals", "new", "stdClass(1,\"two\",3)"]
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_arguments()
    {
        $actual = $this->eval($this->parse($this->fixture("parser/arguments")));
        
        // this formatting looks strange, but it means
        // the individual arguments are being captured
        // by the functionArgument() parser
        $expected = [
            ["string\$foo", "stdClass\$bar=makeObject()", "\$baz=newstdClass()"]
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_return_type()
    {
        $actual = $this->eval($this->parse($this->fixture("parser/return-type")));
        
        $expected = [
            ["return", "string"],
            ["return", "nullable", "string"]
        ];

        $this->assertEquals($expected, $actual);
    }
}
