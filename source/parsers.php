<?php

namespace Yay {
    use Yay\Parser;

    function argument(): Parser {
        return \Pre\Plugin\Parsers\argument();
    }

    function arguments(): Parser {
        return \Pre\Plugin\Parsers\arguments();
    }

    function classConstant(): Parser {
        return \Pre\Plugin\Parsers\classConstant();
    }

    function classFunction(): Parser {
        return \Pre\Plugin\Parsers\classFunction();
    }

    function classProperty(): Parser {
        return \Pre\Plugin\Parsers\classProperty();
    }

    function classTrait(): Parser {
        return \Pre\Plugin\Parsers\classTrait();
    }

    function returnType(): Parser {
        return \Pre\Plugin\Parsers\returnType();
    }

    function type(): Parser {
        return \Pre\Plugin\Parsers\type();
    }

    function visibilityModifiers(): Parser {
        return \Pre\Plugin\Parsers\visibilityModifiers();
    }
}

namespace Pre\Plugin\Parsers {
    use Yay\Parser;
    use function Yay\buffer;
    use function Yay\chain;
    use function Yay\either;
    use function Yay\expression;
    use function Yay\layer;
    use function Yay\ls;
    use function Yay\ns;
    use function Yay\optional;
    use function Yay\repeat;
    use function Yay\token;

    function argument($prefix = null): Parser {
        $combinedPrefix = alias("argument", $prefix);

        return chain(
            optional(buffer("?"))->as(alias("argumentNullable", $prefix)),
            optional(type($combinedPrefix)),
            token(T_VARIABLE)->as(alias("argumentName", $prefix)),
            optional(buffer("=")),
            optional(buffer("new"))->as(alias("argumentNew", $prefix)),
            optional(expression())->as(alias("argumentValue", $prefix))
        )->as(alias("argument", $prefix));
    }

    function arguments($prefix = null): Parser {
        return ls(
            argument($prefix),
            buffer(",")
        )->as(alias("arguments", $prefix));
    }

    function classConstant($prefix = null): Parser {
        $combinedPrefix = alias("classConstant", $prefix);

        return chain(
            optional(visibilityModifiers($combinedPrefix)),
            buffer("const"),
            token(T_STRING)->as(alias("classConstantName", $prefix)),
            buffer("="),
            expression()->as(alias("classConstantValue", $prefix)),
            optional(buffer(";"))
        )->as(alias("classConstant", $prefix));
    }

    function classFunction($prefix = null): Parser {
        $combinedPrefix = alias("classFunction", $prefix);
    
        return chain(
            optional(visibilityModifiers($combinedPrefix)),
            buffer("function"),
            ns()->as(alias("classFunctionName", $prefix)),
            buffer("("),
            optional(arguments($combinedPrefix)),
            buffer(")"),
            optional(returnType($combinedPrefix)),
            buffer("{"),
            layer()->as(alias("classFunctionBody", $prefix)),
            buffer("}")
        )->as(alias("classFunction", $prefix));
    }

    function classProperty($prefix = null): Parser {
        $combinedPrefix = alias("classProperty", $prefix);

        return chain(
            visibilityModifiers($combinedPrefix),
            optional(type($combinedPrefix)),
            token(T_VARIABLE)->as(alias("classPropertyName", $prefix)),
            optional(buffer("=")),
            optional(expression())->as(alias("classPropertyValue", $prefix)),
            optional(buffer(";"))
        )->as(alias("classProperty", $prefix));
    }

    function classTrait($prefix = null): Parser {
        $combinedPrefix = alias("classTrait", $prefix);
        $combinedAliasPrefix = alias("classTraitAlias", $prefix);

        return chain(
            buffer("use"),
            ls(
                ns()->as(alias("classTraitName", $prefix)),
                buffer(",")
            )->as(alias("classTraitNames", $prefix)),
            either(
                chain(
                    buffer("{"),
                    repeat(
                        chain(
                            chain(
                                optional(chain(
                                    token(T_STRING)->as(alias("classTraitAliasLeftClass", $prefix)),
                                    buffer("::")
                                )),
                                token(T_STRING)->as(alias("classTraitAliasLeftMethod", $prefix))
                            )->as(alias("classTraitAliasLeft", $prefix)),
                            either(
                                buffer("insteadof")->as(alias("classTraitAliasInsteadOf", $prefix)),
                                chain(
                                    buffer("as"),
                                    optional(visibilityModifiers($combinedAliasPrefix))
                                )->as(alias("classTraitAliasAs", $prefix))
                            ),
                            chain(
                                optional(chain(
                                    token(T_STRING)->as(alias("classTraitAliasRightClass", $prefix)),
                                    buffer("::")
                                )),
                                token(T_STRING)->as(alias("classTraitAliasRightMethod", $prefix))
                            )->as(alias("classTraitAliasRight", $prefix)),
                            optional(buffer(";"))
                        )->as(alias("classTraitAlias", $prefix))
                    )->as(alias("classTraitAliases", $prefix)),
                    buffer("}")
                )->as(alias("classTraitBody", $prefix)),
                optional(buffer(";"))
            )
        )->as(alias("classTrait", $prefix));
    }

    function returnType($prefix = null): Parser {
        return chain(
            buffer(":"),
            optional(buffer("?"))->as(alias("returnTypeNullable", $prefix)),
            type()->as(alias("returnTypeName", $prefix))
        )->as(alias("returnType", $prefix));
    }

    function type($prefix = null): Parser {
        return either(
            ns(),
            buffer("array"),
            buffer("callable")
        )->as(alias("type", $prefix));
    }

    function visibilityModifiers($prefix = null): Parser {
        return repeat(
            either(
                buffer("public"),
                buffer("protected"),
                buffer("private"),
                buffer("static")
            )->as(alias("visibilityModifier", $prefix))
        )->as(alias("visibilityModifiers", $prefix));
    }

    function alias($name, $prefix = null): string {
        if ($prefix) {
            return $prefix . ucwords($name);
        }

        return $name;
    }
}
